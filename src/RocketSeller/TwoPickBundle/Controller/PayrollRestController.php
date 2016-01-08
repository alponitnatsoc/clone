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
use DateTime;

//use GuzzleHttp\Psr7\Request;
//use Guzzle\Http\Client;
use GuzzleHttp\Client;

use EightPoints\Bundle\GuzzleBundle;

class PayrollRestController extends FOSRestController
{

  public function getContentRecursive($array, &$result, &$errorCode)
  {
    //die(print_r($array,true));
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
          //die(print_r($val,true));
          foreach($val as $i => $j)
          {
            $content = '';
            if(!is_array($j))
              $content = (String)$j;
            $temp[$i] = $content;
          }
          $result[] = $temp;
        }else
        {
            $this->getContentRecursive($val, $result,$errorCode);
        }
    }
  }

  public function formatResponse($array)
  {
      $group = array();
      $bigGroup = array();
      if(count($array) == 0)return;
      foreach($array[0] as $key => $value)
      {
         if($key == 'END_REG')
         {
           $bigGroup[] = $group;
           $group = array();
         }else
         {
           $group[$key] = $value;

         }
      }
      if(count($bigGroup) > 0)
        return $bigGroup;
      return $group;
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
    //$url_request = "http://localhost:8001/api/public/v1/mock/sql/default";
    $response = null;
    $options = array(
                  'form_params' => $parameters,
                  'timeout'     => $timeout,
                );
    $test = http_build_query($parameters);

    str_replace( "%20", "", $test );
    $test = trim(preg_replace('/\s\s+/', '', $test));
    $response = $client->request('GET', $url_request . '?' . str_replace( "%20", "",urldecode($test)));//, ['query' => urldecode($test)]);
    //die($url_request . '?' . str_replace( "%20", "",urldecode($test)));
    // We parse the xml recieved into an xml object, that we will transform.
    $plain_text = (String)$response->getBody();
    // This two lines is to remove extra text from the respose that breaks the
    // php parser.

    $plain_text = preg_replace('/(\<LogProceso\>((\n)|.)*(\<ERRORQ\>))/', "<LogProceso><ERRORQ>", $plain_text);
    $plain_text = preg_replace('/(\<MensajeRetorno>(?!\<)((\n)|.)*(\<ERRORQ\>))/', "<MensajeRetorno><ERRORQ>", $plain_text);

    //$parsed = new \SimpleXMLElement(((String)$response->getBody()) );
    //die($plain_text);

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
    //die('aaaaa' . print_r($array));

    $result = array();
    $errorCode = 201;
    $this->getContentRecursive($array, $result,$errorCode);
    //die('4444' . print_r($result, true));

    //die('asdfasdfasdfasdf' . print_r($array, true));
    //die(print_r(((String)$response->getBody()),true));
    //die(print_r($parsed,true));
    $view = View::create();
    // We set the error code according to a certain place on the XML returned.
    //die($this->getErrorCode($array));

    if($errorCode == 505 || $errorCode == 605)$errorCode = 404;
    $view->setStatusCode($errorCode);
    // We transform the xml into json.
    $result = $this->formatResponse($result);
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
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @RequestParam(name="employee_id", nullable=false, requirements="([0-9])+", strict=true, description="Employee id, must be provided by us, and must be unique. It can't be the CC.")
   * @RequestParam(name="last_name", nullable=false, requirements="([a-z|A-Z| ])+", strict=true, description="Employee Last name(only one).")
   * @RequestParam(name="first_name", nullable=false, requirements="([a-z|A-Z| ])+", strict=true, description="Employee first name.")
   * @RequestParam(name="document_type", nullable=true, requirements="([a-z|A-Z| ])+", description="Document type on two char format, if null CC will be used.")
   * @RequestParam(name="document", nullable=false, requirements="([0-9])+", strict=true, description="Employee document number")
   * @RequestParam(name="gender", nullable=false, requirements="(MAS|FEM)", strict=true, description="Employee gender(MAS or FEM).")
   * @RequestParam(name="birth_date", nullable=false, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Employee birth day on the format DD-MM-YYYY.")
   * @RequestParam(name="start_date", nullable=false, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Day the employee started working on the comopany(format: DD-MM-YYYY).")
   * @RequestParam(name="days_company_seniority", nullable=false, requirements="([0-9])+", strict=true, description="Previous seniority on days.")
   * @RequestParam(name="last_contract_start_date", nullable=false, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Start day of the last work contract(format: DD-MM-YYYY).")
   * @RequestParam(name="contract_number", nullable=false, requirements="([0-9])+", strict=true, description="Employee contract number.")
   * @RequestParam(name="last_contract_end_date", nullable=false, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Last work contract termination day(format: DD-MM-YYYY).")
   * @RequestParam(name="shift", nullable=false, requirements="([0-9])+", strict=true, description="day(1) or night(2) shift")
   * @RequestParam(name="worked_hours_days", nullable=false, requirements="([0-9])+", strict=true, description="Number of hours worked on a day.")
   * @RequestParam(name="payment_method", nullable=false, requirements="(CHE|CON|EFE)", strict=true, description="Code of payment method(CHE, CON, EFE).")
   * @RequestParam(name="liquidation_type", nullable=false, requirements="(J|M|Q)", strict=true, description="Liquidation type, (J daily, M monthly, Q every two weeks).")
   * @RequestParam(name="salary_type", nullable=false, requirements="([0-9])", strict=true, description="How the employees salary is recorded(monthly 1, daily 2, every two weeks 3, hourly 4).")
   *
   * @return View
   */
  public function postAddEmployeeAction(ParamFetcher $paramFetcher)
  {
    $content = array();
    $unico = array();

    $unico['TIPOCON'] = 0;
    $unico['EMP_CODIGO'] = $paramFetcher->get('employee_id');
    $unico['EMP_APELLIDO1'] = $paramFetcher->get('last_name');
    $unico['EMP_NOMBRE'] = $paramFetcher->get('first_name');
    $unico['EMP_TIPO_IDENTIF'] = $paramFetcher->get('document_type');
    $unico['EMP_CEDULA'] = $paramFetcher->get('document');
    $unico['EMP_SEXO'] = $paramFetcher->get('gender');
    $unico['EMP_FECHA_NACI'] = $paramFetcher->get('birth_date');
    $unico['EMP_FECHA_INGRESO'] = $paramFetcher->get('start_date');
    $unico['EMP_ANTIGUEDAD_ANT'] = $paramFetcher->get('days_company_seniority');
    $unico['EMP_FECHA_INI_CONTRATO'] = $paramFetcher->get('last_contract_start_date');
    $unico['EMP_NRO_CONTRATO'] = $paramFetcher->get('contract_number');
    $unico['EMP_FECHA_FIN_CONTRATO'] = $paramFetcher->get('last_contract_end_date');
    $unico['EMP_JORNADA'] = $paramFetcher->get('shift');
    $unico['EMP_HORAS_TRAB'] = $paramFetcher->get('worked_hours_days');
    $unico['EMP_FORMA_PAGO'] = $paramFetcher->get('payment_method');
    $unico['EMP_TIPOLIQ'] = $paramFetcher->get('liquidation_type');
    $unico['EMP_TIPO_SALARIO'] = $paramFetcher->get('salary_type');

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
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @RequestParam(name="employee_id", nullable=false, requirements="([0-9])+", description="Employee id, must be provided by us, and must be unique. It can't be the CC.")
   * @RequestParam(name="last_name", nullable=true, requirements="([a-z|A-Z| ])+", description="Employee Last name(only one).")
   * @RequestParam(name="first_name", nullable=true, requirements="([a-z|A-Z| ])+", description="Employee first name.")
   * @RequestParam(name="document_type", nullable=true, requirements="([a-z|A-Z| ])+", description="Document type on two char format, if null CC will be used.")
   * @RequestParam(name="document", nullable=true, requirements="([0-9])+", description="Employee document number")
   * @RequestParam(name="gender", nullable=true, requirements="(MAS|FEM)", description="Employee gender(MAS or FEM).")
   * @RequestParam(name="birth_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Employee birth day on the format DD-MM-YYYY.")
   * @RequestParam(name="start_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Day the employee started working on the comopany(format: DD-MM-YYYY).")
   * @RequestParam(name="days_company_seniority", nullable=true, requirements="([0-9])+", description="Previous seniority on days.")
   * @RequestParam(name="last_contract_start_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Start day of the last work contract(format: DD-MM-YYYY).")
   * @RequestParam(name="contract_number", nullable=true, requirements="([0-9])+", description="Employee contract number.")
   * @RequestParam(name="last_contract_end_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Last work contract termination day(format: DD-MM-YYYY).")
   * @RequestParam(name="shift", nullable=true, requirements="([0-9])+", description="day(1) or night(2) shift")
   * @RequestParam(name="worked_hours_days", nullable=true, requirements="([0-9])+", description="Number of hours worked on a day.")
   * @RequestParam(name="payment_method", nullable=true, requirements="(CHE|CON|EFE)", description="Code of payment method(CHE, CON, EFE).")
   * @RequestParam(name="liquidation_type", nullable=true, requirements="(J|M|Q)", description="Liquidation type, (J daily, M monthly, Q every two weeks).")
   * @RequestParam(name="salary_type", nullable=true, requirements="([0-9])", description="How the employees salary is recorded(monthly 1, daily 2, every two weeks 3, hourly 4).")
   *
   * @return View
   */
  public function postModifyEmployeeAction(ParamFetcher $paramFetcher)
  {
    $content = array();
    $unico = array();
    $info = $this->getEmployeeAction($paramFetcher->get('employee_id'))->getData();
    //die(print_r($info,true));
    $unico['TIPOCON'] = 1;
    $unico['EMP_CODIGO'] = $paramFetcher->get('employee_id');
    $unico['EMP_APELLIDO1'] = $paramFetcher->get('last_name') ? $paramFetcher->get('last_name') : $info['EMP_APELLIDO1'];
    $unico['EMP_NOMBRE'] = $paramFetcher->get('first_name') ? $paramFetcher->get('first_name') : $info['EMP_NOMBRE'];
    $unico['EMP_TIPO_IDENTIF'] = $paramFetcher->get('document_type') ? $paramFetcher->get('document_type') : $info['EMP_TIPO_IDENTIF'];
    $unico['EMP_CEDULA'] = $paramFetcher->get('document') ? $paramFetcher->get('document') : $info['EMP_CEDULA'];
    $unico['EMP_SEXO'] = $paramFetcher->get('gender') ? $paramFetcher->get('gender') : $info['EMP_SEXO'];
    $unico['EMP_FECHA_NACI'] = $paramFetcher->get('birth_date') ? $paramFetcher->get('birth_date') : $info['EMP_FECHA_NACI'];
    $unico['EMP_FECHA_INGRESO'] = $paramFetcher->get('start_date') ? $paramFetcher->get('start_date') : $info['EMP_FECHA_INGRESO'];
    $unico['EMP_ANTIGUEDAD_ANT'] = $paramFetcher->get('days_company_seniority') ? $paramFetcher->get('days_company_seniority') : $info['EMP_ANTIGUEDAD_ANT'];
    $unico['EMP_FECHA_INI_CONTRATO'] = $paramFetcher->get('last_contract_start_date') ? $paramFetcher->get('last_contract_start_date') : $info['EMP_FECHA_INI_CONTRATO'];
    $unico['EMP_NRO_CONTRATO'] = $paramFetcher->get('contract_number') ? $paramFetcher->get('contract_number') : $info['EMP_NRO_CONTRATO'];
    $unico['EMP_FECHA_FIN_CONTRATO'] = $paramFetcher->get('last_contract_end_date') ? $paramFetcher->get('last_contract_end_date') : $info['EMP_FECHA_FIN_CONTRATO'];
    $unico['EMP_JORNADA'] = $paramFetcher->get('shift') ? $paramFetcher->get('shift') : $info['EMP_JORNADA'];
    $unico['EMP_HORAS_TRAB'] = $paramFetcher->get('worked_hours_days') ? $paramFetcher->get('worked_hours_days') : $info['EMP_HORAS_TRAB'];
    $unico['EMP_FORMA_PAGO'] = $paramFetcher->get('payment_method') ? $paramFetcher->get('payment_method') : $info['EMP_FORMA_PAGO'];
    $unico['EMP_TIPOLIQ'] = $paramFetcher->get('liquidation_type') ? $paramFetcher->get('liquidation_type') : $info['EMP_TIPOLIQ'];
    $unico['EMP_TIPO_SALARIO'] = $paramFetcher->get('salary_type') ? $paramFetcher->get('salary_type') : $info['EMP_TIPO_SALARIO'];

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
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @RequestParam(name="employee_id", nullable=false, requirements="([0-9])+", strict=true, description="Employee id, must be provided by us, and must be unique. It can't be the CC.")
   * @RequestParam(name="concept_id", nullable=false, requirements="([0-9])+", strict=true, description="ID of the concept as described by SQL Software.")
   * @RequestParam(name="value", nullable=false, requirements="([0-9])+(.[0-9]+)?", strict=true, description="Value of the concept.")
   *
   * @return View
   */
  public function postAddFixedConceptsAction(ParamFetcher $paramFetcher)
  {
    $content = array();
    $unico = array();

    $unico['TIPOCON'] = 0;
    $unico['EMP_CODIGO'] = $paramFetcher->get('employee_id');
    $unico['CON_CODIGO'] = $paramFetcher->get('concept_id');
    $unico['COF_VALOR'] = $paramFetcher->get('value');

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
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @RequestParam(name="employee_id", nullable=false, requirements="([0-9])+", strict=true, description="Employee id, must be provided by us, and must be unique. It can't be the CC.")
   * @RequestParam(name="concept_id", nullable=true, requirements="([0-9])+", description="ID of the concept as described by SQL Software.")
   * @RequestParam(name="value", nullable=true, requirements="([0-9])+(.[0-9]+)?", description="Value of the concept.")
   *
   * @return View
   */
  public function postModifyFixedConceptsAction(ParamFetcher $paramFetcher)
  {
    $content = array();
    $unico = array();

    $unico['TIPOCON'] = 1;
    $unico['EMP_CODIGO'] =  $paramFetcher->get('employee_id') ? $paramFetcher->get('employee_id') : $info[EMP_CODIGO];
    $unico['CON_CODIGO'] = $paramFetcher->get('concept_id') ? $paramFetcher->get('concept_id') : $info[CON_CODIGO];
    $unico['COF_VALOR'] = $paramFetcher->get('value') ? $paramFetcher->get('value') : $info[COF_VALOR];

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

}
?>