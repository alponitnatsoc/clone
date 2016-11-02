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

  /**
   * send attached document email
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "send attached document email",
   *   statusCodes = {
   *     200 = "Created successfully",
   *     400 = "Bad Request",
   *   }
   * )
   *
   * @param paramFetcher $paramFetcher ParamFetcher
   *
   * @RequestParam(name="docType", nullable=false, strict=true, description="document type: contrato | mandato")
   * @RequestParam(name="idDocument", nullable=false, strict=true, description="id to fetch docuement")
   *
   * @return View
   */
  public function postSendAttachDocumentEmailAction(ParamFetcher $paramFetcher) {

    // contrato | mandato
    $docType = $paramFetcher->get('docType');
    $idDocument = $paramFetcher->get('idDocument');
    $user = $this->getUser();
    $params = array(
        'ref'=> $docType,
        'id' => $idDocument,
        'type' => 'pdf',
        'attach' => null
    );
    $documentResult = $this->forward('RocketSellerTwoPickBundle:Document:downloadDocuments', $params);
    $file =  $documentResult->getContent();
    if (!file_exists('uploads/temp/mailDocs')) {
        mkdir('uploads/temp/mailDocs', 0777, true);
    }
    $path = 'uploads/temp/mailDocs/'.$user->getId().'_tempDocumentToMail.pdf';
    file_put_contents($path, $file);

    $context = array( 'emailType' => 'contractAttachmentEmail',
                      'userName' => $user->getPersonPerson()->getNames() . ' ' . $user->getPersonPerson()->getLastName1(),
                      'docType' => $docType,
                      'path' => $path,
                      'toEmail' => $user->getEmail(),
                      'documentName' => $docType
                    );

    $smailer = $this->get('symplifica.mailer.twig_swift');
    $send = $smailer->sendEmailByTypeMessage($context);

    $view = View::create();
    $view->setStatusCode(200);
    return $view->setData(array('send' => $send));
  }
}
