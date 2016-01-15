<?php
namespace RocketSeller\TwoPickBundle\Controller;


use RocketSeller\TwoPickBundle\Entity\Employer;
use FOS\RestBundle\Controller\FOSRestController;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use RocketSeller\TwoPickBundle\Entity\Person;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\Workplace;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use DateTime;

//use GuzzleHttp\Psr7\Request;
//use Guzzle\Http\Client;
use GuzzleHttp\Client;

use EightPoints\Bundle\GuzzleBundle;
/**
* Contains all the web services to call the payroll system.
* Get methods can be call as any function.
* If a post method is going to be call from within the application here is an
* example:
*   $request =  new Request();
*   $request->request->set("employee_id", "123456");
*   $request->request->set("concept_id", "1");
*   $this->postFunctionAction($request);
*
*/
class PayrollRestController extends FOSRestController
{

  public function validateParamters($parameters, $regex, $mandatory) {

     foreach($mandatory as $key => $value)
     {
       if(array_key_exists($key, $mandatory) &&
          $mandatory[$key] &&
          (!array_key_exists($key, $parameters)))
            throw new HttpException(400, "The parameter " . $key . " is empty");

       if(array_key_exists($key, $regex) &&
          array_key_exists($key, $parameters) &&
          !preg_match('/^' . $regex[$key] . '$/', $parameters[$key]))
         throw new HttpException(400, "The format of the parameter " .
                                      $key . " is invalid, it doesn't match" .
                                      $regex[$key]);

       if(!$mandatory[$key] && (!array_key_exists($key, $parameters)))
          $parameters[$key] = '';
     }
  }

  public function getContentRecursive($array, &$result, &$errorCode)
  {
    $cuenta = array();
    if (!is_array($array))
      return;
    foreach($array as $key => $val)
    {
        if($key == "ERRORQ")
        {
            $errorCode = $val;
        }
        if($key == 'UNICO')
        {
          $temp = array();
          $count = 0;

          foreach($val as $i => $j)
          {
            $content = '';
            if($i == 'END_REG')continue;
            if(!is_array($j))
            {
              $temp[$i] =(String)$j;
            } else
            {
              // In case is an empty array which means that is an empty text,
              // We just add it as normal but empty.
              if(empty($j))
              {
                $temp[$i] = '';
                continue;
              }
              foreach($j as $index => $text)
              {
                if(!(count($temp) > $index))
                {
                  $temp[] = array();
                }
                if(is_array($text))
                  $temp[$index][$i] = '';
                else
                  $temp[$index][$i] = $text;
              }
            }
          }
          $result[] = $temp;
        }else
        {
            $this->getContentRecursive($val, $result, $errorCode);
        }
    }
  }

  /**
   * Calls the payments api, it receives the headers and the parameters and
   * makes a call using an absolute path, and returns a view with the Json or
   * XML response.
   * @param Array with the header options $headers
   * @param Array with parameter options $parameters
   * @param String with the relative path $path
   * @param Type of action(delete, get, post) default: post $action
   * @param Maximum number of seconds before giving up $timeout
   * @return View with the json response from the payments server.
   */
  public function callApi($parameters, $timeout=10)
  {

    $client = new Client();
    // TODO(daniel.serrano): Make the user and password into variables.
    // URL used for test porpouses, the line below should be used in production.
     $url_request = "http://SRHADMIN:SRHADMIN@52.3.249.135:9090/WS_Xchange/Kic_Adm_Ice.Pic_Proc_Int_SW_Publ";
    //TODO(daniel.serrano): Remove the mock URL.
    // This URL is only for testing porpouses and should be removed.

    $response = null;
    $options = array(
                  'form_params' => $parameters,
                  'timeout'     => $timeout,
                );
    $test = http_build_query($parameters);

    str_replace( "%20", "", $test );
    $test = trim(preg_replace('/\s\s+/', '', $test));
    $response = $client->request('GET', $url_request . '?' . str_replace( "%20", "",urldecode($test)));//, ['query' => urldecode($test)]);

    // We parse the xml recieved into an xml object, that we will transform.
    $plain_text = (String)$response->getBody();

    // This two lines is to remove extra text from the respose that breaks the
    // php parser.
    $plain_text = preg_replace('/(\<LogProceso\>((\n)|.)*(\<ERRORQ\>))/', "<LogProceso><ERRORQ>", $plain_text);
    $plain_text = preg_replace('/(\<MensajeRetorno>(?!\<)((\n)|.)*(\<ERRORQ\>))/', "<MensajeRetorno><ERRORQ>", $plain_text);

    // TODO(daniel.serrano): Remove this debug lines.
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string(utf8_encode($plain_text), "SimpleXMLElement", LIBXML_NOCDATA);
    if ($xml === false) {
        echo "Failed loading XML\n";
        foreach(libxml_get_errors() as $error) {
            echo "\t", $error->message;
        }
    }

    $json = json_encode($xml);
    $array = json_decode($json, TRUE);

    $result = array();
    $errorCode = 201;
    $this->getContentRecursive($array, $result,$errorCode);
    if(count($result)>0)
      $result = $result[0];

    $view = View::create();

    if($errorCode == 505 || $errorCode == 605)$errorCode = 404;
    $view->setStatusCode($errorCode);

    $view->setData($result);
    return $view;
  }

  /**
   * It creates the XML request based on an asociative array. The xml generated
   * will only work for the SQL requests, since we are using their tag names
   * and format.
   * @return Array with the header options.
   */
  private function createXml($content, $idInterfaz, $tipo=1)
  {
    $header = array();
    $answer = "<Interfaz" . $idInterfaz . "Solic>";
    foreach($content as $i)
    {
      // 1 is to insert delete or update 2 is to do a get operation.
      if($tipo == 1)
        $answer .= "<UNICO>";
      else if($tipo == 2)
        $answer .= "<Params>";
      foreach($i as $key => $value)
      {
         $answer .= "<" . $key . ">";
         $answer .= $value;
         $answer .= "</" . $key . ">";
      }
      if($tipo == 1)
        $answer .= "</UNICO>";
      else if($tipo == 2)
        $answer .= "</Params>";
    }
    $answer .= "</Interfaz" . $idInterfaz . "Solic>";
    return $answer;
  }

