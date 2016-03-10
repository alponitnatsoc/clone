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
class PaymentsRestController extends FOSRestController
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
    public function callApi($headers, $parameters, $path, $action = "post", $timeout = 10)
    {
        //$client = $this->get('guzzle.client.api_rest');
        // Line bellow was working, commneted for the change of VPN.
        $client = new Client(['http_errors' => false]);
        $sslParams = array('cert' => ['/home/ubuntu/.ssh/myKeystore.pem', 'N0v0payment']);
        //$client->setDefaultOption('verify', '/home/ubuntu/.ssh/MyKeystore.pem');
        /*$request = $client->get('', array(), array(
            'cert' => array('/home/ubuntu/.ssh/MyKeystore.pem', 'N0v0payment')
        ));*/
        // $url_request = $this->container->getParameter('novo_payments_url') ;
        // URL used for test porpouses, the line above should be used in production.
        if (isset($_SERVER['SERVER_NAME']) && in_array($_SERVER['SERVER_NAME'], array('localhost', '127.0.0.1'))) {
            if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '80') {
                $url_request = "http://localhost/api/public/v1/mock" . $path;
            } else {
                $url_request = "http://localhost:8001/api/public/v1/mock" . $path;
            }
        } else {
            //$url_request = "http://10.0.0.5:8081/3_payment/1.0" . $path;
            $url_request = "https://72.46.255.110:8003/3_payment/1.0" . $path;
        }
        $response = null;
        $options = array(
            'headers' => $headers,
            'json' => $parameters,
            'timeout' => $timeout,
            'verify' => true//'/home/ubuntu/.ssh/myKeystore.pem'
        );
        try {
            if ($action == "post") {
                $response = $client->post($url_request, $options, $sslParams);
            } else if ($action == "delete") {
                $response = $client->delete($url_request, $options, $sslParams);
            } else if ($action == "get") {
                $response = $client->get($url_request, $options, $sslParams);
            } else if ($action == "put") {
                $response = $client->put($url_request, $options, $sslParams);
            }
        } catch (Exception $e) {

        }
        $view = View::create();
        $view->setStatusCode($response->getStatusCode());

        // We decode the json string, meaning we transform it into an array.
        $view->setData(json_decode($response->getBody(), true));

        return $view;
    }

    /**
     * It sets the headers for the payments request. Each request method, recieves
     * parameters in case the client wants something different, but in most cases
     * the header is the same, this function sets de default values.
     * @param Array with the options in the request
     * @return Array with the header options.
     */
    private function setHeaders($parameters = null)
    {
        $header = array();
        $header['x-channel'] = (!$parameters || !isset($parameters['channel'])) ?
                'WEB' : $parameters['channel'];
        $header['x-country'] = (!$parameters || !isset($parameters['country'])) ?
                'Co' : $parameters['country'];
        $header['language'] = (!$parameters || !isset($parameters['language'])) ?
                'es' : $parameters['language'];
        $header['content-type'] = (!$parameters ||
                !isset($parameters['content_type'])) ?
                'application/json' :
                $parameters['content_type'];
        $header['accept'] = (!$parameters || !isset($parameters['accept'])) ?
                'application/json' : $parameters['accept'];
        return $header;
    }

    /**
     * Insert a new client into the payments system(3.1 in Novopayment).<br/>
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
     * @param Request $request.
     * Rest Parameters:
     *
     * (name="documentType", nullable=false, requirements="([A-Z|a-z]){2}", strict=true, description="documentType.")
     * (name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
     * (name="name", nullable=false, requirements="([a-z|A-Z| ])+", strict=true, description="first name.")
     * (name="lastName", nullable=false, requirements="([a-z|A-Z| ])+", strict=true, description="last name.")
     * (name="year", nullable=false, requirements="([0-9]){4}", strict=true, description="year of birth.")
     * (name="month", nullable=false, requirements="([0-9]){2}", strict=true, description="month of birth.")
     * (name="day", nullable=false, requirements="([0-9]){2}", strict=true, description="day of birth.")
     * (name="phone", nullable=false, requirements="([0-9])+", strict=true, description="phone.")
     * (name="email", nullable=false, strict=true, description="email.")
     *
     * @return View
     */
    public function postClientAction(Request $request)
    {
        // This is the asigned path by NovoPayment to this action.
        $path = "/customer/";
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['documentType'] = '([A-Z|a-z]){2}';
        $mandatory['documentType'] = true;
        $regex['documentNumber'] = '([0-9])+';
        $mandatory['documentNumber'] = true;
        $regex['name'] = '([a-z|A-Z| ])+';
        $mandatory['name'] = true;
        $regex['lastName'] = '([a-z|A-Z| ])+';
        $mandatory['lastName'] = true;
        $regex['year'] = '([0-9]){4}';
        $mandatory['year'] = true;
        $regex['month'] = '([0-9]){2}';
        $mandatory['month'] = true;
        $regex['day'] = '([0-9]){2}';
        $mandatory['day'] = true;
        $regex['phone'] = '([0-9])+';
        $mandatory['phone'] = true;
        $mandatory['email'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);

        // Set up the headers to default if none is provided.
        $header = $this->setHeaders($parameters);

        // Create the birth date in the right format.
        $birth = $parameters['year'] . '-' . $parameters['month'] .
                '-' . $parameters['day'];

        $parameters_fixed = array();
        $parameters_fixed['document-type'] = $parameters['documentType'];
        $parameters_fixed['document-number'] = $parameters['documentNumber'];
        $parameters_fixed['name'] = $parameters['name'];
        $parameters_fixed['last-name'] = $parameters['lastName'];
        $parameters_fixed['birth-date'] = $birth;
        $parameters_fixed['phone-number'] = $parameters['phone'];
        $parameters_fixed['email'] = $parameters['email'];

        /** @var View $res */
        $responseView = $this->callApi($header, $parameters_fixed, $path);

        return $responseView;
    }

    /**
     * Get the client informatino from the payment system.(3.2)<br/>
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
    public function getClientAction($documentNumber)
    {
        // This is the asigned path by NovoPayment to this action.
        $path = "/customer/" . $documentNumber;

        // We set up the default headers, so the client doesn't have to provide
        // anything in the get call.
        $header = $this->setHeaders();

        $parameters = array();

        /** @var View $responseView */
        $responseView = $this->callApi($header, $parameters, $path, "get");

        return $responseView;
    }

    /**
     * Modifies a client in the payments system(3.3).<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Modifies a client in the payments system.",
     *   statusCodes = {
     *     201 = "Created",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Not Found"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *
     * (name="documentNumber", nullable=false, requirements="([0-9])+", strict=false, description="document.")
     *
     * (name="documentType", nullable=true, requirements="([A-Z|a-z]){2}", strict=true, description="documentType.")
     * (name="name", nullable=true, requirements="([a-z|A-Z| ])+", strict=false, description="first name.")
     * (name="lastName", nullable=true, requirements="([a-z|A-Z| ])+", strict=false, description="last name.")
     * (name="year", nullable=true, requirements="([0-9]){4}", strict=false, description="year of birth.")
     * (name="month", nullable=true, requirements="([0-9]){2}", strict=false, description="month of birth.")
     * (name="day", nullable=true, requirements="([0-9]){2}", strict=false, description="day of birth.")
     * (name="phone", nullable=true, requirements="([0-9])+", strict=false, description="phone.")
     * (name="email", nullable=true, strict=true, description="email.")
     *
     * @return View
     */
    public function postModifyClientAction(Request $request)
    {
        // This is the asigned path by NovoPayment to this action.

        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();
        // Set all the parameters info.
        $regex['documentType'] = '([A-Z|a-z]){2}';
        $mandatory['documentType'] = true;
        $regex['documentNumber'] = '([0-9])+';
        $mandatory['documentNumber'] = false;
        $regex['name'] = '([a-z|A-Z| ])+';
        $mandatory['name'] = false;
        $regex['lastName'] = '([a-z|A-Z| ])+';
        $mandatory['lastName'] = false;
        $regex['year'] = '([0-9]){4}';
        $mandatory['year'] = false;
        $regex['month'] = '([0-9]){2}';
        $mandatory['month'] = false;
        $regex['day'] = '([0-9]){2}';
        $mandatory['day'] = false;
        $regex['phone'] = '([0-9])+';
        $mandatory['phone'] = false;
        $mandatory['email'] = false;

        $this->validateParamters($parameters, $regex, $mandatory);

        // Set up the headers to default if none is provided.
        $header = $this->setHeaders($parameters);

        // Create the birth date in the right format.
        if (isset($parameters['year']))
            $birth = $parameters['year'] . '-' . $parameters['month'] .
                    '-' . $parameters['day'];

        $path = "/customer/" . $parameters['documentNumber'];

        $parameters_fixed = array();
        if (isset($parameters['documentType']))
            $parameters_fixed['document-type'] = $parameters['documentType'];
        if (isset($parameters['documentNumber']))
            $parameters_fixed['document-number'] = $parameters['documentNumber'];
        if (isset($parameters['name']))
            $parameters_fixed['name'] = $parameters['name'];
        if (isset($parameters['lastName']))
            $parameters_fixed['last-name'] = $parameters['lastName'];
        if (isset($parameters['year']))
            $parameters_fixed['birth-date'] = $birth;
        if (isset($parameters['phone']))
            $parameters_fixed['phone-number'] = $parameters['phone'];
        if (isset($parameters['email']))
            $parameters_fixed['email'] = $parameters['email'];

        /** @var View $res */
        $responseView = $this->callApi($header, $parameters_fixed, $path, "put");

        return $responseView;
    }

    /**
     * This is a proxy method, when is likely to get a time out.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Aproval for a clients payment.",
     *   statusCodes = {
     *     201 = "Created",
     *     400 = "Bad Request",
     *     401 = "Unauthorized"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *
     * (name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
     *
     * (name="header", nullable=false,  strict=true, description="Method id, it is returned when a payment method is created.")
     * (name="parameters_fixed", nullable=false,  strict=true, description="Total amount.")
     * (name="path", nullable=false,  strict=true, description="Tax amount.")
     *
     * @return View
     */
    public function postCallApprovalAction(Request $request)
    {
        $parameters = $request->request->all();
        return $this->callApi($parameters['header'], $parameters['parameters_fixed'], $parameters['path']);
    }

    /**
     * Aproval for a clients payment.(3.4)<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Aproval for a clients payment.",
     *   statusCodes = {
     *     201 = "Created",
     *     400 = "Bad Request",
     *     401 = "Unauthorized"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *
     * (name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
     *
     * (name="MethodId", nullable=false, requirements="([0-9])+", strict=true, description="Method id, it is returned when a payment method is created.")
     * (name="totalAmount", nullable=false, requirements="[0-9]+(\.)?[0-9]*,?([0-9]+)?", strict=true, description="Total amount.")
     * (name="taxAmount", nullable=false, requirements="[0-9]+(\.)?[0-9]*,?([0-9]+)?", strict=true, description="Tax amount.")
     * (name="taxBase", nullable=false, requirements="[0-9]+(\.)?[0-9]*,?([0-9]+)?", strict=true, description="Tax base.")
     * (name="commissionAmount", nullable=false, requirements="[0-9]+(\.)?[0-9]*,?([0-9]+)?", strict=true, description="Commission amount.")
     * (name="commissionBase", nullable=false, requirements="[0-9]+(\.)?[0-9]*(,?[0-9]+)?", strict=true, description="Commission base.")
     * (name="chargeMode", nullable=false, requirements="(1|2|3)", strict=true, description="This is to indicate where the money is going, 1 for validation, 2 for pament and 3 for fee.")
     * (name="chargeId", nullable=false, requirements="([a-zA-Z0-9])+", strict=true, description="Id of the transaction created by us.")
     *
     * @return View
     */
    public function postPaymentAprovalAction(Request $request)
    {
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();

        // Set all the parameters info.
        $regex['documentNumber'] = '([0-9])+';
        $mandatory['documentNumber'] = true;
        $regex['MethodId'] = '([0-9])+';
        $mandatory['MethodId'] = true;
        $regex['totalAmount'] = '[0-9]+(\.)?[0-9]*,?([0-9]+)?';
        $mandatory['totalAmount'] = true;
        $regex['taxAmount'] = '[0-9]+(\.)?[0-9]*,?([0-9]+)?';
        $mandatory['taxAmount'] = true;
        $regex['taxBase'] = '[0-9]+(\.)?[0-9]*,?([0-9]+)?';
        $mandatory['taxBase'] = true;
        $regex['commissionAmount'] = '[0-9]+(\.)?[0-9]*,?([0-9]+)?';
        $mandatory['commissionAmount'] = true;
        $regex['commissionBase'] = '[0-9]+(\.)?[0-9]*,?([0-9]+)?';
        $mandatory['commissionBase'] = true;
        $regex['chargeMode'] = '(1|2|3)';
        $mandatory['chargeMode'] = true;
        $regex['chargeId'] = '([a-zA-Z0-9])+';
        $mandatory['chargeId'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);

        // This is the asigned path by NovoPayment to this action.
        $path = "/customer/" . $parameters['documentNumber'] . "/charge";

        // Set up the headers to default if none is provided.
        $header = $this->setHeaders($parameters);

        $parameters_fixed = array();
        $parameters_fixed['method-id'] = $parameters['MethodId'];
        $parameters_fixed['total-amount'] = $parameters['totalAmount'];
        $parameters_fixed['tax-amount'] = $parameters['taxAmount'];
        $parameters_fixed['tax-base'] = $parameters['taxBase'];
        $parameters_fixed['commission-amount'] = $parameters['commissionAmount'];
        $parameters_fixed['commission-base'] = $parameters['commissionBase'];
        $parameters_fixed['charge-mode'] = $parameters['chargeMode'];
        $parameters_fixed['charge-third-id'] = $parameters['chargeId'];

        /** @var View $responseView */
        //$responseView = $this->callApi($header, $parameters_fixed, $path);

        $request = $this->container->get('request');
        $request->setMethod("POST");
        $request->request->add(array(
            "header" => $header,
            "parameters_fixed" => $parameters_fixed,
            "path" => $path
        ));
        $view = View::create();
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:postCallApproval', array('_format' => 'json'));

        // I check that the problem was not a time out or connection error.
        // IF it was, we have to undo the transaction and return error.
        if ($insertionAnswer->getStatusCode() == 500) {
            // We have a problem here.
            $request = new Request();
            $request->request->set("documentNumber", $parameters['documentNumber']);
            $request->request->set("chargeId", $parameters['chargeId']);
            $this->deleteReversePaymentMethodAction($request);
        }

        return $insertionAnswer;
    }

    /**
     * Get a maximum number of transactions from a client in a day range.(3.5)<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get a maximum number of transactions from a client in a day range.",
     *   statusCodes = {
     *     200 = "OK",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Not Found"
     *   }
     * )
     *
     * @param Int $documentNumber The id of the client in the payments system.
     * @param Int $start The start day of the query. Format: AAAA-MM-DD.
     * @param Int $end The start day of the query. Format: AAAA-MM-DD.
     * @param Int limit Maximum number of queries to be displayed.
     *
     * @return View
     */
    public function getClientRangeMovementsAction($documentNumber, $start, $end, $limit)
    {
        // This is the asigned path by NovoPayment to this action.
        $path = "/account/" . $documentNumber . "/charge?from=" . $start .
                '&to=' . $end . '&q=' . $limit;

        // We set up the default headers, so the client doesn't have to provide
        // anything in the get call.
        $header = $this->setHeaders();

        $parameters = array();

        /** @var View $responseView */
        $responseView = $this->callApi($header, $parameters, $path, "get");

        return $responseView;
    }

    /**
     * Get a specific charge by client.(3.6)<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get a specific charge by client.",
     *   statusCodes = {
     *     200 = "OK",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Not Found"
     *   }
     * )
     *
     * @param Int $documentNumber The id of the client in the payments system.
     * @param Int $chargeId The id of the charge to be queried, provided by us.
     *
     * @return View
     */
    public function getClientSpecificChargeAction($documentNumber, $chargeId)
    {
        // This is the asigned path by NovoPayment to this action.
        $path = "/customer/" . $documentNumber .
                "/charge/" . $chargeId;

        // We set up the default headers, so the client doesn't have to provide
        // anything in the get call.
        $header = $this->setHeaders();

        $parameters = array();

        /** @var View $responseView */
        $responseView = $this->callApi($header, $parameters, $path, "get");

        return $responseView;
    }

    /**
     * Dispersion of beneficiary payment by client.(3.7)<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Dispersion of beneficiary payment by client.",
     *   statusCodes = {
     *     200 = "OK",
     *     201 = "Accepted",
     *     400 = "Bad Request",
     *     401 = "Unauthorized"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *
     * (name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
     *
     * (name="chargeId", nullable=false, requirements="([a-zA-Z0-9])+", strict=true, description="Id of the charge, it was provided by us.")
     * (name="beneficiaryId", nullable=false, requirements="([0-9]| )+", strict=true, description="expiration year.")
     * (name="beneficiaryAmount", nullable=false, requirements="[0-9]+(\.)?[0-9]*,?([0-9]+)?", strict=true, description="Amount of the beneficiary")
     * (name="dispersionType", nullable=false, requirements="(1|2|3)", strict=true, description="Type of dispersion, it is 1 for payroll, 2 for pila and 3 for fee.")
     *
     * @return View
     */
    public function postClientPaymentAction(Request $request)
    {
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();

        // Set all the parameters info.
        $regex['documentNumber'] = '([0-9])+';
        $mandatory['documentNumber'] = true;
        $regex['chargeId'] = '([0-9]| )+';
        $mandatory['chargeId'] = true;
        $regex['beneficiaryId'] = '([0-9]| )+';
        $mandatory['beneficiaryId'] = true;
        $regex['beneficiaryAmount'] = '[0-9]+(\.)?[0-9]*,?([0-9]+)?';
        $mandatory['beneficiaryAmount'] = true;
        $regex['dispersionType'] = '(1|2|3)';
        $mandatory['dispersionType'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);

        // This is the asigned path by NovoPayment to this action.
        $path = "/customer/" . $parameters['documentNumber'] .
                "/transfer";

        // Set up the headers to default if none is provided.
        $header = $this->setHeaders($parameters);

        $parameters_fixed = array();
        // Adding another layer because, NovoPayment needs it like this.
        $layer = array();
        $parameters_fixed['charge-third-id'] = $parameters['chargeId'];

        $layer['document-number'] = $parameters['beneficiaryId'];
        $layer['amount'] = $parameters['beneficiaryAmount'];
        $layer['dispersion-type'] = $parameters['dispersionType'];

        $parameters_fixed['beneficiaries'] = $layer;

        /** @var View $responseView */
        $responseView = $this->callApi($header, $parameters_fixed, $path);

        return $responseView;
    }

    /**
     * Reverse a payment(3.7)<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Reverse a payment.",
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
     * (name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
     * (name="chargeId", nullable=false, requirements="([a-zA-Z0-9])+", strict=true, description="id of the payment method.")
     *
     * @return View
     */
    public function deleteReversePaymentMethodAction(Request $request)
    {
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();

        // Set all the parameters info.
        $regex['documentNumber'] = '([0-9])+';
        $mandatory['documentNumber'] = true;
        $regex['chargeId'] = '([a-zA-Z0-9])+';
        $mandatory['chargeId'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);


        // This is the asigned path by NovoPayment to this action.
        $path = "/customer/" . $parameters['documentNumber'] .
                "/charge/" . $parameters['chargeId'];

        // Set up the headers to default if none is provided.
        $header = $this->setHeaders($parameters);

        $parameters_fixed = array();

        /** @var View $responseView */
        $responseView = $this->callApi($header, $parameters_fixed, $path, 'delete');

        return $responseView;
    }

    /**
     * Set a payment method for a client.(4.1)<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Set a payment method for a client.",
     *   statusCodes = {
     *     201 = "Created",
     *     400 = "Bad Request",
     *     401 = "Unauthorized"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *
     * (name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
     *
     * (name="documentType", nullable=false, requirements="([A-Z|a-z]){2}", strict=true, description="document type.")
     * (name="paymentType", nullable=true, requirements="([A-Z|a-z]| )+", strict=false, description="payment type, 2 MasterCard, 3 Visa.")
     * (name="accountNumber", nullable=false, requirements="([0-9])+", strict=true, description="account number.")
     * (name="expirationYear", nullable=false, requirements="([0-9]){4}", strict=true, description="expiration year.")
     * (name="expirationMonth", nullable=false, requirements="([0-9]){2}", strict=true, description="expiration month.")
     * (name="codeCheck", nullable=false, requirements="([0-9]){3}", strict=true, description="code check.")
     *
     * @return View
     */
    public function postClientPaymentMethodAction(Request $request)
    {
        // Adjust the format of the expiration date.
        $expiration = null;
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();

        // Set all the parameters info.
        $regex['documentType'] = '([A-Z|a-z]){2}';
        $mandatory['documentType'] = true;
        //$regex['paymentType'] = '([A-Z|a-z]| )+'; $mandatory['paymentType'] = true;
        $regex['accountNumber'] = '([0-9])+';
        $mandatory['accountNumber'] = true;
        $regex['expirationYear'] = '([0-9]){4}';
        $mandatory['expirationYear'] = true;
        $regex['expirationMonth'] = '([0-9]){2}';
        $mandatory['expirationMonth'] = true;
        $regex['codeCheck'] = '([0-9]){3}';
        $mandatory['codeCheck'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);


        /*         * **************** chack if is visa or mastercard ************ */
        $card = $parameters['accountNumber'];
        $card = substr($card, 0, 4);
        if (substr($card, 0, 1) == '4') {
            //Visa.
            $parameters['paymentType'] = 3;
        } else if (substr($card, 0, 2) == '51' || substr($card, 0, 2) == '52' ||
                substr($card, 0, 2) == '53' || substr($card, 0, 2) == '54' ||
                substr($card, 0, 2) == '55') {
            // Master card.
            $parameters['paymentType'] = 2;
        }

        // Set expiration card.
        if (isset($parameters['expirationYear']))
            $expiration = $parameters['expirationYear'] . '-' .
                    $parameters['expirationMonth'] . '-01';

        // This is the asigned path by NovoPayment to this action.
        $path = "/customer/" . $parameters['documentNumber'] .
                "/payment-method/";

        // Set up the headers to default if none is provided.
        $header = $this->setHeaders($parameters);

        $parameters_fixed = array();
        $parameters_fixed['document-type'] = $parameters['documentType'];
        $parameters_fixed['payment-type'] = $parameters['paymentType'];
        $parameters_fixed['account'] = $parameters['accountNumber'];
        $parameters_fixed['exp-date'] = $expiration;
        $parameters_fixed['check-code'] = $parameters['codeCheck'];

        /** @var View $responseView */
        $responseView = $this->callApi($header, $parameters_fixed, $path);

        return $responseView;
    }

    /**
     * List all the payment methods by client.(4.2)<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get payment method by client.",
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
    public function getClientListPaymentmethodsAction($documentNumber)
    {
        // This is the asigned path by NovoPayment to this action.
        $path = "/customer/" . $documentNumber . "/payment-method/";

        // We set up the default headers, so the client doesn't have to provide
        // anything in the get call.
        $header = $this->setHeaders();

        $parameters = array();

        /** @var View $responseView */
        $responseView = $this->callApi($header, $parameters, $path, 'get');
        $card = "xxxxx";
        $card = substr($card, 0, 4);
        if (substr($card, 0, 1) == '4') {
            //Visa.
        } else if (substr($card, 0, 2) == '51' || substr($card, 0, 2) == '52' ||
                substr($card, 0, 2) == '53' || substr($card, 0, 2) == '54' ||
                substr($card, 0, 2) == '55') {
            // Master card.
        }
        //$view->setData(json_decode($response->getBody(), true));
        $temp = $this->handleView($responseView);
        $data = json_decode($temp->getContent(), true);
        $code = json_decode($temp->getStatusCode(), true);
        //die(print_r($data, true));
        $retorno = array();
        $terminar = false;
        foreach ($data['payment-methods'] as &$i) {
            $unidad = array();
            if (isset($i['account'])) {
                $card = "" . $i['account'];
                $method_id = $i['method-id'];
            } else {
                $card = "" . $data['payment-methods']['account'];
                $method_id = $data['payment-methods']['method-id'];
                $terminar = true;
            }
            $cardFinal = $card;
            $card = substr($card, 0, 4);
            $type = '';
            if (substr($card, 0, 1) == '4') {
                // Visa.
                $paymentType = '3';
            } else if (substr($card, 0, 2) == '51' || substr($card, 0, 2) == '52' ||
                    substr($card, 0, 2) == '53' || substr($card, 0, 2) == '54' ||
                    substr($card, 0, 2) == '55') {
                // Master card.
                $paymentType = '2';
            }
            $unidad['payment-type'] = $paymentType;
            $unidad['account'] = "" . $cardFinal;
            $unidad['method-id'] = $method_id;
            $retorno['payment-methods'][] = $unidad;
            if ($terminar)
                break;
        }
        $view = View::create();
        $view->setStatusCode($code);
        $view->setData($retorno);
        return $view;
    }

    /**
     * Get payment method by client(4.3).<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get payment method by client.",
     *   statusCodes = {
     *     200 = "OK",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Not Found"
     *   }
     * )
     *
     * @param Int $documentNumber The id of the client in the payments system.
     * @param Int $paymentMethod The id of the payment method to be queried.
     *
     * @return View
     */
    public function getClientPaymentmethodsAction($documentNumber, $paymentMethodId)
    {
        // This is the asigned path by NovoPayment to this action.
        $path = "/customer/" . $documentNumber .
                "/payment-method/" . $paymentMethodId;

        // We set up the default headers, so the client doesn't have to provide
        // anything in the get call.
        $header = $this->setHeaders();

        $parameters = array();

        /** @var View $responseView */
        $responseView = $this->callApi($header, $parameters, $path);

        return $responseView;
    }

    /**
     * Delete payment method for a client.(4.4)<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Delete payment method for a client.",
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
     * (name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
     * (name="paymentMethodId", nullable=false, requirements="([0-9A-Za-z])+", strict=true, description="id of the payment method.")
     *
     * @return View
     */
    public function deleteClientPaymentMethodAction(Request $request)
    {
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();

        // Set all the parameters info.
        $regex['documentNumber'] = '([0-9])+';
        $mandatory['documentNumber'] = true;
        $regex['paymentMethodId'] = '([0-9A-Za-z])+';
        $mandatory['paymentMethodId'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);


        // This is the asigned path by NovoPayment to this action.
        $path = "/customer/" . $parameters['documentNumber'] .
                "/payment-method/" . $parameters['paymentMethodId'];

        // Set up the headers to default if none is provided.
        $header = $this->setHeaders($parameters);

        $parameters_fixed = array();

        /** @var View $responseView */
        $responseView = $this->callApi($header, $parameters_fixed, $path, 'delete');

        return $responseView;
    }

    /**
     * Insert a new beneficiary.(5.1)<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Insert a new beneficiary.",
     *   statusCodes = {
     *     201 = "Created",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *
     * (name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document of the client.")
     *
     * (name="documentType", nullable=false, requirements="([A-Z|a-z]){2}", strict=true, description="documentType.")
     * (name="beneficiaryId", nullable=false, requirements="([0-9])+", strict=true, description="document of the beneficiary.")
     * (name="name", nullable=false, requirements="(.)*", strict=true, description="first name.")
     * (name="lastName", nullable=false, requirements="(.)*", strict=true, description="last name.")
     * (name="yearBirth", nullable=false, requirements="([0-9]){4}", strict=true, description="year of birth.")
     * (name="monthBirth", nullable=false, requirements="([0-9]){2}", strict=true, description="month of birth.")
     * (name="dayBirth", nullable=false, requirements="([0-9]){2}", strict=true, description="day of birth.")
     * (name="phone", nullable=false, requirements="([0-9])+", strict=true, description="phone.")
     * (name="email", nullable=false, strict=true, description="email.")
     * (name="companyId", nullable=false, strict=true, description="id of the company(NIT)")
     * (name="companyBranch", nullable=false, description="Company branch id, 0 by default.")
     * (name="paymentMethodId", nullable=false, requirements="([0-9])+", description="payment method id
     *                                                                     (1-cash, 4 savings account, 5 checking ccount, 6-SVA)")
     * (name="PaymentAccountNumber", nullable=true, requirements="([0-9])+", description="Number of the payment account")
     * (name="PaymentBankNumber", nullable=true, requirements="([0-9])+", description="Id of the bank")
     * (name="PaymentType", nullable=true, description="Ahorros or Corriente")
     *
     * @return View
     */
    public function postBeneficiaryAction(Request $request)
    {
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();

        // Set all the parameters info.
        $regex['documentType'] = '([A-Z|a-z]){2}';
        $mandatory['documentType'] = true;
        $regex['documentNumber'] = '([0-9])+';
        $mandatory['documentNumber'] = true;
        $regex['beneficiaryId'] = '([0-9])+';
        $mandatory['beneficiaryId'] = true;
        $regex['name'] = '(.)*';
        $mandatory['name'] = true;
        $regex['lastName'] = '(.)*';
        $mandatory['lastName'] = true;
        $regex['yearBirth'] = '([0-9]){4}';
        $mandatory['yearBirth'] = true;
        $regex['monthBirth'] = '([0-9]){2}';
        $mandatory['monthBirth'] = true;
        $regex['dayBirth'] = '([0-9]){2}';
        $mandatory['dayBirth'] = true;
        $regex['phone'] = '([0-9])+';
        $mandatory['phone'] = true;
        $mandatory['email'] = true;
        $mandatory['companyId'] = true;
        $mandatory['companyBranch'] = true;
        $regex['paymentMethodId'] = '([0-9])+';
        $mandatory['paymentMethodId'] = true;
        $regex['PaymentAccountNumber'] = '([0-9])+';
        $mandatory['PaymentAccountNumber'] = false;
        $regex['PaymentBankNumber'] = '([0-9])+';
        $mandatory['PaymentBankNumber'] = false;
        $mandatory['PaymentType'] = false;

        $this->validateParamters($parameters, $regex, $mandatory);

        // Create the birth date in the right format.
        $birth = $parameters['dayBirth'] . '/' .
                $parameters['monthBirth'] . '/' .
                $parameters['yearBirth'];
        // This is the asigned path by NovoPayment to this action.
        $path = "/customer/" . $parameters['documentNumber'] .
                "/beneficiary/";

        // Set up the headers to default if none is provided.
        $header = $this->setHeaders($parameters);

        $parameters_fixed = array();
        $parameters_fixed['document-type'] = $parameters['documentType'];
        $parameters_fixed['document-number'] = $parameters['beneficiaryId'];
        $parameters_fixed['name'] = $parameters['name'];
        $parameters_fixed['last-name'] = $parameters['lastName'];
        $parameters_fixed['birth-date'] = $birth;
        $parameters_fixed['phone-number'] = $parameters['phone'];
        $parameters_fixed['email'] = $parameters['email'];
        $parameters_fixed['company-id'] = $parameters['companyId'];
        $parameters_fixed['company-branch'] = $parameters['companyBranch'];
        $parameters_fixed['payment-type'] = $parameters['paymentMethodId'];
        if (isset($parameters['PaymentAccountNumber']))
            $parameters_fixed['payment-mode-account'] = $parameters['PaymentAccountNumber'];
        if (isset($parameters['PaymentBankNumber']))
            $parameters_fixed['payment-mode-bank'] = $parameters['PaymentBankNumber'];
        if (isset($parameters['PaymentType']))
            $parameters_fixed['payment-mode-type'] = $parameters['PaymentType'];
        //die(print_r($parameters_fixed, true));

        /** @var View $responseView */
        $responseView = $this->callApi($header, $parameters_fixed, $path);

        return $responseView;
    }

    /**
     * Get the information of a beneficiary of a client.(5.2)<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get the information of a beneficiary of a client.",
     *   statusCodes = {
     *     200 = "OK",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Not Found"
     *   }
     * )
     *
     * @param Int $documentNumber The id of the client in the payments system.
     * @param Int $beneficiaryId The id of the beneficiary to be queried.
     *
     * @return View
     */
    public function getClientBeneficiaryAction($documentNumber, $beneficiaryId)
    {
        // This is the asigned path by NovoPayment to this action.
        $path = "/customer/" . $documentNumber . "/beneficiary/" . $beneficiaryId;

        // We set up the default headers, so the client doesn't have to provide
        // anything in the get call.
        $header = $this->setHeaders();

        $parameters = array();

        /** @var View $responseView */
        $responseView = $this->callApi($header, $parameters, $path, 'get');

        return $responseView;
    }

    /**
     * Get All the beneficiaries of a client.(5.3)<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get All the beneficiaries of a client.",
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
    public function getClientBeneficiariesAction($documentNumber)
    {
        // This is the asigned path by NovoPayment to this action.
        $path = "/customer/" . $documentNumber . "/beneficiary/";

        // We set up the default headers, so the client doesn't have to provide
        // anything in the get call.
        $header = $this->setHeaders();

        $parameters = array();

        /** @var View $responseView */
        $responseView = $this->callApi($header, $parameters, $path, 'get');

        return $responseView;
    }

    /**
     * Delete a beneficiary by client.(5.4)<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Delete a beneficiary by client.",
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
     * (name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
     * (name="beneficiaryId", nullable=false, requirements="([0-9])+", strict=true, description="document.")
     *
     * @return View
     */
    public function deleteClientBeneficiaryAction(Request $request)
    {

        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();

        // Set all the parameters info.
        $regex['documentNumber'] = '([0-9])+';
        $mandatory['documentNumber'] = true;
        $regex['beneficiaryId'] = '([0-9])+';
        $mandatory['beneficiaryId'] = true;

        $this->validateParamters($parameters, $regex, $mandatory);

        // This is the asigned path by NovoPayment to this action.
        $path = "/customer/" . $parameters['documentNumber'] .
                "/beneficiary/" . $parameters['beneficiaryId'];

        // Set up the headers to default if none is provided.
        $header = $this->setHeaders($parameters);

        $parameters_fixed = array();

        /** @var View $responseView */
        $responseView = $this->callApi($header, $parameters_fixed, $path, "delete");

        return $responseView;
    }

    /**
     * Modifies a beneficiary in the payments system(5.5).<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Modifies a beneficiary in the payments system.",
     *   statusCodes = {
     *     200 = "Created",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Not Found"
     *   }
     * )
     *
     * @param Request $request.
     * Rest Parameters:
     *
     * (name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document Employer.")
     * (name="beneficiaryId", nullable=false, requirements="([0-9])+", strict=true, description="document Employee.")
     *
     * (name="documentType", nullable=true, requirements="([A-Z|a-z]){2}", strict=true, description="documentType.")
     * (name="name", nullable=true, requirements="([a-z|A-Z| ])+", strict=true, description="first name.")
     * (name="lastName", nullable=true, requirements="([a-z|A-Z| ])+", strict=true, description="last name.")
     * (name="yearBirth", nullable=true, requirements="([0-9]){4}", strict=true, description="year of birth.")
     * (name="monthBirth", nullable=true, requirements="([0-9]){2}", strict=true, description="month of birth.")
     * (name="dayBirth", nullable=true, requirements="([0-9]){2}", strict=true, description="day of birth.")
     * (name="phone", nullable=true, requirements="([0-9])+", strict=true, description="phone.")
     * (name="email", nullable=true, strict=true, description="email.")
     * (name="companyId", nullable=true, strict=true, description="id of the company(NIT)")
     * (name="companyBranch", nullable=true, description="Company branch id, 0 by default.")
     * (name="paymentMethodId", nullable=true, requirements="([0-9])+", description="payment method id
     *                                                                     (1-cash, 4 savings account, 5 checking ccount, 6-SVA)")
     * (name="PaymentAccountNumber", nullable=true, requirements="([0-9])+", description="Number of the payment account")
     * (name="PaymentBankNumber", nullable=true, requirements="([0-9])+", description="Id of the bank")
     * (name="PaymentType", nullable=true, description="Ahorros or Corriente")
     *
     * @return View
     */
    public function postModifyBeneficiaryAction(Request $request)
    {
        $parameters = $request->request->all();
        $regex = array();
        $mandatory = array();

        // Set all the parameters info.
        $regex['documentNumber'] = '([0-9])+';
        $mandatory['documentNumber'] = true;
        $regex['beneficiaryId'] = '([0-9])+';
        $mandatory['documentNumber'] = true;

        $regex['documentType'] = '([A-Z|a-z]){2}';
        $mandatory['documentType'] = false;
        $regex['name'] = '([a-z|A-Z| ])+';
        $mandatory['name'] = false;
        $regex['lastName'] = '([a-z|A-Z| ])+';
        $mandatory['lastName'] = false;
        $regex['yearBirth'] = '([0-9]){4}';
        $mandatory['yearBirth'] = false;
        $regex['monthBirth'] = '([0-9]){2}';
        $mandatory['monthBirth'] = false;
        $regex['dayBirth'] = '([0-9]){2}';
        $mandatory['dayBirth'] = false;
        $regex['phone'] = '([0-9])+';
        $mandatory['phone'] = false;
        $mandatory['email'] = false;
        $mandatory['companyId'] = false;
        $mandatory['companyBranch'] = false;
        $regex['paymentMethodId'] = '([0-9])+';
        $mandatory['paymentMethodId'] = false;
        $regex['PaymentAccountNumber'] = '([0-9])+';
        $mandatory['PaymentAccountNumber'] = false;
        $regex['PaymentBankNumber'] = '([0-9])+';
        $mandatory['PaymentBankNumber'] = false;
        $mandatory['PaymentType'] = false;

        $this->validateParamters($parameters, $regex, $mandatory);

        // Create the birth date in the right format.
        if (isset($parameters['yearBirth']))
            $birth = $parameters['dayBirth'] . '/' .
                    $parameters['monthBirth'] . '/' .
                    $parameters['yearBirth'];
        // This is the asigned path by NovoPayment to this action.
        $path = "/customer/" . $parameters['documentNumber'] .
                "/beneficiary/" . $parameters['beneficiaryId'];

        // Set up the headers to default if none is provided.
        $header = $this->setHeaders($parameters);

        $parameters_fixed = array();
        if (isset($parameters['documentType']))
            $parameters_fixed['document-type'] = $parameters['documentType'];
        if (isset($parameters['documentNumber']))
            $parameters_fixed['document-number'] = $parameters['documentNumber'];
        if (isset($parameters['name']))
            $parameters_fixed['name'] = $parameters['name'];
        if (isset($parameters['lastName']))
            $parameters_fixed['last-name'] = $parameters['lastName'];
        if (isset($parameters['yearBirth']))
            $parameters_fixed['birth-date'] = $birth;
        if (isset($parameters['phone']))
            $parameters_fixed['phone-number'] = $parameters['phone'];
        if (isset($parameters['email']))
            $parameters_fixed['email'] = $parameters['email'];
        if (isset($parameters['companyId']))
            $parameters_fixed['company-id'] = $parameters['companyId'];
        if (isset($parameters['companyBranch']))
            $parameters_fixed['company-branch'] = $parameters['companyBranch'];
        if (isset($parameters['paymentMethodId']))
            $parameters_fixed['payment-type'] = $parameters['paymentMethodId'];
        if (isset($parameters['PaymentAccountNumber']))
            $parameters_fixed['payment-mode-account'] = $parameters['PaymentAccountNumber'];
        if (isset($parameters['PaymentBankNumber']))
            $parameters_fixed['payment-mode-bank'] = $parameters['PaymentBankNumber'];
        if (isset($parameters['PaymentType']))
            $parameters_fixed['payment-mode-type'] = $parameters['PaymentType'];

        /** @var View $responseView */
        $responseView = $this->callApi($header, $parameters_fixed, $path, 'put');

        return $responseView;
    }

    /**
     * Get all charges by client.(5.7)<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get a specific charge by client.",
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
    public function getClientChargesAction($documentNumber)
    {
        // This is the asigned path by NovoPayment to this action.
        $path = "/customer/" . $documentNumber . "/charge/";

        // We set up the default headers, so the client doesn't have to provide
        // anything in the get call.
        $header = $this->setHeaders();

        $parameters = array();

        /** @var View $responseView */
        $responseView = $this->callApi($header, $parameters, $path, "get");

        return $responseView;
    }

    /**
     * Get the balance of a client.(6.1)<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get the balance of a client.",
     *   statusCodes = {
     *     200 = "OK",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     404 = "Not Found"
     *   }
     * )
     *
     * @param Int $documentNumber The id of the client in the payments system.
     * @param Int $beneficiaryId The id of the beneficiary of the client.
     *
     * @return View
     */
    public function getClientBalanceAction($documentNumber, $beneficiaryId)
    {
        // This is the asigned path by NovoPayment to this action.
        $path = "/customer/" . $documentNumber .
                "/beneficiary/" . $beneficiaryId . "/balance";

        // We set up the default headers, so the client doesn't have to provide
        // anything in the get call.
        $header = $this->setHeaders();

        $parameters = array();

        /** @var View $responseView */
        $responseView = $this->callApi($header, $parameters, $path, "get");

        return $responseView;
    }

}

?>
