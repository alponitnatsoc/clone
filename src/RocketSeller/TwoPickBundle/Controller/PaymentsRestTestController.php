<?php
namespace RocketSeller\TwoPickBundle\Controller;


use RocketSeller\TwoPickBundle\Entity\Employer;
use FOS\RestBundle\Controller\FOSRestController;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\Post;
use RocketSeller\TwoPickBundle\Entity\Person;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RocketSeller\TwoPickBundle\Entity\Workplace;
use Symfony\Component\Validator\ConstraintViolationList;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use DateTime;

use EightPoints\Bundle\GuzzleBundle;

/**
 * This class is only used to test the REST request services to the payment
 * interface.
 */
class PaymentsRestTestController extends FOSRestController
{
  /**
   * It sets the headers for the payments request. 
   * @return Array with the header options.
   */
  private function setHeaders() {
    $header = array();
    $header['x-channel'] = 'WEB';
    $header['x-country'] = 'CO' ;
    $header['language'] = 'es' ;
    $header['content-type'] = 'application/json';
    $header['accept'] = 'application/json';
    return $header;
  }


  /**
   * @Post("mock/customer/")
   * Mocks the insert client request<br/>
   * If the document is 123456789 it will return success, otherwise it returns
   * a bad request.
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Mocks the insert client request. If the document is
   *                  123456789 it will return success, otherwise it returns
   *                  a bad request",
   *   statusCodes = {
   *     201 = "Created",
   *     400 = "Bad Request",
   *     401 = "Unauthorized"
   *   }
   * )
   *
   * @param ParamFetcher $paramFetcher Paramfetcher
   * @RequestParam(name="document-number", nullable=false, requirements="([0-9])+", strict=true, description="document.")
   * @return View
   */
  public function postMocsksCustomerAction(ParamFetcher $paramFetcher)
  {
    $document = $paramFetcher->get('document-number');
    $view = View::create();
    if ($document == 123456789)
    {
      $view->setStatusCode(201);
    }
    else
    {
      $view->setStatusCode(400);
    }
    return $view;
  }

  /**
   * @Post("mock/customer/{id}/payment-method/")
   * Mocks the insert payment method request<br/>
   * If the document is 123456789 it will return success, otherwise it returns
   * a bad request. It returns a method id: 123456
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Mocks the insert payment request. If the document is
   *                  123456789 it will return success, otherwise it returns
   *                  a bad request",
   *   statusCodes = {
   *     201 = "Created",
   *     400 = "Bad Request",
   *     401 = "Unauthorized"
   *   }
   * )
   *
   * @param ParamFetcher $paramFetcher Paramfetcher
   * @RequestParam(name="document-number", nullable=false, requirements="([0-9])+", strict=true, description="document.")
   * @return View
   */
  public function postMockAddPaymentMethodAction(ParamFetcher $paramFetcher)
  {
    $document = $paramFetcher->get('document-number');
    $view = View::create();
    if ($document == 123456789)
    {
      $view->setStatusCode(201);
    }
    else
    {
      $view->setStatusCode(400);
    }

     $view->setData((array('method-id' => 123456)));
    return $view;
  }

  /**
   * @Post("mock/customer/{id}/beneficiary/")
   * Mocks the insert beneficiary method request<br/>
   * If the document is 123456789 it will return success, otherwise it returns
   * a bad request.
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Mocks the insert beneficiary request. If the document is
   *                  123456789 it will return success, otherwise it returns
   *                  a bad request",
   *   statusCodes = {
   *     201 = "Created",
   *     400 = "Bad Request",
   *     401 = "Unauthorized"
   *   }
   * )
   *
   * @param ParamFetcher $paramFetcher Paramfetcher
   * @RequestParam(name="document-number", nullable=false, requirements="([0-9])+", strict=true, description="document.")
   * @return View
   */
  public function postMockAddBeneficiaryAction(ParamFetcher $paramFetcher)
  {
    $document = $paramFetcher->get('document-number');
    $view = View::create();
    if ($document == 123456789)
    {
      $view->setStatusCode(201);
    }
    else
    {
      $view->setStatusCode(400);
    }
    return $view;
  }

  /**
   * @Post("mock/customer/{id}/charge")
   * Mocks the payment approval method request<br/>
   * If the document is 123456789 it will return success, otherwise it returns
   * a bad request. It returns a charge id: 23456
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Mocks the payment approval method. If the document is
   *                  123456789 it will return success, otherwise it returns
   *                  a bad request. Returns charge-id: 23456",
   *   statusCodes = {
   *     201 = "Created",
   *     400 = "Bad Request",
   *     401 = "Unauthorized"
   *   }
   * )
   *
   * @param ParamFetcher $paramFetcher Paramfetcher
   * @return View
   */
  public function postMockPaymentApprovalAction(ParamFetcher $paramFetcher, $id)
  {
    $view = View::create();
    if ($id == 123456789)
    {
      $view->setStatusCode(201);
    }
    else
    {
      $view->setStatusCode(400);
    }
    $view->setData((array('charge-id' => 23456)));
    return $view;
  }



  /**
   * @Post("mock/customer/{id}/beneficiary/transfer")
   * Mocks the payment transfer method request<br/>
   * If the document is 123456789 it will return success, otherwise it returns
   * a bad request. It returns a transfer id: 34567
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "Mocks the payment transfer request. If the document is
   *                  123456789 it will return success, otherwise it returns
   *                  a bad request. Returns transfer-id: 34567",
   *   statusCodes = {
   *     200 = "OK",
   *     201 = "Created",
   *     400 = "Bad Request",
   *     401 = "Unauthorized"
   *   }
   * )
   *
   * @param ParamFetcher $paramFetcher Paramfetcher
   * @return View
   */
  public function postMockPaymentTransferAction(ParamFetcher $paramFetcher, $id)
  {
    $view = View::create();
    if ($id == 123456789)
    {
      $view->setStatusCode(201);
    }
    else
    {
      $view->setStatusCode(400);
    }
    $view->setData((array('transfer-id' => 34567)));
    return $view;
  }
}
