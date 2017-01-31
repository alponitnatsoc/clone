<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Employer;
use FOS\RestBundle\Controller\FOSRestController;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use RocketSeller\TwoPickBundle\Entity\HighTechLog;
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


class DocumentoSoporte {
        var $nombre;
        var $base64;
        function __construct($a,$b) {
          $this->nombre = $a;
          $this->base64 = $b;
        }
}

class InfoBasica {
        var $primer_apellido;
        var $segundo_apellido;
        var $primer_nombre;
        var $segundo_nombre;
        var $tipo_documento;
        var $numero_documento;
        var $fecha_expedicion;
        var $residente_colombiano;
        var $estado_civil;
        var $lugar_nacimiento;
        var $direccion_residencia;
        var $telefono_residencia;
        var $municipio_residencia;
        var $departamento_residencia;
        var $direccion_oficina;
        var $telefono_oficina;
        var $fax_oficina;
        var $municipio_oficina;
        var $departamento_oficina;
        var $celular;
        var $correo;
        var $administra_recursos_publicos;

        function __construct($a,$b,$c,$d,$e,$f,$g,$h,$i,$j,$k,$l,$m,
                                        $n,$o,$p,$q,$r,$s,$t,$u,$v) {
          $this->primer_apellido = $a;
          $this->segundo_apellido = $b;
          $this->primer_nombre = $c;
          $this->segundo_nombre = $d;
          $this->tipo_documento = $e;
          $this->numero_documento= $f;
          $this->fecha_expedicion = $g;
          $this->residente_colombiano = $h;
          $this->estado_civil = $i;
          $this->lugar_nacimiento = $j;
          $this->direccion_residencia = $k;
          $this->telefono_residencia = $l;
          $this->municipio_residencia = $m;
          $this->departamento_residencia = $n;
          $this->direccion_oficina = $o;
          $this->telefono_oficina = $p;
          $this->fax_oficina = $q;
          $this->municipio_oficina = $r;
          $this->departamento_oficina = $s;
          $this->celular = $t;
          $this->correo = $u;
          $this->administra_recursos_publicos = $v;
        }
}

class InfoEstudio {
        var $nivel_estudios;
        var $titulo_obtenido;
        var $ocupacion;

        function __construct($a=null,$b=null,$c=null) {
          $this->nivel_estudios = $a;
          $this->titulo_obtenido = $b;
          $this->ocupacion = $c;
        }
}

/**
 * Contains all the web services to call the payment system with htsoft.
 * Get methods can be call as any function.
 * If a post method is going to be call from within the application here is an
 * example:
 *   $request =  new Request();
 *   $request->request->set("employee_id", "123456");
 *   $request->request->set("concept_id", "1");
 *   $this->postFunctionAction($request);
 *
 */
