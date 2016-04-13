<?php

namespace RocketSeller\TwoPickBundle\Controller;

use DateTime;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Entity\Product;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\Pay;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use Symfony\Component\HttpFoundation\Request;

class PaymentMethodRestController extends FOSRestController
{
    /**
     * Add the credit card<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Finds the cities of a department.",
     *   statusCodes = {
     *     201 = "Created",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     406 = "Not Acceptable",
     *     409 = "Conflict",
     *     500 = "Novo TimeOut"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @return View
     * @RequestParam(name="accountNumber", nullable=false,  requirements="\d+", strict=true, description="Account Number")
     * @RequestParam(name="bankId", nullable=false,  requirements="\d+", strict=true, description="the bank id")
     * @RequestParam(name="accountTypeId", nullable=false, strict=true, description="Account type ID")
     * @RequestParam(name="userId", nullable=false,  requirements="\d+", strict=true, description="Account type ID")
     */
    public function postAddDebitAccountAction(ParamFetcher $paramFetcher)
    {
        /** @var User $user */
        $user=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User")->find($paramFetcher->get("userId"));
        //TODO buscar en la bd los codigos de hightec
        $person = $user->getPersonPerson();

        $employer=$person->getEmployer();
        $view = View::create();
        $request = $this->container->get('request');
        $request->setMethod("POST");
        $request->request->add(array(
            "accountNumber" => $employer->getIdHighTech(),
            "bankCode" => $paramFetcher->get("bankId"),
            "accountType" => $paramFetcher->get("accountTypeId"),
            "bankAccountNumber" => $paramFetcher->get("accountNumber"),
            "expirationDate" => date('Y-m-d', strtotime('+1 years')),
            "authorizationDocumentName" => $person->getFullName().".txt",
            "authorizationDocument" => $person->getFullName(),
        ));
        $view = View::create();
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:Payments2Rest:postRegisterBankAccount', array('_format' => 'json'));
        if ($insertionAnswer->getStatusCode() != 200) {
            $view->setStatusCode($insertionAnswer->getStatusCode())->setData(json_decode($insertionAnswer->getContent(), true));
            return $view;
        }

        return $view->setStatusCode(201);
    }
    /**
     * Add the credit card<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Finds the cities of a department.",
     *   statusCodes = {
     *     201 = "Created",
     *     400 = "Bad Request",
     *     401 = "Unauthorized",
     *     406 = "Not Acceptable",
     *     409 = "Conflict",
     *     500 = "Novo TimeOut"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @return View
     * @RequestParam(name="credit_card", nullable=false,  requirements="\d+", strict=true, description="CC Number")
     * @RequestParam(name="expiry_date_year", nullable=false,  requirements="[0-9]{4}", strict=true, description="YEAR in YYYY format.")
     * @RequestParam(name="expiry_date_month", nullable=false,  requirements="[0-9]{2}", strict=true, description="Month in MM format.")
     * @RequestParam(name="cvv", nullable=false,  requirements="\d+", strict=true, description="CVV CC.")
     * @RequestParam(name="name_on_card", nullable=false, strict=true, description="The name on card")
     */
    public function postAddCreditCardAction(ParamFetcher $paramFetcher)
    {
        /** @var User $user */
        $user = $this->getUser();
        $person = $user->getPersonPerson();
        $view = View::create();
        $request = $this->container->get('request');
        $request->setMethod("POST");
        $request->request->add(array(
            "documentType" => $person->getDocumentType(),
            "documentNumber" => $person->getDocument(),
            "accountNumber" => $paramFetcher->get("credit_card"),
            "expirationYear" => $paramFetcher->get("expiry_date_year"),
            "expirationMonth" => $paramFetcher->get("expiry_date_month"),
            "codeCheck" => $paramFetcher->get("cvv"),
        ));
        $view = View::create();
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:postClientPaymentMethod', array('_format' => 'json'));
        if ($insertionAnswer->getStatusCode() != 201) {
            $view->setStatusCode($insertionAnswer->getStatusCode())->setData(json_decode($insertionAnswer->getContent(), true));
            return $view;
        }
        $chargeValue = 200;
        $idPayM = json_decode($insertionAnswer->getContent(), true)["method-id"];
        $purchaseOrder = new PurchaseOrders();
        $purchaseOrder->setValue($chargeValue);
        $purchaseOrder->setName("Cargo prueba CC");
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($purchaseOrder);
        $em->flush();
        $purchaseOrderId = $purchaseOrder->getIdPurchaseOrders();
        $em->remove($purchaseOrder);
        $em->flush();
        $request->setMethod("POST");
        $request->request->add(array(
            "documentNumber" => $person->getDocument(),
            "MethodId" => $idPayM,
            "totalAmount" => $chargeValue,
            "taxAmount" => 0,
            "taxBase" => 0,
            "commissionAmount" => 0,
            "commissionBase" => 0,
            "chargeMode" => 1,
            "chargeId" => $purchaseOrderId,
        ));
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:postPaymentAproval', array('_format' => 'json'));
        if ($insertionAnswer->getStatusCode() == 200) {
            $chargeRC = json_decode($insertionAnswer->getContent(), true)["charge-rc"];
        } else {
            $chargeRC = "-1";
        }
        if (!($insertionAnswer->getStatusCode() == 200 && ($chargeRC == "00" || $chargeRC == "08"))) {
            $this->getDeletePayMethodAction($idPayM, $person->getDocument());
            $view->setStatusCode($insertionAnswer->getStatusCode())->setData(array(
                'data' => $insertionAnswer->getContent(),
                'error' => array("Credit Card" => "No se pudo agregar el medio de Pago")));
            return $view;
        }
        $request->setMethod("DELETE");
        $request->request->add(array(
            "documentNumber" => $person->getDocument(),
            "chargeId" => $purchaseOrderId,
        ));
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:deleteReversePaymentMethod', array('_format' => 'json'));
        if ($insertionAnswer->getStatusCode() != 200) {
            $view->setStatusCode(201)->setData(array('response' => array("method-id" => $idPayM), 'error' => array("Credit Card" => "Se agrego la taerjeta de Credito, pero no se pudo reversar el cobro")));
        } else {
            $view->setStatusCode(201)->setData(array('response' => array("method-id" => $idPayM), "extra-data" => $insertionAnswer->getContent()));
        }
        return $view;
    }

