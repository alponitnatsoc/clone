<?php

namespace RocketSeller\TwoPickBundle\Controller;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use RocketSeller\TwoPickBundle\Entity\Config;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\Payroll;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
use RocketSeller\TwoPickBundle\Entity\Product;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\PayMethod;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Traits\PayrollMethodsTrait;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RequestParam;

class PayrollRestSecuredController extends FOSRestController
{

    use PayrollMethodsTrait;


    /**
     * Return the PODS and POS of the requested user
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
     * @param $idUser
     * @return View
     */
    public function getPayAction($idUser)
    {
        $userRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
        /** @var User $user */
        $user = $userRepo->find($idUser);
        $view = View::create();

        if ($user != null) {
            //obtener la informacion de la nomina actial
            $pods = $this->getInfoPayroll($user->getPersonPerson()->getEmployer());
            //obtener los meses de mora
            /** @var User $user */
            $purchaseOrders = $user->getPurchaseOrders();
            $owePurchaseOrders = new ArrayCollection();
            /** @var PurchaseOrders $po */
            foreach ($purchaseOrders as $po) {
                if ($po->getPurchaseOrdersStatus()->getIdNovoPay() == 'P1' && $po->getPurchaseOrderDescriptions()->count() > 0) {
                    $owePurchaseOrders->add($po);
                }
            }

            $ehes = $user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
            $flagAtLeastOne = "false";
            /** @var EmployerHasEmployee $ehe */
            foreach ($ehes as $ehe) {
                if ($ehe->getState() >= 4) {
                    $flagAtLeastOne = "true";
                    break;
                }
            }
            $total = 0;
            if($pods!=null){
                /** @var PurchaseOrdersDescription $pod */
                foreach ($pods as $pod) {
                    $total += $pod->getValue();
                }
                if ($total == 0) {
                    $pods = null;
                }

            }

            return $view->setStatusCode(200)->setData(array(
                'dataNomina' => $pods,
                'debt' => $owePurchaseOrders,
                'flagAtLeastOne' => $flagAtLeastOne
            ));
        }
        return $view->setStatusCode(404);
    }


