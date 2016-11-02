<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Response;

class EmailRestController extends FOSRestController {
  /**
   * send contact email (not registered users)
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "send contact email (not registered users)",
   *   statusCodes = {
   *     200 = "Created successfully",
   *     400 = "Bad Request",
   *   }
   * )
   *
   * @param paramFetcher $paramFetcher ParamFetcher
   *
   * @RequestParam(name="name", nullable=false, strict=true, description="sender name")
   * @RequestParam(name="phone", nullable=false, strict=true, description="sender phone")
   * @RequestParam(name="email", nullable=false, strict=true, description="sender email")
   *
   * @return View
   */
  public function postSendContactEmailAction(ParamFetcher $paramFetcher) {
    $name = $paramFetcher->get('name');
    $phone = $paramFetcher->get('phone');
    $email = $paramFetcher->get('email');

    $context = array( 'emailType' => 'notRegisteredUserApp',
                      'name' => $name,
                      'userEmail' => $email,
                      'phone'=>$phone);

    $smailer = $this->get('symplifica.mailer.twig_swift');
    $send = $smailer->sendEmailByTypeMessage($context);

    $view = View::create();
    $view->setStatusCode(200);
    return $view->setData(array('send' => $send));
  }

}
