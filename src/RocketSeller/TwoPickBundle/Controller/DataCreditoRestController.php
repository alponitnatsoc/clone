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
use FOS\RestBundle\Controller\Annotations\Put;
use Symfony\Component\HttpKernel\Exception\HttpException;
use DateTime;
use GuzzleHttp\Client;
use EightPoints\Bundle\GuzzleBundle;

/**
 * Contains all the web services to call the payment system.
 * Get methods can be call as any function.
 * If a post method is going to be call from within the application here is an
 * example:
 *   $request =  new Request();
 *   $request->request->set("employee_id", "123456");
 *   $request->request->set("concept_id", "1");
 *   $this->postFunctionAction($request);
 *
 */
class DataCreditoRestController extends FOSRestController
{

    /**
     * Verifies that the parameters to the web services, are in place and that
     * have the ccorrect format.
     * @param Array $parameters, Contains the parameters by the client.
     * @param Array $regex, contains the key as parameter and a regex.
     * @param Array $mandatory, contains a bool indicating if it is mandatory.
     */
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
    public function callApi($parameters, $path, $timeout = 10)
    {
        ini_set("soap.wsdl_cache_enabled", 1);
        $opts = array(
            //"ssl" => array("ciphers" => "RC4-SHA")
        );
        //$client = new \SoapClient("http://172.24.14.29:8080/localizacion2/services/ServicioLocalizacion2?wsdl",
        $client = new \SoapClient($path . "?wsdl",
          array("connection_timeout" => $timeout,
              "trace" => true,
              "exceptions" => true,
              "stream_context" => stream_context_create($opts),
              'location'      => $path,
              //"login" => $login,
              //"password" => $pass
        ));
        $user = "900862831";
        $pass = "09KJS";

        $request = '<?xml version="1.0" encoding="UTF-8" ?>
                    <SolicitudDatosLocalizacion tipoIdentificacion="' . $parameters["tipoIdentificacion"] .
                    '" identificacion="' . $parameters["identificacion"] .'" usuario="' . $user .
                    '" clave="' . $pass . '" primerApellido="' . $parameters["primerApellido"] . '" />';

        $args = array('solicitud' => $request);

        $res = $client->consultarDatosLocalizacion2($request);

        // Transform xml to array in the next 4 lines.
        $xml = simplexml_load_string($res, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $array = json_decode($json,TRUE);
        $array = $array["@attributes"];

        $view = View::create();
        $errorCode = 200;
        if (isset($array["respuesta"]) && $array["respuesta"] != 0 && $array["respuesta"] != 1)
            $errorCode = 404;

        // Set status code of view with http codes.
        $view->setStatusCode($errorCode);

        // Return response without the status code.
        $view->setData($array);
        return $view;
   }

    /**
     * Get the client information from the datacredito service.
     * Servicio reconocer<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get the client informatino from the payment system.",
     *   statusCodes = {
     *     200 = "OK",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Not Found"
     *   }
     * )
     *
     * @param Int $documentNumber The id of the client in the payments system.
     *
     * @return View
     */
    public function getClientLocationServiceAction($documentNumber,$identificationType, $surname)
    {
        $parameters = array();
        $regex = array();
        $mandatory = array();

        // Adapt the document type to our standars.
        if($identificationType == "cc" ||
           $identificationType == "CC" ) {
             $identificationType = 1;
        }
        if($identificationType == "nit" ||
          $identificationType == "NIT" ) {
            $identificationType = 2;
        }
        if($identificationType == "ce" ||
          $identificationType == "CE" ) {
            $identificationType = 4;
        }

        $parameters["tipoIdentificacion"] = $identificationType;
        $parameters["identificacion"] = $documentNumber;
        $parameters["primerApellido"] = $surname;

        // Set all the parameters info.
        $regex['tipoIdentificacion'] = '(.)*';
        $mandatory['tipoIdentificacion'] = true;
        $regex['identificacion'] = '([0-9])+';
        $mandatory['identificacion'] = true;
        $regex['primerApellido'] = '(.)*';
        $mandatory['primerApellido'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);

        /** @var View $responseView */
        $responseView = $this->callApi($parameters, "http://52.73.111.160:8080/localizacion2/services/ServicioLocalizacion2");

        return $responseView;
    }

}

?>
