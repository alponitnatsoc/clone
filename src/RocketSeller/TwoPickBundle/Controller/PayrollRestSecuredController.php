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
    public function getPayAction($idUser){
        $userRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
        /** @var User $user */
        $user=$userRepo->find($idUser);
        $view=View::create();

        if($user!=null){
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

            $ehes=$user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
            $flagAtLeastOne="false";
            /** @var EmployerHasEmployee $ehe */
            foreach ($ehes as $ehe) {
                if($ehe->getState()>=4){
                    $flagAtLeastOne="true";
                    break;
                }
            }

            return $view->setStatusCode(200)->setData(array(
                'dataNomina' => $pods,
                'debt' => $owePurchaseOrders,
                'flagAtLeastOne'=>$flagAtLeastOne
            ));
        }
        return $view->setStatusCode(404);
    }


    public function calculateAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        if ($request->isMethod('POST')) {
            /* @var $user User */
            $user = $this->getUser();
            $userPerson = $user->getPersonPerson();
            $payrollToPay = $request->request->all();
            $podRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersDescription");
            if (count($payrollToPay) == 0) {
                //por seguridad se verifica que exista por lo menos un item a pagar
                return $this->redirectToRoute("payroll");
            }
            $total=0;
            $poS=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus");
            $paidStatus=$poS->findOneBy(array('idNovoPay'=>'00'));
            $paidPO=new PurchaseOrders();
            $realtoPay=new PurchaseOrders();

            $paidValue=0;

            $valueToGet4xMilFrom = 0;
            $numberOfPNTrans = 0;
            $willPayPN = false;

            foreach ($payrollToPay as $key=>$value ) {

                /** @var PurchaseOrdersDescription $tempPOD */
                $tempPOD=$podRepo->find($key);

                if($tempPOD==null){
                    //por seguridad en caso de que no exista el POD
                    return $this->redirectToRoute("payroll");
                }
                if($tempPOD->getPayrollPayroll()==null){
                    $person=$tempPOD->getPayrollsPila()->get(0)->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getPersonPerson();
                    $flagFrequency=false;
                }else{
                    $person=$tempPOD->getPayrollPayroll()->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getPersonPerson();
                    if($tempPOD->getPayrollPayroll()->getContractContract()->getPayMethodPayMethod()->getPayTypePayType()->getPayrollCode()=="EFE")
                        $flagFrequency=true;
                    else
                        $flagFrequency=false;
                }
                if ($person->getIdPerson() != $userPerson->getIdPerson()) {
                    //Por seguridad se verifica que los PODS pertenezcan al usuario loggeado
                    return $this->redirectToRoute("payroll");
                }
                $exploded = explode("-", $value);
                if ($exploded[0] == 'p') {
                    if(!$flagFrequency){
                        $total += $tempPOD->getValue();
                        if($tempPOD->getProductProduct()->getSimpleName() == "PN"){
                            $willPayPN = true;
                            $valueToGet4xMilFrom = $valueToGet4xMilFrom + $tempPOD->getValue();
                            $numberOfPNTrans = $numberOfPNTrans + 1;
                        }
                    }
                    if($tempPOD->getProductProduct()->getSimpleName() == "PP"){
                        $valueToGet4xMilFrom = $valueToGet4xMilFrom + $tempPOD->getValue();
                    }
                    $realtoPay->addPurchaseOrderDescription($tempPOD);

                }else{
                    $tempPOD->setPurchaseOrdersStatus($paidStatus);
                    $paidPO->addPurchaseOrderDescription($tempPOD);
                    $paidValue += $tempPOD->getValue();
                }

            }
            //setting the data of the already paid selected items
            $em = $this->getDoctrine()->getManager();

            if ($paidPO->getPurchaseOrderDescriptions()->count() > 0) {
                $paidPO->setValue($paidValue);
                $paidPO->setPurchaseOrdersStatus($paidStatus);
                $paidPO->setDatePaid(new \DateTime());
                $paidPO->setIdUser($user);
                $paidPO->setName("El usuario aceptó haber pagado los siguientes items");
                $em->persist($paidPO);
                $em->flush();
            }
            if($realtoPay->getPurchaseOrderDescriptions()->count()>0){
                //add transaction cost
                $transactionCost = 0;

                // Se agrega el cobro único por pila si no hay pagos de nomina
                if($willPayPN == false){
                  $transactionCost = $transactionCost + 5500;
                }

                // Se agrega el cobro del 4x1000
                $transactionCost = $transactionCost + ( ($valueToGet4xMilFrom / 1000) * 4);
                // Se agrega el cobro por transaccional

                if($numberOfPNTrans == 1){
                  $transactionCost = $transactionCost + 5500;
                }
                elseif ($numberOfPNTrans >= 2 && $numberOfPNTrans <= 5) {
                  $transactionCost = $transactionCost + (5500 * $numberOfPNTrans);
                }
                elseif ($numberOfPNTrans > 5) {
                  $transactionCost = $numberOfPNTrans + (5500 * $numberOfPNTrans);
                }

                $transactionPOD=new PurchaseOrdersDescription();
                $transactionPOD->setDescription("Costo transaccional");
                $transactionPOD->setValue(round($transactionCost,0));
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
                    $productRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Product");
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
            return $this->render('RocketSellerTwoPickBundle:Payroll:calculate.html.twig', array(
                'toPay' => $realtoPay,
                'paid' => $paidPO,
                'payMethods' => $responsePaymentsMethods["payment-methods"]
            ));


            /*
            $payrollToPay = $request->request->get('payrollToPay');
            $data = $this->getInfoPayroll($this->getUser()->getPersonPerson()->getEmployer(), $payrollToPay);
            if ($data) {
                $documentNumber = $this->getUser()->getPersonPerson()->getDocument();
                $clientListPaymentmethods = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:getClientListPaymentMethods', array('idUser' => $user->getId()), array('_format' => 'json'));
                dump($clientListPaymentmethods);
                $responcePaymentsMethods = json_decode($clientListPaymentmethods->getContent(), true);


            } else {
                return $this->redirectToRoute("payroll");
            }*/
        } else {
            return $this->redirectToRoute("payroll");
        }
    }

    public function confirmAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        if ($request->isMethod('POST')) {
            /* @var $user User */
            $user = $this->getUser();
            $userPerson = $user->getPersonPerson();
            $payrollToPay = $request->request->all();
            $podRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersDescription");
            if (count($payrollToPay) == 0) {
                //por seguridad se verifica que exista por lo menos un item a pagar
                return $this->redirectToRoute("payroll");
            }
            $total=0;
            $poS=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus");
            $paidStatus=$poS->findOneBy(array('idNovoPay'=>'00'));
            $paidPO=new PurchaseOrders();
            $realtoPay=new PurchaseOrders();
            $paidValue=0;

            $valueToGet4xMilFrom = 0;
            $numberOfPNTrans = 0;
            $willPayPN = false;

            $paymethodid=$payrollToPay["paymentMethod"];
            unset($payrollToPay["paymentMethod"]);
            foreach ($payrollToPay as $key => $value) {
                /** @var PurchaseOrdersDescription $tempPOD */
                $tempPOD = $podRepo->find($key);
                if ($tempPOD == null) {
                    //por seguridad en caso de que no exista el POD
                    return $this->redirectToRoute("payroll");
                }
                if ($tempPOD->getPayrollPayroll() == null) {
                    $person = $tempPOD->getPayrollsPila()->get(0)->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getPersonPerson();
                    $flagFrequency=false;
                } else {
                    $person = $tempPOD->getPayrollPayroll()->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()->getEmployerEmployer()->getPersonPerson();
                    if($tempPOD->getPayrollPayroll()->getContractContract()->getPayMethodPayMethod()->getPayTypePayType()->getPayrollCode()=="EFE")
                        $flagFrequency=true;
                    else
                        $flagFrequency=false;
                }
                if ($person->getIdPerson() != $userPerson->getIdPerson()) {
                    //Por seguridad se verifica que los PODS pertenezcan al usuario loggeado
                    return $this->redirectToRoute("payroll");
                }
                if(!$flagFrequency){
                    $total += $tempPOD->getValue();
                    if($tempPOD->getProductProduct()->getSimpleName() == "PN"){
                        $willPayPN = true;
                        $valueToGet4xMilFrom = $valueToGet4xMilFrom + $tempPOD->getValue();
                        $numberOfPNTrans = $numberOfPNTrans + 1;
                    }
                }
                if($tempPOD->getProductProduct()->getSimpleName() == "PP"){
                    $valueToGet4xMilFrom = $valueToGet4xMilFrom + $tempPOD->getValue();
                }
                $realtoPay->addPurchaseOrderDescription($tempPOD);
            }
            $em = $this->getDoctrine()->getManager();

            if ($realtoPay->getPurchaseOrderDescriptions()->count() > 0) {
                //add transaction cost
                $transactionCost = 0;

                // Se agrega el cobro único por pila si no hay pagos de nomina
                if($willPayPN == false){
                  $transactionCost = $transactionCost + 5500;
                }

                // Se agrega el cobro del 4x1000
                $transactionCost = $transactionCost + ( ($valueToGet4xMilFrom / 1000) * 4);
                // Se agrega el cobro por transaccional

                if($numberOfPNTrans == 1){
                  $transactionCost = $transactionCost + 5500;
                }
                elseif ($numberOfPNTrans >= 2 && $numberOfPNTrans <= 5) {
                  $transactionCost = $transactionCost + (5500 * $numberOfPNTrans);
                }
                elseif ($numberOfPNTrans > 5) {
                  $transactionCost = $numberOfPNTrans + (5500 * $numberOfPNTrans);
                }

                $transactionPOD=new PurchaseOrdersDescription();
                $productRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Product");
                /** @var Product $productTransaction */
                $productTransaction = $productRepo->findOneBy(array("simpleName" => "CT"));
                $transactionPOD->setDescription("Costo transaccional");
                $transactionPOD->setValue(round($transactionCost,0));
                $transactionPOD->setProductProduct($productTransaction);
                $realtoPay->addPurchaseOrderDescription($transactionPOD);
                $total += $transactionPOD->getValue();
                $dateToday = new DateTime();
                $effectiveDate = $user->getLastPayDate();
                $isFreeMonths = $user->getIsFree();
                if ($isFreeMonths > 0) {
                    $isFreeMonths -= 1;
                    $user->setIsFree($isFreeMonths);
                    $em->persist($user);
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
                    $symplificaPOD->setProductProduct($PS1);
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
                //set all to paid
                $procesingStatus = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus")->findOneBy(array('idNovoPay' => 'S2'));
                $realtoPay->setPurchaseOrdersStatus($procesingStatus);
                $pods = $realtoPay->getPurchaseOrderDescriptions();
                /** @var UtilsController $utils */
                $utils = $this->get('app.symplifica_utils');
                $documentTypeRepo = $em->getRepository('RocketSellerTwoPickBundle:DocumentType');
                /** @var PurchaseOrdersDescription $pod */
                foreach ($pods as $pod) {
                    $pod->setPurchaseOrdersStatus($procesingStatus);
                    if ($pod->getPayrollPayroll() != null) {
                        $actualPayroll = $pod->getPayrollPayroll();
                        if ($actualPayroll->getIdPayroll() == $actualPayroll->getContractContract()->getActivePayroll()->getIdPayroll()) {
                            //create next payroll
                            $newPayroll = new Payroll();
                            $nowDate = new DateTime();
                            $nowDate = new DateTime(date('Y-m-d', strtotime("+1 months", strtotime($nowDate->format("Y-m-") . "1"))));
                            //here i create the comprobante

                            $documentType = 'Comprobante';
                            $msj = "Subir comprobante de " . $utils->mb_capitalize(explode(" ", $person->getNames())[0] . " " . $person->getLastName1());
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
                $em->flush();
                /** @var Config $ucfg */
                $ucfg = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Config")->findOneBy(array('name' => 'ufg'));
                $invoiceNumber = intval($ucfg->getValue()) + 1;
                $ucfg->setValue($invoiceNumber);
                $realtoPay->setInvoiceNumber($invoiceNumber);
                $em->persist($ucfg);
                $em->persist($realtoPay);
                $em->flush();

                return $this->redirectToRoute("payroll_result", array('result' => "s", 'idPO' => $realtoPay->getIdPurchaseOrders()));

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
                return $this->redirectToRoute("payroll_result", array('result' => "e"));

            }

        } else {
            return $this->redirectToRoute("payroll");
        }
    }



}
