<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Person;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\SerializationContext;

class PayRestSecuredController extends FOSRestController
{

  /**
   * get pods currenly in process <br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "get pods currenly in process",
   *   statusCodes = {
   *     200 = "OK",
   *     400 = "Bad Request",
   *     401 = "Unauthorized",
   *     404 = "Not Found"
   *   }
   * )
   *
   *
   * @return View
   */
  public function getListPodsAction()
  {
    /** @var User $user */
    $user = $this->getUser();
    $purchaseOrders = $user->getPurchaseOrders();
    $answer= new ArrayCollection();
    /** @var PurchaseOrders $purchaseOrder */
    foreach ($purchaseOrders as $purchaseOrder) {
        $pods = $purchaseOrder->getPurchaseOrderDescriptions();
        /** @var PurchaseOrdersDescription $pod */
        foreach ($pods as $pod) {
            if($pod->getPurchaseOrdersStatus() == null) {
                continue;
            }
            if( $pod->getPurchaseOrdersStatus()->getIdNovoPay()=="00" ||
                $pod->getPurchaseOrdersStatus()->getIdNovoPay()=="-2" ) {
                $answer->add($pod);
            }
        }
    }

    $view = View::create();
    $view->setStatusCode(200);

    $context = new SerializationContext();
    $context->setSerializeNull(true);
    $serializer = $this->get('jms_serializer');
    $encodedAnswer = $serializer->serialize(array(
        'pods' => $answer), 'json', $context);
    return $view->setData($encodedAnswer);
  }

  /**
   * edit pod which has been rejected
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "edit pod which has been rejected",
   *   statusCodes = {
   *     200 = "Created successfully",
   *     400 = "Bad Request",
   *   }
   * )
   *
   * @param paramFetcher $paramFetcher ParamFetcher
   *
   * @RequestParam(name="idPod", nullable=false, strict=true, description="purhcase order description id")
   * @RequestParam(name="accountType", nullable=false, strict=true, description="employee account type")
   * @RequestParam(name="accountNumber", nullable=false, strict=true, description="employee account number")
   * @RequestParam(name="bankId", nullable=false, strict=true, description="iemployee back id")
   * @RequestParam(name="verificationCode", nullable=false, strict=true, description="phone verification code")
   *
   * @return View
   */
  public function postEditPodAction(ParamFetcher $paramFetcher) {

  }

  /**
   * get pay type fields <br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "get pay type fields",
   *   statusCodes = {
   *     200 = "OK",
   *     400 = "Bad Request",
   *   }
   * )
   *
   *
   * @param String $idPayType id pay type.
   *
   * @return View
   */
  public function getPayTypeFieldsAction($idPayType) {
    $em = $this->getDoctrine()->getManager();

    $payType = $this->getDoctrine()
        ->getRepository('RocketSellerTwoPickBundle:PayType')
        ->find($idPayType);

    $fields = $payType->getPayMethodFields();

    $data = array();
    $data['fields'] = $fields;
    foreach ($fields as $field) {
      $dataType = $field->getDataType();
      if($dataType[0]<="Z") {
        $data[$dataType] = $this->getDoctrine()
            ->getRepository("RocketSellerTwoPickBundle:$dataType")
            ->findAll();
      }
    }

    $view = View::create();
    $view->setStatusCode(200);
    return $view->setData(array("data" => $data));
  }

  /**
   * send verification code to phone <br/>
   *
   * @ApiDoc(
   *   resource = true,
   *   description = "send verification code to phone",
   *   statusCodes = {
   *     200 = "OK",
   *     401 = "Unauthorized",
   *   }
   * )
   *
   *
   * @return View
   */
  public function getSendVerificationCodeAction()
  {
    $em = $this->getDoctrine()->getManager();

    $user = $this->getUser();
    $code = rand(10100, 99999);
    $message = "Tu codigo de confirmacion de Symplifica es: " . $code;

    $user->setSmsCode($code);
    $em->persist($user);
    $em->flush();

    /** @var Phone $phone */
    $phone = $user->getPersonPerson()->getPhones()[0];
    $twilio = $this->get('twilio.api');
    $cellphone = $phone;
    $twilio->account->messages->sendMessage(
        "+19562671001", "+57" . $cellphone->getPhoneNumber(), $message);

    $view = View::create();
    $view->setStatusCode(200);
    return $view->setData(array());
  }
}