    /**
     * Return the overall user list.
     *
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Return the overall User List",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @return View
     */
    public function getDeletePayMethodAction($idPayM, $idUser)
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($user->getPersonPerson()->getDocument() != $idUser) {
            $view = View::create();
            $view->setStatusCode(403);
            return $view;
        }
        $request = $this->container->get('request');
        $request->setMethod("DELETE");
        $request->request->add(array(
            "documentNumber" => $idUser,
            "paymentMethodId" => $idPayM,
        ));
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:deleteClientPaymentMethod', array('_format' => 'json'));
        $view = View::create();
        $view->setStatusCode($insertionAnswer->getStatusCode())->setData($insertionAnswer->getContent());

        return $view;
    }

    /**
     * Return the overall user list.
     *
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Return the overall User List",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @param $idPurchaseOrder
     * @return View
     */
    public function getCalculateChargeAction($idPurchaseOrder)
    {
        /** @var User $user */
        $user = $this->getUser();
        $person = $user->getPersonPerson();
        $em = $this->getDoctrine()->getManager();
        /** @var PurchaseOrders $purchaseOrder */
        $purchaseOrderId = $idPurchaseOrder;
        $purchaseOrder = $em->getRepository("RocketSellerTwoPickBundle:PurchaseOrders")->find($purchaseOrderId);
        /** @var Product $CT */
        $CT = $em->getRepository("RocketSellerTwoPickBundle:Product")->findOneBy(array("simpleName" => "CT"));
        $view = View::create();
        $descriptions = $purchaseOrder->getPurchaseOrderDescriptions();
        $chargeDescription = new PurchaseOrdersDescription();
        $chargeDescription->setProductProduct($CT);
        $chargeDescription->setDescription($CT->getDescription());
        $chargeDescription->setValue(0);
        /** @var PurchaseOrdersDescription $desc */
        foreach ($descriptions as $desc) {
            $product = $desc->getProductProduct();
            //TODO is the new novo the same stuff
        }
        $purchaseOrder->addPurchaseOrderDescription($chargeDescription);
        $em->persist($purchaseOrder);
        $em->flush();
        $view->setStatusCode(200);
        return $view;
    }

    /**
     * Return the overall user list.
     *
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Return the overall User List",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @param $idPurchaseOrder
     * @return View
     */
    public function getPayPurchaseOrderAction($idPurchaseOrder)
    {

        $em = $this->getDoctrine()->getManager();
        /** @var PurchaseOrders $purchaseOrder */
        $purchaseOrderId = $idPurchaseOrder;
        $purchaseOrder = $em->getRepository("RocketSellerTwoPickBundle:PurchaseOrders")->find($purchaseOrderId);
        $pOSRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus");
        /** @var User $user */
        $user = $purchaseOrder->getIdUser();
        $person = $user->getPersonPerson();
        $request = $this->container->get('request');
        $view = View::create();
        $descriptions = $purchaseOrder->getPurchaseOrderDescriptions();
        /** @var PurchaseOrdersDescription $description */
        foreach ($descriptions as $description) {
            $description->getValue();
            $product = $description->getProductProduct();
            $simpleName = $product->getSimpleName();
            if ($simpleName == "PN" || $simpleName == "PP") {
                $request->setMethod("POST");
                $request->request->add(array(
                    "documentNumber" => $person->getDocument(),
                    "MethodId" => $purchaseOrder->getPayMethodId(),
                    "totalAmount" => $purchaseOrder->getValue(),
                    "taxAmount" => 0,
                    "taxBase" => 0,
                    "commissionAmount" => 0,
                    "commissionBase" => 0,
                    "chargeMode" => 2,
                    "chargeId" => $purchaseOrder->getIdPurchaseOrders(),
                ));
                $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:postPaymentAproval', array('_format' => 'json'));
                $answer = json_decode($insertionAnswer->getContent(), true);
                $chargeRC = $answer["charge-rc"];
                if (!($insertionAnswer->getStatusCode() == 200 && ($chargeRC == "00" || $chargeRC == "08"))) {
                    $pOS = $pOSRepo->findOneBy(array("idNovoPay" => $chargeRC));
                    $purchaseOrder->setPurchaseOrdersStatus($pOS);
                    $em->persist($purchaseOrder);
                    $em->flush();
                    $view->setStatusCode(400)->setData(array('error' => array("Credit Card" => "No se pudo hacer el cobro a la tarjeta de Credito", "charge-rc" => $chargeRC)));
                    return $view;
                }
                $pOS = $pOSRepo->findOneBy(array("idNovoPay" => "00"));
                $purchaseOrder->setPurchaseOrdersStatus($pOS);
                $em->persist($purchaseOrder);
                $em->flush();
                /** @var PurchaseOrdersDescription $desc */
                foreach ($descriptions as $desc) {
                    $payRoll = $desc->getPayrollPayroll();
                    if ($desc->getProductProduct()->getSimpleName() == "PP") {
                        $type = 2;
                    } elseif ($desc->getProductProduct()->getSimpleName() == "PN") {
                        $type = 1;
                    } else {
                        continue;
                    }
                    $request->setMethod("POST");
                    $request->request->add(array(
                        "documentNumber" => $person->getDocument(),
                        "beneficiaryId" => $payRoll->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson()->getDocument(),
                        "beneficiaryAmount" => $desc->getValue(),
                        "dispersionType" => $type,
                        "chargeId" => $purchaseOrder->getIdPurchaseOrders(),
                    ));
                    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:postClientPayment', array('_format' => 'json'));
                    if ($insertionAnswer->getStatusCode() == 200) {
                        $transferId = json_decode($insertionAnswer->getContent(), true)["transfer-id"];
                        $pay = new Pay();
                        $pay->setUserIdUser($user);
                        $pay->setIdDispercionNovo($transferId);
                        $pay->setPurchaseOrdersDescription($desc);
                        $em->persist($pay);
                        $em->flush();
                    } else {
                        //TODO TIMEOUT
                        $view->setStatusCode($insertionAnswer->getStatusCode())->setData(array('error' => array('Dispersion' => 'se exedio el tiempo de espera pero el dinero se sacó')));
                        return $view;
                    }
                }

                $view->setStatusCode($insertionAnswer->getStatusCode())->setData($insertionAnswer->getContent());
                return $view;
            } else {
                $tax = $product->getTaxTax();
                $request->setMethod("POST");
                $request->request->add(array(
                    "documentNumber" => $person->getDocument(),
                    "MethodId" => $purchaseOrder->getPayMethodId(),
                    "totalAmount" => $purchaseOrder->getValue(),
                    "taxAmount" => $purchaseOrder->getValue() - ($purchaseOrder->getValue() / ($tax->getValue() + 1)),
                    "taxBase" => $purchaseOrder->getValue() / ($tax->getValue() + 1),
                    "commissionAmount" => 0,
                    "commissionBase" => 0,
                    "chargeMode" => 3,
                    "chargeId" => $purchaseOrder->getIdPurchaseOrders(),
                ));
                $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:postPaymentAproval', array('_format' => 'json'));
                $answer = json_decode($insertionAnswer->getContent(), true);
                $chargeRC = $answer["charge-rc"];
                if (!($insertionAnswer->getStatusCode() == 200 && ($chargeRC == "00" || $chargeRC == "08"))) {
                    $pOS = $pOSRepo->findOneBy(array("idNovoPay" => $chargeRC));
                    $purchaseOrder->setPurchaseOrdersStatus($pOS);
                    $em->persist($purchaseOrder);
                    $em->flush();
                    $view->setStatusCode(400)->setData(array('error' => array("Credit Card" => "No se pudo hacer el cobro a la tarjeta de Credito", "charge-rc" => $chargeRC)));
                    return $view;
                }
                $pOS = $pOSRepo->findOneBy(array("idNovoPay" => "00"));
                $purchaseOrder->setPurchaseOrdersStatus($pOS);
                $invoiceNumber = $this->getInvoiceNumber();
                $purchaseOrder->setInvoiceNumber($invoiceNumber);
                $em->persist($purchaseOrder);
                $pay = new Pay();
                $pay->setUserIdUser($user);
                $pay->setIdDispercionNovo(-1);
                $pay->setPurchaseOrdersDescription($description);
                $em->persist($pay);
                $em->flush();
                $view->setStatusCode($insertionAnswer->getStatusCode())->setData($insertionAnswer->getContent());
                return $view;
            }
        }
    }

    private function getInvoiceNumber()
    {
        $em = $this->getDoctrine()->getManager();
        $configRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Config");
        /** @var \RocketSeller\TwoPickBundle\Entity\Config $ufg */
        $ufg = $configRepo->findOneBy(array(
            'name' => 'ufg'
        ));

        $newInvoiceNumber = $ufg->getValue()+1;

        $ufg->setValue($newInvoiceNumber);
        $em->persist($ufg);
        $em->flush();

        return $newInvoiceNumber;
    }
}