    /**
     * Return the PODS and POS of the requested user
     *
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Return the overall User List",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     403 = "Returned when users not match",
     *     400 = "Returned when the required PODS is empty"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher
     * @RequestParam(name="idUser", nullable=false, strict=true, description="user Id.")
     * @RequestParam(array=true, name="podsToPay", nullable=true, strict=true, description="Purchase order descriptions ids to be paid ")
     * @RequestParam(array=true, name="podsPaid", nullable=true, strict=true, description="Purchase order descriptions ids already paid ")
     * @return View
     *
     */
    public function postCalculatePayAction(ParamFetcher $paramFetcher)
    {
        $idUser = $paramFetcher->get("idUser");
        $view = View::create();
        $userRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
        /** @var User $user */
        $user = $userRepo->find($idUser);
        if ($this->getUser() == null || $user == null || $user->getId() != $this->getUser()->getId()) {
            //return $view->setStatusCode(403);
        }
        $userPerson = $user->getPersonPerson();
        $payrollToPay = $paramFetcher->get("podsToPay");
        $payrollsPaid = $paramFetcher->get("podsPaid");
        $podRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersDescription");
        if (count($payrollToPay) == 0 && count($payrollsPaid) == 0) {
            return $view->setStatusCode(400);
        }
        $total = $totalPayroll = 0;
        $poS = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus");
        $paidStatus = $poS->findOneBy(array('idNovoPay' => '00'));
        $paidPO = new PurchaseOrders();
        $paidPO->setPurchaseOrdersStatus($paidStatus);
        $paidValue = 0;

        $numberOfPNTrans = 0;
        $numberOfTrans = 0;
        $willPayPN = false;
        foreach ($payrollsPaid as $key => $value) {
            /** @var PurchaseOrdersDescription $tempPOD */
            $tempPOD = $podRepo->find($value);
            $paidPO->addPurchaseOrderDescription($tempPOD);
            $paidValue += $tempPOD->getValue();
        }
        //setting the data of the already paid selected items
        $em = $this->getDoctrine()->getManager();
        $pods = $paidPO->getPurchaseOrderDescriptions();
        $pendingStatus = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findOneBy(array('idNovoPay' => 'P1'));
        $documentTypeRepo = $em->getRepository('RocketSellerTwoPickBundle:DocumentType');
        $pilaOwePo=new PurchaseOrders();
        $pendingStatus = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findOneBy(array('idNovoPay' => 'P1'));
        $pilaOwePo->setPurchaseOrdersStatus($pendingStatus);
        $pilaOwePo->setIdUser($user);
        if ($pods->count() > 0) {
            /** @var UtilsController $utils */
            $utils = $this->get('app.symplifica_utils');
            /** @var PurchaseOrdersDescription $pod */
            foreach ($pods as $pod) {
                $pod->setPurchaseOrdersStatus($paidStatus);
                if ($pod->getPayrollPayroll() != null) {
                    $actualPayroll = $pod->getPayrollPayroll();
                    //check if the actual item corresponds to the actual payroll
                    //create next payroll
                    $newPayroll = new Payroll();
                    $nowDate = new DateTime();
                    $nowDate = new DateTime(date('Y-m-d', strtotime("+1 months", strtotime($nowDate->format("Y-m-") . "1"))));
                    //here i create the comprobante
                    $person = $pod->getPayrollPayroll()->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getPersonPerson();
                    $employeePerson=$actualPayroll->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson();

                    $documentType = 'Comprobante';
                    $msj = "Subir comprobante de " . $utils->mb_capitalize(explode(" ", $employeePerson->getNames())[0] . " " . $employeePerson->getLastName1()) . " " . $utils->period_number_to_name($actualPayroll->getPeriod()) . " " . $utils->month_number_to_name($actualPayroll->getMonth());
                    $dUrl = $this->generateUrl("download_documents", array('id' => $actualPayroll->getIdPayroll(), 'ref' => "comprobante", 'type' => 'pdf'));
                    $dAction = "Bajar";
                    $action = "Subir";

                    $documentType = $documentTypeRepo->findByName($documentType)[0];

                    //aqui se envía el id del payroll en vez del de la persona
                    $url = $this->generateUrl("documentos_employee", array('id' => $actualPayroll->getIdPayroll(), 'idDocumentType' => $documentType->getIdDocumentType()));
                    //$url = $this->generateUrl("api_public_post_doc_from");

                    $notification = new Notification();
                    $notification->setPersonPerson($person);
                    $notification->setStatus(1);
                    $notification->setDocumentTypeDocumentType($documentType);
                    $notification->setType('alert');
                    $notification->setDescription($msj);
                    $notification->setRelatedLink($url);
                    $notification->setAccion($action);
                    $notification->setDownloadAction($dAction);
                    $notification->setDownloadLink($dUrl);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($notification);
                    if ($actualPayroll->getIdPayroll() == $actualPayroll->getContractContract()->getActivePayroll()->getIdPayroll()) {
                        //to fix the Pila Pod of disappearing, we pass it to de owe PO if the pila is not getting paid
                        $asociatedPila = $actualPayroll->getPila();
                        if($asociatedPila->getPurchaseOrders()==null||$asociatedPila->getPurchaseOrders()->getPurchaseOrdersStatus()->getIdNovoPay()!="S2"){
                            $asociatedPila->setPurchaseOrdersStatus($pendingStatus);
                            $pilaOwePo->addPurchaseOrderDescription($asociatedPila);
                        }
                        $nowPeriod = $actualPayroll->getPeriod();
                        if ($actualPayroll->getContractContract()->getFrequencyFrequency()->getPayrollCode() == "Q" && $nowPeriod == 4) {
                            $newPayroll->setPeriod(2);
                        } else {
                            $newPayroll->setPeriod(4);
                        }
                        $newPayroll->setMonth($nowDate->format("m"));
                        $newPayroll->setYear($nowDate->format("Y"));
                        $newPayroll->setContractContract($actualPayroll->getContractContract());
                        $actualPayroll->getContractContract()->setActivePayroll($newPayroll);
                        $actualPayroll->setPaid(1);
                        $em->persist($newPayroll);
                        $em->persist($actualPayroll->getContractContract());
                        $em->flush();
                    }
                }
            }

            $paidPO->setValue($paidValue);
            $paidPO->setDatePaid(new \DateTime());
            $paidPO->setName("El usuario aceptó haber pagado los siguientes items");
            $user->addPurchaseOrder($paidPO);
            if($pilaOwePo->getPurchaseOrderDescriptions()->count()>0){
                $pilaOwePo->setIdUser($user);
            }
            $em->persist($user);
            $em->flush();
        }

        $realtoPay = new PurchaseOrders();
        $productRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Product");
        foreach ($payrollToPay as $key => $value) {

            /** @var PurchaseOrdersDescription $tempPOD */
            $tempPOD = $podRepo->find($value);

            if ($tempPOD == null) {
                //por seguridad en caso de que no exista el POD
                return $view->setStatusCode(403)->setData(array('error' => 'no exite pod'));
            }
            if ($tempPOD->getPayrollPayroll() == null) {
                $person = $tempPOD->getPayrollsPila()->get(0)->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getPersonPerson();
                $flagFrequency = false;
                $flagNomi=false;
                $numberOfTrans++;
            } else {
                $person = $tempPOD->getPayrollPayroll()->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getPersonPerson();
                if ($tempPOD->getPayrollPayroll()->getContractContract()->getPayMethodPayMethod()->getPayTypePayType()->getPayrollCode() == "EFE"){
                    $flagFrequency = true;
                    $flagNomi=false;
                }
                else{
                    $flagNomi=true;
                    $flagFrequency = false;
                    $numberOfTrans++;
                }
            }
            if ($person->getIdPerson() != $userPerson->getIdPerson()) {
                //Por seguridad se verifica que los PODS pertenezcan al usuario loggeado
                return $view->setStatusCode(403)->setData(array('error' => 'no pasó seguridad pod'));
            }
            if (!$flagFrequency) {
                if($flagNomi){
                    $totalPayroll += $tempPOD->getValue();
                }
                $total += $tempPOD->getValue();
            }
            $realtoPay->addPurchaseOrderDescription($tempPOD);

        }
        if ($realtoPay->getPurchaseOrderDescriptions()->count() > 0) {
            // add the 4*1000
            /** @var Product $productCT */
            $productCPM = $productRepo->findOneBy(array("simpleName" => "CPM"));
            $fourX1000Cost=round($total*$productCPM->getPrice(), 0, PHP_ROUND_HALF_UP);
            //this is for the payroll to be exclusive with the 4X100 tax, and not include the pila cost
            //$fourX1000Cost=$totalPayroll*$productCPM->getPrice();
            $fourx1000POD = new PurchaseOrdersDescription();
            $fourx1000POD->setDescription("Cuatro por Mil");
            $fourx1000POD->setValue($fourX1000Cost);
            $realtoPay->addPurchaseOrderDescription($fourx1000POD);

            $total += $fourx1000POD->getValue();


            //add transaction cost
            $transactionCost = 0;
            /** @var Product $productCT */
            $productCT = $productRepo->findOneBy(array("simpleName" => "CT"));

            $numberOfPNTrans=$numberOfTrans;
            $transactionCost =  ceil(($productCT->getPrice()+($productCT->getPrice()*$productCT->getTaxTax()->getValue())))*$numberOfPNTrans;


            $transactionPOD = new PurchaseOrdersDescription();
            $transactionPOD->setDescription("Costo transaccional");
            $transactionPOD->setValue($transactionCost);
            $realtoPay->addPurchaseOrderDescription($transactionPOD);
            $total += $transactionPOD->getValue();


            $dateToday = new DateTime();
            $effectiveDate = $user->getLastPayDate();
            $isFreeMonths = $user->getIsFree();
            if ($isFreeMonths > 0) {
                $isFreeMonths -= 1;
            }
            $isFreeMonths += 1;
            $effectiveDate = new DateTime(date('Y-m-d', strtotime("+$isFreeMonths months", strtotime($effectiveDate->format("Y-m-") . "1"))));
            $effectiveDate->setDate($effectiveDate->format("Y"), $effectiveDate->format("m"), 25);

            if ($dateToday->format("d") >= 16 && $dateToday->format("m") == $effectiveDate->format("m") && $dateToday->format("Y") == $effectiveDate->format("Y")) {
                //this means that the user has to pay the symplifica fee this month
                $symplificaPOD = new PurchaseOrdersDescription();
                $symplificaPOD->setDescription("Subscripción Symplifica");
                $ehes = $user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
                /** @var Product $PS1 */
                $PS1 = $productRepo->findOneBy(array("simpleName" => "PS1"));
                /** @var Product $PS2 */
                $PS2 = $productRepo->findOneBy(array("simpleName" => "PS2"));
                /** @var Product $PS3 */
                $PS3 = $productRepo->findOneBy(array("simpleName" => "PS3"));
                $ps1Count = 0;
                $ps2Count = 0;
                $ps3Count = 0;
                /** @var EmployerHasEmployee $ehe */
                foreach ($ehes as $ehe) {
                    if ($ehe->getState() < 3) {
                        continue;
                    }
                    $contracts = $ehe->getContracts();
                    $actualContract = null;
                    /** @var Contract $contract */
                    foreach ($contracts as $contract) {
                        if ($contract->getState() == 1) {
                            $actualContract = $contract;
                            break;
                        }
                    }
                    if ($actualContract == null) {
                        continue;
                    }
                    $actualDays = $actualContract->getWorkableDaysMonth();
                    if ($actualDays < 10) {
                        $ps1Count++;
                    } elseif ($actualDays <= 19) {
                        $ps2Count++;
                    } else {
                        $ps3Count++;
                    }

                }
                $symplificaPOD->setValue(round(($PS1->getPrice() * (1 + $PS1->getTaxTax()->getValue()) * $ps1Count) +
                    ($PS2->getPrice() * (1 + $PS2->getTaxTax()->getValue()) * $ps2Count) +
                    ($PS3->getPrice() * (1 + $PS3->getTaxTax()->getValue()) * $ps3Count), 0));
                $realtoPay->addPurchaseOrderDescription($symplificaPOD);
                $total += $symplificaPOD->getValue();

            }
        }
        $clientListPaymentmethods = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:getClientListPaymentMethods', array('idUser' => $user->getId()), array('_format' => 'json'));
        $responsePaymentsMethods = json_decode($clientListPaymentmethods->getContent(), true);
        $realtoPay->setValue($total);
        return $view->setStatusCode(200)->setData(array(
            'toPay' => $realtoPay,
            'paid' => $paidPO,
            'payMethods' => $responsePaymentsMethods["payment-methods"]
        ));

    }


