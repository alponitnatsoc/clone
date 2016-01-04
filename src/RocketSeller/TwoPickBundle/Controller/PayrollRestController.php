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
  public function callApi($headers, $parameters, $path, $timeout=10)
  {
    //$client = $this->get('guzzle.client.api_rest');
    // $url_request = $this->container->getParameter('novo_payments_url') ;
    $client = new Client();
    // URL used for test porpouses, the line above should be used in production.
    $url_request = "http://SRHADMIN:SRHADMIN@52.3.249.135:9090/WS_Xchange/Kic_Adm_Ice.Pic_Proc_Int_SW_Publ";
    $response = null;
    $options = array(
                  'headers'     => $headers,
                  'form_params' => $parameters,
                  'timeout'     => $timeout,
                );
    $test = http_build_query($parameters);
    //die(urldecode($test));
    // Adding the params mannually according to issue # 113 from guzzle.
    //$response = $client->get($url_request . urldecode($test), $options)->useUrlEncoding(false);;
    //$request = $client->get('http://www.amazon.com?a=1', array('X-Foo' => 'Bar'));
    //die($url_request);
    str_replace( "%20", "", $test );
    $test = trim(preg_replace('/\s\s+/', '', $test));
    $response = $client->request('GET', $url_request . '?' . str_replace( "%20", "",urldecode($test)));//, ['query' => urldecode($test)]);

    //$request = $client->createRequest('GET', $url_request . urldecode($test), []);



    $view = View::create();
    $view->setFormat('xml');
    $view->setStatusCode($response->getStatusCode());
    //die(json_decode((String)$response->getBody(), JSON_UNESCAPED_SLASHES));
    $response_body = (String)$response->getBody();

    $response_array = array();
    $response_array[0] = $response_body;

    // We start the user side error management in case of bad xml.
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string(utf8_encode($response_body), 'SimpleXMLElement', LIBXML_NOCDATA);
    $json = json_encode($xml);
    $array = json_decode($json,TRUE);
    /*libxml_clear_errors();
    libxml_use_internal_errors(false);*/
    if ($xml === false) {
        echo "Error cargando XML\n";
        foreach(libxml_get_errors() as $error) {
            echo "\t", $error->message;
        }
    }

    $view->setData((String)$response->getBody());

    return $view;
  }

  /**
   * It sets the headers for the payments request. Each request method, recieves
   * parameters in case the client wants something different, but in most cases
   * the header is the same, this function sets de default values.
   * @return Array with the header options.
   */
  private function setHeaders()
  {
    $header = array();
    //$username = $this->container->getParameter('nomina_servicio_usuario');
    //$password = $this->container->getParameter('nomina_servicio_contra');

    // TODO(daniel.serrano): Erase this lines and set real user and password.
    $username = 'SRHADMIN';
    $password = 'SRHADMIN';

    $encoded = $username . ':' . $password;
    $encoded = base64_encode($encoded);

    //$header['Authorization'] = 'Basic ' . $encoded;
    return $header;
  }

  /**
   * It creates the XML request based on an asociative array. The xml generated
   * will only work for the SQL requests, since we are using their tag names
   * and format.
   * @return Array with the header options.
   */
  private function createXml($content, $idInterfaz)
  {
    $header = array();
    $answer = "<Interfaz" . $idInterfaz . "Solic>";
    foreach($content as $i)
    {
      $answer .= "<UNICO>";
      foreach($i as $key => $value)
      {
         $answer .= "<" . $key . ">";
         $answer .= $value;
         $answer .= "</" . $key . ">";
      }
      $answer .= "</UNICO>";
    }
    $answer .= "</Interfaz" . $idInterfaz . "Solic>";
    return $answer;
  }

  /**
   * Insert a new client into the payments system.<br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Inserts a new client into the payments system.",
   *   statusCodes = {
   *     201 = "Created",
   *     400 = "Bad Request",
   *     401 = "Unauthorized"
   *   }
   * )
   *
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @return View
   */
  public function postTestAction(ParamFetcher $paramFetcher)
  {
    // This is the asigned path by NovoPayment to this action.
    $path = "/WS_Xchange/Kic_Adm_Ice.Pic_Proc_Int_SW_Publ";
    // Set up the headers to default if none is provided.
    $header = $this->setHeaders();

    $content = array();
    $unico = array();
    //$content[] = array();

    $unico['TIPOCON'] = 1;
    $unico['EMP_CODIGO'] = 1020345201;
    $unico['CON_CODIGO'] = 45;
    $unico['VALOR'] = '';
    $unico['UNIDADES'] = 19;
    $unico['FECHA'] = '2015-01-01';
    $unico['PROD_CODIGO'] = 1;
    $unico['NOV_CONSEC'] = 57190;
    /*
    $xml = '<Interfaz7Solic>
              <UNICO>
                <TIPOCON>1</TIPOCON>
                <EMP_CODIGO>' . 1020345201 . '</EMP_CODIGO>
                <CON_CODIGO>' . 45 . '</CON_CODIGO>
                <VALOR>' . '' . '</VALOR>
                <UNIDADES>' . 19 . '</UNIDADES>
                <FECHA>' . '2015-01-01' . '</FECHA>
                <PROD_CODIGO>' . 1 . '</PROD_CODIGO>
                <NOV_CONSEC>' . 57190 . '</NOV_CONSEC>
              </UNICO>
            </Interfaz7Solic>';
*/
    $content[] = $unico;
    $parameters = array();
    $parameters['inInexCod'] = '07';
    $parameters['clXMLSolic'] = $this->createXml($content, 7);
    die($this->createXml($content, 7));

    /** @var View $res */
    $responseView = $this->callApi($header, $parameters, $path);

    return $responseView;
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
    // This is the asigned path by NovoPayment to this action.
    $path = "/WS_Xchange/Kic_Adm_Ice.Pic_Proc_Int_SW_Publ";
    // Set up the headers to default if none is provided.
    $header = $this->setHeaders();

    $content = array();
    $unico = array();

    $unico['TIPOCON'] = 1;
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
    $parameters['inInexCod'] = '01';
    $parameters['clXMLSolic'] = $this->createXml($content, 1);

    /** @var View $res */
    $responseView = $this->callApi($header, $parameters, $path);

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
    // This is the asigned path by NovoPayment to this action.
    $path = "/WS_Xchange/Kic_Adm_Ice.Pic_Proc_Int_SW_Publ";
    // Set up the headers to default if none is provided.
    $header = $this->setHeaders();

    $content = array();
    $unico = array();

    $unico['EMP_CODIGO'] = $employeeId;

    $content[] = $unico;
    $parameters = array();
    // TODO(daniel.serrano): Change the 07 to 02.
    $parameters['inInexCod'] = '07';
    $parameters['clXMLSolic'] = $this->createXml($content, 7);

    /** @var View $res */
    $responseView = $this->callApi($header, $parameters, $path);

    return $responseView;
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
    // This is the asigned path by NovoPayment to this action.
    $path = "/WS_Xchange/Kic_Adm_Ice.Pic_Proc_Int_SW_Publ";
    // Set up the headers to default if none is provided.
    $header = $this->setHeaders();

    $content = array();
    $unico = array();



    $unico['TIPOCON'] = 2;
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
    $parameters['inInexCod'] = '01';
    $parameters['clXMLSolic'] = $this->createXml($content, 1);

    /** @var View $res */
    $responseView = $this->callApi($header, $parameters, $path);

    return $responseView;
  }








}
?>
