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

use EightPoints\Bundle\GuzzleBundle;

class PaymentsRestController extends FOSRestController
{
  /**
   * Add the novelty<br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Finds the cities of a department.",
   *   statusCodes = {
   *     200 = "Returned when successful",
   *     400 = "Bad Request",
   *     404 = "Returned when the Novelty Type id doesn't exists "
   *   }
   * )
   *
   * @param ParamFetcher $paramFetcher Paramfetcher
   * @RequestParam(name="documentType", nullable=false, requirements="([A-Z|a-z]){2}", strict=true, description="documentType.")
   * @return View
   */
  public function postPaymentsMockAction(ParamFetcher $paramFetcher)
  {
    $a = $paramFetcher->get('documentType');
    $view = View::create();
    $view->setStatusCode(200);
    $view->setData((array('name' => 'ok')));
    return $view;
  }

  /**
   * Add the novelty<br/>
   * @ApiDoc(
   *   resource = true,
   *   description = "Finds the cities of a department.",
   *   statusCodes = {
   *     200 = "Returned when successful",
   *     400 = "Bad Request",
   *     404 = "Returned when the Novelty Type id doesn't exists "
   *   }
   * )
   *
   * @param integer $cedula - Cedula usuario falso
   * @return View
   */
  public function postCustomerAction($cedula)
  {
    $view = View::create();
    $view->setStatusCode(200);
    $view->setData(json_encode(array('document-type' => 'CC',
                                     'document-number' => '765432198',
                                     'name' => 'Alan',
                                     'last-name' => 'Turing',
                                     'birth-date' => '12/12/1912',
                                     'phone-number' => '3456789',
                                     'email' => 'aaaaa@bbb.cc')));
    return $view;
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
  public function callApi($headers, $parameters, $path, $action="post",
                          $timeout=10)
  {
    $client = $this->get('guzzle.client.api_rest');
    $url_request = $this->container->getParameter('novo_payments_url') ;

    // URL used for test porpouses.
    //$url_request = "http://localhost:8001/api/public/v1" . $path;
    $response = null;
    $options = array(
                  'headers'     => $headers,
                  'form_params' => $parameters,
                  'timeout'     => $timeout
                );
    if ($action == "post")
    {
      $response = $client->post($url_request, $options);
    }
    else if ($action == "delete")
    {
      $response = $client->delete($url_request, $options);
    }
    else if ($action == "get")
    {
      $response = $client->get($url_request, $options);
    }
    $view = View::create();
    $view->setStatusCode($response->getStatusCode());
    $view->setData(json_decode((String)$response->getBody()));
    return $view;
  }

  /**
   * It sets the headers for the payments request. Each request method, recieves
   * parameters in case the client wants something different, but in most cases
   * the header is the same, this function sets de default values.
   * @param ParamFetcher with the options in the request $paramFetcher
   * @return Array with the header options.
   */
  private function setHeaders($paramFetcher) {
    $header = array();
    $header['x-channel'] = (!$paramFetcher->get('channel')) ?
                           'WEB' : $paramFetcher->get('channel');
    $header['x-country'] = (!$paramFetcher->get('country')) ?
                           'CO' : $paramFetcher->get('country');
    $header['language'] = (!$paramFetcher->get('language')) ?
                          'es' : $paramFetcher->get('language');
    $header['content-type'] = (!$paramFetcher->get('content_type')) ?
                              'application/json' :
                              $paramFetcher->get('content_type');
    $header['accept'] = (!$paramFetcher->get('accept')) ?
                              'application/json' : $paramFetcher->get('accept');
    return $header;
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
   * @RequestParam(name="documentType", nullable=false, requirements="([A-Z|a-z]){2}", strict=true, description="documentType.")
   * @RequestParam(name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
   * @RequestParam(name="name", nullable=false, requirements="([a-z|A-Z| ])+", strict=true, description="first name.")
   * @RequestParam(name="lastName", nullable=false, requirements="([a-z|A-Z| ])+", strict=true, description="last name.")
   * @RequestParam(name="year", nullable=false, requirements="([0-9]){4}", strict=true, description="year of birth.")
   * @RequestParam(name="month", nullable=false, requirements="([0-9]){2}", strict=true, description="month of birth.")
   * @RequestParam(name="day", nullable=false, requirements="([0-9]){2}", strict=true, description="day of birth.")
   * @RequestParam(name="phone", nullable=false, requirements="([0-9])+", strict=true, description="phone.")
   * @RequestParam(name="email", nullable=false, strict=true, description="email.")
   *
   * @RequestParam(name="channel", nullable=true, description="Channel from where it is requested(MOBILE, WEB).")
   * @RequestParam(name="country", nullable=true, description="Country code from  ISO 3166-1.")
   * @RequestParam(name="language", nullable=true, description="Language code from ISO 639-1.")
   * @RequestParam(name="content_type", nullable=true, description="Request format(application/xml, application/json).")
   * @RequestParam(name="accept", nullable=true, description="Accepted response format(application/xml, application/json).")
   *
   * @return View
   */
  public function postInsertClientAction(ParamFetcher $paramFetcher)
  {
    // Create the birth date in the right format.
    $birth = $paramFetcher->get('day') . '/' . $paramFetcher->get('month') .
             '/' . $paramFetcher->get('year');
    // This is the asigned path by NovoPayment to this action.
    $path = "/customer/";

    // Set up the headers to default if none is provided.
    $header = $this->setHeaders($paramFetcher);

    $parameters = array();
    $parameters['document-type'] = $paramFetcher->get('documentType');
    $parameters['document-number'] = $paramFetcher->get('documentNumber');
    $parameters['name'] = $paramFetcher->get('name');
    $parameters['last-name'] = $paramFetcher->get('lastName');
    $parameters['birth-date'] = $birth;
    $parameters['phone-number'] = $paramFetcher->get('phone');
    $parameters['email'] = $paramFetcher->get('email');

    /** @var View $res */
    $responseView = $this->callApi($header, $parameters, $path);

    return $responseView;
  }

  /**
   * Get the client informatino from the payment system.<br/>
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
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @RequestParam(name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
   *
   * @RequestParam(name="channel", nullable=true, description="Channel from where it is requested(MOBILE, WEB).")
   * @RequestParam(name="country", nullable=true, description="Country code from  ISO 3166-1.")
   * @RequestParam(name="language", nullable=true, description="Language code from ISO 639-1.")
   * @RequestParam(name="content_type", nullable=true, description="Request format(application/xml, application/json).")
   * @RequestParam(name="accept", nullable=true, description="Accepted response format(application/xml, application/json).")
   *
   * @return View
   */
  public function getGetClientAction(ParamFetcher $paramFetcher)
  {
    // This is the asigned path by NovoPayment to this action.
    $path = "/customer/" . $paramFetcher->get('documentNumber');

    // Set up the headers to default if none is provided.
    $header = $this->setHeaders($paramFetcher);

    $parameters = array();

    /** @var View $responseView */
    $responseView = $this->callApi($header, $parameters, $path, "get");

    return $responseView;
  }

  /**
   * Get the clients 10 last movements.<br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Get the clients 10 last movements.",
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
   * @RequestParam(name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
   *
   * @RequestParam(name="channel", nullable=true, description="Channel from where it is requested(MOBILE, WEB).")
   * @RequestParam(name="country", nullable=true, description="Country code from  ISO 3166-1.")
   * @RequestParam(name="language", nullable=true, description="Language code from ISO 639-1.")
   * @RequestParam(name="content_type", nullable=true, description="Request format(application/xml, application/json).")
   * @RequestParam(name="accept", nullable=true, description="Accepted response format(application/xml, application/json).")
   *
   * @return View
   */
  public function getLast10MovementsAction(ParamFetcher $paramFetcher)
  {
    // This is the asigned path by NovoPayment to this action.
    $path = "/customer/" . $paramFetcher->get('documentNumber') . "/movement";

    // Set up the headers to default if none is provided.
    $header = $this->setHeaders($paramFetcher);

    $parameters = array();

    /** @var View $responseView */
    $responseView = $this->callApi($header, $parameters, $path);

    return $responseView;
  }

  /**
   * Get a maximum number of transactions from a client in a day range.<br/>
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
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @RequestParam(name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
   *
   * @RequestParam(name="yearStart", nullable=true, requirements="([0-9]){4}", strict=true, description="year of begining of range.")
   * @RequestParam(name="monthStart", nullable=true, requirements="([0-9]){2}", strict=true, description="month of begining of range.")
   * @RequestParam(name="dayStart", nullable=true, requirements="([0-9]){2}", strict=true, description="day of begining of range.")
   *
   * @RequestParam(name="yearEnd", nullable=true, requirements="([0-9]){4}", strict=true, description="year of ending of range.")
   * @RequestParam(name="monthEnd", nullable=true, requirements="([0-9]){2}", strict=true, description="month of ending of range.")
   * @RequestParam(name="dayEnd", nullable=true, requirements="([0-9]){2}", strict=true, description="day of ending of range.")
   *
   * @RequestParam(name="limit", nullable=true, requirements="([0-9])+", strict=false, description="maximum number of elements returned.")
   *
   * @RequestParam(name="channel", nullable=true, description="Channel from where it is requested(MOBILE, WEB).")
   * @RequestParam(name="country", nullable=true, description="Country code from  ISO 3166-1.")
   * @RequestParam(name="language", nullable=true, description="Language code from ISO 639-1.")
   * @RequestParam(name="content_type", nullable=true, description="Request format(application/xml, application/json).")
   * @RequestParam(name="accept", nullable=true, description="Accepted response format(application/xml, application/json).")
   *
   * @return View
   */
  public function getGetMovementsRangeAction(ParamFetcher $paramFetcher)
  {
    // Adjust the format of the start date.
    $start = null;
    if ($paramFetcher->get('yearStart') != null)
      $start = $paramFetcher->get('yearStart') . '-' .
               $paramFetcher->get('monthStart') . '-' .
               $paramFetcher->get('dayStart');

    $end = null;
    if ($paramFetcher->get('yearEnd') != null)
      $end = $paramFetcher->get('yearEnd') . '-' .
               $paramFetcher->get('monthEnd') . '-' .
               $paramFetcher->get('dayEnd');

    $limit = (!$paramFetcher->get('limit')) ?
                           20 : $paramFetcher->get('limit');

    // This is the asigned path by NovoPayment to this action.
    $path = "/account/" . $paramFetcher->get('documentNumber') .
            "/movement?from=" . $start . '&to=' . $end . '&q=' . $limit;

    // Set up the headers to default if none is provided.
    $header = $this->setHeaders($paramFetcher);

    $parameters = array();

    /** @var View $responseView */
    $responseView = $this->callApi($header, $parameters, $path, "get");

    return $responseView;
  }

  /**
   * Set a payment method for a client.<br/>
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
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @RequestParam(name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
   *
   * @RequestParam(name="documentType", nullable=false, requirements="([A-Z|a-z]){2}", strict=true, description="document type.")
   * @RequestParam(name="paymentType", nullable=false, requirements="([A-Z|a-z]| )+", strict=true, description="payment type.")
   * @RequestParam(name="accountNumber", nullable=false, requirements="([0-9])+", strict=true, description="account number.")
   * @RequestParam(name="expirationYear", nullable=true, requirements="([0-9]){4}", strict=true, description="expiration year.")
   * @RequestParam(name="expirationMonth", nullable=true, requirements="([0-9]){2}", strict=true, description="expiration month.")
   * @RequestParam(name="expirationDay", nullable=true, requirements="([0-9]){2}", strict=true, description="expiration day.")
   * @RequestParam(name="codeCheck", nullable=true, requirements="([0-9]){3}", strict=true, description="code check.")
   * @RequestParam(name="paymentMode", nullable=false, requirements="([A-Z|a-z]| )+", strict=true, description="Payment mode.")
   *
   * @RequestParam(name="channel", nullable=true, description="Channel from where it is requested(MOBILE, WEB).")
   * @RequestParam(name="country", nullable=true, description="Country code from  ISO 3166-1.")
   * @RequestParam(name="language", nullable=true, description="Language code from ISO 639-1.")
   * @RequestParam(name="content_type", nullable=true, description="Request format(application/xml, application/json).")
   * @RequestParam(name="accept", nullable=true, description="Accepted response format(application/xml, application/json).")
   *
   * @return View
   */
  public function postSetPaymentMethodAction(ParamFetcher $paramFetcher)
  {
    // Adjust the format of the expiration date.
    $expiration = null;
    if ($paramFetcher->get('expirationYear') != null)
      $expiration = $paramFetcher->get('expirationYear') . '-' .
               $paramFetcher->get('expirationMonth') . '-' .
               $paramFetcher->get('expirationDay');

    // This is the asigned path by NovoPayment to this action.
    $path = "/customer/" . $paramFetcher->get('documentNumber') .
            "/payment-method/";

    // Set up the headers to default if none is provided.
    $header = $this->setHeaders($paramFetcher);

    $parameters = array();
    $parameters['document-type'] = $paramFetcher->get('documentType');
    $parameters['document-number'] = $paramFetcher->get('documentNumber');
    $parameters['payment-type'] = $paramFetcher->get('paymentType');
    $parameters['account-number'] = $paramFetcher->get('accountNumber');
    $parameters['expiration-date'] = $expiration;
    $parameters['code-check'] = $paramFetcher->get('codeCheck');
    $parameters['payment-mode'] = $paramFetcher->get('paymentMode');

    /** @var View $responseView */
    $responseView = $this->callApi($header, $parameters, $path);

    return $responseView;
  }

  /**
   * Get the last 5 payment methods.<br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Get the last 5 payment methods.",
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
   * @RequestParam(name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
   *
   * @RequestParam(name="channel", nullable=true, description="Channel from where it is requested(MOBILE, WEB).")
   * @RequestParam(name="country", nullable=true, description="Country code from  ISO 3166-1.")
   * @RequestParam(name="language", nullable=true, description="Language code from ISO 639-1.")
   * @RequestParam(name="content_type", nullable=true, description="Request format(application/xml, application/json).")
   * @RequestParam(name="accept", nullable=true, description="Accepted response format(application/xml, application/json).")
   *
   * @return View
   */
  public function getLast5PaymentMethodAction(ParamFetcher $paramFetcher)
  {
    // This is the asigned path by NovoPayment to this action.
    $path = "/customer/" . $paramFetcher->get('documentNumber') .
            "/payment-method/";

    // Set up the headers to default if none is provided.
    $header = $this->setHeaders($paramFetcher);

    $parameters = array();

    /** @var View $responseView */
    $responseView = $this->callApi($header, $parameters, $path, "get");

    return $responseView;
  }

  /**
   * Get payment method by client.<br/>
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
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @RequestParam(name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
   * @RequestParam(name="paymentMethodId", nullable=false, requirements="([0-9])+", strict=true, description="payment method id.")
   *
   * @RequestParam(name="channel", nullable=true, description="Channel from where it is requested(MOBILE, WEB).")
   * @RequestParam(name="country", nullable=true, description="Country code from  ISO 3166-1.")
   * @RequestParam(name="language", nullable=true, description="Language code from ISO 639-1.")
   * @RequestParam(name="content_type", nullable=true, description="Request format(application/xml, application/json).")
   * @RequestParam(name="accept", nullable=true, description="Accepted response format(application/xml, application/json).")
   *
   * @return View
   */
  public function getPaymentMethodByClientAction(ParamFetcher $paramFetcher)
  {
    // This is the asigned path by NovoPayment to this action.
    $path = "/customer/" . $paramFetcher->get('documentNumber') .
            "/payment-method/" . $paramFetcher->get('paymentMethodId');

    // Set up the headers to default if none is provided.
    $header = $this->setHeaders($paramFetcher);

    $parameters = array();

    /** @var View $responseView */
    $responseView = $this->callApi($header, $parameters, $path);

    return $responseView;
  }

  /**
   * Delete payment method for a client.<br/>
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
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @RequestParam(name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
   * @RequestParam(name="paymentMethodId", nullable=false, requirements="([0-9A-Za-z])+", strict=true, description="id of the payment method.")
   *
   * @RequestParam(name="channel", nullable=true, description="Channel from where it is requested(MOBILE, WEB).")
   * @RequestParam(name="country", nullable=true, description="Country code from  ISO 3166-1.")
   * @RequestParam(name="language", nullable=true, description="Language code from ISO 639-1.")
   * @RequestParam(name="content_type", nullable=true, description="Request format(application/xml, application/json).")
   * @RequestParam(name="accept", nullable=true, description="Accepted response format(application/xml, application/json).")
   *
   * @return View
   */
  public function deleteClientPaymentMethodAction(ParamFetcher $paramFetcher)
  {
    // This is the asigned path by NovoPayment to this action.
    $path = "/customer/" . $paramFetcher->get('documentNumber') .
            "/payment-method/" . $paramFetcher->get('paymentMethodId');

    // Set up the headers to default if none is provided.
    $header = $this->setHeaders($paramFetcher);

    $parameters = array();

    /** @var View $responseView */
    $responseView = $this->callApi($header, $parameters, $path, 'delete');

    return $responseView;
  }

  /**
   * Insert a new beneficiary.<br/>
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
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @RequestParam(name="documentType", nullable=false, requirements="([A-Z|a-z]){2}", strict=true, description="documentType.")
   * @RequestParam(name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
   * @RequestParam(name="name", nullable=false, requirements="([a-z|A-Z| ])+", strict=true, description="first name.")
   * @RequestParam(name="lastName", nullable=false, requirements="([a-z|A-Z| ])+", strict=true, description="last name.")
   * @RequestParam(name="yearBirth", nullable=false, requirements="([0-9]){4}", strict=true, description="year of birth.")
   * @RequestParam(name="monthBirth", nullable=false, requirements="([0-9]){2}", strict=true, description="month of birth.")
   * @RequestParam(name="dayBirth", nullable=false, requirements="([0-9]){2}", strict=true, description="day of birth.")
   * @RequestParam(name="phone", nullable=false, requirements="([0-9])+", strict=true, description="phone.")
   * @RequestParam(name="email", nullable=false, strict=true, description="email.")
   * @RequestParam(name="companyId", nullable=false, strict=true, description="id of the company(NIT)")
   * @RequestParam(name="companyBranch", nullable=true, description="Company branch id")
   * @RequestParam(name="paymentMethodId", nullable=false, requirements="([0-9])+", description="payment method id(1-cash, 2-SVA, 3-account)")
   * @RequestParam(name="PaymentAccountNumber", nullable=true, requirements="([0-9])+", description="Number of the payment account")
   * @RequestParam(name="PaymentBankNumber", nullable=true, requirements="([0-9])+", description="Id of the bank")
   * @RequestParam(name="PaymentType", nullable=true, description="Ahorros or Corriente")
   *
   * @RequestParam(name="channel", nullable=true, description="Channel from where it is requested(MOBILE, WEB).")
   * @RequestParam(name="country", nullable=true, description="Country code from  ISO 3166-1.")
   * @RequestParam(name="language", nullable=true, description="Language code from ISO 639-1.")
   * @RequestParam(name="content_type", nullable=true, description="Request format(application/xml, application/json).")
   * @RequestParam(name="accept", nullable=true, description="Accepted response format(application/xml, application/json).")
   *
   * @return View
   */
  public function postInsertBeneficiaryAction(ParamFetcher $paramFetcher)
  {
    // Create the birth date in the right format.
    $birth = $paramFetcher->get('dayBirth') . '/' . $paramFetcher->get('monthBirth') .
             '/' . $paramFetcher->get('yearBirth');
    // This is the asigned path by NovoPayment to this action.
    $path = "/customer/" . $paramFetcher->get('documentNumber') .
            "/beneficiary/";

    // Set up the headers to default if none is provided.
    $header = $this->setHeaders($paramFetcher);

    $parameters = array();
    $parameters['document-type'] = $paramFetcher->get('documentType');
    $parameters['document-number'] = $paramFetcher->get('documentNumber');
    $parameters['name'] = $paramFetcher->get('name');
    $parameters['last-name'] = $paramFetcher->get('lastName');
    $parameters['birth-date'] = $birth;
    $parameters['phone-number'] = $paramFetcher->get('phone');
    $parameters['email'] = $paramFetcher->get('email');
    $parameters['company-id'] = $paramFetcher->get('companyId');
    $parameters['company-branch'] = $paramFetcher->get('companyBranch');
    $parameters['payment-mode-id'] = $paramFetcher->get('paymentMethodId');
    $parameters['payment-mode-account'] = $paramFetcher->get('PaymentAccountNumber');
    $parameters['payment-mode-bank'] = $paramFetcher->get('PaymentBankNumber');
    $parameters['payment-mode-type'] = $paramFetcher->get('PaymentType');

    /** @var View $responseView */
    $responseView = $this->callApi($header, $parameters, $path);

    return $responseView;
  }

  /**
   * Get the information of a beneficiary of a client.<br/>
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
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @RequestParam(name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
   * @RequestParam(name="beneficiaryId", nullable=false, requirements="([0-9])+", strict=true, description="document.")
   *
   * @RequestParam(name="channel", nullable=true, description="Channel from where it is requested(MOBILE, WEB).")
   * @RequestParam(name="country", nullable=true, description="Country code from  ISO 3166-1.")
   * @RequestParam(name="language", nullable=true, description="Language code from ISO 639-1.")
   * @RequestParam(name="content_type", nullable=true, description="Request format(application/xml, application/json).")
   * @RequestParam(name="accept", nullable=true, description="Accepted response format(application/xml, application/json).")
   *
   * @return View
   */
  public function getBeneficiaryByClientAction(ParamFetcher $paramFetcher)
  {
    // This is the asigned path by NovoPayment to this action.
    $path = "/customer/" . $paramFetcher->get('documentNumber') .
            "/beneficiary/" . $paramFetcher->get('beneficiaryId');

    // Set up the headers to default if none is provided.
    $header = $this->setHeaders($paramFetcher);

    $parameters = array();

    /** @var View $responseView */
    $responseView = $this->callApi($header, $parameters, $path, 'get');

    return $responseView;
  }

  /**
   * Get All the beneficiaries of a client.<br/>
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
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @RequestParam(name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
   *
   * @RequestParam(name="channel", nullable=true, description="Channel from where it is requested(MOBILE, WEB).")
   * @RequestParam(name="country", nullable=true, description="Country code from  ISO 3166-1.")
   * @RequestParam(name="language", nullable=true, description="Language code from ISO 639-1.")
   * @RequestParam(name="content_type", nullable=true, description="Request format(application/xml, application/json).")
   * @RequestParam(name="accept", nullable=true, description="Accepted response format(application/xml, application/json).")
   *
   * @return View
   */
  public function getAllBeneficiariesByClientAction(ParamFetcher $paramFetcher)
  {
    // This is the asigned path by NovoPayment to this action.
    $path = "/customer/" . $paramFetcher->get('documentNumber') .
            "/beneficiary/";

    // Set up the headers to default if none is provided.
    $header = $this->setHeaders($paramFetcher);

    $parameters = array();

    /** @var View $responseView */
    $responseView = $this->callApi($header, $parameters, $path, 'get');

    return $responseView;
  }

  /**
   * Delete a beneficiary by client.<br/>
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
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @RequestParam(name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
   * @RequestParam(name="beneficiaryId", nullable=false, requirements="([0-9])+", strict=true, description="document.")
   *
   * @RequestParam(name="channel", nullable=true, description="Channel from where it is requested(MOBILE, WEB).")
   * @RequestParam(name="country", nullable=true, description="Country code from  ISO 3166-1.")
   * @RequestParam(name="language", nullable=true, description="Language code from ISO 639-1.")
   * @RequestParam(name="content_type", nullable=true, description="Request format(application/xml, application/json).")
   * @RequestParam(name="accept", nullable=true, description="Accepted response format(application/xml, application/json).")
   *
   * @return View
   */
  public function deleteBeneficiaryByClientAction(ParamFetcher $paramFetcher)
  {
    // This is the asigned path by NovoPayment to this action.
    $path = "/customer/" . $paramFetcher->get('documentNumber') .
            "/beneficiary/" . $paramFetcher->get('beneficiaryId') ;

    // Set up the headers to default if none is provided.
    $header = $this->setHeaders($paramFetcher);

    $parameters = array();

    /** @var View $responseView */
    $responseView = $this->callApi($header, $parameters, $path, "delete");

    return $responseView;
  }

  /**
   * Aproval for a clients payment.<br/>
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
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @RequestParam(name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
   *
   * @RequestParam(name="paymentMethodId", nullable=true, requirements="([0-9]| )+", strict=true, description="Id of the payment method.")
   * @RequestParam(name="expirationYear", nullable=true, requirements="([0-9]){4}", strict=true, description="expiration year.")
   * @RequestParam(name="expirationMonth", nullable=true, requirements="([0-9]){2}", strict=true, description="expiration month.")
   * @RequestParam(name="expirationDay", nullable=true, requirements="([0-9]){2}", strict=true, description="expiration day.")
   * @RequestParam(name="codeCheck", nullable=true, requirements="([0-9]){3}", strict=true, description="code check.")
   * @RequestParam(name="totalAmount", nullable=false, requirements="[0-9]+(\.)?[0-9]*(,?[0-9]+)?", strict=true, description="Total amount.")
   * @RequestParam(name="taxAmount", nullable=false, requirements="[0-9]+(\.)?[0-9]*(,?[0-9]+)?", strict=true, description="Tax amount.")
   * @RequestParam(name="taxBase", nullable=false, requirements="[0-9]+(\.)?[0-9]*(,?[0-9]+)?", strict=true, description="Tax base.")
   * @RequestParam(name="commissionAmount", nullable=false, requirements="[0-9]+(\.)?[0-9]*(,?[0-9]+)?", strict=true, description="Commission amount.")
   * @RequestParam(name="commissionBase", nullable=false, requirements="[0-9]+(\.)?[0-9]*(,?[0-9]+)?", strict=true, description="Commission base.")
   *
   * @RequestParam(name="channel", nullable=true, description="Channel from where it is requested(MOBILE, WEB).")
   * @RequestParam(name="country", nullable=true, description="Country code from  ISO 3166-1.")
   * @RequestParam(name="language", nullable=true, description="Language code from ISO 639-1.")
   * @RequestParam(name="content_type", nullable=true, description="Request format(application/xml, application/json).")
   * @RequestParam(name="accept", nullable=true, description="Accepted response format(application/xml, application/json).")
   *
   * @return View
   */
  public function postPaymentAprovalAction(ParamFetcher $paramFetcher)
  {
    // Adjust the format of the expiration date.
    $expiration = $paramFetcher->get('expirationYear') . '-' .
             $paramFetcher->get('expirationMonth') . '-' .
             $paramFetcher->get('expirationDay');

    // This is the asigned path by NovoPayment to this action.
    $path = "/customer/" . $paramFetcher->get('documentNumber') .
            "/charge";

    // Set up the headers to default if none is provided.
    $header = $this->setHeaders($paramFetcher);

    $parameters = array();
    $parameters['method-id'] = $paramFetcher->get('paymentMethodId');
    $parameters['expiration-date'] = $expiration;
    $parameters['code-check'] = $paramFetcher->get('codeCheck');
    $parameters['total-amount'] = $paramFetcher->get('totalAmount');
    $parameters['tax-amount'] = $paramFetcher->get('taxAmount');
    $parameters['tax-base'] = $paramFetcher->get('taxBase');
    $parameters['commission-amount'] = $paramFetcher->get('commissionAmount');
    $parameters['commission-base'] = $paramFetcher->get('commissionBase');

    /** @var View $responseView */
    $responseView = $this->callApi($header, $parameters, $path);

    return $responseView;
  }

  /**
   * Dispersion of beneficiary payment by client.<br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Dispersion of beneficiary payment by client.",
   *   statusCodes = {
   *     200 = "OK"
   *     201 = "Accepted",
   *     400 = "Bad Request",
   *     401 = "Unauthorized"
   *   }
   * )
   *
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @RequestParam(name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
   *
   * @RequestParam(name="chargeId", nullable=false, requirements="([0-9]| )+", strict=true, description="Id of the charge.")
   * @RequestParam(name="beneficiaryId", nullable=false, requirements="([0-9]| )+", strict=true, description="expiration year.")
   * @RequestParam(name="beneficiaryAmount", nullable=false, requirements="([0-9]| )+", strict=true, description="Amount of the beneficiary")
   * @RequestParam(name="beneficiaryPhone", nullable=true, requirements="([0-9]| )+", strict=true, description="Phone number of the beneficiary.")
   *
   * @RequestParam(name="channel", nullable=true, description="Channel from where it is requested(MOBILE, WEB).")
   * @RequestParam(name="country", nullable=true, description="Country code from  ISO 3166-1.")
   * @RequestParam(name="language", nullable=true, description="Language code from ISO 639-1.")
   * @RequestParam(name="content_type", nullable=true, description="Request format(application/xml, application/json).")
   * @RequestParam(name="accept", nullable=true, description="Accepted response format(application/xml, application/json).")
   *
   * @return View
   */
  public function postPaymentDispersionByClientAction(ParamFetcher $paramFetcher)
  {
    // This is the asigned path by NovoPayment to this action.
    $path = "/customer/" . $paramFetcher->get('documentNumber') .
            "/beneficiary/transfer";

    // Set up the headers to default if none is provided.
    $header = $this->setHeaders($paramFetcher);

    $parameters = array();
    $parameters['charge-id'] = $paramFetcher->get('chargeId');
    $parameters['beneficiary-id'] = $paramFetcher->get('beneficiaryId');
    $parameters['beneficiary-amount'] = $paramFetcher->get('beneficiaryAmount');
    $parameters['beneficiary-phone-number'] = $paramFetcher->get('beneficiaryPhone');

    /** @var View $responseView */
    $responseView = $this->callApi($header, $parameters, $path);

    return $responseView;
  }

  /**
   * Get a specific charge by client.<br/>
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
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @RequestParam(name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
   *
   * @RequestParam(name="channel", nullable=true, description="Channel from where it is requested(MOBILE, WEB).")
   * @RequestParam(name="country", nullable=true, description="Country code from  ISO 3166-1.")
   * @RequestParam(name="language", nullable=true, description="Language code from ISO 639-1.")
   * @RequestParam(name="content_type", nullable=true, description="Request format(application/xml, application/json).")
   * @RequestParam(name="accept", nullable=true, description="Accepted response format(application/xml, application/json).")
   *
   * @return View
   */
  public function getChargeByClientAction(ParamFetcher $paramFetcher)
  {
    // This is the asigned path by NovoPayment to this action.
    $path = "/customer/" . $paramFetcher->get('documentNumber') .
            "/charge/";

    // Set up the headers to default if none is provided.
    $header = $this->setHeaders($paramFetcher);

    $parameters = array();

    /** @var View $responseView */
    $responseView = $this->callApi($header, $parameters, $path, "get");

    return $responseView;
  }

  /**
   * Get a specific charge by client.<br/>
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
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @RequestParam(name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
   * @RequestParam(name="chargeId", nullable=false, requirements="([0-9])+", strict=true, description="charge Id.")
   *
   * @RequestParam(name="channel", nullable=true, description="Channel from where it is requested(MOBILE, WEB).")
   * @RequestParam(name="country", nullable=true, description="Country code from  ISO 3166-1.")
   * @RequestParam(name="language", nullable=true, description="Language code from ISO 639-1.")
   * @RequestParam(name="content_type", nullable=true, description="Request format(application/xml, application/json).")
   * @RequestParam(name="accept", nullable=true, description="Accepted response format(application/xml, application/json).")
   *
   * @return View
   */
  public function getChargeByClientAction(ParamFetcher $paramFetcher)
  {
    // This is the asigned path by NovoPayment to this action.
    $path = "/customer/" . $paramFetcher->get('documentNumber') .
            "/charge/" . $paramFetcher->get('chargeId');

    // Set up the headers to default if none is provided.
    // Set up the headers to default if none is provided.
    $header = $this->setHeaders($paramFetcher);

    $parameters = array();

    /** @var View $responseView */
    $responseView = $this->callApi($header, $parameters, $path, "get");

    return $responseView;
  }

  /**
   * Get dispersion of transfer by client.<br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Get dispersion of transfer by client.",
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
   * @RequestParam(name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
   * @RequestParam(name="transferId", nullable=false, requirements="([0-9])+", strict=true, description="charge Id.")
   *
   * @RequestParam(name="channel", nullable=true, description="Channel from where it is requested(MOBILE, WEB).")
   * @RequestParam(name="country", nullable=true, description="Country code from  ISO 3166-1.")
   * @RequestParam(name="language", nullable=true, description="Language code from ISO 639-1.")
   * @RequestParam(name="content_type", nullable=true, description="Request format(application/xml, application/json).")
   * @RequestParam(name="accept", nullable=true, description="Accepted response format(application/xml, application/json).")
   *
   * @return View
   */
  public function getChargeByClientAction(ParamFetcher $paramFetcher)
  {
    // This is the asigned path by NovoPayment to this action.
    $path = "/customer/" . $paramFetcher->get('documentNumber') .
            "/beneficiary/transfer/" . $paramFetcher->get('transferId');

    // Set up the headers to default if none is provided.
    $header = $this->setHeaders($paramFetcher);

    $parameters = array();

    /** @var View $responseView */
    $responseView = $this->callApi($header, $parameters, $path, "get");

    return $responseView;
  }

  /**
   * Get the balance of a client.<br/>
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
   * @param ParamFetcher $paramFetcher Paramfetcher
   *
   * @RequestParam(name="documentNumber", nullable=false, requirements="([0-9])+", strict=true, description="document.")
   * @RequestParam(name="beneficiaryId", nullable=false, requirements="([0-9])+", strict=true, description="charge Id.")
   *
   * @RequestParam(name="channel", nullable=true, description="Channel from where it is requested(MOBILE, WEB).")
   * @RequestParam(name="country", nullable=true, description="Country code from  ISO 3166-1.")
   * @RequestParam(name="language", nullable=true, description="Language code from ISO 639-1.")
   * @RequestParam(name="content_type", nullable=true, description="Request format(application/xml, application/json).")
   * @RequestParam(name="accept", nullable=true, description="Accepted response format(application/xml, application/json).")
   *
   * @return View
   */
  public function getBalanceByClientAction(ParamFetcher $paramFetcher)
  {
    // This is the asigned path by NovoPayment to this action.
    $path = "/customer/" . $paramFetcher->get('documentNumber') .
            "/beneficiary/" . $paramFetcher->get('beneficiaryId') . "/balance";

    // Set up the headers to default if none is provided.
    $header = $this->setHeaders($paramFetcher);

    $parameters = array();

    /** @var View $responseView */
    $responseView = $this->callApi($header, $parameters, $path, "get");

    return $responseView;
  }

}
?>