    /**
     * Confirms the paymet of podsToPay with specific paymentMethod
     *
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Confirms the paymet of podsToPay with specific paymentMethod",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     403 = "Returned when users not match",
     *     400 = "Returned when the required PODS is empty"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher
     * @RequestParam(name="idUser", nullable=false, strict=true, description="user Id.")
     * @RequestParam(name="paymentMethod", nullable=false, strict=true, description="The Pay method ID")
     * @RequestParam(array=true, name="podsToPay", nullable=false, strict=true, description="Purchase order descriptions ids to be paid ")
     * @return View
     *
     */
    public function postConfirmAction(ParamFetcher $paramFetcher)
    {
        $userRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
        $view = View::create();
        /* @var $user User */
        $user = $userRepo->find($paramFetcher->get("idUser"));
        $userPerson = $user->getPersonPerson();
        $payrollToPay = $paramFetcher->get("podsToPay");
        $podRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersDescription");
        if (count($payrollToPay) == 0) {
            //por seguridad se verifica que exista por lo menos un item a pagar
            return $this->redirectToRoute("payroll");
        }
        $total = 0;
        $totalPayroll=0;
        $poS = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus");
        $paidStatus = $poS->findOneBy(array('idNovoPay' => '00'));
        $realtoPay = new PurchaseOrders();

        $valueToGet4xMilFrom = 0;
        $numberOfPNTrans = 0;
        $numberOfTrans=0;
        $willPayPN = false;

        $paymethodid = $paramFetcher->get("paymentMethod");
        $productRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Product");

        foreach ($payrollToPay as $key => $value) {

            /** @var PurchaseOrdersDescription $tempPOD */
            $tempPOD = $podRepo->find($value);

            if ($tempPOD == null) {
                //por seguridad en caso de que no exista el POD
                return $view->setStatusCode(403)->setData(array('error' => 'no exite pod'));
            }
            if ($tempPOD->getPayrollPayroll() == null) {
                $person = $tempPOD->getPayrollsPila()->get(0)->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getPersonPerson();
                $flagFrequency = false;
                $flagNomi=false;
                $numberOfTrans++;
            } else {
                $person = $tempPOD->getPayrollPayroll()->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getPersonPerson();
                if ($tempPOD->getPayrollPayroll()->getContractContract()->getPayMethodPayMethod()->getPayTypePayType()->getPayrollCode() == "EFE"){
                    $flagFrequency = true;
                    $flagNomi=false;
                }
                else{
                    $flagNomi=true;
                    $flagFrequency = false;
                    $numberOfTrans++;
                }
            }
            if ($person->getIdPerson() != $userPerson->getIdPerson()) {
                //Por seguridad se verifica que los PODS pertenezcan al usuario loggeado
                return $view->setStatusCode(403)->setData(array('error' => 'no pasó seguridad pod'));
            }
            if (!$flagFrequency) {
                if($flagNomi){
                    $totalPayroll += $tempPOD->getValue();
                }
                $total += $tempPOD->getValue();
            }
            $realtoPay->addPurchaseOrderDescription($tempPOD);

        }
        $em = $this->getDoctrine()->getManager();

        if ($realtoPay->getPurchaseOrderDescriptions()->count() > 0) {
            // add the 4*1000
            /** @var Product $productCT */
            $productCPM = $productRepo->findOneBy(array("simpleName" => "CPM"));
            $fourX1000Cost=round($total*$productCPM->getPrice(), 0, PHP_ROUND_HALF_UP);
            //this is for the payroll to be exclusive with the 4X100 tax, and not include the pila cost
            //$fourX1000Cost=$totalPayroll*$productCPM->getPrice();
            $fourx1000POD = new PurchaseOrdersDescription();
            $fourx1000POD->setDescription("Cuatro por Mil");
            $fourx1000POD->setValue($fourX1000Cost);
            $realtoPay->addPurchaseOrderDescription($fourx1000POD);

            $total += $fourx1000POD->getValue();


            //add transaction cost
            $transactionCost = 0;
            /** @var Product $productCT */
            $productCT = $productRepo->findOneBy(array("simpleName" => "CT"));

            $numberOfPNTrans=$numberOfTrans;
            $transactionCost =  ceil(($productCT->getPrice()+($productCT->getPrice()*$productCT->getTaxTax()->getValue())))*$numberOfPNTrans;


            $transactionPOD = new PurchaseOrdersDescription();
            $transactionPOD->setDescription("Costo transaccional");
            $transactionPOD->setValue($transactionCost);
            $realtoPay->addPurchaseOrderDescription($transactionPOD);
            $total += $transactionPOD->getValue();


            $dateToday = new DateTime();
            $effectiveDate = $user->getLastPayDate();
            $isFreeMonths = $user->getIsFree();
            if ($isFreeMonths > 0) {
                $isFreeMonths -= 1;
            }
            $isFreeMonths += 1;
            $effectiveDate = new DateTime(date('Y-m-d', strtotime("+$isFreeMonths months", strtotime($effectiveDate->format("Y-m-") . "1"))));
            $effectiveDate->setDate($effectiveDate->format("Y"), $effectiveDate->format("m"), 25);

            if ($dateToday->format("d") >= 16 && $dateToday->format("m") == $effectiveDate->format("m") && $dateToday->format("Y") == $effectiveDate->format("Y")) {
                //this means that the user has to pay the symplifica fee this month
                $symplificaPOD = new PurchaseOrdersDescription();
                $symplificaPOD->setDescription("Subscripción Symplifica");
                $ehes = $user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
                /** @var Product $PS1 */
                $PS1 = $productRepo->findOneBy(array("simpleName" => "PS1"));
                /** @var Product $PS2 */
                $PS2 = $productRepo->findOneBy(array("simpleName" => "PS2"));
                /** @var Product $PS3 */
                $PS3 = $productRepo->findOneBy(array("simpleName" => "PS3"));
                $ps1Count = 0;
                $ps2Count = 0;
                $ps3Count = 0;
                /** @var EmployerHasEmployee $ehe */
                foreach ($ehes as $ehe) {
                    if ($ehe->getState() < 3) {
                        continue;
                    }
                    $contracts = $ehe->getContracts();
                    $actualContract = null;
                    /** @var Contract $contract */
                    foreach ($contracts as $contract) {
                        if ($contract->getState() == 1) {
                            $actualContract = $contract;
                            break;
                        }
                    }
                    if ($actualContract == null) {
                        continue;
                    }
                    $actualDays = $actualContract->getWorkableDaysMonth();
                    if ($actualDays < 10) {
                        $ps1Count++;
                    } elseif ($actualDays <= 19) {
                        $ps2Count++;
                    } else {
                        $ps3Count++;
                    }

                }
                $symplificaPOD->setValue(round(($PS1->getPrice() * (1 + $PS1->getTaxTax()->getValue()) * $ps1Count) +
                    ($PS2->getPrice() * (1 + $PS2->getTaxTax()->getValue()) * $ps2Count) +
                    ($PS3->getPrice() * (1 + $PS3->getTaxTax()->getValue()) * $ps3Count), 0));
                $realtoPay->addPurchaseOrderDescription($symplificaPOD);
                $total += $symplificaPOD->getValue();

            }
        }
        $realtoPay->setIdUser($user);
        $realtoPay->setDatePaid(new DateTime());
        $realtoPay->setValue($total);
        $realtoPay->setPayMethodId($paymethodid);
        $em->persist($realtoPay);
        $em->flush();
        $response = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:getPayPurchaseOrder', array(
            "idPurchaseOrder" => $realtoPay->getIdPurchaseOrders()), array('_format' => 'json'));
        if ($response->getStatusCode() == 200) {

            $pods = $realtoPay->getPurchaseOrderDescriptions();
            /** @var UtilsController $utils */
            $utils = $this->get('app.symplifica_utils');
            $documentTypeRepo = $em->getRepository('RocketSellerTwoPickBundle:DocumentType');
            $pilaOwePo=new PurchaseOrders();
            $pendingStatus = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findOneBy(array('idNovoPay' => 'P1'));
            $pilaOwePo->setPurchaseOrdersStatus($pendingStatus);
            $pilaOwePo->setIdUser($user);
            //set all to paid
            $procesingStatus = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findOneBy(array('idNovoPay' => 'S2'));
            $realtoPay->setPurchaseOrdersStatus($procesingStatus);
            /** @var PurchaseOrdersDescription $pod */
            foreach ($pods as $pod) {
                $pod->setPurchaseOrdersStatus($procesingStatus);
                if ($pod->getPayrollPayroll() != null) {
                    $actualPayroll = $pod->getPayrollPayroll();
                    //check if the actual item corresponds to the actual payroll
                    //create next payroll
                    $newPayroll = new Payroll();
                    $nowDate = new DateTime();
                    $nowPeriod = $actualPayroll->getPeriod();
                    if ($actualPayroll->getContractContract()->getFrequencyFrequency()->getPayrollCode() == "Q" && $nowPeriod == 4) {
                        $monthsToAdd = 0;
                    } else {
                        $monthsToAdd = 1;
                    }
                    $nowDate = new DateTime(date('Y-m-d', strtotime("+$monthsToAdd months", strtotime($nowDate->format("Y-m-") . "1"))));
                    //here i create the comprobante
                    $employeePerson=$actualPayroll->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson();
                    $documentType = 'Comprobante';
                    $msj = "Subir comprobante de " . $utils->mb_capitalize(explode(" ", $employeePerson->getNames())[0] . " " . $employeePerson->getLastName1());
                    $dUrl = $this->generateUrl("download_documents", array('id' => $actualPayroll->getIdPayroll(), 'ref' => "comprobante", 'type' => 'pdf'));
                    $dAction = "Bajar";
                    $action = "Subir";

                    $documentType = $documentTypeRepo->findByName($documentType)[0];
                    $url = $this->generateUrl("documentos_employee", array('id' => $person->getIdPerson(), 'idDocumentType' => $documentType->getIdDocumentType()));
                    //$url = $this->generateUrl("api_public_post_doc_from");

                    $notification = new Notification();
                    $notification->setPersonPerson($person);
                    $notification->setStatus(1);
                    $notification->setDocumentTypeDocumentType($documentType);
                    $notification->setType('alert');
                    $notification->setDescription($msj);
                    $notification->setRelatedLink($url);
                    $notification->setAccion($action);
                    $notification->setDownloadAction($dAction);
                    $notification->setDownloadLink($dUrl);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($notification);

                    if ($actualPayroll->getIdPayroll() == $actualPayroll->getContractContract()->getActivePayroll()->getIdPayroll()) {

                        //to fix the Pila Pod of disappearing, we pass it to de owe PO if the pila is not getting paid
                        $asociatedPila = $actualPayroll->getPila();
                        if($asociatedPila->getPurchaseOrders()==null|$asociatedPila->getPurchaseOrders()->getPurchaseOrdersStatus()->getIdNovoPay()!="S2"){
                            $asociatedPila->setPurchaseOrdersStatus($pendingStatus);
                            $pilaOwePo->addPurchaseOrderDescription($asociatedPila);
                        }
                        $nowPeriod = $actualPayroll->getPeriod();
                        if ($actualPayroll->getContractContract()->getFrequencyFrequency()->getPayrollCode() == "Q" && $nowPeriod == 4) {
                            $newPayroll->setPeriod(2);
                        } else {
                            $newPayroll->setPeriod(4);
                        }
                        $newPayroll->setMonth($nowDate->format("m"));
                        $newPayroll->setYear($nowDate->format("Y"));
                        $newPayroll->setContractContract($actualPayroll->getContractContract());
                        $actualPayroll->getContractContract()->setActivePayroll($newPayroll);
                        $actualPayroll->setPaid(1);
                        $em->persist($newPayroll);
                        $em->persist($actualPayroll->getContractContract());
                        $em->flush();
                    }
                }
            }

            if($pilaOwePo->getPurchaseOrderDescriptions()->count()>0){
                $pilaOwePo->setIdUser($user);
                $em->persist($user);
            }
            $em->flush();
            /** @var Config $ucfg */
            $ucfg = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Config")->findOneBy(array('name' => 'ufg'));
            $invoiceNumber = intval($ucfg->getValue()) + 1;
            $ucfg->setValue($invoiceNumber);
            $realtoPay->setInvoiceNumber($invoiceNumber);
            $em->persist($ucfg);
            $em->persist($realtoPay);
            $em->flush();

            return $view->setStatusCode(200)->setData(array('result' => "s", 'idPO' => $realtoPay->getIdPurchaseOrders()));

        } else {
            //reverse the Purchase Order
            $procesingStatus = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findOneBy(array('idNovoPay' => 'P1'));
            $realtoPay->setPurchaseOrdersStatus($procesingStatus);
            $pods = $realtoPay->getPurchaseOrderDescriptions();
            /** @var PurchaseOrdersDescription $pod */
            foreach ($pods as $pod) {
                if (!($pod->getProductProduct()->getSimpleName() == "PP" || $pod->getProductProduct()->getSimpleName() == "PN")) {
                    $em->remove($pod);
                    $em->flush();
                }
                $pod->setPurchaseOrdersStatus($procesingStatus);
            }
            $em->persist($realtoPay);
            $em->flush();
            return $view->setStatusCode(200)->setData(array('result' => "e"));
        }

    }


}
