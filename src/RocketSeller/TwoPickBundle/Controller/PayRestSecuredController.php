<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Payroll;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\SerializationContext;
use RocketSeller\TwoPickBundle\Traits\SubscriptionMethodsTrait;

class PayRestSecuredController extends FOSRestController
{
    use SubscriptionMethodsTrait;

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
        $answer = new ArrayCollection();
        $answerPo = new ArrayCollection();
        /** @var PurchaseOrders $purchaseOrder */
        foreach ($purchaseOrders as $purchaseOrder) {
            if ($purchaseOrder->getPurchaseOrdersStatus()->getIdNovoPay() == "S2" && $purchaseOrder->getPurchaseOrderDescriptions()->count() > 0) {
                $answerPo->add($purchaseOrder);
                continue;
            }
            $pods = $purchaseOrder->getPurchaseOrderDescriptions();
            /** @var PurchaseOrdersDescription $pod */
            foreach ($pods as $pod) {
                if ($pod->getPurchaseOrdersStatus() == null) {
                    continue;
                }
                if ($pod->getPurchaseOrdersStatus()->getIdNovoPay() == "00" ||
                    $pod->getPurchaseOrdersStatus()->getIdNovoPay() == "-2"
                ) {
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
            'pods' => $answer,
            'pos' => $answerPo), 'json', $context);
        return $view->setData($encodedAnswer);
    }



    /**
     * edit pay method of pod which has been rejected
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "edit pay method of pod which has been rejected",
     *   statusCodes = {
     *     200 = "Created successfully",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *   }
     * )
     *
     * @param paramFetcher $paramFetcher ParamFetcher
     *
     * @RequestParam(name="accountTypeId", nullable=true, strict=true, description="employee account type")
     * @RequestParam(name="accountNumber", nullable=true, strict=true, description="employee account number")
     * @RequestParam(name="bankId", nullable=true, strict=true, description="employee back id")
     * @RequestParam(name="daviplataPhone", nullable=true, strict=true, description="daviplata phone number")
     * @RequestParam(name="verificationCode", nullable=false, strict=true, description="phone verification code")
     * @RequestParam(name="idPod", nullable=false, strict=true, description="id purchase order description")
     *
     * @return View
     */
    public function postEditPodPayMethodAction(ParamFetcher $paramFetcher)
    {
        $accountTypeId = $paramFetcher->get('accountTypeId');
        $accountNumber = $paramFetcher->get('accountNumber');
        $bankId = $paramFetcher->get('bankId');
        $daviplataPhone = $paramFetcher->get('daviplataPhone');
        $verificationCode = $paramFetcher->get('verificationCode');
        $idPod = $paramFetcher->get('idPod');

        $podRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersDescription");
        /** @var PurchaseOrdersDescription $realPod */
        $realPod = $podRepo->find($idPod);
        /** @var User $user */
        $user = $this->getUser();
        if ($user->getId() == $realPod->getPurchaseOrders()->getIdUser()->getId() &&
            $realPod->getPurchaseOrdersStatus()->getIdNovoPay() == "-2"
        ) {
            $payMethod = $realPod->getPayMethod();
            $contract = $realPod->getPayrollPayroll()->getContractContract();

            if ($user->getSmsCode() != $verificationCode) {
                $view = View::create();
                $view->setStatusCode(401);
                return $view->setData(array('response' => 'Codigo incorrecto'));
            }
            if ($accountTypeId != null && $accountNumber != null && $bankId != null) {
                $bank = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Bank")->find($bankId);
                $accountType = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:AccountType")->find($accountTypeId);
                $payMethod->setBankBank($bank);
                $payMethod->setAccountTypeAccountType($accountType);
                $payMethod->setAccountNumber($accountNumber);
            } else if ($daviplataPhone != null && $payMethod->getPayTypePayType()->getSimpleName() == "DAV") {
                $payMethod->setCellPhone($daviplataPhone);
            } else {
                $view = View::create();
                $view->setStatusCode(400);
                return $view->setData(array('response' => 'values not set'));
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($payMethod);
            $em->flush();

            $params = array(
                'idEmployerHasEmployee' => $contract->
                getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()
            );

            // $adding = $this->forward('RocketSellerTwoPickBundle:Pay:addEmployeeToHighTech', $params);
            $adding = $this->addEmployeeToHighTech($contract->getEmployerHasEmployeeEmployerHasEmployee());
            if ($adding) {
                $view = View::create();
                $view->setStatusCode(200);
                return $view->setData(array('payMethod' => $payMethod));
            } else {
                $view = View::create();
                $view->setStatusCode(400);
                return $view->setData(array('response' => 'No se agregó a Hightech'));
            }

        }

        $view = View::create();
        $view->setStatusCode(401);
        return $view->setData(array());
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
    public function getPayTypeFieldsAction($idPayType)
    {
        $em = $this->getDoctrine()->getManager();

        $payType = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:PayType')
            ->find($idPayType);

        $fields = $payType->getPayMethodFields();

        $data = array();
        $data['fields'] = $fields;
        foreach ($fields as $field) {
            $dataType = $field->getDataType();
            if ($dataType[0] <= "Z") {
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