  /**
   * Insert employee personal information. The id should be created in our side
   * and we should keep track of it for further actions.<br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Insert employee personal information. The id should be
   *                  created in our side and we should keep track of it for
   *                  further actions.",
   *   statusCodes = {
   *     200 = "OK",
   *     400 = "Bad Request",
   *     401 = "Unauthorized",
   *     404 = "Not Found"
   *   }
   * )
   *
   * @param Request $request.
   * Rest Parameters:
   *
   *   (name="employee_id", nullable=false, requirements="([0-9])+", strict=true, description="Employee id, must be provided by us, and must be unique. It can't be the CC.")
   *   (name="last_name", nullable=false, requirements="([a-z|A-Z| ])+", strict=true, description="Employee Last name(only one).")
   *   (name="first_name", nullable=false, requirements="([a-z|A-Z| ])+", strict=true, description="Employee first name.")
   *   (name="document_type", nullable=true, requirements="([a-z|A-Z| ])+", description="Document type on two char format, if null CC will be used.")
   *   (name="document", nullable=false, requirements="([0-9])+", strict=true, description="Employee document number")
   *   (name="gender", nullable=false, requirements="(MAS|FEM)", strict=true, description="Employee gender(MAS or FEM).")
   *   (name="birth_date", nullable=false, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Employee birth day on the format DD-MM-YYYY.")
   *   (name="start_date", nullable=false, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Day the employee started working on the comopany(format: DD-MM-YYYY).")
   *   (name="days_company_seniority", nullable=false, requirements="([0-9])+", strict=true, description="Previous seniority on days.")
   *   (name="last_contract_start_date", nullable=false, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Start day of the last work contract(format: DD-MM-YYYY).")
   *   (name="contract_number", nullable=false, requirements="([0-9])+", strict=true, description="Employee contract number.")
   *   (name="last_contract_end_date", nullable=false, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Last work contract termination day(format: DD-MM-YYYY).")
   *   (name="worked_hours_days", nullable=false, requirements="([0-9])+", strict=true, description="Number of hours worked on a day.")
   *   (name="payment_method", nullable=false, requirements="(CHE|CON|EFE)", strict=true, description="Code of payment method(CHE, CON, EFE).")
   *   (name="liquidation_type", nullable=false, requirements="(J|M|Q)", strict=true, description="Liquidation type, (J daily, M monthly, Q every two weeks).")
   *   (name="salary_type", nullable=false, requirements="([0-9])", strict=true, description="How the employees salary is recorded(monthly 1, daily 2, every two weeks 3, hourly 4).")
   *
   * @return View
   */
  public function postAddEmployeeAction(Request $request)
  {

    $parameters = $request->request->all();
    $regex = array();
    $mandatory = array();
    // Set all the parameters info.
    $regex['employee_id'] = '([0-9])+';$mandatory['employee_id'] = true;
    $regex['last_name'] = '([a-z|A-Z| ])+';$mandatory['last_name'] = true;
    $regex['first_name'] = '([a-z|A-Z| ])+';$mandatory['first_name'] = true;
    $regex['document_type'] = '([a-z|A-Z| ])+';$mandatory['document_type'] = true;
    $regex['document'] = '([0-9])+';$mandatory['document'] = true;
    $regex['gender'] = '(MAS|FEM)';$mandatory['gender'] = true;
    $regex['birth_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['birth_date'] = true;
    $regex['start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['start_date'] = true;
    $regex['days_company_seniority'] = '([0-9])+';$mandatory['days_company_seniority'] = true;
    $regex['last_contract_start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['last_contract_start_date'] = true;
    $regex['contract_number'] = '([0-9])+';$mandatory['contract_number'] = true;
    $regex['last_contract_end_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['last_contract_end_date'] = true;
    $regex['worked_hours_days'] = '([0-9])+';$mandatory['worked_hours_days'] = true;
    $regex['payment_method'] = '(CHE|CON|EFE)';$mandatory['payment_method'] = true;
    $regex['liquidation_type'] = '(J|M|Q)';$mandatory['liquidation_type'] = true;
    $regex['salary_type'] = '([0-9])';$mandatory['salary_type'] = true;

    $this->validateParamters($parameters, $regex, $mandatory);

    $content = array();
    $unico = array();

    $unico['TIPOCON'] = 0;
    $unico['EMP_CODIGO'] = $parameters['employee_id'];
    $unico['EMP_APELLIDO1'] = $parameters['last_name'];
    $unico['EMP_NOMBRE'] = $parameters['first_name'];
    $unico['EMP_TIPO_IDENTIF'] = $parameters['document_type'];
    $unico['EMP_CEDULA'] = $parameters['document'];
    $unico['EMP_SEXO'] = $parameters['gender'];
    $unico['EMP_FECHA_NACI'] = $parameters['birth_date'];
    $unico['EMP_FECHA_INGRESO'] = $parameters['start_date'];
    $unico['EMP_ANTIGUEDAD_ANT'] = $parameters['days_company_seniority'];
    $unico['EMP_FECHA_INI_CONTRATO'] = $parameters['last_contract_start_date'];
    $unico['EMP_NRO_CONTRATO'] = $parameters['contract_number'];
    $unico['EMP_FECHA_FIN_CONTRATO'] = $parameters['last_contract_end_date'];
    $unico['EMP_JORNADA'] = $parameters['shift'];
    $unico['EMP_HORAS_TRAB'] = $parameters['worked_hours_days'];
    $unico['EMP_FORMA_PAGO'] = $parameters['payment_method'];
    $unico['EMP_TIPOLIQ'] = $parameters['liquidation_type'];
    $unico['EMP_TIPO_SALARIO'] = $parameters['salary_type'];

    $content[] = $unico;
    $parameters = array();
    $parameters['inInexCod'] = '601';
    $parameters['clXMLSolic'] = $this->createXml($content, 601);

    /** @var View $res */
    $responseView = $this->callApi($parameters);

    return $responseView;
  }

  /**
   * Modifies an employee personal information. The id is mandatory. Only
   * include fields that will be changed.<br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Modifies an employee personal information. The id is
   *                  mandatory. Only include fields that will be changed.",
   *   statusCodes = {
   *     200 = "OK",
   *     400 = "Bad Request",
   *     401 = "Unauthorized",
   *     404 = "Not Found"
   *   }
   * )
   *
   * @param Request $request.
   * Rest Parameters:
   *
   *   (name="employee_id", nullable=false, requirements="([0-9])+", description="Employee id, must be provided by us, and must be unique. It can't be the CC.")
   *   (name="last_name", nullable=true, requirements="([a-z|A-Z| ])+", description="Employee Last name(only one).")
   *   (name="first_name", nullable=true, requirements="([a-z|A-Z| ])+", description="Employee first name.")
   *   (name="document_type", nullable=true, requirements="([a-z|A-Z| ])+", description="Document type on two char format, if null CC will be used.")
   *   (name="document", nullable=true, requirements="([0-9])+", description="Employee document number")
   *   (name="gender", nullable=true, requirements="(MAS|FEM)", description="Employee gender(MAS or FEM).")
   *   (name="birth_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Employee birth day on the format DD-MM-YYYY.")
   *   (name="start_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Day the employee started working on the comopany(format: DD-MM-YYYY).")
   *   (name="days_company_seniority", nullable=true, requirements="([0-9])+", description="Previous seniority on days.")
   *   (name="last_contract_start_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Start day of the last work contract(format: DD-MM-YYYY).")
   *   (name="contract_number", nullable=true, requirements="([0-9])+", description="Employee contract number.")
   *   (name="last_contract_end_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Last work contract termination day(format: DD-MM-YYYY).")
   *   (name="worked_hours_days", nullable=true, requirements="([0-9])+", description="Number of hours worked on a day.")
   *   (name="payment_method", nullable=true, requirements="(CHE|CON|EFE)", description="Code of payment method(CHE, CON, EFE).")
   *   (name="liquidation_type", nullable=true, requirements="(J|M|Q)", description="Liquidation type, (J daily, M monthly, Q every two weeks).")
   *   (name="salary_type", nullable=true, requirements="([0-9])", description="How the employees salary is recorded(monthly 1, daily 2, every two weeks 3, hourly 4).")
   *
   * @return View
   */
  public function postModifyEmployeeAction(Request $request)
  {
    $parameters = $request->request->all();
    $regex = array();
    $mandatory = array();
    // Set all the parameters info.
    $regex['employee_id'] = '([0-9])+';$mandatory['employee_id'] = true;
    $regex['last_name'] = '([a-z|A-Z| ])+';$mandatory['last_name'] = false;
    $regex['first_name'] = '([a-z|A-Z| ])+';$mandatory['first_name'] = false;
    $regex['document_type'] = '([a-z|A-Z| ])+';$mandatory['document_type'] = false;
    $regex['document'] = '([0-9])+';$mandatory['document'] = false;
    $regex['gender'] = '(MAS|FEM)';$mandatory['gender'] = false;
    $regex['birth_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['birth_date'] = false;
    $regex['start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['start_date'] = false;
    $regex['days_company_seniority'] = '([0-9])+';$mandatory['days_company_seniority'] = false;
    $regex['last_contract_start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['last_contract_start_date'] = false;
    $regex['contract_number'] = '([0-9])+';$mandatory['contract_number'] = false;
    $regex['last_contract_end_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['last_contract_end_date'] = false;
    $regex['worked_hours_days'] = '([0-9])+';$mandatory['worked_hours_days'] = false;
    $regex['payment_method'] = '(CHE|CON|EFE)';$mandatory['payment_method'] = false;
    $regex['liquidation_type'] = '(J|M|Q)';$mandatory['liquidation_type'] = false;
    $regex['salary_type'] = '([0-9])';$mandatory['salary_type'] = false;

    $this->validateParamters($parameters, $regex, $mandatory);

    $content = array();
    $unico = array();
    $info = $this->getEmployeeAction($parameters['employee_id'])->getData();

    $unico['TIPOCON'] = 1;
    $unico['EMP_CODIGO'] = $parameters['employee_id'];
    $unico['EMP_APELLIDO1'] = isset($parameters['last_name']) ? $parameters['last_name'] : $info['EMP_APELLIDO1'];
    $unico['EMP_NOMBRE'] =  isset($parameters['first_name']) ? $parameters['first_name'] : $info['EMP_NOMBRE'];
    $unico['EMP_TIPO_IDENTIF'] =  isset($parameters['document_type']) ? $parameters['document_type'] : $info['EMP_TIPO_IDENTIF'];
    $unico['EMP_CEDULA'] =  isset($parameters['document']) ? $parameters['document'] : $info['EMP_CEDULA'];
    $unico['EMP_SEXO'] =  isset($parameters['gender']) ? $parameters['gender'] : $info['EMP_SEXO'];
    $unico['EMP_FECHA_NACI'] =  isset($parameters['birth_date']) ? $parameters['birth_date'] : $info['EMP_FECHA_NACI'];
    $unico['EMP_FECHA_INGRESO'] =  isset($parameters['start_date']) ? $parameters['start_date'] : $info['EMP_FECHA_INGRESO'];
    $unico['EMP_ANTIGUEDAD_ANT'] =  isset($parameters['days_company_seniority']) ? $parameters['days_company_seniority'] : $info['EMP_ANTIGUEDAD_ANT'];
    $unico['EMP_FECHA_INI_CONTRATO'] =  isset($parameters['last_contract_start_date']) ? $parameters['last_contract_start_date'] : $info['EMP_FECHA_INI_CONTRATO'];
    $unico['EMP_NRO_CONTRATO'] =  isset($parameters['contract_number']) ? $parameters['contract_number'] : $info['EMP_NRO_CONTRATO'];
    $unico['EMP_FECHA_FIN_CONTRATO'] =  isset($parameters['last_contract_end_date']) ? $parameters['last_contract_end_date'] : $info['EMP_FECHA_FIN_CONTRATO'];
    $unico['EMP_HORAS_TRAB'] =  isset($parameters['worked_hours_days']) ? $parameters['worked_hours_days'] : $info['EMP_HORAS_TRAB'];
    $unico['EMP_FORMA_PAGO'] =  isset($parameters['payment_method']) ? $parameters['payment_method'] : $info['EMP_FORMA_PAGO'];
    $unico['EMP_TIPOLIQ'] =  isset($parameters['liquidation_type']) ? $parameters['liquidation_type'] : $info['EMP_TIPOLIQ'];
    $unico['EMP_TIPO_SALARIO'] =  isset($parameters['salary_type']) ? $parameters['salary_type'] : $info['EMP_TIPO_SALARIO'];

    $content[] = $unico;
    $parameters = array();
    $parameters['inInexCod'] = '601';
    $parameters['clXMLSolic'] = $this->createXml($content, 601);

    /** @var View $res */
    $responseView = $this->callApi($parameters);

    return $responseView;
  }

  /**
   * Gets all the information of the employee.<br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Gets all the information of a given employee.",
   *   statusCodes = {
   *     200 = "OK",
   *     400 = "Bad Request",
   *     401 = "Unauthorized",
   *     404 = "Not Found"
   *   }
   * )
   *
   * @param Int $employeeId The id of the employee to be queried.
   *
   * @return View
   */
  public function getEmployeeAction($employeeId)
  {
    $content = array();
    $unico = array();

    $unico['EMPCODIGO'] = $employeeId;

    $content[] = $unico;
    $parameters = array();

    $parameters['inInexCod'] = '602';
    $parameters['clXMLSolic'] = $this->createXml($content, 602, 2);

    /** @var View $res */
    $responseView = $this->callApi($parameters);

    return $responseView;
  }

  /**
   * Inserts a fixed concept for a given employee.<br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = " Inserts a fixed concept for a given employee.",
   *   statusCodes = {
   *     200 = "OK",
   *     400 = "Bad Request",
   *     401 = "Unauthorized",
   *     404 = "Not Found"
   *   }
   * )
   *
   * @var Request $request
   *
   *    (name="employee_id", nullable=false, requirements="([0-9])+", strict=true, description="Employee id, must be provided by us, and must be unique. It can't be the CC.")
   *    (name="concept_id", nullable=false, requirements="([0-9])+", description="ID of the concept as described by SQL Software.")
   *    (name="value", nullable=false, requirements="([0-9])+(.[0-9]+)?", description="Value of the concept.")
   *
   *
   * @return View
   */
  public function postAddFixedConceptsAction(Request $request)
  {

    $parameters = $request->request->all();
    $regex = array();
    $mandatory = array();
    // Set all the parameters info.
    $regex['employee_id'] = '([0-9])+';$mandatory['employee_id'] = true;
    $regex['concept_id'] = '([0-9])+';$mandatory['concept_id'] = true;
    $regex['value'] = '([0-9])+(.[0-9]+)?';$mandatory['value'] = true;

    $this->validateParamters($parameters, $regex, $mandatory);

    $content = array();
    $unico = array();
    $unico['TIPOCON'] = 0;
    $unico['EMP_CODIGO'] = $parameters['employee_id'];
    $unico['CON_CODIGO'] = $parameters['concept_id'];
    $unico['COF_VALOR'] = $parameters['value'];

    $content[] = $unico;
    $parameters = array();
    $parameters['inInexCod'] = '605';
    $parameters['clXMLSolic'] = $this->createXml($content, 605);

    /** @var View $res */
    $responseView = $this->callApi($parameters);

    return $responseView;
  }

  /**
   * Modifies a fixed concept for a given employee.<br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = " Modifies a fixed concept for a given employee.",
   *   statusCodes = {
   *     200 = "OK",
   *     400 = "Bad Request",
   *     401 = "Unauthorized",
   *     404 = "Not Found"
   *   }
   * )
   *
   * @param Request $request.
   * Rest Parameters:
   *
   *    (name="employee_id", nullable=false, requirements="([0-9])+", strict=true, description="Employee id, must be provided by us, and must be unique. It can't be the CC.")
   *    (name="concept_id", nullable=true, requirements="([0-9])+", description="ID of the concept as described by SQL Software.")
   *    (name="value", nullable=true, requirements="([0-9])+(.[0-9]+)?", description="Value of the concept.")
   *
   * @return View
   */
  public function postModifyFixedConceptsAction(Request $request)
  {
    $parameters = $request->request->all();
    $regex = array();
    $mandatory = array();
    // Set all the parameters info.
    $regex['employee_id'] = '([0-9])+';$mandatory['employee_id'] = true;
    $regex['concept_id'] = '([0-9])+';$mandatory['concept_id'] = false;
    $regex['value'] = '([0-9])+(.[0-9]+)?';$mandatory['value'] = false;

    $this->validateParamters($parameters, $regex, $mandatory);

    $content = array();
    $unico = array();
    $info = $this->getFixedConceptsAction($parameters['employee_id'])->getData();

    $unico['TIPOCON'] = 1;
    $unico['EMP_CODIGO'] =  isset($parameters['employee_id']) ? $parameters['employee_id'] : $info['EMP_CODIGO'];
    $unico['CON_CODIGO'] = isset($parameters['concept_id']) ? $parameters['concept_id'] : $info['CON_CODIGO'];
    $unico['COF_VALOR'] = isset($parameters['value']) ? $parameters['value'] : $info['COF_VALOR'];

    $content[] = $unico;
    $parameters = array();
    $parameters['inInexCod'] = '605';
    $parameters['clXMLSolic'] = $this->createXml($content, 605);

    /** @var View $res */
    $responseView = $this->callApi($parameters);

    return $responseView;
  }

  /**
   * Gets all the information of the employee.<br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Gets all the information of a given employee.",
   *   statusCodes = {
   *     200 = "OK",
   *     400 = "Bad Request",
   *     401 = "Unauthorized",
   *     404 = "Not Found"
   *   }
   * )
   *
   * @param Int $employeeId The id of the employee to be queried.
   *
   * @return View
   */
  public function getFixedConceptsAction($employeeId)
  {
    $content = array();
    $unico = array();

    $unico['EMPCODIGO'] = $employeeId;

    $content[] = $unico;
    $parameters = array();

    $parameters['inInexCod'] = '606';
    $parameters['clXMLSolic'] = $this->createXml($content, 606, 2);

    /** @var View $res */
    $responseView = $this->callApi($parameters);

    return $responseView;
  }

  /**
   * Gets all the information of the employee.<br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Gets all the information of a given employee.",
   *   statusCodes = {
   *     200 = "OK",
   *     400 = "Bad Request",
   *     401 = "Unauthorized",
   *     404 = "Not Found"
   *   }
   * )
   *
   * @param Int $employeeId The id of the employee to be queried.
   *
   * @return View
   */
  public function getHistoryFixedConceptsAction($employeeId)
  {
    $content = array();
    $unico = array();

    $unico['EMPCODIGO'] = $employeeId;

    $content[] = $unico;
    $parameters = array();

    $parameters['inInexCod'] = '607';
    $parameters['clXMLSolic'] = $this->createXml($content, 607, 2);

    /** @var View $res */
    $responseView = $this->callApi($parameters);

    return $responseView;
  }



  /**
   * Insert a new entity for an employee.<br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = " Insert a new entity for an employee.",
   *   statusCodes = {
   *     200 = "OK",
   *     400 = "Bad Request",
   *     401 = "Unauthorized",
   *     404 = "Not Found"
   *   }
   * )
   *
   * @param Request $request.
   * Rest Parameters:
   *
   *    (name="employee_id", nullable=false, requirements="([0-9])+", strict=true, description="Employee id")
   *    (name="entity_type_code", nullable=false, requirements="([A-Za-z])+", strict=true, description="Code of the entity type as described by sql software")
   *    (name="coverage_code", nullable=false, requirements="([0-9])+", strict=true, description="Code of the coverage as described by sql software.")
   *    (name="entity_code", nullable=false, requirements="([0-9])+", description="Code of the entity as described by sql software")
   *    (name="start_date", nullable=false, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Day the employee started working on the comopany(format: DD-MM-YYYY).")
   *
   * @return View
   */
  public function postAddEmployeeEntityAction(Request $request)
  {

    $parameters = $request->request->all();
    $regex = array();
    $mandatory = array();
    // Set all the parameters info.
    $regex['employee_id'] = '([0-9])+';$mandatory['employee_id'] = true;
    $regex['entity_type_code'] = '([A-Za-z])+';$mandatory['entity_type_code'] = true;
    $regex['coverage_code'] = '([0-9])+';$mandatory['coverage_code'] = true;
    $regex['entity_code'] = '([0-9])+';$mandatory['entity_code'] = true;
    $regex['start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['start_date'] = true;

    $this->validateParamters($parameters, $regex, $mandatory);

    $content = array();
    $unico = array();

    $unico['TIPOCON'] = 0;
    $unico['EMP_CODIGO'] = $parameters['employee_id'];
    $unico['TENT_CODIGO'] = $parameters['entity_type_code'];
    $unico['COB_CODIGO'] = $parameters['coverage_code'];
    $unico['ENT_CODIGO'] = $parameters['entity_code'];
    $unico['FECHA_INICIO'] = $parameters['start_date'];

    $content[] = $unico;
    $parameters = array();
    $parameters['inInexCod'] = '608';
    $parameters['clXMLSolic'] = $this->createXml($content, 608);

    /** @var View $res */
    $responseView = $this->callApi($parameters);

    return $responseView;
  }

  /**
   * Modifies a  entity of an employee.<br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = " Insert a new entity for an employee.",
   *   statusCodes = {
   *     200 = "OK",
   *     400 = "Bad Request",
   *     401 = "Unauthorized",
   *     404 = "Not Found"
   *   }
   * )
   *
   * @param Request $request.
   * Rest Parameters:
   *
   *    (name="employee_id", nullable=false, requirements="([0-9])+", description="Employee id")
   *    (name="entity_type_code", nullable=true, requirements="([A-Za-z])+", description="Code of the entity type as described by sql software")
   *    (name="coverage_code", nullable=true, requirements="([0-9])+", description="Code of the coverage as described by sql software.")
   *    (name="entity_code", nullable=true, requirements="([0-9])+", description="Code of the entity as described by sql software")
   *    (name="start_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Day the employee started working on the comopany(format: DD-MM-YYYY).")
   *
   * @return View
   */
  public function postModifyEmployeeEntityAction(Request $request)
  {
    $parameters = $request->request->all();
    $regex = array();
    $mandatory = array();
    // Set all the parameters info.
    $regex['employee_id'] = '([0-9])+';$mandatory['employee_id'] = true;
    $regex['entity_type_code'] = '([A-Za-z])+';$mandatory['entity_type_code'] = false;
    $regex['coverage_code'] = '([0-9])+';$mandatory['coverage_code'] = false;
    $regex['entity_code'] = '([0-9])+';$mandatory['entity_code'] = false;
    $regex['start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['start_date'] = false;

    $this->validateParamters($parameters, $regex, $mandatory);

    $content = array();
    $unico = array();
    $info = $this->getEmployeeEntityAction($parameters['employee_id'])->getData();


    $unico['EMP_CODIGO'] = isset($parameters['employee_id']) ? $parameters['employee_id'] : $info['EMP_CODIGO'];

    $unico['TIPOCON'] = 1;
    $unico['EMP_CODIGO'] = isset($parameters['employee_id']) ? $parameters['employee_id'] : $info['EMP_CODIGO'];
    $unico['TENT_CODIGO'] = isset($parameters['entity_type_code']) ? $parameters['entity_type_code'] : $info['TENT_CODIGO'];
    $unico['COB_CODIGO'] = isset($parameters['coverage_code']) ? $parameters['coverage_code'] : $info['COB_CODIGO'];
    $unico['ENT_CODIGO'] = isset($parameters['entity_code']) ? $parameters['entity_code'] : $info['ENT_CODIGO'];
    $unico['FECHA_INICIO'] = isset($parameters['start_date']) ? $parameters['start_date'] : $info['FECHA_INICIO'];

    $content[] = $unico;
    $parameters = array();
    $parameters['inInexCod'] = '608';
    $parameters['clXMLSolic'] = $this->createXml($content, 608);

    /** @var View $res */
    $responseView = $this->callApi($parameters);

    return $responseView;
  }

  /**
   * Gets all the information related to the entities of the employee.<br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Gets all the information related to the entities of the employee.",
   *   statusCodes = {
   *     200 = "OK",
   *     400 = "Bad Request",
   *     401 = "Unauthorized",
   *     404 = "Not Found"
   *   }
   * )
   *
   * @param Int $employeeId The id of the employee to be queried.
   *
   * @return View
   */
  public function getEmployeeEntityAction($employeeId)
  {
    $content = array();
    $unico = array();

    $unico['EMPCODIGO'] = $employeeId;

    $content[] = $unico;
    $parameters = array();

    $parameters['inInexCod'] = '609';
    $parameters['clXMLSolic'] = $this->createXml($content, 609, 2);

    /** @var View $res */
    $responseView = $this->callApi($parameters);

    return $responseView;
  }

  /**
   * Insert a new occasional novelty for an employee.<br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Insert a new occasional novelty for an employee.",
   *   statusCodes = {
   *     200 = "OK",
   *     400 = "Bad Request",
   *     401 = "Unauthorized",
   *     404 = "Not Found"
   *   }
   * )
   *
   * @param Request $request.
   * Rest Parameters:
   *
   *    (name="employee_id", nullable=false, requirements="([0-9])+", strict=true, description="Employee id")
   *    (name="novelty_concept_id", nullable=false, requirements="([0-9])+", strict=true, description="Code of the concept as provided by SQL")
   *    (name="novelty_value", nullable=true, requirements="([0-9])+(.[0-9]+)?", description="Value in COP of the novelty, is optional")
   *    (name="liquidation_type_id", nullable=false, requirements="([0-9])+", strict=true, description="Code of the liquidation type")
   *    (name="unity_numbers", nullable=true, requirements="([0-9])+", strict=true, description="Number of units of the novelty")
   *    (name="novelty_start_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Day the novelty starts(format: DD-MM-YYYY)")
   *    (name="novelty_end_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Day the novelty ends(format: DD-MM-YYYY)")
   *
   * @return View
   */
  public function postAddNoveltyEmployeeAction(Request $request)
  {
    $parameters = $request->request->all();
    $regex = array();
    $mandatory = array();
    // Set all the parameters info.
    $regex['employee_id'] = '([0-9])+';$mandatory['employee_id'] = true;
    $regex['novelty_concept_id'] = '([0-9])+';$mandatory['novelty_concept_id'] = true;
    $regex['novelty_value'] = '([0-9])+(.[0-9]+)?';$mandatory['novelty_value'] = false;
    $regex['liquidation_type_id'] = '([0-9])+';$mandatory['liquidation_type_id'] = true;
    $regex['unity_numbers'] = '([0-9])+';$mandatory['unity_numbers'] = false;
    $regex['novelty_start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['novelty_start_date'] = false;
    $regex['novelty_end_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['novelty_end_date'] = false;

    $this->validateParamters($parameters, $regex, $mandatory);

    $content = array();
    $unico = array();

    $unico['TIPOCON'] = 0;
    $unico['EMP_CODIGO'] = $parameters['employee_id'];
    $unico['CON_CODIGO'] = $parameters['novelty_concept_id'];
    $unico['NOV_VALOR_LOCAL'] = $parameters['novelty_value'];
    $unico['FLIQ_CODIGO'] = $parameters['liquidation_type_id'];
    $unico['NOV_UNIDADES'] = $parameters['unity_numbers'];
    $unico['NOV_FECHA_DESDE_CAUSA'] = $parameters['novelty_start_date'];
    $unico['NOV_FECHA_HASTA_CAUSA'] = $parameters['novelty_end_date'];

    $content[] = $unico;
    $parameters = array();
    $parameters['inInexCod'] = '612';
    $parameters['clXMLSolic'] = $this->createXml($content, 612);

    /** @var View $res */
    $responseView = $this->callApi($parameters);

    return $responseView;
  }

  /**
   * Modifies a new occasional novelty for an employee.<br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Modifies a new occasional novelty for an employee.",
   *   statusCodes = {
   *     200 = "OK",
   *     400 = "Bad Request",
   *     401 = "Unauthorized",
   *     404 = "Not Found"
   *   }
   * )
   *
   * @param Request $request.
   * Rest Parameters:
   *
   *    (name="employee_id", nullable=false, requirements="([0-9])+", strict=true, description="Employee id")
   *    (name="novelty_concept_id", nullable=true, requirements="([0-9])+", strict=true, description="Code of the concept as provided by SQL")
   *    (name="novelty_value", nullable=true, requirements="([0-9])+(.[0-9]+)?", description="Value in COP of the novelty, is optional")
   *    (name="liquidation_type_id", nullable=false, requirements="([0-9])+", strict=true, description="Code of the liquidation type")
   *    (name="unity_numbers", nullable=true, requirements="([0-9])+", strict=true, description="Number of units of the novelty")
   *    (name="novelty_start_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Day the novelty starts(format: DD-MM-YYYY)")
   *    (name="novelty_end_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Day the novelty ends(format: DD-MM-YYYY)")
   *
   * @return View
   */
  public function postModifyNoveltyEmployeeAction(Request $request)
  {
    $parameters = $request->request->all();
    $regex = array();
    $mandatory = array();
    // Set all the parameters info.
    $regex['employee_id'] = '([0-9])+';$mandatory['employee_id'] = true;
    $regex['novelty_concept_id'] = '([0-9])+';$mandatory['novelty_concept_id'] = false;
    $regex['novelty_value'] = '([0-9])+(.[0-9]+)?';$mandatory['novelty_value'] = false;
    $regex['liquidation_type_id'] = '([0-9])+';$mandatory['liquidation_type_id'] = false;
    $regex['unity_numbers'] = '([0-9])+';$mandatory['unity_numbers'] = false;
    $regex['novelty_start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['novelty_start_date'] = false;
    $regex['novelty_end_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['novelty_end_date'] = false;

    $this->validateParamters($parameters, $regex, $mandatory);

    $content = array();
    $unico = array();
    $info = $this->getEmployeeNoveltyAction($parameters['employee_id'])->getData();


    $unico['EMP_CODIGO'] = isset($parameters['employee_id']) ? $parameters['employee_id'] : $info['EMP_CODIGO'];

    $unico['TIPOCON'] = 1;
    $unico['EMP_CODIGO'] = isset($parameters['employee_id']) ? $parameters['employee_id'] : $info['EMP_CODIGO'];
    $unico['CON_CODIGO'] = isset($parameters['novelty_concept_id']) ? $parameters['novelty_concept_id'] : $info['CON_CODIGO'];
    $unico['NOV_VALOR_LOCAL'] = isset($parameters['novelty_value']) ? $parameters['novelty_value'] : $info['NOV_VALOR_LOCAL'];
    $unico['FLIQ_CODIGO'] = isset($parameters['liquidation_type_id']) ? $parameters['liquidation_type_id'] : $info['FLIQ_CODIGO'];
    $unico['NOV_UNIDADES'] = isset($parameters['unity_numbers']) ? $parameters['unity_numbers'] : $info['NOV_UNIDADES'];
    $unico['NOV_FECHA_DESDE_CAUSA'] = isset($parameters['novelty_start_date']) ? $parameters['novelty_start_date'] : $info['NOV_FECHA_DESDE_CAUSA'];
    $unico['NOV_FECHA_HASTA_CAUSA'] = isset($parameters['novelty_end_date']) ? $parameters['novelty_end_date'] : $info['NOV_FECHA_HASTA_CAUSA'];

    $content[] = $unico;
    $parameters = array();
    $parameters['inInexCod'] = '612';
    $parameters['clXMLSolic'] = $this->createXml($content, 612);

    /** @var View $res */
    $responseView = $this->callApi($parameters);

    return $responseView;
  }

  /**
   * Gets the novelties of an employee.<br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Gets the novelties of an employee.",
   *   statusCodes = {
   *     200 = "OK",
   *     400 = "Bad Request",
   *     401 = "Unauthorized",
   *     404 = "Not Found"
   *   }
   * )
   *
   * @param Int $employeeId The id of the employee to be queried.
   * @param String $liquidation_date The date of the liquidation, only
   *        registers with a higher date will be shown, it is optional.
   *
   * @return View
   */
  public function getEmployeeNoveltyAction($employeeId, $liquidation_date=null)
  {
    $content = array();
    $unico = array();

    $unico['EMPCODIGO'] = $employeeId;
    if($liquidation_date != null)
      $unico['NOV_FECHA_LIQ'] = $liquidation_date;

    $content[] = $unico;
    $parameters = array();

    $parameters['inInexCod'] = '613';
    $parameters['clXMLSolic'] = $this->createXml($content, 613, 2);

    /** @var View $res */
    $responseView = $this->callApi($parameters);

    return $responseView;
  }



    /**
     * Insert a new fixed novelty for an employee.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Insert a new fixed novelty for an employee.",
     *   statusCodes = {
     *     200 = "OK",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Not Found"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *
     *    (name="employee_id", nullable=false, requirements="([0-9])+", strict=true, description="Employee id")
     *    (name="novelty_concept_id", nullable=false, requirements="([0-9])+", strict=true, description="Code of the concept as provided by SQL")
     *    (name="novelty_value", nullable=false, requirements="([0-9])+", description="Value in COP of the novelty, is optional")
     *    (name="novelty_start_date", nullable=false, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Day the novelty starts(format: DD-MM-YYYY)")
     *    (name="novelty_end_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="(optional)Day the novelty ends(format: DD-MM-YYYY)")
     *
     * @return View
     */
    public function postAddFixedNoveltyEmployeeAction(Request $request)
    {
      $parameters = $request->request->all();
      $regex = array();
      $mandatory = array();
      // Set all the parameters info.
      $regex['employee_id'] = '([0-9])+';$mandatory['employee_id'] = true;
      $regex['novelty_concept_id'] = '([0-9])+';$mandatory['novelty_concept_id'] = true;
      $regex['novelty_value'] = '([0-9])+';$mandatory['novelty_value'] = true;
      $regex['novelty_start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['novelty_start_date'] = true;
      $regex['novelty_end_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['novelty_end_date'] = false;

      $this->validateParamters($parameters, $regex, $mandatory);

      $content = array();
      $unico = array();

      $unico['TIPOCON'] = 0;
      $unico['EMP_CODIGO'] = $parameters['employee_id'];
      $unico['CON_CODIGO'] = $parameters['novelty_concept_id'];
      $unico['NOVF_VALOR'] = $parameters['novelty_value'];
      $unico['NOVF_FECHA_INICIAL'] = $parameters['novelty_start_date'];
      $unico['NOVF_FECHA_FINAL'] = $parameters['novelty_end_date'];

      $content[] = $unico;
      $parameters = array();
      $parameters['inInexCod'] = '614';
      $parameters['clXMLSolic'] = $this->createXml($content, 614);

      /** @var View $res */
      $responseView = $this->callApi($parameters);

      return $responseView;
    }

    /**
     * Modifies a fixed novelty for an employee.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Modifies a fixed novelty for an employee.",
     *   statusCodes = {
     *     200 = "OK",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Not Found"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *
     *    (name="employee_id", nullable=false, requirements="([0-9])+", strict=true, description="Employee id")
     *    (name="novelty_id", nullable=true, requirements="([0-9])+", strict=true, description="Novelty id")
     *    (name="novelty_concept_id", nullable=true, requirements="([0-9])+", strict=true, description="Code of the concept as provided by SQL")
     *    (name="novelty_value", nullable=true, requirements="([0-9])+", description="Value in COP of the novelty, is optional")
     *    (name="novelty_start_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Day the novelty starts(format: DD-MM-YYYY)")
     *    (name="novelty_end_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="(optional)Day the novelty ends(format: DD-MM-YYYY)")
     *
     * @return View
     */
    public function postModifyFixedNoveltyEmployeeAction(Request $request)
    {
      $parameters = $request->request->all();
      $regex = array();
      $mandatory = array();
      // Set all the parameters info.
      $regex['employee_id'] = '([0-9])+';$mandatory['employee_id'] = true;
      $regex['novelty_concept_id'] = '([0-9])+';$mandatory['novelty_concept_id'] = false;
      $regex['novelty_value'] = '([0-9])+';$mandatory['novelty_value'] = false;
      $regex['novelty_start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['novelty_start_date'] = false;
      $regex['novelty_end_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['novelty_end_date'] = false;

      $this->validateParamters($parameters, $regex, $mandatory);

      $content = array();

      $unico = array();
      $info = $this->getEmployeeFixedNoveltyAction($parameters['employee_id'])->getData();

      $unico['EMP_CODIGO'] = $parameters['employee_id'];

      $unico['TIPOCON'] = 1;
      $unico['NOVF_CONSEC'] = isset($parameters['novelty_id']) ? $parameters['novelty_id'] : $info['NOVF_CONSEC'];
      $unico['CON_CODIGO'] = isset($parameters['novelty_concept_id']) ? $parameters['novelty_concept_id'] : $info['CON_CODIGO'];
      $unico['NOVF_VALOR'] = isset($parameters['novelty_value']) ? $parameters['novelty_value'] : $info['NOVF_VALOR'];
      $unico['NOVF_FECHA_INICIAL'] = isset($parameters['novelty_start_date']) ? $parameters['novelty_start_date'] : $info['NOVF_FECHA_INICIAL'];
      $unico['NOVF_FECHA_FINAL'] = isset($parameters['novelty_end_date']) ? $parameters['novelty_end_date'] : $info['NOVF_FECHA_FINAL'];

      $content[] = $unico;
      $parameters = array();
      $parameters['inInexCod'] = '614';
      $parameters['clXMLSolic'] = $this->createXml($content, 614);

      /** @var View $res */
      $responseView = $this->callApi($parameters);

      return $responseView;
    }

    /**
     * Gets the fixed novelties of an employee.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets the fixed novelties of an employee.",
     *   statusCodes = {
     *     200 = "OK",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Not Found"
     *   }
     * )
     *
     * @param Int $employeeId The id of the employee to be queried.
     * @param String $query_date If used, it will only show novelties in this
     *        range, start_date >= x >= end_date. It is optional.
     *
     * @return View
     */
    public function getEmployeeFixedNoveltyAction($employeeId, $query_date=null)
    {
      $content = array();
      $unico = array();

      $unico['EMPCODIGO'] = $employeeId;
      if($query_date != null)
        $unico['FECHACONSULTA'] = $query_date;

      $content[] = $unico;
      $parameters = array();

      $parameters['inInexCod'] = '615';
      $parameters['clXMLSolic'] = $this->createXml($content, 615, 2);

      /** @var View $res */
      $responseView = $this->callApi($parameters);

      return $responseView;
    }

    /**
     * Insert a new absenteeism for an employee.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Insert a new absenteeism for an employee.y",
     *   statusCodes = {
     *     200 = "OK",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Not Found"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *
     *    (name="employee_id", nullable=false, requirements="([0-9])+", strict=true, description="Employee id")
     *    (name="absenteeism_type_id", nullable=false, requirements="([0-9])+", strict=true, description="Code of the type of absenteeism as provided by SQL")
     *    (name="absenteeism_start_date", nullable=false, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Day the absenteeism starts(format: DD-MM-YYYY)")
     *    (name="absenteeism_end_date", nullable=false, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Day the absenteeism ends(format: DD-MM-YYYY)")
     *    (name="absenteeism_units", nullable=false, requirements="([0-9])+", description="Number of units, can be hours or days")
     *    (name="absenteeism_state", nullable=true, requirements="(ACT|CAN)", strict=true, description="State of the absenteeism ACT active or CAN cancelled")
     *
     * @return View
     */
    public function postAddAbsenteeismEmployeeAction(Request $request)
    {
      $parameters = $request->request->all();
      $regex = array();
      $mandatory = array();
      // Set all the parameters info.
      $regex['employee_id'] = '([0-9])+';$mandatory['employee_id'] = true;
      $regex['absenteeism_type_id'] = '([0-9])+';$mandatory['absenteeism_type_id'] = true;
      $regex['absenteeism_start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['absenteeism_start_date'] = true;
      $regex['absenteeism_end_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['absenteeism_end_date'] = true;
      $regex['absenteeism_units'] = '([0-9])+';$mandatory['absenteeism_units'] = true;
      $regex['absenteeism_state'] = '(ACT|CAN)';$mandatory['absenteeism_state'] = false;

      $this->validateParamters($parameters, $regex, $mandatory);

      $content = array();
      $unico = array();

      $unico['TIPOCON'] = 0;
      $unico['EMP_CODIGO'] = $parameters['employee_id'];
      $unico['TAUS_CODIGO'] = $parameters['absenteeism_type_id'];
      $unico['AUS_FECHA_INICIAL'] = $parameters['absenteeism_start_date'];
      $unico['AUS_FECHA_FINAL'] = $parameters['absenteeism_end_date'];
      $unico['AUS_UNIDADES'] = $parameters['absenteeism_units'];
      $unico['AUS_ESTADO'] = $parameters['absenteeism_state'];

      $content[] = $unico;
      $parameters = array();
      $parameters['inInexCod'] = '623';
      $parameters['clXMLSolic'] = $this->createXml($content, 623);

      /** @var View $res */
      $responseView = $this->callApi($parameters);

      return $responseView;
    }

    /**
     * Modifies an absenteeism for an employee.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Modifies an absenteeism for an employee.y",
     *   statusCodes = {
     *     200 = "OK",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Not Found"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *
     *    (name="employee_id", nullable=false, requirements="([0-9])+", strict=true, description="Employee id")
     *    (name="absenteeism_id", nullable=true, requirements="([0-9])+", description="Consecutive number of the absenteeism")
     *    (name="absenteeism_type_id", nullable=true, requirements="([0-9])+", description="Code of the type of absenteeism as provided by SQL")
     *    (name="absenteeism_start_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Day the absenteeism starts(format: DD-MM-YYYY)")
     *    (name="absenteeism_end_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Day the absenteeism ends(format: DD-MM-YYYY)")
     *    (name="absenteeism_units", nullable=true, requirements="([0-9])+", description="Number of units, can be hours or days")
     *    (name="absenteeism_state", nullable=true, requirements="(ACT|CAN)", description="State of the absenteeism ACT active or CAN cancelled")
     *
     * @return View
     */
    public function postModifyAbsenteeismEmployeeAction(Request $request)
    {
      $parameters = $request->request->all();
      $regex = array();
      $mandatory = array();
      // Set all the parameters info.
      $regex['employee_id'] = '([0-9])+';$mandatory['employee_id'] = true;
      $regex['absenteeism_type_id'] = '([0-9])+';$mandatory['absenteeism_type_id'] = false;
      $regex['absenteeism_start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['absenteeism_start_date'] = false;
      $regex['absenteeism_end_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['absenteeism_end_date'] = false;
      $regex['absenteeism_units'] = '([0-9])+';$mandatory['absenteeism_units'] = false;
      $regex['absenteeism_state'] = '(ACT|CAN)';$mandatory['absenteeism_state'] = false;

      $this->validateParamters($parameters, $regex, $mandatory);

      $content = array();
      $unico = array();

      $info = $this->getAbsenteeismEmployeeAction($parameters['employee_id'])->getData();

      $unico['TIPOCON'] = 1;
      $unico['EMP_CODIGO'] = isset($parameters['employee_id']) ? $parameters['employee_id'] : $info['EMP_CODIGO'];
      $unico['TAUS_CODIGO'] = isset($parameters['absenteeism_type_id']) ? $parameters['absenteeism_type_id'] : $info['TAUS_CODIGO'];
      $unico['AUS_FECHA_INICIAL'] = isset($parameters['absenteeism_start_date']) ? $parameters['absenteeism_start_date'] : $info['AUS_FECHA_INICIAL'];
      $unico['AUS_FECHA_FINAL'] = isset($parameters['absenteeism_end_date']) ? $parameters['absenteeism_end_date'] : $info['AUS_FECHA_FINAL'];
      $unico['AUS_UNIDADES'] = isset($parameters['absenteeism_units']) ? $parameters['absenteeism_units'] : $info['AUS_UNIDADES'];
      $unico['AUS_ESTADO'] = isset($parameters['absenteeism_state']) ? $parameters['absenteeism_state'] : $info['AUS_ESTADO'];

      $content[] = $unico;
      $parameters = array();
      $parameters['inInexCod'] = '623';
      $parameters['clXMLSolic'] = $this->createXml($content, 623);

      /** @var View $res */
      $responseView = $this->callApi($parameters);

      return $responseView;
    }

    /**
     * Gets the abstenteeisms information of an employee.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets the abstenteeisms information of an employee.",
     *   statusCodes = {
     *     200 = "OK",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Not Found"
     *   }
     * )
     *
     * @param Int $employeeId The id of the employee to be queried.
     * @param String $state The status of the absenteeism, it can be ACT or CAN,
     *        It is optional.
     *
     * @return View
     */
    public function getAbsenteeismEmployeeAction($employeeId, $state=null)
    {
      $content = array();
      $unico = array();

      $unico['EMPCODIGO'] = $employeeId;
      if($state != null)
        $unico['AUS_ESTADO'] = $state;

      $content[] = $unico;
      $parameters = array();

      $parameters['inInexCod'] = '624';
      $parameters['clXMLSolic'] = $this->createXml($content, 624, 2);

      /** @var View $res */
      $responseView = $this->callApi($parameters);

      return $responseView;
    }

    /**
     * Insert a new extension for an employee.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Insert a new extension for an employee.y",
     *   statusCodes = {
     *     200 = "OK",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Not Found"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *
     *    (name="employee_id", nullable=false, requirements="([0-9])+", strict=true, description="Employee id")
     *    (name="contract_type_id", nullable=false, requirements="([0-9])+", strict=true, description="Code of the type of absenteeism as provided by SQL")
     *    (name="contract_start_date", nullable=false, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Day the absenteeism starts(format: DD-MM-YYYY)")
     *    (name="extension_reason_id", nullable=false, requirements="(.*)", strict=true, description="Day the absenteeism ends(format: DD-MM-YYYY)")
     *    (name="employee_contract_number", nullable=true, requirements="([0-9])+", description="Number of units, can be hours or days")
     *    (name="employee_leaving_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="State of the absenteeism ACT active or CAN cancelled")
     *
     * @return View
     */
    public function postAddExtensionEmployeeAction(Request $request)
    {
      $parameters = $request->request->all();
      $regex = array();
      $mandatory = array();
      // Set all the parameters info.
      $regex['employee_id'] = '([0-9])+';$mandatory['employee_id'] = true;
      $regex['contract_type_id'] = '([0-9])+';$mandatory['contract_type_id'] = true;
      $regex['contract_start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['contract_start_date'] = true;
      $regex['extension_reason_id'] = '(.*)';$mandatory['extension_reason_id'] = true;
      $regex['employee_contract_number'] = '([0-9])+';$mandatory['employee_contract_number'] = false;
      $regex['employee_leaving_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['employee_leaving_date'] = false;



      $this->validateParamters($parameters, $regex, $mandatory);

      $content = array();
      $unico = array();

      $unico['TIPOCON'] = 0;
      $unico['EMP_CODIGO'] = $parameters['employee_id'];
      $unico['TCO_CODIGO'] = $parameters['contract_type_id'];
      $unico['HPRO_FECHA_INGRESO'] = $parameters['contract_start_date'];
      $unico['MPRO_CODIGO'] = $parameters['extension_reason_id'];
      $unico['HPRO_NRO_CONTRATO'] = $parameters['employee_contract_number'];
      $unico['HPRO_EMP_FECHA_RETIRO'] = $parameters['employee_leaving_date'];

      $content[] = $unico;
      $parameters = array();
      $parameters['inInexCod'] = '637';
      $parameters['clXMLSolic'] = $this->createXml($content, 637);

      /** @var View $res */
      $responseView = $this->callApi($parameters);

      return $responseView;
    }

    /**
     * Modifies a new extension for an employee.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Modifies a new extension for an employee.y",
     *   statusCodes = {
     *     200 = "OK",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Not Found"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *
     *    (name="employee_id", nullable=false, requirements="([0-9])+", strict=true, description="Employee id")
     *    (name="contract_type_id", nullable=true, requirements="([0-9])+", strict=true, description="Code of the type of absenteeism as provided by SQL")
     *    (name="contract_start_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Day the absenteeism starts(format: DD-MM-YYYY)")
     *    (name="extension_reason_id", nullable=true, requirements="(.*)", strict=true, description="Day the absenteeism ends(format: DD-MM-YYYY)")
     *    (name="employee_contract_number", nullable=true, requirements="([0-9])+", description="Number of units, can be hours or days")
     *    (name="employee_leaving_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="State of the absenteeism ACT active or CAN cancelled")
     *
     * @return View
     */
    public function postModifyExtensionEmployeeAction(Request $request)
    {
      $parameters = $request->request->all();
      $regex = array();
      $mandatory = array();
      // Set all the parameters info.
      $regex['employee_id'] = '([0-9])+';$mandatory['employee_id'] = true;
      $regex['contract_type_id'] = '([0-9])+';$mandatory['contract_type_id'] = false;
      $regex['contract_start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['contract_start_date'] = false;
      $regex['extension_reason_id'] = '(.*)';$mandatory['extension_reason_id'] = false;
      $regex['employee_contract_number'] = '([0-9])+';$mandatory['employee_contract_number'] = false;
      $regex['employee_leaving_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['employee_leaving_date'] = false;



      $this->validateParamters($parameters, $regex, $mandatory);

      $content = array();
      $unico = array();

      $info = $this->getExtensionEmployeeAction($parameters['employee_id'])->getData();

      $unico['TIPOCON'] = 1;
      $unico['EMP_CODIGO'] = isset($parameters['employee_id']) ? $parameters['employee_id'] : $info['EMP_CODIGO'];
      $unico['TCO_CODIGO'] = isset($parameters['contract_type_id']) ? $parameters['contract_type_id'] : $info['TCO_CODIGO'];
      $unico['HPRO_FECHA_INGRESO'] = isset($parameters['contract_start_date']) ? $parameters['contract_start_date'] : $info['HPRO_FECHA_INGRESO'];
      $unico['MPRO_CODIGO'] = isset($parameters['extension_reason_id']) ? $parameters['extension_reason_id'] : $info['MPRO_CODIGO'];
      $unico['HPRO_NRO_CONTRATO'] = isset($parameters['employee_contract_number']) ? $parameters['employee_contract_number'] : $info['HPRO_NRO_CONTRATO'];
      $unico['HPRO_EMP_FECHA_RETIRO'] = isset($parameters['employee_leaving_date']) ? $parameters['employee_leaving_date'] : $info['HPRO_EMP_FECHA_RETIRO'];

      $content[] = $unico;
      $parameters = array();
      $parameters['inInexCod'] = '637';
      $parameters['clXMLSolic'] = $this->createXml($content, 637);

      /** @var View $res */
      $responseView = $this->callApi($parameters);

      return $responseView;
    }

    /**
     * Gets the extensions of an employee.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets the extensions of an employee.",
     *   statusCodes = {
     *     200 = "OK",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Not Found"
     *   }
     * )
     *
     * @param Int $employeeId The id of the employee to be queried.
     *
     * @return View
     */
    public function getExtensionEmployeeAction($employeeId)
    {
      $content = array();
      $unico = array();

      $unico['EMPCODIGO'] = $employeeId;

      $content[] = $unico;
      $parameters = array();

      $parameters['inInexCod'] = '638';
      $parameters['clXMLSolic'] = $this->createXml($content, 638, 2);

      /** @var View $res */
      $responseView = $this->callApi($parameters);

      return $responseView;
    }
}
?>
