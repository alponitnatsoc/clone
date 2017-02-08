<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Employer;
use FOS\RestBundle\Controller\FOSRestController;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
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
use RocketSeller\TwoPickBundle\Traits\LiquidationMethodsTrait;
use RocketSeller\TwoPickBundle\Traits\NoveltyTypeMethodsTrait;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Traits\PayrollMethodsTrait;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;

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
//     use LiquidationMethodsTrait;
//     use NoveltyTypeMethodsTrait;

    public function validateParamters($parameters, $regex, $mandatory)
    {

        foreach ($mandatory as $key => $value) {
            if (array_key_exists($key, $mandatory) &&
                    $mandatory[$key] &&
                    (!array_key_exists($key, $parameters)))
                throw new HttpException(400, "The parameter " . $key . " is empty");

            if (array_key_exists($key, $regex) &&
                    array_key_exists($key, $parameters) &&
                    !preg_match('/^' . $regex[$key] . '$/', $parameters[$key]))
                throw new HttpException(400, "The format of the parameter " .
                $key . " is invalid, it doesn't match" .
                $regex[$key]);

            if (!$mandatory[$key] && (!array_key_exists($key, $parameters)))
                $parameters[$key] = '';
        }
    }

    // It is used to see how many levels of UNICO are, mostly for get general
    // payroll function.
    public function array_depth(array $array)
    {
        $max_depth = 1;

        foreach ($array as $value) {
            if (is_array($value)) {
                if (empty($value))
                    continue;
                $depth = $this->array_depth($value) + 1;

                if ($depth > $max_depth) {
                    $max_depth = $depth;
                }
            }
        }

        return $max_depth;
    }

    public function getContentRecursive($array, &$result, &$errorCode)
    {
        $cuenta = array();
        if (!is_array($array))
            return;
        foreach ($array as $key => $val) {
            if ($key == "ERRORQ") {
                $errorCode = $val;
            }
            if ($key == 'UNICO') {
                $temp = array();
                $count = 0;
                // if solo hay uno dbe ser val.
                $unicos = array();
                // This is because, there are cases with more than one unico, in This
                // cases we have to treat it different.
                if ($this->array_depth($val) == 2) { // Many Unicos.
                    // If there are many we just make it equal.
                    $unicos = $val;
                } else {
                    // If there is just one we make it deeper.
                    $unicos[] = $val;
                }
                $conta = 0;
                foreach ($unicos as $val2) {
                    $temp = array();
                    foreach ($val2 as $i => $j) {
                        $content = '';

                        if ($i == 'END_REG')
                            continue;
                        if (!is_array($j)) {
                            $temp[$i] = (String) $j;
                        } else {
                            // In case is an empty array which means that is an empty text,
                            // We just add it as normal but empty.
                            if (empty($j)) {
                                $temp[$i] = '';
                                continue;
                            }
                            foreach ($j as $index => $text) {
                                if (!(count($temp) > $index)) {
                                    $temp[] = array();
                                }
                                if (is_array($text))
                                    $temp[$index][$i] = '';
                                else
                                    $temp[$index][$i] = $text;
                            }
                        }
                    }
                    if (!empty($temp))
                        $result[] = $temp;
                    $conta++;
                }
            }else {
                $this->getContentRecursive($val, $result, $errorCode);
            }
        }
    }

    public function getNoveltyDetail($payroll_code)
    {
        $NoveltyTypeRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:NoveltyType");
        /** @var $NoveltyType NoveltyType  */
        $NoveltyType = $NoveltyTypeRepo->findOneBy(array('payroll_code' => $payroll_code));

        if ($NoveltyType && !empty($NoveltyType) && $NoveltyType !== null) {
            $serializer = $this->get('jms_serializer');
            $data = $serializer->serialize($NoveltyType, 'json');
            //die("__" . print_r(json_decode($data, true), true));
            //$data = $serializer->deserialize($data, 'RocketSeller\TwoPickBundle\Entity\NoveltyType', 'xml');
            $arreglo = json_decode($data, true);
            if (isset($arreglo['required_documents']))
                unset($arreglo['required_documents']);
            if (isset($arreglo['required_fields']))
                unset($arreglo['required_fields']);
            return $arreglo;
        }
        return false;
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
    public function callApi($parameters, $timeout = 10)
    {

        $client = new Client();


        // We choose the ip based on the environment.

        $ambiente = '';
        if($this->container->hasParameter('ambiente'))
          $ambiente = $this->container->getParameter('ambiente');
        else
          $ambiente = 'desarrollo';

        $ip_environment = '';
        if($ambiente == 'desarrollo')
          $ip_environment = '52.202.135.221'; // Query7Oracle-DEV.
        else
          $ip_environment = '10.0.0.91'; // Query7Oracle.
         $url_request = "http://SRHADMIN:SRHADMIN@";
         $url_request .= $ip_environment;
         $url_request .= ":9090/WS_Xchange/Kic_Adm_Ice.Pic_Proc_Int_SW_Publ";

        $response = null;
        $options = array(
            'form_params' => $parameters,
            'timeout' => $timeout,
        );
        $test = http_build_query($parameters);

        str_replace("%20", "", $test);
        $test = trim(preg_replace('/\s\s+/', '', $test));
//        dump ($url_request . '?' . str_replace( "%20", "",urldecode($test)));
//        die();

        $response = $client->request('GET', $url_request . '?' . str_replace("%20", "", urldecode($test)), ['timeout' => 20]); //, ['query' => urldecode($test)]);
        // die ($url_request . '?' . str_replace( "%20", "",urldecode($test)));
        // We parse the xml recieved into an xml object, that we will transform.
        $plain_text = (String) $response->getBody();

        // This two lines is to remove extra text from the respose that breaks the
        // php parser.
        $plain_text = preg_replace('/(\<LogProceso\>((\n)|.)*(\<ERRORQ\>))/', "<LogProceso><ERRORQ>", $plain_text);
        $plain_text = preg_replace('/(\<MensajeRetorno>(?!\<)((\n)|.)*(\<ERRORQ\>))/', "<MensajeRetorno><ERRORQ>", $plain_text);
        // This line is to put every piece into a different unico, because end_reg
        // doesn'e match the xml standard.
        $plain_text = preg_replace('/\<END_REG\>\<\/END_REG\>/', "</UNICO><UNICO>", $plain_text);
        // TODO(daniel.serrano): Remove this debug lines.
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string(utf8_encode($plain_text), "SimpleXMLElement", LIBXML_NOCDATA);
        if ($xml === false) {
            echo "Failed loading XML\n";
            foreach (libxml_get_errors() as $error) {
                echo "\t", $error->message;
            }
        }

        $json = json_encode($xml);
        $array = json_decode($json, TRUE);

        $result = array();
        $errorCode = 200;
        $this->getContentRecursive($array, $result, $errorCode);
        if (count($result) == 1)
            $result = $result[0];

        $view = View::create();

        if ($errorCode == 505 || $errorCode == 605 || $errorCode == 609 || $errorCode == 603 )
            $errorCode = 404;
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
    private function createXml($content, $idInterfaz, $tipo = 1)
    {
        $header = array();
        $answer = "<Interfaz" . $idInterfaz . "Solic>";
        foreach ($content as $i) {
            // 1 is to insert delete or update 2 is to do a get operation.
            if ($tipo == 1)
                $answer .= "<UNICO>";
            else if ($tipo == 2)
                $answer .= "<Params>";
            foreach ($i as $key => $value) {
                $answer .= "<" . $key . ">";
                $answer .= $value;
                $answer .= "</" . $key . ">";
            }
            if ($tipo == 1)
                $answer .= "</UNICO>";
            else if ($tipo == 2)
                $answer .= "</Params>";
        }
        $answer .= "</Interfaz" . $idInterfaz . "Solic>";
        return $answer;
    }

    /**
     * Insert a new society .<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Inserts a new society in the SQL software, this
     *                  represents the employer.",
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
     *   (name="society_nit", nullable=false, requirements="[0-9]+", strict=true, description="nit of the society, this must be unique, can be something else in natural people.")
     *   (name="society_name", nullable=false, requirements="(.)*", strict=true, description="Name of the society, this can be the same name of the employer.")
     *   (name="society_start_date", nullable=false, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="start day on the format DD-MM-YYYY. It can be the day of registration if person.")
     *   (name="society_mail", nullable=false, requirements="(.)*", strict=true, description="Company mail, it can be the mail registered in symplifica.")
     *
     * @return View
     */
    public function postAddSocietyAction(Request $request)
    {

        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['society_nit'] = '(.)*';
        $mandatory['society_nit'] = true;
        $regex['society_name'] = '(.)*';
        $mandatory['society_name'] = true;
        $regex['society_start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['society_start_date'] = true;
        $regex['society_mail'] = '(.)*';
        $mandatory['society_mail'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);

        $content = array();
        $unico = array();

        $unico['TIPOCON'] = 0;
        $unico['COD_SOCIEDAD'] = '';
        $unico['NOMBRE_SOCIEDAD'] = $parameters['society_name'];
        $unico['SOCIEDAD_NIT'] = $parameters['society_nit'];
        $unico['SOCIEDAD_FECHA_CONSTITUCION  '] = $parameters['society_start_date'];
        $unico['SOC_EMAIL'] = $parameters['society_mail'];

        $content[] = $unico;
        $parameters = array();
        $parameters['inInexCod'] = '649';
        $parameters['clXMLSolic'] = $this->createXml($content, 649);

        /** @var View $res */
        $responseView = $this->callApi($parameters);

        return $responseView;
    }

    /**
     * Modify a new society .<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Modify a new society in the SQL software, this
     *                  represents the employer.",
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
     *   (name="society_id", nullable=false, requirements="[0-9]+", strict=true, description="Id of the society, this must be unique.")
     *   (name="society_name", nullable=true, requirements="(.)*", strict=false, description="Name of the society, this can be the same name of the employer.")
     *   (name="society_nit", nullable=true, requirements="([0-9])+(-[0-9]+)?", strict=false, description="Nit of the society it could be the root or the C.C.")
     *   (name="society_start_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=false, description="start day on the format DD-MM-YYYY. It can be the day of registration if person.")
     *   (name="society_mail", nullable=true, requirements="(.)*", strict=false, description="Company mail, it can be the mail registered in symplifica.")
     *
     * @return View
     */
    public function postModifySocietyAction(Request $request)
    {

        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['society_id'] = '[0-9]+';
        $mandatory['society_id'] = true;
        $regex['society_name'] = '(.)*';
        $mandatory['society_name'] = false;
        $regex['society_nit'] = '(.)*';
        $mandatory['society_nit'] = false;
        $regex['society_start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['society_start_date'] = false;
        $regex['society_mail'] = '(.)*';
        $mandatory['society_mail'] = false;

        $this->validateParamters($parameters, $regex, $mandatory);

        $content = array();
        $unico = array();

        $unico['TIPOCON'] = 1;
        $unico['COD_SOCIEDAD'] = isset($parameters['society_id']) ? $parameters['society_id'] : ['COD_SOCIEDAD'];
        $unico['NOMBRE_SOCIEDAD'] = isset($parameters['society_name']) ? $parameters['society_name'] : ['NOMBRE_SOCIEDAD'];
        $unico['SOCIEDAD_NIT'] = isset($parameters['society_nit']) ? $parameters['society_nit'] : ['SOCIEDAD_NIT'];
        $unico['SOCIEDAD_FECHA_CONSTITUCION  '] = isset($parameters['society_start_date']) ? $parameters['society_start_date'] : ['SOCIEDAD_FECHA_CONSTITUCION'];
        $unico['SOC_EMAIL'] = isset($parameters['society_mail']) ? $parameters['society_mail'] : ['SOC_EMAIL'];

        $content[] = $unico;
        $parameters = array();
        $parameters['inInexCod'] = '649';
        $parameters['clXMLSolic'] = $this->createXml($content, 649);

        /** @var View $res */
        $responseView = $this->callApi($parameters);

        return $responseView;
    }

    /**
     * Gets all the information of the society.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets all the information of a given society.",
     *   statusCodes = {
     *     200 = "OK",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Not Found"
     *   }
     * )
     *
     * @param Int $societyId The id of the society to be queried.
     *
     * @return View
     */
    public function getSocietyAction($societyNit)
    {
        $content = array();
        $unico = array();

        $unico['CODSOCIEDAD'] = "";
        $unico['SOCIEDADNIT'] = $societyNit;

        $content[] = $unico;
        $parameters = array();

        $parameters['inInexCod'] = '653';
        $parameters['clXMLSolic'] = $this->createXml($content, 653, 2);

        /** @var View $res */
        $responseView = $this->callApi($parameters);

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
     *   (name="last_contract_end_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Last work contract termination day, only termnio indefinido(format: DD-MM-YYYY).")
     *   (name="worked_hours_day", nullable=false, requirements="([0-9])+", strict=true, description="Number of hours worked on a day.")
     *   (name="worked_days_week", nullable=false, requirements="([0-9])+", strict=true, description="Number of days worked in a week.")
     *   (name="payment_method", nullable=false, requirements="(CHE|CON|EFE)", strict=true, description="Code of payment method(CHE, CON, EFE). This code can be obtained using the table pay_type, field payroll_code.")
     *   (name="liquidation_type", nullable=false, requirements="(J|M|Q)", strict=true, description="Liquidation type, (J daily, M monthly, Q every two weeks). This code can obtained using the table frequency field payroll_code.")
     *   (name="contract_type", nullable=false, requirements="([0-9])", strict=true, description="Contract type of the employee, this can be found in the table contract_type, field payroll_code.")
     *   (name="transport_aux", nullable=false, requirements="(S|N)", strict=true, description="Weather or not it needs transportation help. Its just a flag SQL looks for it legaly.")
     *   (name="society", nullable=false, requirements="(.)*", strict=true, description="Id of the society(id of the employeer).")
     *   (name="payroll_type", nullable=false, requirements="4|6|1", strict=true, description="Payroll type, 4 for full time 6 part time 1 regular payroll.")
     *   (name="cal_type", nullable=true, requirements="3|2|1", strict=true, description="1 from Monday to Friday, 2 from Monday to Saturday, 3 all the days")
     *
     * @return View
     */
    public function postAddEmployeeAction(Request $request)
    {

        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['employee_id'] = '([0-9])+';
        $mandatory['employee_id'] = true;
        $regex['last_name'] = '(.)+';
        $mandatory['last_name'] = true;
        $regex['first_name'] = '(.)+';
        $mandatory['first_name'] = true;
        $regex['document_type'] = '([a-z|A-Z| ])+';
        $mandatory['document_type'] = true;
        $regex['document'] = '([0-9])+';
        $mandatory['document'] = true;
        $regex['gender'] = '(MAS|FEM)';
        $mandatory['gender'] = true;
        $regex['birth_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['birth_date'] = true;
        $regex['start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['start_date'] = false;
        $regex['contract_number'] = '([0-9])+';
        $mandatory['contract_number'] = false;
        $regex['last_contract_end_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['last_contract_end_date'] = false;
        $regex['worked_hours_day'] = '([0-9])+';
        $mandatory['worked_hours_day'] = true;
        $regex['payment_method'] = '(CHE|CON|EFE)';
        $mandatory['payment_method'] = true;
        $regex['liquidation_type'] = '(J|M|Q)';
        $mandatory['liquidation_type'] = true;
        $regex['contract_type'] = '([0-9])';
        $mandatory['contract_type'] = true;
        $mandatory['salary_type'] = false;
        $regex['transport_aux'] = '(S|N)';
        $mandatory['transport_aux'] = true;
        $regex['society'] = '(.)*';
        $mandatory['society'] = true;
        $regex['payroll_type'] = '4|6|1|7';
        $mandatory['payroll_type'] = true;
        $regex['cal_type'] = '3|2|1|';
        $mandatory['cal_type'] = false;
	     // $regex['worked_days_week'] = '([0-9])+';
	      //$mandatory['worked_days_week'] = false;

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
        $unico['EMP_HORAS_TRAB'] = $parameters['worked_hours_day'];
        $unico['EMP_FORMA_PAGO'] = $parameters['payment_method'];
        $unico['EMP_TIPOLIQ'] = $parameters['liquidation_type'];
        $unico['EMP_TIPO_CONTRATO'] = $parameters['contract_type'];
        $unico['RECIBE_AUX_TRA'] = $parameters['transport_aux'];
        $unico['EMP_SOCIEDAD'] = $parameters['society'];
        //$unico['EMP_TIPO_SALARIO'] = 1; // Meaning monthly. SQL removed this.
        $unico['EMP_TIPO_NOMINA'] = $parameters['payroll_type'];
	    
	      /*if( $parameters['payroll_type'] == 6 ){
	      	$unico['DIAS_LABOR_SEM'] = $parameters['worked_days_week'];
	      }*/
	
        if( $parameters['payroll_type'] == 4 ){
            $unico['EMP_TIP_CAL'] = $parameters['cal_type'];
        }

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
     *   (name="contract_type", nullable=true, requirements="([0-9])", strict=false, description="1 Termino indefinido 2 termino fijo.")
     *   (name="transport_aux", nullable=true, requirements="(S|N)", strict=false, description="Weather or not it needs transportation help, if empty it uses the law.")
     *   (name="society", nullable=true, requirements="(.)*", strict=true, description="Id of the society(id of the employeer).")
     *   (name="payroll_type", nullable=true, requirements="4|6|1", strict=true, description="Payroll type, 4 for full time 6 part time 1 regular payroll.")
     *   (name="cal_type", nullable=true, requirements="3|2|1", strict=true, description="1 from Monday to Friday, 2 from Monday to Saturday, 3 all the days")
     *
     * @return View
     */
    public function postModifyEmployeeAction(Request $request)
    {
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['employee_id'] = '([0-9])+';
        $mandatory['employee_id'] = true;
        $regex['last_name'] = '(.)+';
        $mandatory['last_name'] = false;
        $regex['first_name'] = '(.)+';
        $mandatory['first_name'] = false;
        $regex['document_type'] = '([a-z|A-Z| ])+';
        $mandatory['document_type'] = false;
        $regex['document'] = '([0-9])+';
        $mandatory['document'] = false;
        $regex['gender'] = '(MAS|FEM)';
        $mandatory['gender'] = false;
        $regex['birth_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['birth_date'] = false;
        $regex['start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['start_date'] = false;
        $regex['last_contract_start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['last_contract_start_date'] = false;
        $regex['contract_number'] = '([0-9])+';
        $mandatory['contract_number'] = false;
        $regex['last_contract_end_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['last_contract_end_date'] = false;
        $regex['worked_hours_days'] = '([0-9])+';
        $mandatory['worked_hours_days'] = false;
        $regex['payment_method'] = '(CHE|CON|EFE)';
        $mandatory['payment_method'] = false;
        $regex['liquidation_type'] = '(J|M|Q)';
        $mandatory['liquidation_type'] = false;
        $regex['salary_type'] = '([0-9])';
        $mandatory['salary_type'] = false;
        $regex['contract_type'] = '([0-9])';
        $mandatory['contract_type'] = false;
        $regex['transport_aux'] = '(S|N)';
        $mandatory['transport_aux'] = false;
        $regex['society'] = '(.)*';
        $mandatory['society'] = false;
        $regex['payroll_type'] = '4|6|1';
        $mandatory['payroll_type'] = false;
        $regex['cal_type'] = '3|2|1|';
        $mandatory['cal_type'] = false;

        $this->validateParamters($parameters, $regex, $mandatory);

        $content = array();
        $unico = array();
        $info = $this->getEmployeeAction($parameters['employee_id'])->getData();
	
	      if(!isset($info['RECIBE_AUX_TRA'])) $info['RECIBE_AUX_TRA'] = 'N';

        $unico['TIPOCON'] = 1;
        $unico['EMP_CODIGO'] = $parameters['employee_id'];
        $unico['EMP_APELLIDO1'] = isset($parameters['last_name']) ? $parameters['last_name'] : $info['EMP_APELLIDO1'];
        $unico['EMP_NOMBRE'] = isset($parameters['first_name']) ? $parameters['first_name'] : $info['EMP_NOMBRE'];
        $unico['EMP_TIPO_IDENTIF'] = isset($parameters['document_type']) ? $parameters['document_type'] : $info['EMP_TIPO_IDENTIF'];
        $unico['EMP_CEDULA'] = isset($parameters['document']) ? $parameters['document'] : $info['EMP_CEDULA'];
        $unico['EMP_SEXO'] = isset($parameters['gender']) ? $parameters['gender'] : $info['EMP_SEXO'];
        $unico['EMP_FECHA_NACI'] = isset($parameters['birth_date']) ? $parameters['birth_date'] : $info['EMP_FECHA_NACI'];
        //TODO(andres_ramirez) preguntar si este se manda
//        $unico['EMP_SOCIEDAD'] = isset($parameters['society']) ? $parameters['society'] : $info['EMP_SOCIEDAD'];
        $unico['EMP_FECHA_INGRESO'] = isset($parameters['start_date']) ? $parameters['start_date'] : $info['EMP_FECHA_INGRESO'];
        $unico['EMP_FECHA_INI_CONTRATO'] = isset($parameters['start_date']) ? $parameters['start_date'] : $info['EMP_FECHA_INGRESO'];
        $unico['EMP_FECHA_FIN_CONTRATO'] = isset($parameters['last_contract_end_date']) ? $parameters['last_contract_end_date'] : $info['EMP_FECHA_FIN_CONTRATO'];
        $unico['EMP_NRO_CONTRATO'] = isset($parameters['contract_number']) ? $parameters['contract_number'] : $info['EMP_NRO_CONTRATO'];
        $unico['EMP_JORNADA'] = 1; //Is a must
        $unico['EMP_HORAS_TRAB'] = isset($parameters['worked_hours_days']) ? $parameters['worked_hours_days'] : $info['EMP_HORAS_TRAB'];
        $unico['EMP_FORMA_PAGO'] = isset($parameters['payment_method']) ? $parameters['payment_method'] : $info['EMP_FORMA_PAGO'];
        $unico['EMP_TIPOLIQ'] = isset($parameters['liquidation_type']) ? $parameters['liquidation_type'] : $info['EMP_TIPOLIQ'];
        $unico['EMP_TIPO_SALARIO'] = isset($parameters['salary_type']) ? $parameters['salary_type'] : $info['EMP_TIPO_SALARIO'];
        $unico['RECIBE_AUX_TRA'] = isset($parameters['transport_aux']) ? $parameters['transport_aux'] : $info['RECIBE_AUX_TRA'];
        $unico['EMP_TIPO_NOMINA'] = isset($parameters['payroll_type']) ? $parameters['payroll_type'] : $info['EMP_TIPO_NOMINA'];
        if (isset($info['EMP_TIPO_CONTRATO']))
            $unico['EMP_TIPO_CONTRATO'] = isset($parameters['contract_type']) ? $parameters['contract_type'] : $info['EMP_TIPO_CONTRATO'];
        if( $unico['EMP_TIPO_NOMINA'] == 4 ){
            $unico['EMP_TIP_CAL'] = isset($parameters['cal_type']) ? $parameters['cal_type'] : $info['EMP_TIP_CAL'];
        }else{
            $unico['EMP_TIP_CAL'] = 1;
        }
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
     *    (name="date_change", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Day to apply the salary, it can be the same as the start date.")
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
        $regex['employee_id'] = '([0-9])+';
        $mandatory['employee_id'] = true;
        $regex['value'] = '([0-9])+(.[0-9]+)?';
        $mandatory['value'] = true;
        $regex['date_change'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['date_change'] = false;

        $this->validateParamters($parameters, $regex, $mandatory);

        $content = array();
        $unico = array();
        $unico['TIPOCON'] = 0;
        $unico['EMP_CODIGO'] = $parameters['employee_id'];
        $unico['CON_CODIGO'] = 1; // 1 is salary, it is always our case.
        $unico['COF_VALOR'] = $parameters['value'];
        if (isset($parameters['date_change']))
            $unico['COF_FECHA_CAMBIO'] = $parameters['date_change'];

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
     *    (name="date_change", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Day to apply the salary, it can be the same as the start date.")
     *
     * @return View
     */
    public function postModifyFixedConceptsAction(Request $request)
    {
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['employee_id'] = '([0-9])+';
        $mandatory['employee_id'] = true;
        $regex['value'] = '([0-9])+(.[0-9]+)?';
        $mandatory['value'] = false;
        $regex['date_change'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['date_change'] = false;

        $this->validateParamters($parameters, $regex, $mandatory);

        $content = array();
        $unico = array();
        $info = $this->getFixedConceptsAction($parameters['employee_id'])->getData();

        $unico['TIPOCON'] = 1;
        $unico['EMP_CODIGO'] = isset($parameters['employee_id']) ? $parameters['employee_id'] : $info['EMP_CODIGO'];
        $unico['COF_VALOR'] = isset($parameters['value']) ? $parameters['value'] : $info['COF_VALOR'];
        $unico['CON_CODIGO'] = 1;
        $unico['COF_FECHA_CAMBIO'] = isset($parameters['date_change']) ? $parameters['date_change'] : $info['COF_FECHA_CAMBIO'];

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
     *    (name="coverage_code", nullable=false, requirements="([0-9])+", strict=true, description="Code of the coverage as described by sql software, it can be found in the table position field payroll_coverage_code if it is an ARP.)                                                                                             For EPS, it should always be used the code 1, meaning that it is individual, parafiscal should always be 1 menaing caja de compensacion.")
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
        $regex['employee_id'] = '([0-9])+';
        $mandatory['employee_id'] = true;
        $regex['entity_type_code'] = '([A-Za-z])+';
        $mandatory['entity_type_code'] = true;
        $regex['coverage_code'] = '([0-9])+';
        $mandatory['coverage_code'] = true;
        $regex['entity_code'] = '([0-9])+';
        $mandatory['entity_code'] = true;
        $regex['start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['start_date'] = true;

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
        $regex['employee_id'] = '([0-9])+';
        $mandatory['employee_id'] = true;
        $regex['entity_type_code'] = '([A-Za-z])+';
        $mandatory['entity_type_code'] = false;
        $regex['coverage_code'] = '([0-9])+';
        $mandatory['coverage_code'] = false;
        $regex['entity_code'] = '([0-9])+';
        $mandatory['entity_code'] = false;
        $regex['start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['start_date'] = false;

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
        $employeeId = intval($employeeId);
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
     *    (name="unity_numbers", nullable=false, requirements="([0-9])+", strict=true, description="Number of units of the novelty, it can be in hours or days, depending on the novelty.")
     *    (name="novelty_start_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Day the novelty starts(format: DD-MM-YYYY)")
     *    (name="novelty_end_date", nullable=false, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Day the novelty ends(format: DD-MM-YYYY)")
     *    (name="novelty_base", nullable=false, requirements="([0-9])+(.[0-9]+)?", strict=true, description="Only to be sent for reporting a day in part time.")
     *
     * @return View
     */
    public function postAddNoveltyEmployeeAction(Request $request)
    {
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Remove all the null values from the array.
        $parameters = array_filter($parameters);

        // Set all the parameters info.
        $regex['employee_id'] = '([0-9])+';
        $mandatory['employee_id'] = true;
        $regex['novelty_concept_id'] = '([0-9])+';
        $mandatory['novelty_concept_id'] = true;
        $regex['novelty_value'] = '([0-9])+(.[0-9]+)?';
        $mandatory['novelty_value'] = false;
        $regex['unity_numbers'] = '([0-9])+';
        $mandatory['unity_numbers'] = false;
        $regex['novelty_start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['novelty_start_date'] = false;
        $regex['novelty_end_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['novelty_end_date'] = false;
        $regex['novelty_base'] = '([0-9])+(.[0-9]+)?';
        $mandatory['novelty_base'] = false;

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
        $unico['COD_PROC'] = '1'; // Always process as payroll.
        $unico['USUARIO'] = 'SRHADMIN'; // This may change in the future.
        $unico['NOV_BASE'] = isset($parameters['novelty_base']) ? $parameters['novelty_base'] : "";

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
     *    (name="liquidation_type_id", nullable=true, requirements="([A-Z])+", strict=true, description="Code of the liquidation type")
     *    (name="unity_numbers", nullable=true, requirements="([0-9])+", strict=true, description="Number of units of the novelty")
     *    (name="novelty_start_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Day the novelty starts(format: DD-MM-YYYY)")
     *    (name="novelty_end_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Day the novelty ends(format: DD-MM-YYYY)"
     *    (name="novelty_consec", nullable=true, requirements="([0-9])+", strict=true, description="Identifier used on SQL to identify the novelty")
     *
     * @return View
     */
    public function postModifyNoveltyEmployeeAction(Request $request)
    {

        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['employee_id'] = '([0-9])+';
        $mandatory['employee_id'] = true;
        $regex['novelty_concept_id'] = '([0-9])+';
        $mandatory['novelty_concept_id'] = false;
        $regex['novelty_value'] = '([0-9])+(.[0-9]+)?';
        $mandatory['novelty_value'] = false;
        $regex['liquidation_type_id'] = '([A-Z])+';
        $mandatory['liquidation_type_id'] = false;
        $regex['unity_numbers'] = '([0-9])+';
        $mandatory['unity_numbers'] = false;
        $regex['novelty_start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['novelty_start_date'] = false;
        $regex['novelty_end_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['novelty_end_date'] = false;
        $regex['novelty_consec'] = '([0-9])+';
        $mandatory['novelty_consec'] = false;

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
        $unico['NOV_CONSEC'] = isset($parameters['novelty_consec']) ? $parameters['novelty_consec'] : $info['NOV_CONSEC'];

        $content[] = $unico;
        $parameters = array();
        $parameters['inInexCod'] = '612';
        $parameters['clXMLSolic'] = $this->createXml($content, 612);

        /** @var View $res */
        $responseView = $this->callApi($parameters);

        return $responseView;
    }

    /**
     * Deletes an occasional novelty for an employee.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Deletes an occasional novelty for an employee.",
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
     *    (name="novelty_consec", nullable=false, requirements="([0-9])+", strict=true, description="Identifier used on SQL to identify the novelty")
     *
     * @return View
     */
    public function postDeleteNoveltyEmployeeAction(Request $request)
    {

        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['employee_id'] = '([0-9])+';
        $mandatory['employee_id'] = true;
        $regex['novelty_concept_id'] = '([0-9])+';
        $mandatory['novelty_concept_id'] = false;
        $regex['novelty_value'] = '([0-9])+(.[0-9]+)?';
        $mandatory['novelty_value'] = false;
        $regex['liquidation_type_id'] = '([A-Z])+';
        $mandatory['liquidation_type_id'] = false;
        $regex['unity_numbers'] = '([0-9])+';
        $mandatory['unity_numbers'] = false;
        $regex['novelty_start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['novelty_start_date'] = false;
        $regex['novelty_end_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['novelty_end_date'] = false;
        $regex['novelty_consec'] = '([0-9])+';
        $mandatory['novelty_consec'] = false;

        $this->validateParamters($parameters, $regex, $mandatory);

        $content = array();
        $unico = array();
        $info = $this->getEmployeeNoveltyAction($parameters['employee_id'])->getData();
        if( $info['NOV_CONSEC'] == $parameters['novelty_consec']){
            $use = $info;
        }

        $unico['TIPOCON'] = 2;
        $unico['EMP_CODIGO'] = isset($parameters['employee_id']) ? $parameters['employee_id'] : $use['EMP_CODIGO'];
        $unico['CON_CODIGO'] = isset($parameters['novelty_concept_id']) ? $parameters['novelty_concept_id'] : $use['CON_CODIGO'];
        $unico['NOV_VALOR_LOCAL'] = isset($parameters['novelty_value']) ? $parameters['novelty_value'] : $use['NOV_VALOR_LOCAL'];
        $unico['FLIQ_CODIGO'] = isset($parameters['liquidation_type_id']) ? $parameters['liquidation_type_id'] : $use['FLIQ_CODIGO'];
        $unico['NOV_UNIDADES'] = isset($parameters['unity_numbers']) ? $parameters['unity_numbers'] : $use['NOV_UNIDADES'];
        $unico['NOV_FECHA_DESDE_CAUSA'] = isset($parameters['novelty_start_date']) ? $parameters['novelty_start_date'] : $use['NOV_FECHA_DESDE_CAUSA'];
        $unico['NOV_FECHA_HASTA_CAUSA'] = isset($parameters['novelty_end_date']) ? $parameters['novelty_end_date'] : $use['NOV_FECHA_HASTA_CAUSA'];
        $unico['NOV_CONSEC'] = isset($parameters['novelty_consec']) ? $parameters['novelty_consec'] : $use['NOV_CONSEC'];


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
    public function getEmployeeNoveltyAction($employeeId, $liquidation_date = null)
    {
        $content = array();
        $unico = array();

        $unico['EMPCODIGO'] = $employeeId;
        if ($liquidation_date != null && $liquidation_date != "NULL")
            $unico['NOVFECHALIQ'] = $liquidation_date;
        else
            $unico['NOVFECHALIQ'] = '';


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
     *    (name="novelty_value", nullable=true, requirements="([0-9])+", description="Value in COP of the novelty, is optional, and is the monthly value.")
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
        $regex['employee_id'] = '([0-9])+';
        $mandatory['employee_id'] = true;
        $regex['novelty_concept_id'] = '([0-9])+';
        $mandatory['novelty_concept_id'] = true;
        $regex['novelty_value'] = '([0-9])+';
        $mandatory['novelty_value'] = false;
        $regex['novelty_start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['novelty_start_date'] = true;
        $regex['novelty_end_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['novelty_end_date'] = false;

        $this->validateParamters($parameters, $regex, $mandatory);

        $content = array();
        $unico = array();

        $unico['TIPOCON'] = 0;
        $unico['EMP_CODIGO'] = $parameters['employee_id'];
        $unico['CON_CODIGO'] = $parameters['novelty_concept_id'];
        $unico['NOVF_VALOR'] = isset($parameters['novelty_value']) ? $parameters['novelty_value'] : "";
        $unico['NOVF_FECHA_INICIAL'] = $parameters['novelty_start_date'];
        $unico['NOVF_FECHA_FINAL'] = isset($parameters['novelty_end_date']) ? $parameters['novelty_end_date'] : "";
	      $unico['COD_PROC'] = '1';
	      $unico['USUARIO'] = 'SRHADMIN';

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
        $regex['employee_id'] = '([0-9])+';
        $mandatory['employee_id'] = true;
        $regex['novelty_concept_id'] = '([0-9])+';
        $mandatory['novelty_concept_id'] = false;
        $regex['novelty_value'] = '([0-9])+';
        $mandatory['novelty_value'] = false;
        $regex['novelty_start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['novelty_start_date'] = false;
        $regex['novelty_end_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['novelty_end_date'] = false;

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
    public function getEmployeeFixedNoveltyAction($employeeId, $query_date = null)
    {
        $content = array();
        $unico = array();

        $unico['EMPCODIGO'] = $employeeId;
        if ($query_date != null)
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
     *    (name="absenteeism_end_date", nullable=false, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="End day the absenteeism starts(format: DD-MM-YYYY)")
     *    (name="absenteeism_state", nullable=true, requirements="(ACT|CAN)", strict=true, description="State of the absenteeism ACT active or CAN cancelled")
     *
     * @return View
     */
    public function postAddAbsenteeismEmployeeAction(Request $request)
    {
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Remove all the null values from the array.
        $parameters = array_filter($parameters);
        // Set all the parameters info.
        $regex['employee_id'] = '([0-9])+';
        $mandatory['employee_id'] = true;
        $regex['absenteeism_type_id'] = '([0-9])+';
        $mandatory['absenteeism_type_id'] = true;
        $regex['absenteeism_start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['absenteeism_start_date'] = true;
        $regex['absenteeism_end_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['absenteeism_end_date'] = false;
        $regex['absenteeism_state'] = '(ACT|CAN)';
        $mandatory['absenteeism_state'] = false;

        $this->validateParamters($parameters, $regex, $mandatory);

        $content = array();
        $unico = array();

        $unico['TIPOCON'] = 0;
        $unico['EMP_CODIGO'] = $parameters['employee_id'];
        $unico['TAUS_CODIGO'] = $parameters['absenteeism_type_id'];
        $unico['AUS_FECHA_INICIAL'] = $parameters['absenteeism_start_date'];
        $unico['AUS_FECHA_FINAL'] = $parameters['absenteeism_end_date'];
        $unico['AUS_ESTADO'] = isset($parameters['absenteeism_state']) ? $parameters['absenteeism_state'] : "ACT";
        $unico['COD_PROC'] = '1'; // Always send it as payroll;
        $unico['USUARIO'] = 'SRHADMIN';

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
        $regex['employee_id'] = '([0-9])+';
        $mandatory['employee_id'] = true;
        $regex['absenteeism_type_id'] = '([0-9])+';
        $mandatory['absenteeism_type_id'] = false;
        $regex['absenteeism_start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['absenteeism_start_date'] = false;
        $regex['absenteeism_end_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['absenteeism_end_date'] = false;
        $regex['absenteeism_units'] = '([0-9])+';
        $mandatory['absenteeism_units'] = false;
        $regex['absenteeism_state'] = '(ACT|CAN)';
        $mandatory['absenteeism_state'] = false;

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
    public function getAbsenteeismEmployeeAction($employeeId, $state = null)
    {
        $content = array();
        $unico = array();

        $unico['EMPCODIGO'] = $employeeId;
        if ($state != null)
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
        $regex['employee_id'] = '([0-9])+';
        $mandatory['employee_id'] = true;
        $regex['contract_type_id'] = '([0-9])+';
        $mandatory['contract_type_id'] = true;
        $regex['contract_start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['contract_start_date'] = true;
        $regex['employee_contract_number'] = '([0-9])+';
        $mandatory['employee_contract_number'] = false;
        $regex['employee_leaving_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['employee_leaving_date'] = false;



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
        $regex['employee_id'] = '([0-9])+';
        $mandatory['employee_id'] = true;
        $regex['contract_type_id'] = '([0-9])+';
        $mandatory['contract_type_id'] = false;
        $regex['contract_start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['contract_start_date'] = false;
        $regex['extension_reason_id'] = '(.*)';
        $mandatory['extension_reason_id'] = false;
        $regex['employee_contract_number'] = '([0-9])+';
        $mandatory['employee_contract_number'] = false;
        $regex['employee_leaving_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['employee_leaving_date'] = false;

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
     * @param Boolean mock_final_liquidation Important, only for tests,
     *                do not use in production, tells if final or general payrrol.
     *
     * @return View
     */
    public function getGeneralPayrollAction($employeeId, $period = null, $month = null, $year = null, $mockFinalLiquidation = false)
    {
        // Only in tests, do not use in production!!.
        // TODO(daniel.serrano): Delete this, important!!.
        if ($mockFinalLiquidation)
            $employeeId = $employeeId * -1;

        $content = array();
        $unico = array();

        $unico['EMPCODIGO'] = $employeeId;
        if ($period == 'NULL' || $period == "null")
            $period = null;
        $unico['NOMIPERIODO'] = $period ? : "";
        $unico['NOMIMES'] = $month ? : "";
        $unico['NOMIANO'] = $year ? : "";

        $content[] = $unico;
        $parameters = array();

        $parameters['inInexCod'] = '616';
        $parameters['clXMLSolic'] = $this->createXml($content, 616, 2);

        /** @var View $res */
        $responseView = $this->callApi($parameters);

        $temp = $this->handleView($responseView);
        $data = json_decode($temp->getContent(), true);
        $code = json_decode($temp->getStatusCode(), true);
        $nuevo = array();
        foreach ($data as &$i) {

            // This means there was a problem and only one item was returned.
            $finish = false;
            if(!is_array($i)) {
              $i = json_decode($temp->getContent(), true);
              $finish = true;
            }
            // Go thorug each novelty.
            foreach ($i as $key => $val) {
                if ($key == 'CON_CODIGO') {
                    $i['CON_CODIGO_DETAIL'] = $this->getNoveltyDetail($val);
                }
            }
            if($finish)break;
        }
        $view = View::create();
        $view->setStatusCode($code);
        $view->setData($data);

        return $view;
    }

    /**
     * Gets liquidation external entities, this is useful to know how much does
     * the employer has to pay.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets liquidation external entities, this is useful to
     *                  know how much does the employer has to pay.",
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
    public function getExternalEntitiesLiquidationAction($employeeId)
    {
        $content = array();
        $unico = array();

        $unico['EMPCODIGO'] = $employeeId;

        $content[] = $unico;
        $parameters = array();

        $parameters['inInexCod'] = '619';
        $parameters['clXMLSolic'] = $this->createXml($content, 619, 2);

        /** @var View $res */
        $responseView = $this->callApi($parameters);

        return $responseView;
    }


    /**
     * Gets the general payroll history.<br/>
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
    public function getGeneralCumulativeAction($employeeId, $period = null, $month = null, $year = null)
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
     * Gets the general external entities history.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets the external entities history.",
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
    public function getExternalEntitiesCumulativeAction($employeeId, $period = null, $month = null, $year = null)
    {
        $content = array();
        $unico = array();

        $unico['EMPCODIGO'] = $employeeId;
        $unico['APR_PERIODO'] = $period;
        $unico['APR_MES'] = $month;
        $unico['APR_ANO'] = $year;

        $content[] = $unico;
        $parameters = array();

        $parameters['inInexCod'] = '657';
        $parameters['clXMLSolic'] = $this->createXml($content, 657, 2);

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
        $regex['employee_id'] = '([0-9])+';
        $mandatory['employee_id'] = true;
        $regex['year'] = '([0-9])+';
        $mandatory['year'] = true;
        $regex['month'] = '([0-9])+';
        $mandatory['month'] = true;
        $regex['period'] = '([0-9])+';
        $mandatory['period'] = true;
        $regex['cutDate'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['cutDate'] = true;
        $regex['processDate'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['processDate'] = true;
        $regex['retirementCause'] = '([0-9])+';
        $mandatory['retirementCause'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);

        $content = array();
        $unico = array();

        $unico['TIPOCON'] = 0;
        $unico['EMP_CODIGO'] = $parameters['employee_id'];
        $unico['USERNAME'] = 'SRHADMIN';
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
        $regex['employee_id'] = '([0-9])+';
        $mandatory['employee_id'] = true;
        $regex['year'] = '([0-9])+';
        $mandatory['year'] = false;
        $regex['month'] = '([0-9])+';
        $mandatory['month'] = false;
        $regex['period'] = '([0-9])+';
        $mandatory['period'] = false;
        $regex['cutDate'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['cutDate'] = false;
        $regex['processDate'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['processDate'] = false;
        $regex['retirementCause'] = '([0-9])+';
        $mandatory['retirementCause'] = false;

        $this->validateParamters($parameters, $regex, $mandatory);

        $content = array();
        $unico = array();

        $info = $this->getFinalLiquidationParametersAction($parameters['employee_id'])->getData();

        $unico['TIPOCON'] = 1;
        $unico['USERNAME'] = 'SRHADMIN';
        $unico['EMP_CODIGO'] =  $parameters['employee_id'];
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
		 *    (name="year", nullable=true, requirements="([0-9])+", strict=true, description="Year of the process execution(format: DD-MM-YYYY)")
		 *    (name="month", nullable=true, requirements="([0-9])+", strict=true, description="Month of the process execution(format: DD-MM-YYYY)")
		 *    (name="period", nullable=true, requirements="([0-9])+", strict=true, description="Period of the process execution(format: DD-MM-YYYY)")
		 *    (name="cutDate", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", description="Date of the cut for the process execution(format: DD-MM-YYYY).")
		 *    (name="processDate", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Date of the of the process execution(format: DD-MM-YYYY)")
		 *    (name="retirementCause", nullable=true, requirements="([0-9])+", strict=true, description="ID of the retirement cause.")
		 *
		 * @return View
		 */
		public function postUnprocessFinalLiquidationParametersAction(Request $request)
		{
			$parameters = $request->request->all();
			$regex = array();
			$mandatory = array();
			// Set all the parameters info.
			$regex['employee_id'] = '([0-9])+';
			$mandatory['employee_id'] = true;
			$regex['year'] = '([0-9])+';
			$mandatory['year'] = false;
			$regex['month'] = '([0-9])+';
			$mandatory['month'] = false;
			$regex['period'] = '([0-9])+';
			$mandatory['period'] = false;
			$regex['cutDate'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
			$mandatory['cutDate'] = false;
			$regex['processDate'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
			$mandatory['processDate'] = false;
			$regex['retirementCause'] = '([0-9])+';
			$mandatory['retirementCause'] = false;
			
			$this->validateParamters($parameters, $regex, $mandatory);
			
			$content = array();
			$unico = array();
			
			$info = $this->getFinalLiquidationParametersAction($parameters['employee_id'])->getData();
			
			$unico['TIPOCON'] = 2;
			$unico['USERNAME'] = 'SRHADMIN';
			$unico['EMP_CODIGO'] =  $parameters['employee_id'];
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
     *    (name="number_days", nullable=false, requirements="([0-9]+)", strict=true, description="Return day to the company.(format: DD-MM-YYYY).")
     *    (name="money_days", nullable=true, requirements="([0-9])+", strict=true, description="Days to be paid in money.")
     *
     * @return View
     */
    public function postAddVacationParametersAction(Request $request)
    {
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Remove all the null values from the array.
        $parameters = array_filter($parameters);
        // Set all the parameters info.
        $regex['employee_id'] = '([0-9])+';
        $mandatory['employee_id'] = true;
        $regex['exit_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['exit_date'] = true;
        $regex['number_days'] = '([0-9]+)';
        $mandatory['number_days'] = true;
        $regex['money_days'] = '([0-9])+';
        $mandatory['money_days'] = false;


        $this->validateParamters($parameters, $regex, $mandatory);

        // We first execute the pending days.
        $request2 =  new Request();
        $request2->request->set("employee_id", $parameters['employee_id']);
        $request2->request->set("execution_type", "P");

        $this->postExecutePendingVacationDaysAction($request2);

        $content = array();
        $unico = array();

        $unico['EMP_CODIGO'] = isset($parameters['employee_id']) ? $parameters['employee_id'] : '';


        $unico['TIPOCON'] = 0;
        $unico['EMP_CODIGO'] = $parameters['employee_id'];
        $unico['PARV_FECHA_SALIDA'] = $parameters['exit_date'];
        $unico['PARV_DIAS_TIEMPO'] = $parameters['number_days'];
        $unico['PARV_DIAS_DINERO'] = isset($parameters['money_days']) ? $parameters['money_days'] : '';

        $content[] = $unico;
        $parameters = array();
        $parameters['inInexCod'] = '625';
        $parameters['clXMLSolic'] = $this->createXml($content, 625);

        /** @var View $res */
        $responseView = $this->callApi($parameters);

        return $responseView;
    }

    /**
     * Adds Pending vacation for employees with history.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Adds Pending vacation for employees with history.",
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
     *    (name="pending_days", nullable=false, requirements="([0-9])+(\.[0-9]+)?", strict=true, description="Number of pending vacation days, it can have decimals.")
     *
     * @return View
     */
    public function postAddPendingVacationDaysAction(Request $request)
    {
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['employee_id'] = '([0-9])+';
        $mandatory['employee_id'] = true;
        $regex['pending_days'] = '(-)?([0-9])+(\.[0-9]+)?';
        $mandatory['pending_days'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);

        $content = array();
        $unico = array();

        // $unico['TIPOCON'] = 0; // Doesn't use this, not sure why.
        $unico['EMP_CODIGO'] = $parameters['employee_id'];
        $unico['DIAS_PENDIENTES'] = $parameters['pending_days'];

        $content[] = $unico;
        $parameters = array();
        $parameters['inInexCod'] = '655';
        $parameters['clXMLSolic'] = $this->createXml($content, 655);

        /** @var View $res */
        $responseView = $this->callApi($parameters);

        return $responseView;
    }

    /**
     * Adds cumulative salary, for average calculations, this web service has
     * to be called once for each month of service in the last year.
     * It can have the same value always.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Adds cumulative salary, for average calculations, this web service has
     *                  to be called once for each month of service in the last year.
     *                  It can have the same value always.",
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
     *    (name="units", nullable=false, requirements="([0-9])+", strict=true, description="Number of days worked, it is 30 for full time, or any other number for part time.")
     *    (name="value", nullable=false, requirements="([0-9])+(\.[0-9]+)?", strict=true, description="Total amount payed to the employee in the month.")
     *    (name="year", nullable=false, requirements="([0-9])+", strict=true, description="Year when the salary was payed.")
     *    (name="month", nullable=false, requirements="([0-9])+", strict=true, description="Month when the salary was payed.")
     *    (name="period", nullable=false, requirements="([0-9])+", strict=true, description="Period when the slary was payed.(2 or 4).")
     *
     * @return View
     */
    public function postAddCumulativesAction(Request $request)
    {
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['employee_id'] = '([0-9])+';
        $mandatory['employee_id'] = true;
        $regex['units'] = '([0-9])+';
        $mandatory['units'] = true;
        $regex['value'] = '([0-9])+(\.[0-9]+)?';
        $mandatory['value'] = true;
        $regex['year'] = '([0-9])+';
        $mandatory['year'] = true;
        $regex['month'] = '([0-9])+';
        $mandatory['month'] = true;
        $regex['period'] = '([0-9])+';
        $mandatory['period'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);

        $content = array();
        $unico = array();

        // $unico['TIPOCON'] = 0; // Doesn't use this, not sure why.
        $unico['EMP_CODIGO'] = $parameters['employee_id'];
        $unico['CON_CODIGO'] = 1; // 1 because it is salary.
        $unico['ACUM_UNIDADES'] = $parameters['units'];
        $unico['ACUM_VALOR'] = $parameters['value'];
        $unico['ACUM_ANO'] = $parameters['year'];
        $unico['ACUM_MES'] = $parameters['month'];
        $unico['ACUM_PERIODO'] = $parameters['period'];

        $content[] = $unico;
        $parameters = array();
        $parameters['inInexCod'] = '656';
        $parameters['clXMLSolic'] = $this->createXml($content, 656);

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
     *    (name="exit_date", nullable=false, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="Exit day of the period to be enjoyed by the employee.")
     *    (name="number_days", nullable=false, requirements="([0-9]+)", strict=true, description="Return day to the company.(format: DD-MM-YYYY).")
     *    (name="money_days", nullable=true, requirements="([0-9])+", strict=true, description="Days to be paid in money.")
     * @return View
     */
    public function postModifyVacationParametersAction(Request $request)
    {
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['employee_id'] = '([0-9])+';
        $mandatory['employee_id'] = true;
        $regex['exit_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['exit_date'] = false;
        $regex['number_days'] = '([0-9]+)';
        $mandatory['number_days'] = false;
        $regex['money_days'] = '([0-9])+';
        $mandatory['money_days'] = false;

        $this->validateParamters($parameters, $regex, $mandatory);

        $content = array();
        $unico = array();

        $info = $this->getVacationParametersAction($parameters['employee_id'])->getData();

        $unico['USERNAME'] = isset($parameters['username']) ? $parameters['username'] : $info[''];


        $unico['TIPOCON'] = 1;
        $unico['EMP_CODIGO'] = isset($parameters['employee_id']) ? $parameters['employee_id'] : $info['EMP_CODIGO'];
        $unico['PARV_FECHA_SALIDA'] = isset($parameters['exit_date']) ? $parameters['exit_date'] : $info['PARV_FECHA_SALIDA'];
        $unico['PARV_DIAS_TIEMPO'] = isset($parameters['number_days']) ? $parameters['number_days'] : $info['PARV_DIAS_TIEMPO'];
        $unico['PARV_DIAS_DINERO'] = isset($parameters['money_days']) ? $parameters['money_days'] : $info['PARV_DIAS_DINERO'];

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

        $unico['EMP_CODIGO'] = $employeeId;

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

        $unico['EMP_CODIGO'] = $employeeId;

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
    public function getPendingVacationsAction($employeeId)
    {
        $content = array();
        $unico = array();

        $unico['EMP_CODIGO'] = $employeeId;

        $content[] = $unico;
        $parameters = array();

        $parameters['inInexCod'] = '628';
        $parameters['clXMLSolic'] = $this->createXml($content, 628, 2);

        /** @var View $res */
        $responseView = $this->callApi($parameters);

        return $responseView;
    }

    /**
     * Adds the partial cesantias parameters.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Adds the partial cesantias parameters.",
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
     *    (name="value", nullable=false, requirements="\d+(\.\d+)?", strict=true, description="Value of the cesantias to be given to the employee.")
     *    (name="payment_date", nullable=true, requirements="[0-9]{2}-[0-9]{2}-[0-9]{4}", strict=true, description="When is it going to be payed(DD-MM-YYYY)")
     *
     * @return View
     */
    public function postAddPartialCesantiasAction(Request $request)
    {
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['employee_id'] = '([0-9])+';
        $mandatory['employee_id'] = true;
        $regex['value'] = '\d+(\.\d+)?';
        $mandatory['value'] = true;
        $regex['payment_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['payment_date'] = false;

        $this->validateParamters($parameters, $regex, $mandatory);

        $content = array();
        $unico = array();


        $unico['TIPOCON'] = 0; // 0 is create.
        $unico['EMP_CODIGO'] = $parameters['employee_id'];
        $unico['PCES_VALOR_SOL'] = $parameters['value'];
        $unico['PCES_FECHA_PAGO'] = $parameters['payment_date'];

        $content[] = $unico;
        $parameters = array();
        $parameters['inInexCod'] = '630';
        $parameters['clXMLSolic'] = $this->createXml($content, 630);

        /** @var View $res */
        $responseView = $this->callApi($parameters);

        return $responseView;
    }

    /**
     * Executes the final liquidation process.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Executes the final liquidation process.",
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
     *    (name="execution_type", nullable=false, requirements="(P|D|C)", strict=true, description="P for process, D for unprocess and C for close")
     *
     * @return View
     */
    public function postExecuteFinalLiquidationAction(Request $request)
    {
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['employee_id'] = '([0-9])+';
        $mandatory['employee_id'] = true;
        $regex['execution_type'] = '(P|D|C)';
        $mandatory['execution_type'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);

        $content = array();
        $unico = array();


        $unico['COD_PROC'] = 3; // Final liquidation is always 3.
        $unico['USUARIO'] = ''; // Empty by default.
        $unico['EMP_CODIGO'] = $parameters['employee_id'];
        $unico['TIP_EJEC'] = $parameters['execution_type'];

        $content[] = $unico;
        $parameters = array();
        $parameters['inInexCod'] = '611';
        $parameters['clXMLSolic'] = $this->createXml($content, 611);

        /** @var View $res */
        $responseView = $this->callApi($parameters);

        return $responseView;
    }

    /**
     * Liquidates all the payrolls and change active period, should be called
     * each period to changeir, twice a month using Q, and at the end of
     * the month using M.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Liquidates all the payrolls and change active period.",
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
     *    (name="period", nullable=false, requirements="M|Q", strict=true, description="M meaning monthly and Q meaning byweekly")
     *
     * @return View
     */
    public function postChangeActivePeriodAction(Request $request)
    {
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['period'] = 'M|Q';
        $mandatory['period'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);

        $content = array();
        $unico = array();


        $unico['COD_PROC'] = 1; // payroll liquidation is always 1.
        $unico['USUARIO'] = 'SRHADMIN';
        $unico['EMP_CODIGO'] = $parameters['period'];
        $unico['TIP_EJEC'] = 'C'; // In this case is allways C.

        $content[] = $unico;
        $parameters = array();
        $parameters['inInexCod'] = '611';
        $parameters['clXMLSolic'] = $this->createXml($content, 611);

        /** @var View $res */
        $responseView = $this->callApi($parameters);

        return $responseView;
    }

    /**
     * Executes the payroll liquidation process.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Executes the payroll liquidation process.",
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
     *    (name="execution_type", nullable=false, requirements="(P|D|C)", strict=true, description="P for process, D for unprocess and C for close")
     *
     * @return View
     */
    public function postExecutePayrollLiquidationAction(Request $request)
    {
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['employee_id'] = '([0-9])+';
        $mandatory['employee_id'] = true;
        $regex['execution_type'] = '(P|D|C)';
        $mandatory['execution_type'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);

        $content = array();
        $unico = array();


        $unico['COD_PROC'] = 1; // payroll liquidation is always 1.
        $unico['USUARIO'] = 'SRHADMIN'; // Empty by default.
        $unico['EMP_CODIGO'] = $parameters['employee_id'];
        $unico['TIP_EJEC'] = $parameters['execution_type'];

        $content[] = $unico;
        $parameters = array();
        $parameters['inInexCod'] = '611';
        $parameters['clXMLSolic'] = $this->createXml($content, 611);

        /** @var View $res */
        $responseView = $this->callApi($parameters);

        return $responseView;
    }

    /**
     * Executes the contributions liquidation process.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Executes the contributions liquidation process.",
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
     *    (name="execution_type", nullable=false, requirements="(P|D|C)", strict=true, description="P for process, D for unprocess and C for close")
     *
     * @return View
     */
    public function postExecuteContributionsLiquidationAction(Request $request)
    {
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['employee_id'] = '([0-9])+';
        $mandatory['employee_id'] = true;
        $regex['execution_type'] = '(P|D|C)';
        $mandatory['execution_type'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);

        $content = array();
        $unico = array();


        $unico['COD_PROC'] = 100; // payroll liquidation is always 100.
        $unico['USUARIO'] = 'SRHADMIN'; // Empty by default.
        $unico['EMP_CODIGO'] = $parameters['employee_id'];
        $unico['TIP_EJEC'] = $parameters['execution_type'];

        $content[] = $unico;
        $parameters = array();
        $parameters['inInexCod'] = '611';
        $parameters['clXMLSolic'] = $this->createXml($content, 611);

        /** @var View $res */
        $responseView = $this->callApi($parameters);

        return $responseView;
    }

    /**
     * Executes the vacations liquidation process.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Executes the contributions liquidation process.",
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
     *    (name="execution_type", nullable=false, requirements="(P|D|C)", strict=true, description="P for process, D for unprocess and C for close")
     *
     * @return View
     */
    public function postExecuteVacationLiquidationAction(Request $request)
    {
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['employee_id'] = '([0-9])+';
        $mandatory['employee_id'] = true;
        $regex['execution_type'] = '(P|D|C)';
        $mandatory['execution_type'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);

        $content = array();
        $unico = array();


        $unico['COD_PROC'] = 2; // vacation liquidation is always 2.
        $unico['USUARIO'] = ''; // Empty by default.
        $unico['EMP_CODIGO'] = $parameters['employee_id'];
        $unico['TIP_EJEC'] = $parameters['execution_type'];

        $content[] = $unico;
        $parameters = array();
        $parameters['inInexCod'] = '611';
        $parameters['clXMLSolic'] = $this->createXml($content, 611);

        /** @var View $res */
        $responseView = $this->callApi($parameters);

        return $responseView;
    }

    /**
     * Executes the vacation pending days.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Executes the vacation pending days.",
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
     *    (name="execution_type", nullable=false, requirements="(P|D|C)", strict=true, description="P for process, D for unprocess and C for close")
     *
     * @return View
     */
    public function postExecutePendingVacationDaysAction(Request $request)
    {
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['employee_id'] = '([0-9])+';
        $mandatory['employee_id'] = true;
        $regex['execution_type'] = '(P|D|C)';
        $mandatory['execution_type'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);

        $content = array();
        $unico = array();


        $unico['COD_PROC'] = 201; // pending days is always 201.
        $unico['USUARIO'] = 'SRHADMIN';
        $unico['EMP_CODIGO'] = $parameters['employee_id'];
        $unico['TIP_EJEC'] = $parameters['execution_type'];

        $content[] = $unico;
        $parameters = array();
        $parameters['inInexCod'] = '611';
        $parameters['clXMLSolic'] = $this->createXml($content, 611);

        /** @var View $res */
        $responseView = $this->callApi($parameters);

        return $responseView;
    }
	
	/**
	 * Deletes an occasional novelty for an employee when exists as array (two salary novelties or more).<br/>
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Deletes an occasional novelty for an employee when exists as array (two salary novelties or more).",
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
	 *    (name="novelty_consec", nullable=false, requirements="([0-9])+", strict=true, description="Identifier used on SQL to identify the novelty")
	 *
	 * @return View
	 */
	public function postDeleteNoveltyEmployeeArrayAction(Request $request)
	{
		
		$parameters = $request->request->all();
		$regex = array();
		$mandatory = array();
		// Set all the parameters info.
		$regex['employee_id'] = '([0-9])+';
		$mandatory['employee_id'] = true;
		$regex['novelty_concept_id'] = '([0-9])+';
		$mandatory['novelty_concept_id'] = false;
		$regex['novelty_value'] = '([0-9])+(.[0-9]+)?';
		$mandatory['novelty_value'] = false;
		$regex['liquidation_type_id'] = '([A-Z])+';
		$mandatory['liquidation_type_id'] = false;
		$regex['unity_numbers'] = '([0-9])+';
		$mandatory['unity_numbers'] = false;
		$regex['novelty_start_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
		$mandatory['novelty_start_date'] = false;
		$regex['novelty_end_date'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
		$mandatory['novelty_end_date'] = false;
		$regex['novelty_consec'] = '([0-9])+';
		$mandatory['novelty_consec'] = false;
		
		$this->validateParamters($parameters, $regex, $mandatory);
		
		$content = array();
		$unico = array();
		$info = $this->getEmployeeNoveltyAction($parameters['employee_id'])->getData();
		
		foreach ( $info as $singleConsec){
			if($parameters['novelty_consec'] == $singleConsec['NOV_CONSEC'] ){
				$use = $singleConsec;
				break;
			}
		}
		
		$unico['TIPOCON'] = 2;
		$unico['EMP_CODIGO'] = isset($parameters['employee_id']) ? $parameters['employee_id'] : $use['EMP_CODIGO'];
		$unico['CON_CODIGO'] = isset($parameters['novelty_concept_id']) ? $parameters['novelty_concept_id'] : $use['CON_CODIGO'];
		$unico['NOV_VALOR_LOCAL'] = isset($parameters['novelty_value']) ? $parameters['novelty_value'] : $use['NOV_VALOR_LOCAL'];
		$unico['FLIQ_CODIGO'] = isset($parameters['liquidation_type_id']) ? $parameters['liquidation_type_id'] : $use['FLIQ_CODIGO'];
		$unico['NOV_UNIDADES'] = isset($parameters['unity_numbers']) ? $parameters['unity_numbers'] : $use['NOV_UNIDADES'];
		$unico['NOV_FECHA_DESDE_CAUSA'] = isset($parameters['novelty_start_date']) ? $parameters['novelty_start_date'] : $use['NOV_FECHA_DESDE_CAUSA'];
		$unico['NOV_FECHA_HASTA_CAUSA'] = isset($parameters['novelty_end_date']) ? $parameters['novelty_end_date'] : $use['NOV_FECHA_HASTA_CAUSA'];
		$unico['NOV_CONSEC'] = isset($parameters['novelty_consec']) ? $parameters['novelty_consec'] : $use['NOV_CONSEC'];
		
		
		$content[] = $unico;
		$parameters = array();
		$parameters['inInexCod'] = '612';
		$parameters['clXMLSolic'] = $this->createXml($content, 612);
		
		/** @var View $res */
		$responseView = $this->callApi($parameters);
		
		return $responseView;
	}
	
	/**
	 * Modifies an employee worked days per week <br/>
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Modifies an employee worked days per week",
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
	 *   (name="worked_week_days", nullable=true, requirements="([0-9])+", description="Number of days worked on a week.")
	 *
	 * @return View
	 */
	public function postModifyEmployeeWorkedDaysWeekAction(Request $request)
	{
		$parameters = $request->request->all();
		$regex = array();
		$mandatory = array();
		// Set all the parameters info.
		$regex['employee_id'] = '([0-9])+';
		$mandatory['employee_id'] = true;
		$regex['worked_week_days'] = '([0-9])+';
		$mandatory['worked_week_days'] = true;
		
		$this->validateParamters($parameters, $regex, $mandatory);
		
		$content = array();
		$unico = array();
		
		$unico['TIPOCON'] = 3;
		$unico['EMP_CODIGO'] = $parameters['employee_id'];
		$unico['DIAS_LABOR_SEM'] =  $parameters['worked_week_days'];
		
		$content[] = $unico;
		
		$parameters = array();
		$parameters['inInexCod'] = '601';
		$parameters['clXMLSolic'] = $this->createXml($content, 601);
		
		/** @var View $res */
		$responseView = $this->callApi($parameters);
		
		return $responseView;
	}
	
	/**
	 * Process 100 process and acumulate <br/>
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Process 100 process and acumulate",
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
	 *    (name="year", nullable=false, requirements="([0-9])+", strict=true, description="Year of the period to be close")
	 *    (name="month", nullable=false, requirements="([0-9])+", strict=true, description="Month of the period to be close.")
	 *    (name="from_date", nullable=false, requirements="[0-9]{4}-[0-9]{2}-[0-9]{2}", strict=true, description="Y-m-d start of the month")
	 *    (name="to_date", nullable=false, requirements="[0-9]{4}-[0-9]{2}-[0-9]{2}", strict=true, description="Y-m-d end of the month")
	 *    (name="cut_date", nullable=false, requirements="[0-9]{4}-[0-9]{2}-[0-9]{2}", strict=true, description="Y-m-d end of the month")
	 *    (name="pay_date", nullable=false, requirements="[0-9]{4}-[0-9]{2}-[0-9]{2}", strict=true, description="Y-m-d end of the month")
	 *
	 * @return View
	 */
	public function postProcessAndAcumulateAction(Request $request)
	{
		$parameters = $request->request->all();
		$regex = array();
		$mandatory = array();
		// Set all the parameters info.
		$regex['year'] = '([0-9])+';
		$mandatory['year'] = true;
		$regex['month'] = '([0-9])+';
		$mandatory['month'] = true;
		$regex['from_date'] = '[0-9]{4}-[0-9]{2}-[0-9]{2}';
		$mandatory['from_date'] = true;
		$regex['to_date'] = '[0-9]{4}-[0-9]{2}-[0-9]{2}';
		$mandatory['to_date'] = true;
		$regex['cut_date'] = '[0-9]{4}-[0-9]{2}-[0-9]{2}';
		$mandatory['cut_date'] = true;
		$regex['pay_date'] = '[0-9]{4}-[0-9]{2}-[0-9]{2}';
		$mandatory['pay_date'] = true;
		
		$this->validateParamters($parameters, $regex, $mandatory);
		
		$content = array();
		$unico = array();
		
		$unico['PAR_ANO'] = $parameters['year'];
		$unico['PAR_MES'] = $parameters['month'];
		$unico['PAR_PERIODO'] = 4;
		$unico['PAR_FECHA_DESDE'] = $parameters['from_date'];
		$unico['PAR_FECHA_HASTA'] = $parameters['to_date'];
		$unico['PAR_FECHA_CORTE'] = $parameters['cut_date'];
		$unico['PAR_FECHA_PAGO'] = $parameters['pay_date'];
		$unico['PAR_EMP_CODI_INI'] = 0;
		$unico['PAR_EMP_CODI_FIN'] = 99999;
		$unico['PAR_TIPO_LIQ'] = 'M';
		$unico['CERRARPROCESO100'] = 'S';
		
		$content[] = $unico;
		
		$parameters = array();
		$parameters['inInexCod'] = '610';
		$parameters['clXMLSolic'] = $this->createXml($content, 610);
		
		$unico['PAR_TIPO_LIQ'] = 'Q';
		
		$content[] = $unico;
		
		$parameters = array();
		$parameters['inInexCod'] = '610';
		$parameters['clXMLSolic'] = $this->createXml($content, 610);
		
		/** @var View $res */
		$responseView = $this->callApi($parameters);
		
		return $responseView;
	}
	
	/**
	 * Executes the vacation pending days.<br/>
	 *
	 * @ApiDoc(
	 *   resource = true,
	 *   description = "Executes the vacation pending days.",
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
	 *    (name="cod_process", nullable=false, requirements="([0-9])+", strict=true, description="code of the process to execute")
	 *    (name="employee_id", nullable=false, requirements="([0-9])+", strict=true, description="Employee id")
	 *    (name="execution_type", nullable=false, requirements="(P|D|C)", strict=true, description="P for process, D for unprocess and C for close")
	 *
	 * @return View
	 */
	public function postProcessExecutionAction(Request $request)
	{
		$parameters = $request->request->all();
		$regex = array();
		$mandatory = array();
		// Set all the parameters info.
		$regex['cod_process'] = '([0-9])+';
		$mandatory['cod_process'] = true;
		$regex['employee_id'] = '([0-9])+';
		$mandatory['employee_id'] = true;
		$regex['execution_type'] = '(P|D|C)';
		$mandatory['execution_type'] = true;
		$this->validateParamters($parameters, $regex, $mandatory);
		$content = array();
		$unico = array();
		
		
		$unico['COD_PROC'] = $parameters['cod_process'];
		$unico['USUARIO'] = 'SRHADMIN';
		$unico['EMP_CODIGO'] = $parameters['employee_id'];
		$unico['TIP_EJEC'] = $parameters['execution_type'];
		
		$content[] = $unico;
		$parameters = array();
		$parameters['inInexCod'] = '611';
		$parameters['clXMLSolic'] = $this->createXml($content, 611);
		
		/** @var View $res */
		$responseView = $this->callApi($parameters);
		return $responseView;
	}
}
