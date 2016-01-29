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
   *   (name="start_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Day the employee started working on the comopany(format: DD-MM-YYYY).")
   *   (name="contract_number", nullable=true, requirements="([0-9])+", strict=true, description="Employee contract number.")
   *   (name="last_contract_end_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Last work contract termination day(format: DD-MM-YYYY).")
   *   (name="worked_hours_days", nullable=false, requirements="([0-9])+", strict=true, description="Number of hours worked on a day.")
   *   (name="payment_method", nullable=false, requirements="(CHE|CON|EFE)", strict=true, description="Code of payment method(CHE, CON, EFE). This code can be obtained using the table pay_type, field payroll_code.")
   *   (name="liquidation_type", nullable=false, requirements="(J|M|Q)", strict=true, description="Liquidation type, (J daily, M monthly, Q every two weeks). This code can obtained using the table frequency field payroll_code.")
   *   (name="contract_type", nullable=false, requirements="([0-9])", strict=true, description="Contract type of the employee, this can be found in the table contract_type, field payroll_code.")
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
    $regex['start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['start_date'] = false;
    $regex['contract_number'] = '([0-9])+';$mandatory['contract_number'] = false;
    $regex['last_contract_end_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['last_contract_end_date'] = false;
    $regex['worked_hours_days'] = '([0-9])+';$mandatory['worked_hours_days'] = true;
    $regex['payment_method'] = '(CHE|CON|EFE)';$mandatory['payment_method'] = true;
    $regex['liquidation_type'] = '(J|M|Q)';$mandatory['liquidation_type'] = true;
    $regex['contract_type'] = '([0-9])';$mandatory['contract_type'] = true;

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
    $unico['EMP_ANTIGUEDAD_ANT'] = 0; // Default value by SQL Software.
    $unico['EMP_FECHA_INI_CONTRATO'] = $parameters['start_date']; // Same date.
    $unico['EMP_NRO_CONTRATO'] = $parameters['contract_number'];
    $unico['EMP_FECHA_FIN_CONTRATO'] = isset($parameters['last_contract_end_date']) ? $parameters['last_contract_end_date'] : '';
    $unico['EMP_HORAS_TRAB'] = $parameters['worked_hours_days'];
    $unico['EMP_FORMA_PAGO'] = $parameters['payment_method'];
    $unico['EMP_TIPOLIQ'] = $parameters['liquidation_type'];
    $unico['EMP_TIPO_CONTRATO'] = $parameters['contract_type'];
    $unico['EMP_TIPO_SALARIO'] = 1; // Meaning monthly.

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
   *   (name="last_contract_start_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Start day of the last work contract(format: DD-MM-YYYY).")
   *   (name="contract_number", nullable=true, requirements="([0-9])+", description="Employee contract number.")
   *   (name="last_contract_end_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Last work contract termination day(format: DD-MM-YYYY).")
   *   (name="worked_hours_days", nullable=true, requirements="([0-9])+", description="Number of hours worked on a day.")
   *   (name="payment_method", nullable=true, requirements="(CHE|CON|EFE)", strict=false, description="Code of payment method(CHE, CON, EFE). This code can be obtained using the table pay_type, field payroll_code.")
   *   (name="liquidation_type", nullable=true, requirements="(J|M|Q)", strict=false, description="Liquidation type, (J daily, M monthly, Q every two weeks). This code can obtained using the table frequency field payroll_code.")
   *   (name="contract_type", nullable=true, requirements="([0-9])", strict=false, description="Contract type of the employee, this can be found in the table contract_type, field payroll_code.")
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
    $regex['last_contract_start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['last_contract_start_date'] = false;
    $regex['contract_number'] = '([0-9])+';$mandatory['contract_number'] = false;
    $regex['last_contract_end_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['last_contract_end_date'] = false;
    $regex['worked_hours_days'] = '([0-9])+';$mandatory['worked_hours_days'] = false;
    $regex['payment_method'] = '(CHE|CON|EFE)';$mandatory['payment_method'] = false;
    $regex['liquidation_type'] = '(J|M|Q)';$mandatory['liquidation_type'] = false;
    $regex['salary_type'] = '([0-9])';$mandatory['salary_type'] = false;
    $regex['contract_type'] = '([0-9])';$mandatory['contract_type'] = false;

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
    $unico['EMP_FECHA_INI_CONTRATO'] =  isset($parameters['start_date']) ? $parameters['start_date'] : $info['EMP_FECHA_INGRESO'];
    $unico['EMP_NRO_CONTRATO'] =  isset($parameters['contract_number']) ? $parameters['contract_number'] : $info['EMP_NRO_CONTRATO'];
    $unico['EMP_FECHA_FIN_CONTRATO'] =  isset($parameters['last_contract_end_date']) ? $parameters['last_contract_end_date'] : $info['EMP_FECHA_FIN_CONTRATO'];
    $unico['EMP_HORAS_TRAB'] =  isset($parameters['worked_hours_days']) ? $parameters['worked_hours_days'] : $info['EMP_HORAS_TRAB'];
    $unico['EMP_FORMA_PAGO'] =  isset($parameters['payment_method']) ? $parameters['payment_method'] : $info['EMP_FORMA_PAGO'];
    $unico['EMP_TIPOLIQ'] =  isset($parameters['liquidation_type']) ? $parameters['liquidation_type'] : $info['EMP_TIPOLIQ'];
    $unico['EMP_TIPO_SALARIO'] =  isset($parameters['salary_type']) ? $parameters['salary_type'] : $info['EMP_TIPO_SALARIO'];
    $unico['EMP_TIPO_CONTRATO'] =  isset($parameters['contract_type']) ? $parameters['contract_type'] : $info['EMP_TIPO_CONTRATO'];
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
    $regex['value'] = '([0-9])+(.[0-9]+)?';$mandatory['value'] = true;

    $this->validateParamters($parameters, $regex, $mandatory);

    $content = array();
    $unico = array();
    $unico['TIPOCON'] = 0;
    $unico['EMP_CODIGO'] = $parameters['employee_id'];
    $unico['CON_CODIGO'] = 1; // 1 is salary, it is always our case.
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
    $regex['value'] = '([0-9])+(.[0-9]+)?';$mandatory['value'] = false;

    $this->validateParamters($parameters, $regex, $mandatory);

    $content = array();
    $unico = array();
    $info = $this->getFixedConceptsAction($parameters['employee_id'])->getData();

    $unico['TIPOCON'] = 1;
    $unico['EMP_CODIGO'] =  isset($parameters['employee_id']) ? $parameters['employee_id'] : $info['EMP_CODIGO'];
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
   *    (name="entity_type_code", nullable=false, requirements="([A-Za-z])+", strict=true, description="Code of the entity type as described by sql software, it can be found in the table entity_type, field payroll_code")
   *    (name="coverage_code", nullable=false, requirements="([0-9])+", strict=true, description="Code of the coverage as described by sql software, it can be found in the table position field payroll_coverage_code, if it is an ARP, and in entity for AFP under payroll_code. For EPS, it should always be used the code 1, meaning that it is individual.")
   *    (name="entity_code", nullable=false, requirements="([0-9])+", description="Code of the entity as described by sql software, it is found in the table entity, under payroll_code")
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
   *    (name="entity_type_code", nullable=true, requirements="([A-Za-z])+", description="Code of the entity type as described by sql software, it can be found in the table entity_type, field payroll_code")
   *    (name="coverage_code", nullable=true, requirements="([0-9])+", description="Code of the coverage as described by sql software, it can be found in the table position field payroll_coverage_code, if it is an ARP, and in entity for AFP under payroll_code. For EPS, it should always be used the code 1, meaning that it is individual.")
   *    (name="entity_code", nullable=true, requirements="([0-9])+", description="Code of the entity as described by sql software, it is found in the table entity, under payroll_code")
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
   *    (name="novelty_concept_id", nullable=false, requirements="([0-9])+", strict=true, description="Code of the concept as provided by SQL, it can be found in the table novelty_type, under payroll_code")
   *    (name="novelty_value", nullable=true, requirements="([0-9])+(.[0-9]+)?", description="Value in COP of the novelty, is optional")
   *    (name="unity_numbers", nullable=true, requirements="([0-9])+", strict=true, description="Number of units of the novelty, it can be in hours or days, depending on the novelty.")
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
    $regex['unity_numbers'] = '([0-9])+';$mandatory['unity_numbers'] = false;
    $regex['novelty_start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['novelty_start_date'] = false;
    $regex['novelty_end_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['novelty_end_date'] = false;

    $this->validateParamters($parameters, $regex, $mandatory);

    $content = array();
    $unico = array();

    $unico['TIPOCON'] = 0;
    $unico['EMP_CODIGO'] = $parameters['employee_id'];
    $unico['CON_CODIGO'] = $parameters['novelty_concept_id'];
    $unico['NOV_VALOR_LOCAL'] = isset($parameters['novelty_value']) ? $parameters['novelty_value'] : "";
    $unico['FLIQ_CODIGO'] = 'NOC'; // It means novedad ocasional, it's always the same.
    $unico['NOV_UNIDADES'] = isset($parameters['unity_numbers']) ? $parameters['unity_numbers'] : "";
    $unico['NOV_FECHA_DESDE_CAUSA'] = isset($parameters['novelty_start_date']) ? $parameters['novelty_start_date'] : "";
    $unico['NOV_FECHA_HASTA_CAUSA'] = isset($parameters['novelty_end_date']) ? $parameters['novelty_end_date'] : "";

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
   *    (name="novelty_concept_id", nullable=true, requirements="([0-9])+", description="Code of the concept as provided by SQL, it can be found in the table novelty_type, under payroll_code")
   *    (name="novelty_value", nullable=true, requirements="([0-9])+(.[0-9]+)?", description="Value in COP of the novelty, is optional")
   *    (name="liquidation_type_id", nullable=true, requirements="([0-9])+", strict=true, description="Code of the liquidation type")
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
      $unico['NOVFECHALIQ'] = $liquidation_date;

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
     *    (name="novelty_concept_id", nullable=false, requirements="([0-9])+", strict=true, description="Code of the concept as provided by SQL, it can be found in the table novelty_type, under payroll_code")
     *    (name="novelty_value", nullable=false, requirements="([0-9])+", description="Value in COP of the novelty, is optional, and is the monthly value.")
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
      $unico['NOVF_FECHA_FINAL'] = isset($parameters['novelty_end_date']) ? $parameters['novelty_end_date'] : "";

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
     *    (name="novelty_concept_id", nullable=true, requirements="([0-9])+", description="Code of the concept as provided by SQL, it can be found in the table novelty_type, under payroll_code")
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
     *    (name="absenteeism_type_id", nullable=false, requirements="([0-9])+", strict=true, description="Code of the type of absenteeism as provided by SQL, it can be found on the table novelty_type, under payroll_code, there is a column  absenteeism_or_novelty, to get if it can be used here.")
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
      $unico['AUS_ESTADO'] = isset($parameters['absenteeism_state']) ? $parameters['absenteeism_state']: "";

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
        $unico['AUSESTADO'] = $state;

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
     *    (name="contract_type_id", nullable=false, requirements="([0-9])+", strict=true, description="Contract type of the employee, this can be found in the table contract_type, field payroll_code.")
     *    (name="contract_start_date", nullable=false, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Day the absenteeism starts(format: DD-MM-YYYY)")
     *    (name="employee_contract_number", nullable=true, requirements="([0-9])+", description="Number of the emplyee contract.")
     *    (name="employee_leaving_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Retirement day.")
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
      $regex['employee_contract_number'] = '([0-9])+';$mandatory['employee_contract_number'] = false;
      $regex['employee_leaving_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['employee_leaving_date'] = false;



      $this->validateParamters($parameters, $regex, $mandatory);

      $content = array();
      $unico = array();

      $unico['TIPOCON'] = 0;
      $unico['EMP_CODIGO'] = $parameters['employee_id'];
      $unico['TCO_CODIGO'] = $parameters['contract_type_id'];
      $unico['HPRO_FECHA_INGRESO'] = $parameters['contract_start_date'];
      $unico['MPRO_CODIGO'] = 4; // It is Boss request, which we will use it by default.
      $unico['HPRO_NRO_CONTRATO'] = isset($parameters['employee_contract_number']) ? $parameters['employee_contract_number'] : "";
      $unico['HPRO_EMP_FECHA_RETIRO'] = isset($parameters['employee_leaving_date']) ? $parameters['employee_leaving_date'] : "";

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
     *    (name="employee_id", nullable=false, requirements="([0-9])+", strict=true, description="Employee id")
     *    (name="contract_type_id", nullable=true, requirements="([0-9])+", strict=true, description="Contract type of the employee, this can be found in the table contract_type, field payroll_code.")
     *    (name="contract_start_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Day the absenteeism starts(format: DD-MM-YYYY)")
     *    (name="extension_reason_id", nullable=true, requirements="(.*)", strict=true, description="Reason of the extension as described by SQL, default is 4, boss request.")
     *    (name="employee_contract_number", nullable=true, requirements="([0-9])+", description="Number of the emplyee contract.")
     *    (name="employee_leaving_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Retirement day.")
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

    /* Here starts the third part delivered by SQL Software. */

    /**
     * Gets the general payroll.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets the general payroll.",
     *   statusCodes = {
     *     200 = "OK",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Not Found"
     *   }
     * )
     *
     * @param Int $employeeId The id of the employee to be queried.
     * @param Int $period Period of the liquidation, 2 or 4 if it is bimonthly,
     *        depending on the week to be paid, monthly always 4.
     * @param Int $month Month of the liquidation.
     * @param Int $year Year of the liquidation.
     *
     * @return View
     */
    public function getGeneralPayrollAction($employeeId, $period=null,
                                            $month=null, $year=null)
    {
      $content = array();
      $unico = array();

      $unico['EMPCODIGO'] = $employeeId;
      if($period)
        $unico['NOMI_PERIODO'] = $period;
      if($month)
        $unico['NOMI_MES'] = $month;
      if($year)
        $unico['NOMI_ANO'] = $year;

      $content[] = $unico;
      $parameters = array();

      $parameters['inInexCod'] = '616';
      $parameters['clXMLSolic'] = $this->createXml($content, 616, 2);

      /** @var View $res */
      $responseView = $this->callApi($parameters);

      return $responseView;
    }

    /**
     * Gets the general payroll.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets the general payroll.",
     *   statusCodes = {
     *     200 = "OK",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Not Found"
     *   }
     * )
     *
     * @param Int $employeeId The id of the employee to be queried.
     * @param Int $period Period of the liquidation, 2 or 4 if it is bimonthly,
     *        depending on the week to be paid, monthly always 4.
     * @param Int $month Month of the liquidation.
     * @param Int $year Year of the liquidation.
     *
     * @return View
     */
    public function getGeneralCumulativeAction($employeeId, $period=null,
                                                $month=null, $year=null)
    {
      $content = array();
      $unico = array();

      $unico['EMPCODIGO'] = $employeeId;
      $unico['NOMI_PERIODO'] = $period;
      $unico['NOMI_MES'] = $month;
      $unico['NOMI_ANO'] = $year;

      $content[] = $unico;
      $parameters = array();

      $parameters['inInexCod'] = '617';
      $parameters['clXMLSolic'] = $this->createXml($content, 617, 2);

      /** @var View $res */
      $responseView = $this->callApi($parameters);

      return $responseView;
    }

    /**
     * Adds final liquidation parameters.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Adds final liquidation parameters.",
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
     *    (name="username", nullable=false, requirements="(.*)", strict=true, description="Username of the employer.")
     *    (name="year", nullable=false, requirements="([0-9])+", strict=true, description="Year of the process execution(format: DD-MM-YYYY)")
     *    (name="month", nullable=false, requirements="([0-9])+", strict=true, description="Month of the process execution(format: DD-MM-YYYY)")
     *    (name="period", nullable=false, requirements="([0-9])+", strict=true, description="Period of the process execution(format: DD-MM-YYYY)")
     *    (name="cutDate", nullable=false, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Date of the cut for the process execution(format: DD-MM-YYYY).")
     *    (name="processDate", nullable=false, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Date of the of the process execution(format: DD-MM-YYYY)")
     *    (name="retirementCause", nullable=false, requirements="([0-9])+", strict=true, description="ID of the retirement cause.")//TODO: Complete comment saying where to find the information.
     *
     * @return View
     */
    public function postAddFinalLiquidationParametersAction(Request $request)
    {
      $parameters = $request->request->all();
      $regex = array();
      $mandatory = array();
      // Set all the parameters info.
      $regex['employee_id'] = '([0-9])+';$mandatory['employee_id'] = true;
      $regex['username'] = '(.*)';$mandatory['username'] = true;
      $regex['year'] = '([0-9])+';$mandatory['year'] = true;
      $regex['month'] = '([0-9])+';$mandatory['month'] = true;
      $regex['period'] = '([0-9])+';$mandatory['period'] = true;
      $regex['cutDate'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['cutDate'] = true;
      $regex['processDate'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['processDate'] = true;
      $regex['retirementCause'] = '([0-9])+';$mandatory['retirementCause'] = true;



      $this->validateParamters($parameters, $regex, $mandatory);

      $content = array();
      $unico = array();

      $unico['TIPOCON'] = 0;
      $unico['EMP_CODIGO'] = $parameters['employee_id'];
      $unico['USERNAME'] = $parameters['username'];
      $unico['PDEF_ANO'] = $parameters['year'];
      $unico['PDEF_MES'] = $parameters['month'];
      $unico['PDEF_PERIODO'] = $parameters['period'];
      $unico['PDEF_FECORTE'] = $parameters['cutDate'];
      $unico['PDEF_FEPAGO'] = $parameters['processDate'];
      $unico['CAUSA_RETIRO'] = $parameters['retirementCause'];


      $content[] = $unico;
      $parameters = array();
      $parameters['inInexCod'] = '620';
      $parameters['clXMLSolic'] = $this->createXml($content, 620);

      /** @var View $res */
      $responseView = $this->callApi($parameters);

      return $responseView;
    }

    /**
     * Modify final liquidation parameters.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Modify final liquidation parameters.",
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
     *    (name="username", nullable=true, requirements="(.*)", strict=true, description="Username of the person generating the parameters.")
     *    (name="year", nullable=true, requirements="([0-9])+", strict=true, description="Year of the process execution(format: DD-MM-YYYY)")
     *    (name="month", nullable=true, requirements="([0-9])+", strict=true, description="Month of the process execution(format: DD-MM-YYYY)")
     *    (name="period", nullable=true, requirements="([0-9])+", strict=true, description="Period of the process execution(format: DD-MM-YYYY)")
     *    (name="cutDate", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Date of the cut for the process execution(format: DD-MM-YYYY).")
     *    (name="processDate", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Date of the of the process execution(format: DD-MM-YYYY)")
     *    (name="retirementCause", nullable=true, requirements="([0-9])+", strict=true, description="ID of the retirement cause.")
     *
     * @return View
     */
    public function postModifyFinalLiquidationParametersAction(Request $request)
    {
      $parameters = $request->request->all();
      $regex = array();
      $mandatory = array();
      // Set all the parameters info.
      $regex['employee_id'] = '([0-9])+';$mandatory['employee_id'] = true;
      $regex['username'] = '(.*)';$mandatory['username'] = false;
      $regex['year'] = '([0-9])+';$mandatory['year'] = false;
      $regex['month'] = '([0-9])+';$mandatory['month'] = false;
      $regex['period'] = '([0-9])+';$mandatory['period'] = false;
      $regex['cutDate'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['cutDate'] = false;
      $regex['processDate'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['processDate'] = false;
      $regex['retirementCause'] = '([0-9])+';$mandatory['retirementCause'] = false;

      $this->validateParamters($parameters, $regex, $mandatory);

      $content = array();
      $unico = array();

      $info = $this->getFinalLiquidationParametersAction($parameters['employee_id'])->getData();

      $unico['TIPOCON'] = 1;
      $unico['USERNAME'] = isset($parameters['username']) ? $parameters['username'] : $info['USERNAME'];
      $unico['PDEF_ANO'] = isset($parameters['year']) ? $parameters['year'] : $info['PDEF_ANO'];
      $unico['PDEF_MES'] = isset($parameters['month']) ? $parameters['month'] : $info['PDEF_MES'];
      $unico['PDEF_PERIODO'] = isset($parameters['period']) ? $parameters['period'] : $info['PDEF_PERIODO'];
      $unico['PDEF_FECORTE'] = isset($parameters['cutDate']) ? $parameters['cutDate'] : $info['PDEF_FECORTE'];
      $unico['PDEF_FEPAGO'] = isset($parameters['processDate']) ? $parameters['processDate'] : $info['PDEF_FEPAGO'];
      $unico['CAUSA_RETIRO'] = isset($parameters['retirementCause']) ? $parameters['retirementCause'] : $info['CAUSA_RETIRO'];


      $content[] = $unico;
      $parameters = array();
      $parameters['inInexCod'] = '620';
      $parameters['clXMLSolic'] = $this->createXml($content, 620);

      /** @var View $res */
      $responseView = $this->callApi($parameters);

      return $responseView;
    }

    /**
     * Gets the final liquidation parameteres.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets the final liquidation parameteres.",
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
    public function getFinalLiquidationParametersAction($employeeId)
    {
      $content = array();
      $unico = array();

      $unico['EMPCODIGO'] = $employeeId;

      $content[] = $unico;
      $parameters = array();

      $parameters['inInexCod'] = '621';
      $parameters['clXMLSolic'] = $this->createXml($content, 621, 2);

      /** @var View $res */
      $responseView = $this->callApi($parameters);

      return $responseView;
    }

    /**
     * Adds parameters of employee liquidation(vacations).<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Adds parameters of employee liquidation(vacations).",
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
     *    (name="exit_date", nullable=false, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Exit day of the period to be enjoyed by the employee.")
     *    (name="period", nullable=false, requirements="([0-9])+", strict=true, description="Period to be inserted in the cumulatives.")
     *    (name="month", nullable=false, requirements="([0-9])+", strict=true, description="Month when it is inserted")
     *    (name="year", nullable=false, requirements="([0-9])+", strict=true, description="Day when it is inserted")
     *    (name="payment_date", nullable=false, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Date for payment of the vacations(format: DD-MM-YYYY).")
     *    (name="payment_method", nullable=true, requirements="(.)*", strict=true, description="How the vacations are going to be payed, accoirding to SQL software codes.")
     *    (name="cut_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Cut date for averages(format: DD-MM-YYYY).")
     *    (name="days", nullable=true, requirements="([0-9])+", strict=true, description="Days to be paid in money.")
     *    (name="calendar_days", nullable=true, requirements="([0-9])+", strict=true, description="Calendar days to be enjoyed.")
     *    (name="days_time", nullable=true, requirements="([0-9])+", strict=true, description="Days payed in time.")
     *
     * @return View
     */
    public function postAddVacationParametersAction(Request $request)
    {
      $parameters = $request->request->all();
      $regex = array();
      $mandatory = array();
      // Set all the parameters info.
      $regex['employee_id'] = '([0-9])+';$mandatory['employee_id'] = true;
      $regex['exit_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory[''] = true;
      $regex['period'] = '([0-9])+';$mandatory['period'] = true;
      $regex['month'] = '([0-9])+';$mandatory['month'] = true;
      $regex['year'] = '([0-9])+';$mandatory['year'] = true;
      $regex['payment_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['payment_date'] = true;
      $regex['payment_method'] = '(.)*';$mandatory['payment_method'] = false;
      $regex['cut_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['cut_date'] = false;
      $regex['days'] = '([0-9])+';$mandatory['days'] = false;
      $regex['calendar_days'] = '([0-9])+';$mandatory['calendar_days'] = false;
      $regex['days_time'] = '([0-9])+';$mandatory['days_time'] = false;

      $this->validateParamters($parameters, $regex, $mandatory);

      $content = array();
      $unico = array();

      $unico['EMP_CODIGO'] = isset($parameters['employee_id']) ? $parameters[''] : '';


      $unico['TIPOCON'] = 0;
      $unico['EMP_CODIGO'] = $parameters['employee_id'];
      $unico['PARV_FECHA_SALIDA'] = $parameters['exit_date'];
      $unico['PARV_PERIODO'] = $parameters['period'];
      $unico['PARV_MES'] = $parameters['month'];
      $unico['PARV_ANO'] = $parameters['year'];
      $unico['PARV_FEC_PAGO'] = $parameters['payment_date'];
      $unico['PARV_FORMA_PAGO'] = isset($parameters['payment_method']) ? $parameters['payment_method'] : '';
      $unico['PARV_FEC_CORTE'] = isset($parameters['cut_date']) ? $parameters['cut_date'] : '';
      $unico['PARV_DIAS_DINERO'] = isset($parameters['days']) ? $parameters['days'] : '';
      $unico['PARV_DIAS_CALENDARIO'] = isset($parameters['calendar_days']) ? $parameters['calendar_days'] : '';
      $unico['PARV_DIAS_TIEMPO'] = isset($parameters['days_time']) ? $parameters['days_time'] : '';

      $content[] = $unico;
      $parameters = array();
      $parameters['inInexCod'] = '625';
      $parameters['clXMLSolic'] = $this->createXml($content, 625);

      /** @var View $res */
      $responseView = $this->callApi($parameters);

      return $responseView;
    }

    /**
     * Modify parameters of employee liquidation(vacations).<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Modify parameters of employee liquidation(vacations).",
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
     *    (name="exit_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Exit day of the period to be enjoyed by the employee.")
     *    (name="period", nullable=true, requirements="([0-9])+", strict=true, description="Period to be inserted in the cumulatives.")
     *    (name="month", nullable=true, requirements="([0-9])+", strict=true, description="Month when it is inserted")
     *    (name="year", nullable=true, requirements="([0-9])+", strict=true, description="Day when it is inserted")
     *    (name="payment_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Date for payment of the vacations(format: DD-MM-YYYY).")
     *    (name="payment_method", nullable=true, requirements="(.)*", strict=true, description="How the vacations are going to be payed, accoirding to SQL software codes.")
     *    (name="cut_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Cut date for averages(format: DD-MM-YYYY).")
     *    (name="days", nullable=true, requirements="([0-9])+", strict=true, description="Days to be paid in money.")
     *    (name="calendar_days", nullable=true, requirements="([0-9])+", strict=true, description="Calendar days to be enjoyed.")
     *    (name="days_time", nullable=true, requirements="([0-9])+", strict=true, description="Days payed in time.")     *
     * @return View
     */
    public function postModifyVacationParametersAction(Request $request)
    {
      $parameters = $request->request->all();
      $regex = array();
      $mandatory = array();
      // Set all the parameters info.
      $regex['employee_id'] = '([0-9])+';$mandatory['employee_id'] = true;
      $regex['exit_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory[''] = false;
      $regex['period'] = '([0-9])+';$mandatory['period'] = false;
      $regex['month'] = '([0-9])+';$mandatory['month'] = false;
      $regex['year'] = '([0-9])+';$mandatory['year'] = false;
      $regex['payment_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['payment_date'] = false;
      $regex['payment_method'] = '(.)*';$mandatory['payment_method'] = false;
      $regex['cut_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';$mandatory['cut_date'] = false;
      $regex['days'] = '([0-9])+';$mandatory['days'] = false;
      $regex['calendar_days'] = '([0-9])+';$mandatory['calendar_days'] = false;
      $regex['days_time'] = '([0-9])+';$mandatory['days_time'] = false;

      $this->validateParamters($parameters, $regex, $mandatory);

      $content = array();
      $unico = array();

      $info = $this->getVacationParametersAction($parameters['employee_id'])->getData();

      $unico['USERNAME'] = isset($parameters['username']) ? $parameters[''] : $info[''];


      $unico['TIPOCON'] = 1;
      $unico['EMP_CODIGO'] = isset($parameters['employee_id']) ? $parameters['employee_id'] : $info['EMP_CODIGO'];
      $unico['PARV_FECHA_SALIDA'] = isset($parameters['exit_date']) ? $parameters['exit_date'] : $info['PARV_FECHA_SALIDA'];
      $unico['PARV_PERIODO'] = isset($parameters['period']) ? $parameters['period'] : $info['PARV_PERIODO'];
      $unico['PARV_MES'] = isset($parameters['month']) ? $parameters['month'] : $info['PARV_MES'];
      $unico['PARV_ANO'] = isset($parameters['year']) ? $parameters['year'] : $info['PARV_ANO'];
      $unico['PARV_FEC_PAGO'] = isset($parameters['payment_date']) ? $parameters['payment_date'] : $info['PARV_FEC_PAGO'];
      $unico['PARV_FORMA_PAGO'] = isset($parameters['payment_method']) ? $parameters['payment_method'] : $info['PARV_FORMA_PAGO'];
      $unico['PARV_FEC_CORTE'] = isset($parameters['cut_date']) ? $parameters['cut_date'] : $info['PARV_FEC_CORTE'];
      $unico['PARV_DIAS_DINERO'] = isset($parameters['days']) ? $parameters['days'] : $info['PARV_DIAS_DINERO'];
      $unico['PARV_DIAS_CALENDARIO'] = isset($parameters['calendar_days']) ? $parameters['calendar_days'] : $info['PARV_DIAS_CALENDARIO'];
      $unico['PARV_DIAS_TIEMPO'] = isset($parameters['days_time']) ? $parameters['days_time'] : $info['PARV_DIAS_TIEMPO'];

      $content[] = $unico;
      $parameters = array();
      $parameters['inInexCod'] = '625';
      $parameters['clXMLSolic'] = $this->createXml($content, 625);

      /** @var View $res */
      $responseView = $this->callApi($parameters);

      return $responseView;
    }

    /**
     * Gets the parameters for vacation liquidation.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets the parameters for vacation liquidation.",
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
    public function getVacationParametersAction($employeeId)
    {
      $content = array();
      $unico = array();

      $unico['EMPCODIGO'] = $employeeId;

      $content[] = $unico;
      $parameters = array();

      $parameters['inInexCod'] = '626';
      $parameters['clXMLSolic'] = $this->createXml($content, 626, 2);

      /** @var View $res */
      $responseView = $this->callApi($parameters);

      return $responseView;
    }

    /**
     * Gets the history vacation liquidation.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets the history vacation liquidation.",
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
    public function getVacationHistoryAction($employeeId)
    {
      $content = array();
      $unico = array();

      $unico['EMPCODIGO'] = $employeeId;

      $content[] = $unico;
      $parameters = array();

      $parameters['inInexCod'] = '627';
      $parameters['clXMLSolic'] = $this->createXml($content, 627, 2);

      /** @var View $res */
      $responseView = $this->callApi($parameters);

      return $responseView;
    }

    /**
     * Gets pending vacations.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets pending vacations.",
     *   statusCodes = {
     *     200 = "OK",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Not Found"
     *   }
     * )
     *
     * @param Int $employeeId The id of the employee to be queried.
     * @param Int $companyId The id of the employee to be queried.
     *
     * @return View
     */
    public function getPendingVacationsAction($employeeId, $companyId)
    {
      $content = array();
      $unico = array();

      $unico['EMPCODIGO'] = $employeeId;
      $unico['EN1_CODIGO'] = $companyId;

      $content[] = $unico;
      $parameters = array();

      $parameters['inInexCod'] = '628';
      $parameters['clXMLSolic'] = $this->createXml($content, 628, 2);

      /** @var View $res */
      $responseView = $this->callApi($parameters);

      return $responseView;
    }
}
?>