class Payments2RestController extends FOSRestController
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
    public function callApi($parameters, $path, $methodName,$timeout = 10)
    {

      ini_set("soap.wsdl_cache_enabled", 1);
      $ambiente = '';
      if($this->container->hasParameter('ambiente'))
        $ambiente = $this->container->getParameter('ambiente');
      else
        $ambiente = 'desarrollo';

      if($ambiente == 'produccion')
        $url_base = "https://cpsuite.htsoft.co:8080/dssp/services/";
      else
        $url_base = "http://test.cpsuite.htsoft.co:6565/dssp/services/";

       $opts = array(
           //"ssl" => array("ciphers" => "RC4-SHA")
       );

       $parametros_soap = array("connection_timeout" => 20,
             "trace" => true,
             "exceptions" => true,
             "stream_context" => stream_context_create($opts),
             //"login" => $login,
             //"password" => $pass
      );
      // This is because of the https problem.
      if($ambiente == 'produccion')
        $parametros_soap['location'] = 'https://cpsuite.htsoft.co:8080/dssp/services/' . $methodName . '/';
        $em = $this->getDoctrine()->getManager();
        $htLog= null;
        if($methodName!='ConsultarFuentesPago'){
            $htLog = new HighTechLog();
            $htLog->setServiceCalled($methodName);
            $htLog->setParameters($parameters);
            $htLog->setTimeWhenCalled(new DateTime());

            $em->persist($htLog);
            $em->flush();
        }

      
      $client = new \SoapClient($url_base . $path . "?wsdl", $parametros_soap);

      $res = $client->__soapCall($methodName, array($parameters));
      
      // This other way also works, may be usefull.
      //$res = $client->RegistrarBeneficiario($parameters);
      // Trick to get everything as an array.
      $res = json_decode(json_encode($res), True);

      $responseCode = $res['codigoRespuesta'];
      // Remove the status code so we can return the entire object.
      //unset($res['codigoRespuesta']);

      $view = View::create();
      $errorCode = 404;
      if ($responseCode == 0)
          $errorCode = 200;
      else if($responseCode == 101)
          $errorCode = 404;
      else if($responseCode == 102)
          $errorCode = 422;
      if($methodName!='ConsultarFuentesPago'){
          $htLog->setResultCode($errorCode);
          $htLog->setRawResultCode($responseCode);
          $em->persist($htLog);
          $em->flush();
      }

      
      // Set status code of view with http codes.
      $view->setStatusCode($errorCode);

      // Return response without the status code.
      $view->setData($res);
      return $view;
    }


    /**
     * Registers a natural person as an employer.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = " Registers a natural person as an employer.",
     *   statusCodes = {
     *     200 = "Created",
     *     400 = "Bad Request",
     *     404 = "Not found",
     *     422 = "Bad parameters"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *
     * (name="firstLastName", nullable=false, requirements="(.)*", strict=true, description="Code of the bank, can be found int he table Bank.")
     * (name="secondLastName", nullable=true, requirements="(.)*", strict=true, description="Checking or saving account.")
     * (name="name", nullable=false, requirements="(.)*", strict=true, description="Name of the person.")
     * (name="documentType", nullable=false, requirements="(CC|cc|nit|NIT|ce|CE|PASAPORTE)", strict=true, description="File of the letter authorizing symplifica, in base 64.")
     * (name="documentNumber", nullable=false, requirements="\d+", strict=true, description="File of the letter authorizing symplifica, in base 64.")
     * (name="documentExpeditionDate", nullable=true, requirements="[0-9]{4}-[0-9]{2}-[0-9]{2}", strict=true, description="YYYY-MM-DD")
     * (name="civilState", nullable=true, requirements="(SOLTERO|CASADO|UNION LIBRE|VIUDO|DIVORCIADO)", strict=true, description="")
     * (name="address", nullable=false, requirements="(.)*", strict=true, description="")
     * (name="phone", nullable=false, requirements="\d+", strict=true, description="")
     * (name="municipio", nullable=true, requirements="(.)*", strict=true, description="")
     * (name="department", nullable=false, requirements="(.)*", strict=true, description="")
     * (name="mail", nullable=false, requirements="(.)*", strict=true, description="")
     *
     * @return View
     */
    public function postRegisterNaturalPersonAction(Request $request)
    {
        $path = "VinculacionPN";
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();

        // Set all the parameters info.
        $regex['firstLastName'] = '(.)*';
        $mandatory['firstLastName'] = true;
        $regex['secondLastName'] = '(.)*';
        $mandatory['secondLastName'] = false;
        $regex['name'] = '(.)*';
        $mandatory['name'] = true;
        $regex['documentType'] = '(CC|cc|nit|NIT|ce|CE|PASAPORTE)';
        $mandatory['documentType'] = true;
        $regex['documentNumber'] = '\d+';
        $mandatory['documentNumber'] = true;
        $regex['documentExpeditionDate'] = '[0-9]{4}-[0-9]{2}-[0-9]{2}|(^$)';
        $mandatory['documentExpeditionDate'] = false;
        $regex['civilState'] = '(SOLTERO|CASADO|UNION LIBRE|VIUDO|DIVORCIADO)';
        $mandatory['civil_state'] = false;
        $regex['address'] = '(.)*';
        $mandatory['address'] = true;
        $regex['phone'] = '\d+';
        $mandatory['phone'] = true;
        $regex['municipio'] = '(.)*';
        $mandatory['municipio'] = false;
        $regex['department'] = '(.)*';
        $mandatory['department'] = true;
        $regex['mail'] = '(.)*';
        $mandatory['mail'] = true;

        // Separate first and second name.
        $names = explode(' ', $parameters['name']);
        $parameters['firstFirstName'] = $names[0];
        $parameters['secondFirstName'] = count($names) > 1 ? $names[1] : null;
        //die(print_r($parameters['firstFirstName']));
        $this->validateParamters($parameters, $regex, $mandatory);

        $parameters_fixed = array();
        $info_estudio = new InfoEstudio();
        $info_basica =
        new InfoBasica($parameters['firstLastName'],
                       $parameters['secondLastName'],
                       $parameters['firstFirstName'],
                       $parameters['secondFirstName'],
                       $parameters['documentType'],
                       $parameters['documentNumber'],
                       $parameters['documentExpeditionDate'],
                       null,
                       $parameters['civilState'],
                       null,
                       $parameters['address'],
                       $parameters['phone'],
                       $parameters['municipio'],
                       $parameters['department'],
                       null,
                       null,
                       null,
                       null,
                       null,
                       null,
                       $parameters['mail'],
                       null,
                       null,
                       null,
                       null,
                       null,
                       null,
                       null,
                       null,
                       null,
                       null,
                       null,
                       null,
                       null,
                       null
                     );
        $parameters_fixed['info_basica'] = $info_basica;
        $parameters_fixed['info_estudio'] = $info_estudio;

        /** @var View $res */
        $responseView = $this->callApi($parameters_fixed, $path, "VinculacionPN");
        $temp = $this->handleView($responseView);
        $data = json_decode($temp->getContent(), true);
        $code = json_decode($temp->getStatusCode(), true);

        if($code != 200) {
          $view = View::create();
          $view->setStatusCode($code);
          $view->setData($data);
          return $view;
        }

        $codigoView = $this->getEmployerAction($data['numeroRadicado']);

        $temp = $this->handleView($codigoView);
        $dataCodigo = json_decode($temp->getContent(), true);

        $res = array();
        $res['cuentaGSC'] = $dataCodigo['cuentaGSC'];
        $view = View::create();
        $view->setStatusCode($code);
        $view->setData($res);

        return $view;
    }

    /**
     * Edits an employer, all the information should be provided, it will be replaced completly.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = " Registers a natural person as an employer.",
     *   statusCodes = {
     *     200 = "Created",
     *     400 = "Bad Request",
     *     404 = "Not found",
     *     422 = "Bad parameters"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *
     * (name="accountNumber", nullable=false, requirements="([0-9|-]| +)", strict=true, description="Global account
     *                                                  number of the employeer, this is the employer primary key in the system.")
     * (name="firstLastName", nullable=false, requirements="(.)*", strict=true, description="Code of the bank, can be found int he table Bank.")
     * (name="firstLastName", nullable=false, requirements="(.)*", strict=true, description="Code of the bank, can be found int he table Bank.")
     * (name="secondLastName", nullable=true, requirements="(.)*", strict=true, description="Checking or saving account.")
     * (name="name", nullable=false, requirements="(.)*", strict=true, description="Name of the person.")
     * (name="documentType", nullable=false, requirements="(CC|cc|nit|NIT|ce|CE||PASAPORTE)", strict=true, description="File of the letter authorizing symplifica, in base 64.")
     * (name="documentNumber", nullable=false, requirements="\d+", strict=true, description="File of the letter authorizing symplifica, in base 64.")
     * (name="documentExpeditionDate", nullable=true, requirements="[0-9]{4}-[0-9]{2}-[0-9]{2}", strict=true, description="YYYY-MM-DD")
     * (name="civilState", nullable=true, requirements="(SOLTERO|CASADO|UNION LIBRE|VIUDO|DIVORCIADO)", strict=true, description="")
     * (name="address", nullable=false, requirements="(.)*", strict=true, description="")
     * (name="phone", nullable=false, requirements="\d+", strict=true, description="")
     * (name="municipio", nullable=true, requirements="(.)*", strict=true, description="")
     * (name="department", nullable=false, requirements="(.)*", strict=true, description="")
     * (name="mail", nullable=false, requirements="(.)*", strict=true, description="")
     *
     * @return View
     */
    public function postEditNaturalPersonAction(Request $request)
    {
        $path = "ActualizacionPN";
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();

        // Set all the parameters info.
        $regex['accountNumber'] = '([0-9|-]| +)';
        $mandatory['accountNumber'] = true;
        $regex['firstLastName'] = '(.)*';
        $mandatory['firstLastName'] = true;
        $regex['secondLastName'] = '(.)*';
        $mandatory['secondLastName'] = false;
        $regex['name'] = '(.)*';
        $mandatory['name'] = true;
        $regex['documentType'] = '(CC|cc|nit|NIT|ce|CE|PASAPORTE)';
        $mandatory['documentType'] = true;
        $regex['documentNumber'] = '\d+';
        $mandatory['documentNumber'] = true;
        $regex['documentExpeditionDate'] = '[0-9]{4}-[0-9]{2}-[0-9]{2}|(^$)';
        $mandatory['documentExpeditionDate'] = false;
        $regex['civilState'] = '(SOLTERO|CASADO|UNION LIBRE|VIUDO|DIVORCIADO)';
        $mandatory['civil_state'] = false;
        $regex['address'] = '(.)*';
        $mandatory['address'] = true;
        $regex['phone'] = '\d+';
        $mandatory['phone'] = true;
        $regex['municipio'] = '(.)*';
        $mandatory['municipio'] = false;
        $regex['department'] = '(.)*';
        $mandatory['department'] = true;
        $regex['mail'] = '(.)*';
        $mandatory['mail'] = true;

        // Separate first and second name.
        $names = explode(' ', $parameters['name']);
        $parameters['firstFirstName'] = $names[0];
        $parameters['secondFirstName'] = count($names) > 1 ? $names[1] : null;
        //die(print_r($parameters['firstFirstName']));
        $this->validateParamters($parameters, $regex, $mandatory);

        $parameters_fixed = array();
        $info_estudio = new InfoEstudio();
        $info_basica =
        new InfoBasica($parameters['firstLastName'],
                       $parameters['secondLastName'],
                       $parameters['firstFirstName'],
                       $parameters['secondFirstName'],
                       $parameters['documentType'],
                       $parameters['documentNumber'],
                       $parameters['documentExpeditionDate'],
                       null,
                       $parameters['civilState'],
                       null,
                       $parameters['address'],
                       $parameters['phone'],
                       $parameters['municipio'],
                       $parameters['department'],
                       null,
                       null,
                       null,
                       null,
                       null,
                       null,
                       $parameters['mail'],
                       null,
                       null,
                       null,
                       null,
                       null,
                       null,
                       null,
                       null,
                       null,
                       null,
                       null,
                       null,
                       null,
                       null
                     );
        $parameters_fixed['info_basica'] = $info_basica;
        $parameters_fixed['info_estudio'] = $info_estudio;
        $parameters_fixed['cuentaGSC'] = $parameters['accountNumber'];

        /** @var View $res */
        $responseView = $this->callApi($parameters_fixed, $path, "ActualizacionPN");

        return $responseView;
    }


    /**
     * Gets the employer information.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets the employer information.",
     *   statusCodes = {
     *     200 = "Created",
     *     400 = "Bad Request",
     *     404 = "Not found",
     *     422 = "Bad parameters"
     *   }
     * )
     *
     * @param Int $radicatedNumber Radicated number, returned in created method.
     *
     * @return View
     */
    public function getEmployerAction($radicatedNumber)
    {
        $path = "ConsultarApertura";

        $parameters_fixed = array();
        $parameters_fixed['numeroRadicado'] = $radicatedNumber;

        /** @var View $res */
        $responseView = $this->callApi($parameters_fixed, $path, "ConsultarApertura");

        return $responseView;
    }

    /**
     * Registers a new bank account for an employer.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Registers a new bank account for an employer.",
     *   statusCodes = {
     *     200 = "Created",
     *     400 = "Bad Request",
     *     404 = "Not found",
     *     422 = "Bad parameters"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *
     * (name="accountNumber", nullable=false, requirements="([0-9|-]| +)", strict=true, description="Global account
     *                                                  number of the employeer, this is the employer primary key in the system.")
     * (name="bankCode", nullable=false, requirements="(([0-9|-]| )+|(GS|PL))", strict=true, description="Code of the bank, can be found int he table Bank.")
     * (name="accountType", nullable=false, requirements="(AH|CC)", strict=true, description="Checking or saving account.")
     * (name="bankAccountNumber", nullable=false, requirements="([0-9|-]| +)", strict=true, description="Number of the bank account.")
     * (name="expirationDate", nullable=false, requirements="[0-9]{4}-[0-9]{2}-[0-9]{2}", strict=true, description="Expiration date for this account(YYYY-MM-DD).")
     * (name="authorizationDocumentName", nullable=false, requirements="(.)*", strict=true, description="Name of the file of the letter authorizing symplifica.")
     * (name="authorizationDocument", nullable=false, requirements="", strict=true, description="File of the letter authorizing symplifica, in base 64.")
     *
     * @return View
     */
    public function postRegisterBankAccountAction(Request $request)
    {
        $path = "RegistrarCuentaBancaria";
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['accountNumber'] = '([0-9|-]| )+';
        $mandatory['accountNumber'] = true;
        $regex['bankCode'] = '(([0-9|-]| )+|(GS|PL))';
        $mandatory['bankCode'] = true;
        $regex['accountType'] = '(AH|CC)';
        $mandatory['accountType'] = true;
        $regex['bankAccountNumber'] = '([0-9|-]| )+';
        $mandatory['bankAccountNumber'] = true;
        $regex['expirationDate'] = '[0-9]{4}-[0-9]{2}-[0-9]{2}';
        $mandatory['expirationDate'] = true;
        $regex['authorizationDocumentName'] = '(.)*';
        $mandatory['authorizationDocumentName'] = true;
        //$regex['authorizationDocument'] = ''; No regex, binary.
        $mandatory['authorizationDocument'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);

        $parameters_fixed = array();
        $parameters_fixed['cuentaGSC'] = $parameters['accountNumber'];
        $parameters_fixed['codBanco'] = $parameters['bankCode'];
        $parameters_fixed['tipoCuenta'] = $parameters['accountType'];
        $parameters_fixed['numeroCuenta'] = $parameters['bankAccountNumber'];
        $parameters_fixed['fechaVencimiento'] = $parameters['expirationDate'];
        $parameters_fixed['documentoSoporteAutorizacion'] =
        new DocumentoSoporte($parameters['authorizationDocumentName'],
                            base64_encode('Yo ' . $parameters['authorizationDocument'] . ' Autorizo a Symplifica a debitar de mi cuenta bancaria.'));

        /** @var View $res */
        $responseView = $this->callApi($parameters_fixed, $path, "RegistrarCuentaBancaria");

        return $responseView;
    }

    /**
     * Deletes a bank account of an employer.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Deletes a bank account of an employer.",
     *   statusCodes = {
     *     200 = "Created",
     *     400 = "Bad Request",
     *     404 = "Not found",
     *     422 = "Bad parameters"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *
     * (name="accountNumber", nullable=false, requirements="([0-9|-]| +)", strict=true, description="Global account
     *                                                  number of the employeer, this is the employer primary key in the system.")
     * (name="bankCode", nullable=false, requirements="(([0-9|-]| )+|(GS|PL))", strict=true, description="Code of the bank, can be found int he table Bank.")
     * (name="accountType", nullable=false, requirements="(AH|CC)", strict=true, description="Checking or saving account.")
     * (name="bankAccountNumber", nullable=false, requirements="([0-9|-]| +)", strict=true, description="Number of the bank account.")
     *
     * @return View
     */
    public function deleteRemoveBankAccountAction(Request $request)
    {
        $path = "EliminarCuentaBancaria";
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['accountNumber'] = '([0-9|-]| )+';
        $mandatory['accountNumber'] = true;
        $regex['bankCode'] = '(([0-9|-]| )+|(GS|PL))';
        $mandatory['bankCode'] = true;
        $regex['accountType'] = '(AH|CC)';
        $mandatory['accountType'] = true;
        $regex['bankAccountNumber'] = '([0-9|-]| )+';
        $mandatory['bankAccountNumber'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);

        $parameters_fixed = array();
        $parameters_fixed['cuentaGSC'] = $parameters['accountNumber'];
        $parameters_fixed['codBanco'] = $parameters['bankCode'];
        $parameters_fixed['tipoCuenta'] = $parameters['accountType'];
        $parameters_fixed['numeroCuenta'] = $parameters['bankAccountNumber'];

        /** @var View $res */
        $responseView = $this->callApi($parameters_fixed, $path, "EliminarCuentaBancaria");

        return $responseView;
    }

    /**
     * Registers a new beneficiary of an employer, this is an employee.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Registers a new beneficiary of an employer, this is an employee.",
     *   statusCodes = {
     *     200 = "Created",
     *     400 = "Bad Request",
     *     404 = "Not found",
     *     422 = "Bad parameters"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *
     * (name="accountNumber", nullable=false, requirements="([0-9|-]| )+", strict=true, description="Global account
     *                                                  number of the employeer, this is the employer primary key in the system.")
     * (name="documentEmployer", nullable=false, requirements="([0-9|-]| )+", strict=true, description="Document number of the employer.")
     * (name="documentTypeEmployer", nullable=false, requirements="(CC|cc|nit|NIT|ce|CE)", strict=true, description="Document type of the employer")
     * (name="documentEmployee", nullable=false, requirements="([0-9|-]| )+", strict=true, description="Document number of the employee.")
     * (name="documentTypeEmployee", nullable=false, requirements="(CC|NIT)", strict=true, description="Document tpe of the employee.")
     * (name="employeeName", nullable=false, requirements="(.)*", strict=true, description="Name of the employee.")
     * (name="employeeMail", nullable=true, requirements="(.)*", strict=true, description="Mail of the employee, it is optional.")
     * (name="employeeBankCode", nullable=false, requirements="([0-9]|-| )+", strict=true, description="Bank code of the employee, can be found on table Bank.")
     * (name="employeeCellphone", nullable=true, requirements="([0-9])+", strict=true, description="Cellphone of the employee, it is optional.")
     * (name="employeeAccountType", nullable=false, requirements="(AH|CC|EN|DP)", strict=true, description="Employee account type(Savings, checking or encargo fiduciario).")
     * (name="employeeAccountNumber", nullable=false, requirements="([0-9]|-| )+", strict=true, description="Bank account number of the employee.")
     * (name="employeeAddress", nullable=true, requirements="(.)*", strict=true, description="Address of the employee, it is optional.")
     *
     * @return View
     */
    public function postRegisterBeneficiaryAction(Request $request)
    {
        $path = "RegistrarBeneficiario";
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['accountNumber'] = '([0-9|-]| )+';
        $mandatory['accountNumber'] = true;
        $regex['documentEmployer'] = '([0-9|-]| )+';
        $mandatory['documentEmployer'] = true;
        $regex['documentTypeEmployer'] = '(CC|cc|nit|NIT|ce|CE|PASAPORTE|pasaporte)';
        $mandatory['documentTypeEmployer'] = true;
        $regex['documentEmployee'] = '([0-9|-]| )+';
        $mandatory['documentEmployee'] = true;
        $regex['documentTypeEmployee'] = '(CC|cc|nit|NIT|ce|CE|PASAPORTE|pasaporte)';
        $mandatory['documentTypeEmployee'] = true;
        $regex['employeeName'] = '(.)*';
        $mandatory['employeeName'] = true;
        $regex['employeeMail'] = '(.)*';
        $mandatory['employeeMail'] = false;
        $regex['employeeBankCode'] = '([0-9|-]| )+';
        $mandatory['employeeBankCode'] = true;
        $regex['employeeCelphone'] = '([0-9])+';
        $mandatory['employeeCellphone'] = false;
        $regex['employeeAccountType'] = '(AH|CC|EN|DP)';
        $mandatory['employeeAccountType'] = true;
        $regex['employeeAccountNumber'] = '([0-9|-]| )+';
        $mandatory['employeeAccountNumber'] = true;
        $regex['employeeAddress'] = '(.)*';
        $mandatory['employeeAddress'] = false;

        $this->validateParamters($parameters, $regex, $mandatory);

        // Fix the format of document type.
        if($parameters['documentTypeEmployer'] == 'cc' || $parameters['documentTypeEmployer'] == 'CC') {
          $parameters['documentTypeEmployer'] = 'CEDULA';
        }
        if($parameters['documentTypeEmployer'] == 'nit') {
          $parameters['documentTypeEmployer'] = 'NIT';
        }
        if($parameters['documentTypeEmployer'] == 'ce') {
            $parameters['documentTypeEmployer'] = 'CE';
        }
        if($parameters['documentTypeEmployer'] == 'pasaporte') {
            $parameters['documentTypeEmployer'] = 'PASAPORTE';
        }
        if($parameters['documentTypeEmployee'] == 'cc' || $parameters['documentTypeEmployee'] == 'CC') {
          $parameters['documentTypeEmployee'] = 'CEDULA';
        }
        if($parameters['documentTypeEmployee'] == 'nit') {
          $parameters['documentTypeEmployee'] = 'NIT';
        }
        if($parameters['documentTypeEmployee'] == 'ce') {
            $parameters['documentTypeEmployee'] = 'CE';
        }
        if($parameters['documentTypeEmployee'] == 'pasaporte') {
            $parameters['documentTypeEmployee'] = 'PASAPORTE';
        }


        $parameters_fixed = array();
        $parameters_fixed['cuentaGSC'] = $parameters['accountNumber'];
        $parameters_fixed['tipoDocumentoTitular'] = $parameters['documentTypeEmployer'];
        $parameters_fixed['numeroDocumentoTitular'] = $parameters['documentEmployer'];
        $parameters_fixed['tipoDocumentoBeneficiario'] = $parameters['documentTypeEmployee'];
        $parameters_fixed['numeroDocumentoBeneficiario'] = $parameters['documentEmployee'];
        $parameters_fixed['nombre'] = $parameters['employeeName'];
        $parameters_fixed['codBanco'] = $parameters['employeeBankCode'];
        $parameters_fixed['tipoCuenta'] = $parameters['employeeAccountType'];
        $parameters_fixed['numeroCuenta'] = $parameters['employeeAccountNumber'];
        if(isset($parameters['employeeMail']))
          $parameters_fixed['correo'] = $parameters['employeeMail'];
        if(isset($parameters['employeeCellphone']))
          $parameters_fixed['celular'] = $parameters['employeeCellphone'];
        if(isset($parameters['employeeAddress']))
          $parameters_fixed['direccion'] = $parameters['employeeAddress'];

        /** @var View $res */
        $responseView = $this->callApi($parameters_fixed, $path, "RegistrarBeneficiario");

        return $responseView;
    }

    /**
     * Deletes a beneficiary of an employer.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Deletes a beneficiary of an employer.",
     *   statusCodes = {
     *     200 = "Created",
     *     400 = "Bad Request",
     *     404 = "Not found",
     *     422 = "Bad parameters"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *
     * (name="accountNumber", nullable=false, requirements="([0-9|-]| +)", strict=true, description="Global account
     *                                                  number of the employeer, this is the employer primary key in the system.")
     * (name="documentEmployee", nullable=false, requirements="([0-9|-]| )+", strict=true, description="Document number of the employee.")
     * (name="documentTypeEmployee", nullable=false, requirements="(CC|NIT)", strict=true, description="Document tpe of the employee.")
     *
     * @return View
     */
    public function deleteRemoveBeneficiaryAction(Request $request)
    {
        $path = "EliminarBeneficiario";
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['accountNumber'] = '([0-9|-]| )+';
        $mandatory['accountNumber'] = true;
        $regex['documentEmployee'] = '([0-9|-]| )+';
        $mandatory['documentEmployee'] = true;
        $regex['documentTypeEmployee'] = '(CC|cc|nit|NIT|ce|CE|PASAPORTE|pasaporte)';
        $mandatory['documentTypeEmployee'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);

        if($parameters['documentTypeEmployee'] == 'cc' || $parameters['documentTypeEmployee'] == 'CC') {
          $parameters['documentTypeEmployee'] = 'CEDULA';
        }
        if($parameters['documentTypeEmployee'] == 'nit') {
          $parameters['documentTypeEmployee'] = 'NIT';
        }
        if($parameters['documentTypeEmployee'] == 'ce') {
            $parameters['documentTypeEmployee'] = 'CE';
        }
        if($parameters['documentTypeEmployee'] == 'pasaporte') {
            $parameters['documentTypeEmployee'] = 'PASAPORTE';
        }
        $parameters_fixed = array();
        $parameters_fixed['cuentaGSC'] = $parameters['accountNumber'];
        $parameters_fixed['tipoDocumentoBeneficiario'] = $parameters['documentTypeEmployee'];
        $parameters_fixed['numeroDocumentoBeneficiario'] = $parameters['documentEmployee'];

        /** @var View $res */
        $responseView = $this->callApi($parameters_fixed, $path, "EliminarBeneficiario");

        return $responseView;
    }

    /**
     * Gets the payment methods of an employer.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets the payment methods of an employer.",
     *   statusCodes = {
     *     200 = "Created",
     *     400 = "Bad Request",
     *     404 = "Not found",
     *     422 = "Bad parameters"
     *   }
     * )
     *
     * @param Int $accountNumber Global account number of the employeer.
     *
     * @return View
     */
    public function getEmployerPaymentMethodsAction($accountNumber)
    {
       // TODO(daniel.serrano): Arreglar el formato de respuesta de este servicio(IMPORTANTE!).
        $path = "ConsultarFuentesPago";

        $parameters_fixed = array();
        $parameters_fixed['cuentaGSC'] = $accountNumber;

        /** @var View $res */
        $responseView = $this->callApi($parameters_fixed, $path, "ConsultarFuentesPago");

        $temp = $this->handleView($responseView);
        $data = json_decode($temp->getContent(), true);
        $code = json_decode($temp->getStatusCode(), true);

        if($code != 200 || !isset($data['cuentas']['cuenta'])) {
          $view = View::create();
          $view->setStatusCode($code);
          $view->setData([]);
          return $view;
        }

        $res = array();
        $res['payment-methods'] = array();
        $aux = array();
        foreach($data['cuentas']['cuenta'] as $key => $val) {
          if(is_numeric($key)) { // es numero.
            $res['payment-methods'][] = $val;
          } else {
            $aux[$key] = $val;
          }
        }
        if(count($aux) > 0)
          $res['payment-methods'][] = $aux;

        $view = View::create();
        $view->setStatusCode($code);
        $view->setData($res);

        return $view;
    }

    /**
     * Registers a new beneficiary of an employer, this is an employee.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Registers a new beneficiary of an employer, this is an employee.",
     *   statusCodes = {
     *     200 = "Created",
     *     400 = "Bad Request",
     *     404 = "Not found",
     *     422 = "Bad parameters"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *
     * (name="accountNumber", nullable=false, requirements="([0-9|-]| )+", strict=true, description="Global account
     *                                                  number of the employeer, this is the employer primary key in the system.")
     * (name="accountId", nullable=false, requirements="([0-9|-]| )+", strict=true, description="Internal account number.")
     * (name="value", nullable=false, requirements="[0-9]+(\.[0-9]+)?", strict=true, description="value of the transaction.")
     *
     * @return View
     */
    public function postClientGscPaymentAction(Request $request)
    {
        $path = "SolicitarRecaudo";
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['accountNumber'] = '([0-9|-]| )+';
        $mandatory['accountNumber'] = true;
        $regex['accountId'] = '([0-9|-]| )+';
        $mandatory['accountId'] = true;
        $regex['value'] = '[0-9]+(\.[0-9]+)?';
        $mandatory['value'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);

        $parameters_fixed = array();
        $parameters_fixed['cuentaGSC'] = $parameters['accountNumber'];
        $parameters_fixed['idCuenta'] = $parameters['accountId'];
        $parameters_fixed['valor'] = $parameters['value'];

        /** @var View $res */
        $responseView = $this->callApi($parameters_fixed, $path, "SolicitarRecaudo");

        return $responseView;
    }

    /**
     * Gets the payment state.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets the payment state.",
     *   statusCodes = {
     *     200 = "Created",
     *     400 = "Bad Request",
     *     404 = "Not found",
     *     422 = "Bad parameters"
     *   }
     * )
     *
     * @param Int $radicatedNumber Radicated number when the payment was made.
     *
     * @return View
     */
    public function getPaymentStateAction($radicatedNumber)
    {
        $path = "ConsultarEstadoRecaudo";

        $parameters_fixed = array();
        $parameters_fixed['numeroRadicado'] = $radicatedNumber;

        /** @var View $res */
        $responseView = $this->callApi($parameters_fixed, $path, "ConsultarEstadoRecaudo");

        return $responseView;
    }

    /**
     * Registers a new dispersion.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Registers a new dispersion",
     *   statusCodes = {
     *     200 = "Created",
     *     400 = "Bad Request",
     *     404 = "Not found",
     *     422 = "Bad parameters"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *
     * (name="accountNumber", nullable=false, requirements="([0-9|-]| )+", strict=true, description="Global account
     *                                                  number of the employeer, this is the employer primary key in the system.")
     * (name="documentTypeEmployee", nullable=false, requirements="(CC|cc|nit|NIT)", strict=true, description="Document type of the employee.")
     * (name="documentEmployee", nullable=false, requirements="([0-9|-]| )+", strict=true, description="Document number of the employee.")
     * (name="bankCode", nullable=false, requirements="(([0-9|-]| )+|(GS|PL))", strict=true, description="Code of the bank of the employee. This can be obtained in the beneficiary.")
     * (name="accountType", nullable=false, requirements="(AH|CC|EN|DP)", strict=true, description="Account type of the employee savings checking.")
     * (name="accountBankNumber", nullable=false, requirements="([0-9|-]| )+", strict=true, description="Account number of the employee, real number not internal.")
     * (name="value", nullable=false, requirements="[0-9]+(\.[0-9]+)?", strict=true, description="value of the transaction.")
     * (name="reference", nullable=true, requirements="(.)*", strict=false, description="Reference number for the pila, only if its different from the document number.")
     * (name="payment_date", nullable=true, requirements="[0-9]{4}-[0-9]{2}-[0-9]{2}", strict=true, description="Day when it has to be payed, can be null and will be payed inmediatley.(YYYY-MM-DD).")
     * (name="source", nullable=true, requirements="101|100", description="showing the source of the account 100 for hightech and 101 for novopayment, the default will be 100.)
     *
     * @return View
     */
    public function postRegisterDispersionAction(Request $request)
    {
        $path = "RegistrarOrdenPago";
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['accountNumber'] = '([0-9|-]| )+';
        $mandatory['accountNumber'] = true;
        $regex['documentTypeEmployee'] = '(CC|cc|nit|NIT)';
        $mandatory['documentTypeEmployee'] = true;
        $regex['documentEmployee'] = '([0-9|-]| )+';
        $mandatory['documentEmployee'] = true;
        $regex['bankCode'] = '(([0-9|-]| )+|(GS|PL|PC))';
        $mandatory['bankCode'] = true;
        $regex['accountType'] = '(AH|CC|EN|DP)';
        $mandatory['accountType'] = true;
        $regex['accountBankNumber'] = '([0-9|-]| )+';
        $mandatory['accountBankNumber'] = true;
        $regex['value'] = '[0-9]+(\.[0-9]+)?';
        $mandatory['value'] = true;
        $regex['reference'] = '(.)*';
        $mandatory['reference'] = false;
        $regex['payment_date'] = '[0-9]{4}-[0-9]{2}-[0-9]{2}';
        $mandatory['payment_date'] = false;
        $regex['source'] = '(101|100)';
        $mandatory['source'] = false;

        $this->validateParamters($parameters, $regex, $mandatory);

        if(!isset($parameters['source']))
          $parameters['source'] = 100; // Meaning hightech.

        if($parameters['documentTypeEmployee'] == 'cc' || $parameters['documentTypeEmployee'] == 'CC') {
          $parameters['documentTypeEmployee'] = 'CEDULA';
        }
        if($parameters['documentTypeEmployee'] == 'nit') {
          $parameters['documentTypeEmployee'] = 'NIT';
        }

        $parameters_fixed = array();
        $parameters_fixed['cuentaGSC'] = $parameters['accountNumber'];
        $parameters_fixed['tipoDocumentoBeneficiario'] = $parameters['documentTypeEmployee'];
        $parameters_fixed['numeroDocumentoBeneficiario'] = $parameters['documentEmployee'];
        $parameters_fixed['codBanco'] = $parameters['bankCode'];
        $parameters_fixed['tipoCuenta'] = $parameters['accountType'];
        $parameters_fixed['numeroCuenta'] = $parameters['accountBankNumber'];
        $parameters_fixed['valor'] = $parameters['value'];
        $parameters_fixed['origen'] = $parameters['source'];
        if(isset($parameters['reference']))
          $parameters_fixed['referencia'] = $parameters['reference'];
        if(isset($parameters['payment_date']))
          $parameters_fixed['fechaPago'] = $parameters['payment_date'];
        else
          $parameters_fixed['fechaPago'] = null;
        /** @var View $res */
        $responseView = $this->callApi($parameters_fixed, $path, "RegistrarOrdenPago");

        return $responseView;
    }


    /**
     * Checks the state of a transaction.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Checks the state of a transaction",
     *   statusCodes = {
     *     200 = "Created",
     *     400 = "Bad Request",
     *     404 = "Not found",
     *     422 = "Bad parameters"
     *   }
     * )
     *
     * @param Int $radicatedNumber radicated number of the collection.
     *
     * @return View
     */
    public function getTransactionStateAction( $radicatedNumber)
    {
        $path = "ConsultarEstadoRecaudo";
        $parameters_fixed = array();
        $parameters_fixed['numeroRadicado'] = $radicatedNumber;

        /** @var View $res */
        $responseView = $this->callApi($parameters_fixed, $path, "ConsultarEstadoRecaudo");

        return $responseView;
    }

    /**
     * Gets the dispersion state.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets the dispersion state.",
     *   statusCodes = {
     *     200 = "Created",
     *     400 = "Bad Request",
     *     404 = "Not found",
     *     422 = "Bad parameters"
     *   }
     * )
     *
     * @param Int $radicatedNumber radicated number when the dispersion was made.
     *
     * @return View
     */
    public function getDispersionStateAction($radicatedNumber)
    {
        $path = "ConsultarEstado";

        $parameters_fixed = array();
        $parameters_fixed['numeroRadicado'] = $radicatedNumber;

        /** @var View $res */
        $responseView = $this->callApi($parameters_fixed, $path, "ConsultarEstado");

        return $responseView;
    }

    /**
     * Call the devolution of the collected money in case that the distribution fails<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Calls for devolution",
     *   statusCodes = {
     *     200 = "Created",
     *     400 = "Bad Request",
     *     404 = "Not found",
     *     422 = "Bad parameters"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *
     * (name="source", nullable=true, requirements="101|100", description="showing the source of the account 100 for hightech and 101 for novopayment, the default will be 100.")
     * (name="accountNumber", nullable=false, requirements="[0-9]+")
     * (name="accountId", nullable=false, requirements="[0-9]+")
     * (name="value", nullable=false, requirements="[0-9]+(\.[0-9]+)?")
     *
     * @return View
     */
    public function postRegisterDevolutionAction(Request $request)
    {
        $path = "RegistrarDevolucion";
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['accountNumber'] = '[0-9]+';
        $mandatory['accountNumber'] = true;
        $regex['accountId'] = '[0-9]+';
        $mandatory['accountId'] = true;
        $regex['value'] = '[0-9]+(\.[0-9]+)?';
        $mandatory['value'] = true;
        $regex['source'] = '(101|100)';
        $mandatory['source'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);

        if(!isset($parameters['source']))
          $parameters['source'] = 100; // Meaning hightech.

        $parameters_fixed = array();
        $parameters_fixed['cuentaGSC'] = $parameters['accountNumber'];
        $parameters_fixed['idCuenta'] = $parameters['accountId'];
        $parameters_fixed['valor'] = $parameters['value'];
        $parameters_fixed['origen'] = $parameters['source'];

        /** @var View $res */
        $responseView = $this->callApi($parameters_fixed, $path, "RegistrarDevolucion");

        return $responseView;
    }

    /**
     * Registers an employer to Enlace Operativo<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Register employee to Enlace Operativo",
     *   statusCodes = {
     *     200 = "Created",
     *     400 = "Bad Request",
     *     404 = "Not found",
     *     422 = "Bad parameters"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *
     * (name="GSCAccount", nullable=false, requirements="[0-9]+", description="Id used by Hightech to store employers")
     *
     * @return View
     */
     public function postRegisterEmployerToPilaOperatorAction(Request $request){
       $path = "RegistrarEmpleadorPila";
       $parameters = $request->request->all();
       $regex = array();
       $mandatory = array();
       // Set all the parameters info.
       $regex['GSCAccount'] = '[0-9]+';
       $mandatory['GSCAccount'] = true;

       $this->validateParamters($parameters, $regex, $mandatory);

       $parameters_fixed = array();
       $parameters_fixed['cuentaGSC'] = $parameters['GSCAccount'];

       /** @var View $res */
       $responseView = $this->callApi($parameters_fixed, $path, "RegistrarEmpleadorPila");

       $temp = $this->handleView($responseView);
       $data = json_decode($temp->getContent(), true);
       $code = json_decode($temp->getStatusCode(), true);

       $view = View::create();
       $view->setStatusCode($code);
       $view->setData($data);
       return $view;
     }

  /**
   * Check state of the employer registration on the pila operator<br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Check state of the employer registration on Enlace Operativo",
   *   statusCodes = {
   *     200 = "Created",
   *     400 = "Bad Request",
   *     404 = "Not found",
   *     422 = "Bad parameters"
   *   }
   * )
   *
   * @param Request $request.
   * Rest Parameters:
   *
   * (name="radicatedNumber", nullable=false, requirements="[0-9]+", description="number given by Hightech when the request is send")
   *
   * @return View
   */
  public function postCheckStateRegisterEmployerPilaOperatorAction(Request $request){
    $path = "ConsultarEstadoRegistroEmpleadorPila";
    $parameters = $request->request->all();
    $regex = array();
    $mandatory = array();
    // Set all the parameters info.
    $regex['radicatedNumber'] = '[0-9]+';
    $mandatory['radicatedNumber'] = true;

    $this->validateParamters($parameters, $regex, $mandatory);

    $parameters_fixed = array();
    $parameters_fixed['numeroRadicado'] = $parameters['radicatedNumber'];

    /** @var View $res */
    $responseView = $this->callApi($parameters_fixed, $path, "ConsultarEstadoRegistroEmpleadorPila");

    $temp = $this->handleView($responseView);
    $data = json_decode($temp->getContent(), true);
    $code = json_decode($temp->getStatusCode(), true);

    if($code != 200){
      $view = View::create();
      $view->setStatusCode($code);
      $view->setData($data);
      return $view;
    }
    
    $estadoEmpleador = -1;
    
    if( isset($data['estadoEmpleador']) && $data['estadoEmpleador'] != NULL){
      if($data['estadoEmpleador'] == "C"){
        $estadoEmpleador = 0;
      }
      else{
        $estadoEmpleador = 1;
      }
    }
    
    $request->setMethod("PUT");
    $request->request->add(array(
      "radicatedNumber"=> $parameters['radicatedNumber'],
      "registerState"=> $estadoEmpleador,
      "errorLog" => isset($data['logBase64']) && $data['logBase64'] != NULL ? $data['logBase64'] : "",
      "errorMessage" => isset($data['mensajesError']) && $data['mensajesError'] != NULL ? $data['mensajesError']: ""
    ));

    return $this->forward('RocketSellerTwoPickBundle:HighTechRest:putProcessRegisterEmployerPilaOperator', array('_format' => 'json'));
  }


  /**
   * Uploads a pila file to Enlace Operativo<br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Uploads a pila file to Enlace Operativo",
   *   statusCodes = {
   *     200 = "Created",
   *     400 = "Bad Request",
   *     404 = "Not found",
   *     422 = "Bad parameters"
   *   }
   * )
   *
   * @param Request $request.
   * Rest Parameters:
   *
   * (name="GSCAccount", nullable=false, requirements="[0-9]+", description="Id used by Hightech to store employers")
   * (name="FileToUpload", nullable=false, requirements="(.)*", description="File that will be upload")
   *
   * @return View
   */
  public function postUploadFileToPilaOperatorAction(Request $request){
    $path = "CargarPlanillaPila";
    $parameters = $request->request->all();
    $regex = array();
    $mandatory = array();
    // Set all the parameters info.
    $regex['GSCAccount'] = '[0-9]+';
    $mandatory['GSCAccount'] = true;

    $this->validateParamters($parameters, $regex, $mandatory);

    $parameters_fixed = array();
    $parameters_fixed['cuentaGSC'] = $parameters['GSCAccount'];
    $parameters_fixed['planillaBase64'] = $parameters['FileToUpload'];

    /** @var View $res */
    $responseView = $this->callApi($parameters_fixed, $path, "CargarPlanillaPila");

    $temp = $this->handleView($responseView);
    $data = json_decode($temp->getContent(), true);
    $code = json_decode($temp->getStatusCode(), true);

    $view = View::create();
    $view->setStatusCode($code);
    $view->setData($data);
    return $view;
  }

  /**
   * Check state of the pila file upload process on pila operator<br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Check state of the pila file upload process on pila operator",
   *   statusCodes = {
   *     200 = "Created",
   *     400 = "Bad Request",
   *     404 = "Not found",
   *     422 = "Bad parameters"
   *   }
   * )
   *
   * @param Request $request.
   * Rest Parameters:
   *
   * (name="radicatedNumber", nullable=false, requirements="[0-9]+", description="number given by Hightech when the request is send")
   *
   * @return View
   */
  public function postCheckStateUploadFilePilaOperatorAction(Request $request){
    $path = "ConsultarEstadoCargaPlanillaPila";
    $parameters = $request->request->all();
    $regex = array();
    $mandatory = array();
    // Set all the parameters info.
    $regex['radicatedNumber'] = '[0-9]+';
    $mandatory['radicatedNumber'] = true;

    $this->validateParamters($parameters, $regex, $mandatory);

    $parameters_fixed = array();
    $parameters_fixed['numeroRadicado'] = $parameters['radicatedNumber'];

    /** @var View $res */
    $responseView = $this->callApi($parameters_fixed, $path, "ConsultarEstadoCargaPlanillaPila");

    $temp = $this->handleView($responseView);
    $data = json_decode($temp->getContent(), true);
    $code = json_decode($temp->getStatusCode(), true);

    if($code != 200){
      $view = View::create();
      $view->setStatusCode($code);
      $view->setData($data);
      return $view;
    }
  
    $estadoPlanilla = -1;
  
    if( isset($data['estadoPlanilla']) && $data['estadoPlanilla'] != NULL){
      if($data['estadoPlanilla'] == "C"){
        $estadoPlanilla = 0;
      }
      else{
        $estadoPlanilla = 1;
      }
    }

    $request->setMethod("PUT");
    $request->request->add(array(
      "radicatedNumber"=> $parameters['radicatedNumber'],
      "planillaState"=> $estadoPlanilla,
      "errorLog" => isset($data['logBase64']) && $data['logBase64'] != NULL ? $data['logBase64'] : "",
      "planillaNumber" => isset($data['numeroPlanilla']) && $data['numeroPlanilla'] != NULL ? $data['numeroPlanilla'] : "",
      "errorMessage" => isset($data['mensajesError']) && $data['mensajesError'] != NULL ? $data['mensajesError']: ""
    ));

    return $this->forward('RocketSellerTwoPickBundle:HighTechRest:putProcessUploadFilePilaOperator', array('_format' => 'json'));
  }
  
  /**
   * Gets the payslip of the pila payment<br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Gets the payslip of the pila payment",
   *   statusCodes = {
   *     200 = "Created",
   *     400 = "Bad Request",
   *     404 = "Not found",
   *     422 = "Bad parameters"
   *   }
   * )
   *
   * @param Int $GSCAccount Global account number of the employeer.
   * @param String $payslipNumber Number of the payslip to be retrieved
   *
   * @return View
   */
  public function getPayslipPilaPaymentAction($GSCAccount, $payslipNumber){
    $path = "DescargarComprobantePagoPila";
    
    $parameters_fixed = array();
    $parameters_fixed['cuentaGSC'] = $GSCAccount;
    $parameters_fixed['numeroPlanilla'] = $payslipNumber;
    
    /** @var View $res */
    $responseView = $this->callApi($parameters_fixed, $path, "DescargarComprobantePagoPila");
    
    $temp = $this->handleView($responseView);
    $data = json_decode($temp->getContent(), true);
    $code = json_decode($temp->getStatusCode(), true);
    
    $view = View::create();
    $view->setStatusCode($code);
    $view->setData($data);
    return $view;
  }
  
  /**
   * Retrieve a radicated number with no action <br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Retrieve a radicated number with no action",
   *   statusCodes = {
   *     200 = "Created",
   *     400 = "Bad Request",
   *     404 = "Not found",
   *     422 = "Bad parameters"
   *   }
   * )
   *
   * @param Request $request.
   * Rest Parameters:
   *
   * (name="radicatedNumber", nullable=false, requirements="[0-9]+", description="number given by Hightech when the request is send")
   * (name="path", nullable=false, requirements="(.)*", description="Service to be called")
   *
   * @return View
   */
  public function postCheckStateRegisterEmployerPilaOperatorWithoutAction(Request $request){
    $parameters = $request->request->all();
    $path = $parameters['path'];
    $regex = array();
    $mandatory = array();
    // Set all the parameters info.
    $regex['radicatedNumber'] = '[0-9]+';
    $mandatory['radicatedNumber'] = true;
    $regex['path'] = '(.)*';
    $mandatory['path'] = true;
    
    $this->validateParamters($parameters, $regex, $mandatory);
    
    $parameters_fixed = array();
    $parameters_fixed['numeroRadicado'] = $parameters['radicatedNumber'];
    
    /** @var View $res */
    $responseView = $this->callApi($parameters_fixed, $path, $path);
    
    $temp = $this->handleView($responseView);
    $data = json_decode($temp->getContent(), true);
    $code = json_decode($temp->getStatusCode(), true);
    
    $view = View::create();
    $view->setStatusCode($code);
    $view->setData($data);
    return $view;
  }

    /**
     * Gets the proof of the severances payment<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Gets the proof of the severances payment",
     *   statusCodes = {
     *     200 = "Created",
     *     400 = "Bad Request",
     *     404 = "Not found",
     *     422 = "Bad parameters"
     *   }
     * )
     *
     * @param Int $GSCAccount Global account number of the employeer.
     * @param String $filename name of the severances document payment to be obtained
     *
     * @return View
     */
    public function getSeverancesPaymentAction($GSCAccount, $filename){
        $path = "DescargarComprobantePagoCesantias";

        $parameters_fixed = array();
        $parameters_fixed['cuentaGSC'] = $GSCAccount;
        $parameters_fixed['numeroPlanilla'] = $filename;

        /** @var View $res */
        $responseView = $this->callApi($parameters_fixed, $path, "DescargarComprobantePagoCesantias");

        $temp = $this->handleView($responseView);
        $data = json_decode($temp->getContent(), true);
        $code = json_decode($temp->getStatusCode(), true);

        $view = View::create();
        $view->setStatusCode($code);
        $view->setData($data);
        return $view;
    }
  
}

?>
