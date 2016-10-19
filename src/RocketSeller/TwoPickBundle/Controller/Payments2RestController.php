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
     * (name="documentType", nullable=false, requirements="(CC|cc|nit|NIT|ce|CE)", strict=true, description="File of the letter authorizing symplifica, in base 64.")
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
     * (name="documentType", nullable=false, requirements="(CC|cc|nit|NIT|ce|CE)", strict=true, description="File of the letter authorizing symplifica, in base 64.")
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
        $regex['documentType'] = '(CC|cc|nit|NIT|ce|CE)';
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
        $regex['documentTypeEmployer'] = '(CC|cc|nit|NIT|ce|CE|PASAPORTE)';
        $mandatory['documentTypeEmployer'] = true;
        $regex['documentEmployee'] = '([0-9|-]| )+';
        $mandatory['documentEmployee'] = true;
        $regex['documentTypeEmployee'] = '(CC|cc|nit|NIT)';
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
        if($parameters['documentTypeEmployer'] == 'cc' || $parameters['documentTypeEmployer'] == 'CC' || $parameters['documentTypeEmployer'] == 'CE' || $parameters['documentTypeEmployer'] == 'ce') {
          $parameters['documentTypeEmployer'] = 'CEDULA';
        }
        if($parameters['documentTypeEmployer'] == 'nit') {
          $parameters['documentTypeEmployer'] = 'NIT';
        }
        if($parameters['documentTypeEmployee'] == 'cc' || $parameters['documentTypeEmployee'] == 'CC') {
          $parameters['documentTypeEmployee'] = 'CEDULA';
        }
        if($parameters['documentTypeEmployee'] == 'nit') {
          $parameters['documentTypeEmployee'] = 'NIT';
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
        $regex['documentTypeEmployee'] = '(CC|cc|nit|NIT)';
        $mandatory['documentTypeEmployee'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);

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
        $regex['bankCode'] = '(([0-9|-]| )+|(GS|PL))';
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

    $request->setMethod("PUT");
    $request->request->add(array(
      "radicatedNumber"=> $parameters['radicatedNumber'],
      "registerState"=> $data['estadoEmpleador'],
      "errorLog" => $data['logBase64'] != NULL ? $data['logBase64'] : "",
      "errorMessage" => $data['mensajesError'] != NULL ? $data['mensajesError']: ""
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

    $request->setMethod("PUT");
    $request->request->add(array(
      "radicatedNumber"=> $parameters['radicatedNumber'],
      "planillaState"=> $data['estadoPlanilla'],
      "errorLog" => $data['logBase64'] != NULL ? $data['logBase64'] : "",
      "planillaNumber" => $data['numeroPlanilla'] != NULL ? $data['numeroPlanilla'] : "",
      "errorMessage" => $data['mensajesError'] != NULL ? $data['mensajesError']: ""
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
    //$data = array( 'comprobanteBase64' => "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" );
    
    $temp = $this->handleView($responseView);
    $data = json_decode($temp->getContent(), true);
    $code = json_decode($temp->getStatusCode(), true);
    
    $data["codigoRespuesta"] = "OK";
    $data["comprobanteBase64"] = "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";
    
    $view = View::create();
    $view->setStatusCode($code);
    $view->setData($data);
    return $view;
  }
}

?>
