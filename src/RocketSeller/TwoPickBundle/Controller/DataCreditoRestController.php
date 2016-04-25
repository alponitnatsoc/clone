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

public function fixArrayLocalizacion($array, &$new_array) {
    foreach($array as $key => $val) {
        if($key == '@attributes') {
            // Conta is in case the field is called @attribute.
            $conta = 2;
            foreach($val as $i => $j) {
              if($i != '@attributes')
                $new_array[$i] = $j;
              else
                $new_array[$conta] = $j;
              $conta ++;
            }
            } else {
            if(is_array($val)){
              $temp = array();
              $this->fixArrayLocalizacion($val, $temp);
              $new_array[$key] = $temp;
            }else{
              $new_array[$key] = $val;
            }
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
              //"login" => '900862831',
              //"password" => $pass
        ));
        $user = "900862831";
        $pass = "09KJS";

        $request = '<?xml version="1.0" encoding="UTF-8" ?>
                    <SolicitudDatosLocalizacion tipoIdentificacion="' . $parameters["tipoIdentificacion"] .
                    '" identificacion="' . $parameters["identificacion"] .'" usuario="' . $user .
                    '" clave="' . $pass . '" primerApellido="' . $parameters["primerApellido"] . '" />';

        //$args = array('datosValidacion' => $request, 'paramProducto'=>'2940', 'producto'=>'007', 'canal'=>'001');

        $res = $client->consultarDatosLocalizacion2($request);
        //die($res);
        // Transform xml to array in the next 4 lines.
        $xml = simplexml_load_string($res, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $array2 = json_decode($json,TRUE);
        //$array = $array["@attributes"];
        $array = array();
        $this->fixArrayLocalizacion($array2, $array);

        $view = View::create();
        $errorCode = 200;
        if (isset($array["respuesta"]) && $array["respuesta"] != 13)
            $errorCode = 404;

        // Set status code of view with http codes.
        $view->setStatusCode($errorCode);

        // Return response without the status code.
        $view->setData($array);
        return $view;
   }


   public function fixArray($array, &$new_array) {
     foreach($array as $key => $val) {
        if($key == '@attributes') {
             foreach($val as $i => $j) {
				  $new_array[$i] = $j;
             }
        } else {
          // No es @attributes.
          if(is_array($val))
            foreach($val as $i => $j) {
      			  if(is_array($j)) {
        				$temp_array = array();
        			  $this->fixArray($j, $temp_array);
                // @attributes seems to be special that's why we use ===.
                if($i === '@attributes')continue;
        				$new_array[$i] = $temp_array;
      			  }
            }
        }
     }
   }

   /**
    * Calls the payments api, it receives the headers and the parameters and
    * makes a call using an absolute path, and returns a view with the Json or
    * XML response. We are using two different call api methods, because the
    * two services have different behaviors.
    * @param Array with the header options $headers
    * @param Array with parameter options $parameters
    * @param String with the relative path $path
    * @param Type of action(delete, get, post) default: post $action
    * @param Maximum number of seconds before giving up $timeout
    * @return View with the json response from the payments server.
    */
   public function callApiIdentificacion($parameters, $path, $methodName,$request,$differentCall=false,$timeout = 10)
   {
       ini_set("soap.wsdl_cache_enabled", 1);
       $opts = array(
           //"ssl" => array("ciphers" => "RC4-SHA")
       );
       $user = "900862831";
       //$client = new \SoapClient("http://172.24.14.29:8080/localizacion2/services/ServicioLocalizacion2?wsdl",
       $client = new \SoapClient($path . "?wsdl",
         array("connection_timeout" => $timeout,
             "trace" => true,
             "exceptions" => true,
             "stream_context" => stream_context_create($opts),
             'location'      => $path,
             "login" => '900862831',
             //"password" => $pass
       ));

       // var_dump($client->__getFunctions());

       $paramProducto = '2940';
       $producto = '007';
       $canal = '001';
       $res=null;
       if(!$differentCall)
         $res = $client->__soapCall($methodName, array($paramProducto, $producto, $canal, $request));
       else
         $res = $client->__soapCall($methodName, array($producto, $paramProducto, $request));

       // Transform xml to array in the next 3 lines.
       $xml = simplexml_load_string($res, "SimpleXMLElement", LIBXML_NOCDATA);
       $json = json_encode($xml);
       $array2 = json_decode($json,TRUE);

       // Fixarray takes as reference the second parameter, so we are going to
       // store the fixed array here.
       $array = array();
       $this->fixArray($array2, $array);

       $view = View::create();
       $errorCode = 200;
       if (isset($array["resultado"]) &&  $array["resultado"] != "01")
           $errorCode = 404;

       // Set status code of view with http codes.
       $view->setStatusCode($errorCode);

       // Return response without the status code.
       $view->setData($array);
       return $view;
  }

    public function adaptLocationService($json) {
      $array = json_decode($json, true);
      $res = array();
      // Nombres y apellidos.
      if(isset($array['NaturalNacional'])) {
        if(isset($array['NaturalNacional']['nombres'])) {
          $res['nombres'] = $array['NaturalNacional']['nombres'];
        } else {
          $res['nombres'] = '';
        }
          isset($array['NaturalNacional']['primerApellido']) ?
            $res['primerApellido'] = $array['NaturalNacional']['primerApellido'] : '';
          isset($array['NaturalNacional']['segundoApellido']) ?
            $res['segundoApellido'] = $array['NaturalNacional']['segundoApellido'] : '';

          // Identificacion which is inside NaturalNacional.
          if(isset($array['NaturalNacional']['Identificacion'])) {
            $fecha = isset($array['NaturalNacional']['Identificacion']['fechaExpedicion']) ? $array['NaturalNacional']['Identificacion']['fechaExpedicion'] : '';
            if($fecha != '') {
              $fecha = explode('-', $fecha);
              $res['fechaExpedicionAno'] = $fecha[0];
              $res['fechaExpedicionMes'] = $fecha[1];
              $res['fechaExpedicionDia'] = $fecha[2];
            }

            $res['ciudadExpedicion'] = isset($array['NaturalNacional']['Identificacion']['ciudad']) ? $array['NaturalNacional']['Identificacion']['ciudad'] : '';
          } else {
            $res['fechaExpedicion'] = '';
            $res['ciudadExpedicion'] = '';
          }

          // Genero.
          if(isset($array['NaturalNacional']['genero'])) {
            $codGenero = $array['NaturalNacional']['genero'];
            if($codGenero == 1 || $codGenero == 2 || $codGenero == 3) {
              $res['genero'] = 'F';
            } elseif($codGenero == '4') {
              $res['genero'] = 'M';
            } else {
              $res['genero'] = '';
            }
          } else {
            $res['genero'] = '';
          }
        } else {
          // If we don't have NaturalNacional we set everything in null.
          $res['primerNombre'] = '';
          $res['segundoNombre'] = '';
          $res['primerApellido'] = '';
          $res['segundoApellido'] = '';
          $res['fechaExpedicion'] = '';
          $res['ciudadExpedicion'] = '';
          $res['genero'] = '';
       }

       if(isset($array['Direccion'])) {
         if(isset($array['Direccion'][1]))
          $array_direccion = $array['Direccion'][1];
         else
          $array_direccion = $array['Direccion'];
         $res['direccion'] = isset($array_direccion['direccion']) ? $array_direccion['direccion'] : '';
         $res['ciudad'] = isset($array_direccion['nombreCiudad']) ? $array_direccion['nombreCiudad'] : '';
         $res['departamento'] = isset($array_direccion['nombreDepartamento']) ? $array_direccion['nombreDepartamento'] : '';
       } else {
         $res['direccion'] = '';
         $res['ciudad'] = '';
         $res['departamento'] = '';
       }



       if(isset($array['Celular'])) {
         if(isset($array['Celular'][1]))
          $array_direccion = $array['Celular'][1];
        else
          $array_direccion = $array['Celular'];
         $res['telefono'] = isset($array_direccion['celular']) ? $array_direccion['celular'] : '';
       }
       if(!isset($res['telefono']) || $res['telefono'] == null) {
         if(isset($array['Telefono'])) {
           $array_direccion = $array['Telefono'][1];
           $res['telefono'] = isset($array_direccion['telefono']) ? $array_direccion['telefono'] : '';
         }
       }
       if(!isset($res['telefono']))$res['telefono'] = '';


       if(isset($array['Email'])) {
         if(isset($array['Email'][1]))
          $array_direccion = $array['Email'][1];
         else
          $array_direccion = $array['Email'];
         $res['mail'] = isset($array_direccion['email']) ? $array_direccion['email'] : '';
       } else {
         $res['mail'] = '';
       }
       return $res;
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
     * @param Int $documentNumber The document number of the client.
     * @param String $documentNumber The document type of the client.
     * @param Int $surname The last name of the client.
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
        $surname = mb_strtoupper ($surname, 'utf-8');
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

        $temp = $this->handleView($responseView);
        //$data = json_decode($temp->getContent(), true);
        $code = json_decode($temp->getStatusCode(), true);

        $newinfo = $this->adaptLocationService($temp->getContent());

        $view = View::create();
        $view->setStatusCode($code);
        $view->setData($newinfo);

        return $view;
    }

    /**
     * Get the validation for the questions in the datacredito process.
     * This process has to be called prior to the get questions method to get
     * the id.
     * Servicio identificacion<br/>
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
     * @param Int $documentNumber The document number of the client.
     * @param String $identificationType The type of the document.
     * @param String $surname The last name of the client.
     * @param Int $names The first and middle name of the client.
     * @param String $documentExpeditionDate format DD-MM-YYYY.
     *
     * @return View
     */
    public function getClientIdentificationServiceExperianValidarAnswersAction($documentNumber,$identificationType, $surname, $names, $documentExpeditionDate)
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
        $parameters["nombres"] = $names;
        $parameters["documentExpeditionDate"] = $documentExpeditionDate;

        // Set all the parameters info.
        $regex['tipoIdentificacion'] = '(.)*';
        $mandatory['tipoIdentificacion'] = true;
        $regex['identificacion'] = '([0-9])+';
        $mandatory['identificacion'] = true;
        $regex['primerApellido'] = '(.)*';
        $mandatory['primerApellido'] = true;
        $regex['nombres'] = '(.)*';
        $mandatory['nombres'] = true;
        $regex['documentExpeditionDate'] = '[0-9]{2}-[0-9]{2}-[0-9]{4}';
        $mandatory['nombres'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);

        // We adapt the date to the new format.
        $newFormated = new DateTime($parameters["documentExpeditionDate"],  new \DateTimeZone('UTC'));
        $parameters["documentExpeditionDate"]  = $newFormated->getTimestamp();
        $parameters["documentExpeditionDate"] .= '000'; // Not sure why we need this, but this is the format in datacredito.

        // TODO(daniel.serrano): Change the timestamp to real value.
        $request = '<?xml version="1.0" encoding="UTF-8"?> <DatosValidacion>
                    <Identificacion numero="' . $parameters["identificacion"] .'" tipo="' . $parameters["tipoIdentificacion"] .
                    '" /> <PrimerApellido>' . $parameters["primerApellido"] . '</PrimerApellido> <Nombres>' . $parameters["nombres"] . '</Nombres>
                    <FechaExpedicion timestamp="' . $parameters["documentExpeditionDate"] . '" />
                    </DatosValidacion>';

        /** @var View $responseView */
        $responseView = $this->callApiIdentificacion($parameters, "http://52.73.111.160:8080/idws2/services/ServicioIdentificacion", 'validar', $request);

        return $responseView;
    }


    /**
     * Get the questions for the datacredito process.
     * Servicio identificacion<br/>
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
     * @param Int $documentNumber The document number of the client.
     * @param String $identificationType The type of the document.
     * @param Int $registerValidation The id gotten on the validation method, in the field: regValidacion.
     *
     * @return View
     */
    public function getClientIdentificationServiceExperianPreguntasAction($documentNumber,$identificationType,$registerValidation)
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
        $parameters["regValidacion"] = $registerValidation;

        // Set all the parameters info.
        $regex['tipoIdentificacion'] = '(.)*';
        $mandatory['tipoIdentificacion'] = true;
        $regex['identificacion'] = '([0-9])+';
        $mandatory['identificacion'] = true;
        $regex['regValidacion'] = '[0-9]+';
        $mandatory['regValidacion'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);

        $request = '<?xml version="1.0" encoding="UTF-8"?>
        <SolicitudCuestionario tipoId="' . $parameters["tipoIdentificacion"] .
        '" identificacion="' . $parameters["identificacion"] .
        '" regValidacion="' . $parameters["regValidacion"] .'" />';

        /** @var View $responseView */
        $responseView = $this->callApiIdentificacion($parameters, "http://52.73.111.160:8080/idws2/services/ServicioIdentificacion", 'preguntas', $request);

        return $responseView;
    }


    /**
     * Verifies the answers to the questions, even though this is a get method,
     * we are going to be using a request object instead of regular parameters,
     * because it allows us to use arrays as parameters<br/>.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Verifies the answers to the questions, even though this
     *                  is a get method, we are going to be using a request
     *                  object instead of regular parameters, because it allows
     *                  us to use arrays as parameters.",
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
     * (name="documentNumber", nullable=false, requirements="([0-9|-]| )+", strict=true, description="Document number of the client.")
     * (name="documentType", nullable=false, requirements="(cc|CC|nit|NIT|1|2|3|4)", strict=true, description="Document type of the client.")
     * (name="idQuestions", nullable=false, requirements="[0-9]+", strict=true, description="Id of the questioner, it is returned in the questions method in the field: id.")
     * (name="regQuestions", nullable=false, requirements="[0-9]+", strict=true, description="Id of the questioner regitery, it is returned int he questions method in the field: registro.")
     * (name="answers", nullable=false, requirements="", strict=true, description="This is an array containing the answers to the questions it must be in the form:
     *                                                                             ["id_question"=>"id_answer"]. id_question is in the field order.")
     *
     * @return View
     */
    public function getClientIdentificationServiceExperianVerifyPreguntasAction(Request $request)
    {
        $parameters = array();
        $regex = array();
        $mandatory = array();
        $parameters = $request->query->all();

        // Set all the parameters info.
        $regex['documentNumber'] = '([0-9|-]| )+';
        $mandatory['documentNumber'] = true;
        $regex['documentType'] = '(cc|CC|nit|NIT|1|2|3|4)';
        $mandatory['documentType'] = true;
        $regex['idQuestions'] = '[0-9]+';
        $mandatory['idQuestions'] = true;
        $regex['regQuestions'] = '[0-9]+';
        $mandatory['regQuestions'] = true;
        $mandatory['answers'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);

        // Adapt the document type to our standars.
        if($parameters['documentType'] == "cc" ||
           $parameters['documentType'] == "CC" ) {
             $parameters['documentType'] = 1;
        }
        if($parameters['documentType'] == "nit" ||
          $parameters['documentType'] == "NIT" ) {
            $parameters['documentType'] = 2;
        }
        if($parameters['documentType'] == "ce" ||
          $parameters['documentType'] == "CE" ) {
            $parameters['documentType'] = 4;
        }



        $request = '<?xml version="1.0" encoding="UTF-8"?>
        <Respuestas idCuestionario="' . $parameters['idQuestions'] .'" regCuestionario="' . $parameters['regQuestions']  . '">
        <Identificacion numero="' . $parameters['documentNumber'] .'" tipo="' . $parameters['documentType'] . '" />';

        // We add all the answers and questions to the xml.
        foreach($parameters['answers'] as $key=>$value) {
          $request .= '<Respuesta idPregunta="' . $key . '" idRespuesta="' . $value . '" />';
        }
        $request .= '</Respuestas>';
        //die(print_r($request));
        /** @var View $responseView */
        $responseView = $this->callApiIdentificacion($parameters, "http://52.73.111.160:8080/idws2/services/ServicioIdentificacion", 'verificar', $request, true);

        return $responseView;
    }
}

?>
