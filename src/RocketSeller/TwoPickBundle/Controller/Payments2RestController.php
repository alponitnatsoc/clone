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
        function MemberLoginCredentials($a,$b) {
          $this->nombre = $a;
          $this->base64 = $b;
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
      $url_base = "http://52.86.183.212:8080/dssp/services/";
       $opts = array(
           //"ssl" => array("ciphers" => "RC4-SHA")
       );

       $client = new \SoapClient($url_base . $path . "?wsdl",
         array("connection_timeout" => 20,
               "trace" => true,
               "exceptions" => true,
               "stream_context" => stream_context_create($opts),
               //"login" => $login,
               //"password" => $pass
        ));

      $res = $client->__soapCall($methodName, array($parameters));

      // This other way also works, may be usefull.
      //$res = $client->RegistrarBeneficiario($parameters);
      $res = (array)$res;

      $responseCode = $res['codigoRespuesta'];

      // Remove the status code so we can return the entire object.
      unset($res['codigoRespuesta']);

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
     * (name="bankCode", nullable=false, requirements="([0-9|-]| +)", strict=true, description="Code of the bank, can be found int he table Bank.")
     * (name="accountType", nullable=false, requirements="(AH|CC)", strict=true, description="Checking or saving account.")
     * (name="bankAccountNumber", nullable=false, requirements="([0-9|-]| +)", strict=true, description="Number of the bank account.")
     * (name="autorizationDocumentName", nullable=false, requirements="(.)*", strict=true, description="Name of the file of the letter authorizing symplifica.")
     * (name="autorizationDocument", nullable=false, requirements="", strict=true, description="File of the letter authorizing symplifica, in base 64.")
     *
     * @return View
     */
    public function postRegisterBankAccountAction(Request $request)
    {
        // This is the asigned path by NovoPayment to this action.
        $path = "RegistrarCuentaBancaria";
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['accountNumber'] = '([0-9|-]| )+';
        $mandatory['accountNumber'] = true;
        $regex['bankCode'] = '([0-9|-]| )+';
        $mandatory['bankCode'] = true;
        $regex['accountType'] = '(AH|CC)';
        $mandatory['accountType'] = true;
        $regex['bankAccountNumber'] = '([0-9|-]| )+';
        $mandatory['bankAccountNumber'] = true;
        $regex['autorizationDocumentName'] = '(.)*';
        $mandatory['autorizationDocumentName'] = true;
        //$regex['autorizationDocument'] = ''; No regex, binary.
        $mandatory['autorizationDocument'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);

        $parameters_fixed = array();
        $parameters_fixed['cuentaGSC'] = $parameters['accountNumber'];
        $parameters_fixed['codBanco'] = $parameters['bankCode'];
        $parameters_fixed['tipoCuenta'] = $parameters['accountType'];
        $parameters_fixed['numeroCuenta'] = $parameters['bankAccountNumber'];
        $parameters_fixed['documentoSoporteAutorizacion'] =
        new DocumentoSoporte($parameters['autorizationDocumentName'], $parameters['autorizationDocument']);

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
     * (name="bankCode", nullable=false, requirements="([0-9|-]| +)", strict=true, description="Code of the bank, can be found int he table Bank.")
     * (name="accountType", nullable=false, requirements="(AH|CC)", strict=true, description="Checking or saving account.")
     * (name="bankAccountNumber", nullable=false, requirements="([0-9|-]| +)", strict=true, description="Number of the bank account.")
     *
     * @return View
     */
    public function deleteRemoveBankAccountAction(Request $request)
    {
        // This is the asigned path by NovoPayment to this action.
        $path = "EliminarCuentaBancaria";
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['accountNumber'] = '([0-9|-]| )+';
        $mandatory['accountNumber'] = true;
        $regex['bankCode'] = '([0-9|-]| )+';
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
     * (name="documentTypeEmployer", nullable=false, requirements="(CC|NIT)", strict=true, description="Document type of the employer")
     * (name="documentEmployee", nullable=false, requirements="([0-9|-]| )+", strict=true, description="Document number of the employee.")
     * (name="documentTypeEmployee", nullable=false, requirements="(CC|NIT)", strict=true, description="Document tpe of the employee.")
     * (name="employeeName", nullable=false, requirements="(.)*", strict=true, description="Name of the employee.")
     * (name="employeeMail", nullable=true, requirements="(.)*", strict=true, description="Mail of the employee, it is optional.")
     * (name="employeeBankCode", nullable=false, requirements="([0-9]|-| )+", strict=true, description="Bank code of the employee, can be found on table Bank.")
     * (name="employeeCelphone", nullable=true, requirements="([0-9])+", strict=true, description="Cellphone of the employee, it is optional.")
     * (name="employeeAccountType", nullable=false, requirements="(AH|CC|EN)", strict=true, description="Employee account type(Savings, checking or encargo fiduciario).")
     * (name="employeeAccountNumber", nullable=false, requirements="([0-9]|-| )+", strict=true, description="Bank account number of the employee.")
     * (name="employeeAddress", nullable=true, requirements="(.)*", strict=true, description="Address of the employee, it is optional.")
     *
     * @return View
     */
    public function postRegisterBeneficiaryAction(Request $request)
    {
        // This is the asigned path by NovoPayment to this action.
        $path = "RegistrarBeneficiario";
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['accountNumber'] = '([0-9|-]| )+';
        $mandatory['accountNumber'] = true;
        $regex['documentEmployer'] = '([0-9|-]| )+';
        $mandatory['documentEmployer'] = true;
        $regex['documentTypeEmployer'] = '(CC|NIT)';
        $mandatory['documentTypeEmployer'] = true;
        $regex['documentEmployee'] = '([0-9|-]| )+';
        $mandatory['documentEmployee'] = true;
        $regex['documentTypeEmployee'] = '(CC|NIT)';
        $mandatory['documentTypeEmployee'] = true;
        $regex['employeeName'] = '(.)*';
        $mandatory['employeeName'] = true;
        $regex['employeeMail'] = '(.)*';
        $mandatory['employeeMail'] = false;
        $regex['employeeBankCode'] = '([0-9|-]| )+';
        $mandatory['employeeBankCode'] = true;
        $regex['employeeCelphone'] = '([0-9])+';
        $mandatory['employeeCelphone'] = false;
        $regex['employeeAccountType'] = '(AH|CC|EN)';
        $mandatory['employeeAccountType'] = true;
        $regex['employeeAccountNumber'] = '([0-9|-]| )+';
        $mandatory['employeeAccountNumber'] = true;
        $regex['employeeAddress'] = '(.)*';
        $mandatory['employeeAddress'] = false;

        $this->validateParamters($parameters, $regex, $mandatory);

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
        if(isset($parameters['employeeCelphone']))
          $parameters_fixed['celular'] = $parameters['employeeCelphone'];
        if(isset($parameters['employeeAddress']))
          $parameters_fixed['direccion'] = $parameters['employeeAddress'];

        /** @var View $res */
        $responseView = $this->callApi($parameters_fixed, $path, "RegistrarBeneficiario");

        return $responseView;
    }


}

?>
