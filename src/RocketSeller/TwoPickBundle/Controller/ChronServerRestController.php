<?php

namespace RocketSeller\TwoPickBundle\Controller;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Product;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersStatus;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Date;

class ChronServerRestController extends FOSRestController
{

    public function __construct( $container=null)
    {
        if($container)
            $this->setContainer($container);
    }

    /**
     *  Charge Symplifica membership<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "retry pay pod",
     *   statusCodes = {
     *     200 = "OK"
     *   }
     * )
     *
     * @return View
     */
    public function putAutoChargeMembershipAction()
    {

        $users = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User")->findAll();
        $dateNow= new DateTime();
        $response= array();
        $productRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Product");
        /** @var Product $PS1 */
        $PS1 = $productRepo->findOneBy(array("simpleName" => "PS1"));
        /** @var Product $PS2 */
        $PS2 = $productRepo->findOneBy(array("simpleName" => "PS2"));
        /** @var Product $PS3 */
        $PS3 = $productRepo->findOneBy(array("simpleName" => "PS3"));
        /** @var User $user */
        foreach ($users as $user) {
            $realtoPay= new PurchaseOrders();
            $realtoPay->setIdUser($user);
            $total=0;
            $em=$this->getDoctrine()->getEntityManager();
            $isFreeMonths = $user->getIsFree();
            if($user->getLastPayDate()==null)
                continue;
            if ($isFreeMonths > 0) {
                $isFreeMonths -= 1;
            }
            $isFreeMonths += 1;
            $effectiveDate = new DateTime(date('Y-m-d', strtotime("+$isFreeMonths months", strtotime($user->getLastPayDate()->format("Y-m-1")))));
            if($effectiveDate<=$dateNow){
                $ps1Count=$ps2Count=$ps3Count=0;
                $atLeastOne=false;
                $employees = $user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
                /** @var EmployerHasEmployee $employee */
                foreach ($employees as $employee) {
                    if($employee->getState()>=3){
                        $atLeastOne=true;
                        $contracts = $employee->getContracts();
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
                }
                if($atLeastOne){
                    $dateToday=new DateTime();
                    $response[]=$user->getPersonPerson()->getFullName();
                    $symplificaPOD = new PurchaseOrdersDescription();
                    $symplificaPOD->setDescription("Subscripción Symplifica");
                    $symplificaPOD->setValue(round(($PS1->getPrice() * (1 + $PS1->getTaxTax()->getValue()) * $ps1Count) +
                        ($PS2->getPrice() * (1 + $PS2->getTaxTax()->getValue()) * $ps2Count) +
                        ($PS3->getPrice() * (1 + $PS3->getTaxTax()->getValue()) * $ps3Count), 0));
                    $realtoPay->addPurchaseOrderDescription($symplificaPOD);
                    $total += $symplificaPOD->getValue();
                    $symplificaPOD->setProductProduct($PS3);
                    $realtoPay->addPurchaseOrderDescription($symplificaPOD);
                    $total += $symplificaPOD->getValue();
                    $user->setLastPayDate(new DateTime($dateToday->format("Y-m-")."25"));
                    $user->setIsFree(0);
                    $em->persist($user);
                }

            }
            //now we search if there is any owe pod
            $this->checkPendingSubscription($realtoPay,$total);

        }
        $view = View::create();
        $view->setStatusCode(200);
        return $view->setData(array('response' => $response));

    }

    /**
     * @param PurchaseOrders $realtoPay
     * @param $total
     */
    private function checkPendingSubscription(PurchaseOrders &$realtoPay, &$total){
        $purchaseOrderRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrders");
        $purchaseOrderStatusRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PurchaseOrdersStatus");
        /** @var PurchaseOrdersStatus $pedingStatus */
        $pedingStatus = $purchaseOrderStatusRepo->findOneBy(array('idNovoPay'=>'P1'));
        $pendingStuff = $purchaseOrderRepo->findBy(array('purchaseOrdersStatus'=>$pedingStatus,'idUser'=>$realtoPay->getIdUser()));
        $pendingPODS = new ArrayCollection();
        if($pendingStuff!=null&&count($pendingStuff)>0){
            /** @var PurchaseOrders $po */
            foreach ($pendingStuff as $po) {
                $pods = $po->getPurchaseOrderDescriptions();
                /** @var PurchaseOrdersDescription $pod */
                foreach ($pods as $pod) {
                    if($pod->getProductProduct()->getSimpleName()=='PS1'||$pod->getProductProduct()->getSimpleName()=='PS2'||$pod->getProductProduct()->getSimpleName()=='PS3'){
                        $pendingPODS->add($pod);
                    }
                }
            }
            if($pendingPODS->count()>0){
                /** @var PurchaseOrdersDescription $pPod */
                foreach ($pendingPODS as $pPod) {
                    $realtoPay->addPurchaseOrderDescription($pPod);
                    $total+= $pPod->getValue();
                }
            }
        }
    }

    /**
     *  Send reminder tu upload pending documents<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Send reminder tu upload pending documents",
     *   statusCodes = {
     *     200 = "OK"
     *   }
     * )
     *
     * @return View
     */
    public function putPendingDocumentsRemainderAction() {

        $userRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
        $users = $userRepo->findAll();
        $resultUsers = array();
        foreach($users as $user) {
            //status 2 -> completed step 3
            if($user->getStatus() != 2) continue;

            if($user->getDevices()->count() == 0) continue;

            if($user->getRealProcedures() == null || $user->getRealProcedures()->count() == 0) {
                continue;
            }
            $procedure = $user->getRealProcedures()->first();
            $dateCreated = $procedure->getCreatedAt();
            $today = new DateTime();
            $difference = $today->diff($dateCreated);

            //day 1, 3 or 7 after finished step 3
            if($difference->d == 1 || $difference->d == 3 || $difference->d == 7) {
                $person = $user->getPersonPerson();
                $employer = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Employer")
                                ->findOneBy(array("personPerson" => $person));

                $arrEmployerHasEmployee = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:EmployerHasEmployee")
                                ->findBy(array("employerEmployer" => $employer));
                $hasDocumentsPending = false;
                foreach ($arrEmployerHasEmployee as $employerHasEmployee) {
                    //TODO cambiar cuando me diga andres que ya está la tabla de los document status
                    if($employerHasEmployee->getDocumentStatus() < 2) {
                        $hasDocumentsPending = true;
                        break;
                    }
                }

                if($hasDocumentsPending) {
                    $message = "¡Recuerda subir tus documentos faltantes!";
                    $title = "Symplifica";
                    $longMessage = "¡Recuerda subir tus documentos faltantes! Escanéalos desde la APP con pocos clics";

                    $request = new Request();
                    $request->setMethod("POST");
                    $request->request->add(array(
                        "idUser" => $user->getId(),
                        "title" => $title,
                        "message" => $message,
                        "longMessage" => $longMessage
                    ));
                    $pushNotificationService = $this->get('app.symplifica_push_notification');
                    $result = $pushNotificationService->postPushNotificationAction($request);
                    $collect = $result->getData();
                    $resultUsers[] = array('userId' => $user->getId(), 'result' => $collect);
                }
            }
        }
        $view = View::create();
        $view->setStatusCode(200);
        return $view->setData($resultUsers);
    }

    /**
     *  Send reminder of payments and register novelties<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Send reminder tu upload pending documents",
     *   statusCodes = {
     *     200 = "OK"
     *   }
     * )
     *
     * @return View
     */
    public function putPaymentRemainderAction($message, $longMessage, $period) {
        $payrollRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Payroll");
        $now = new \DateTime('now');
        $currMonth = $now->format('m');
        $currYear = $now->format('Y');
        $payrolls = $payrollRepo->findBy(array('period' => $period,
                                               'year' => $currYear,
                                               'month' => $currMonth,
                                               'paid' => 0));
        $people = array();
        foreach ($payrolls as $payroll) {
            if($payroll->getPaid() == 0) {
                $person = $payroll->getContractContract()->getEmployerHasEmployeeEmployerHasEmployee()
                                    ->getEmployerEmployer()->getPersonPerson();
                $people[] = $person;
            }
        }
        $userRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
        $users = $userRepo->findBy(array('personPerson' => $people));
        $resultUsers = array();
        foreach ($users as $user) {
            $title = "Symplifica";

            $request = new Request();
            $request->setMethod("POST");
            $request->request->add(array(
                "idUser" => $user->getId(),
                "title" => $title,
                "message" => $message,
                "longMessage" => $longMessage,
                "page" => 'PaymentsPage'
            ));
            $pushNotificationService = $this->get('app.symplifica_push_notification');
            $result = $pushNotificationService->postPushNotificationAction($request);
            $collect = $result->getData();
            $resultUsers[] = array('userId' => $user->getId(), 'result' => $collect);
        }

        $view = View::create();
        $view->setStatusCode(200);
        return $view->setData($resultUsers);
    }

}
