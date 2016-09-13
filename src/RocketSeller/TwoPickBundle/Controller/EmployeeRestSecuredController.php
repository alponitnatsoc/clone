<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Response;

class EmployeeRestSecuredController extends FOSRestController
{
  /**
   * save employee daviplata phone
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "save employee daviplata phone",
   *   statusCodes = {
   *     200 = "Created successfully",
   *     400 = "Bad Request",
   *     401 = "Unauthorized",
   *   }
   * )
   *
   * @param paramFetcher $paramFetcher ParamFetcher
   *
   * @RequestParam(name="payMethodId", nullable=true, strict=true, description="employee payment method id")
   * @RequestParam(name="cellphone", nullable=true, strict=true, description="employee daviplata cellphone")
   *
   * @return View
   */
  public function postSaveDaviplataPhoneAction(ParamFetcher $paramFetcher) {
    $payMethodId = $paramFetcher->get('payMethodId');
    $cellphone = $paramFetcher->get('cellphone');

    $user = $this->getUser();

    $methodRepo = $em->getRepository("RocketSellerTwoPickBundle:PayMethod");
    /** @var \RocketSeller\TwoPickBundle\Entity\PayMethod $paym */
    $paym = $methodRepo->find($payMethodId);
    if($paym==null) {
      $view = View::create();
      $view->setStatusCode(400);
      return $view->setData(array());
    }
    if($paym->getUserUser()->getId() != $user->getId()) {
      $view = View::create();
      $view->setStatusCode(401);
      return $view->setData(array());
    }

    $paym->setCellPhone($cellphone);
    $em->persist($paym);
    $em->flush();

    $view = View::create();
    $view->setStatusCode(200);
    return $view->setData(array());
  }
}
