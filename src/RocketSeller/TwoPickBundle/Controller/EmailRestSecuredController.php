<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Response;

class EmailRestSecuredController extends FOSRestController {
  /**
   * send help email
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "send help email ",
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
   * @RequestParam(name="subject", nullable=false, strict=true, description="email subject")
   * @RequestParam(name="message", nullable=false, strict=true, description="email message")
   *
   * @return View
   */
  public function postSendHelpEmailAction(ParamFetcher $paramFetcher) {
    $name = $paramFetcher->get('name');
    $phone = $paramFetcher->get('phone');
    $email = $paramFetcher->get('email');
    $subject = $paramFetcher->get('subject');
    $message = $paramFetcher->get('message');

    $context = array( 'emailType' => 'help',
                      'name' => $name,
                      'fromEmail' => $email,
                      'subject' => $subject,
                      'message' => $message,
                      'ip' => 'Enviado desde movil',
                      'phone'=>$phone);

    $smailer = $this->get('symplifica.mailer.twig_swift');
    $send = $smailer->sendEmailByTypeMessage($context);

    $view = View::create();
    $view->setStatusCode(200);
    return $view->setData(array('send' => $send));
  }
}
