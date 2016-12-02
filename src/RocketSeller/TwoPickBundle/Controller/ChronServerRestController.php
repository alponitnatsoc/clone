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
    public function putPendingDocumentsReminderAction() {

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
                /** @var EmployerHasEmployee $employerHasEmployee */
                foreach ($arrEmployerHasEmployee as $employerHasEmployee) {
                    if($employerHasEmployee->getDateDocumentsUploaded() == null) {
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
     *  Send reminder tu upload contract<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Send reminder tu upload contract",
     *   statusCodes = {
     *     200 = "OK"
     *   }
     * )
     *
     * @return View
     */
    public function putUploadContractReminderAction() {

        $userRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
        $users = $userRepo->findAll();
        $resultUsers = array();
        foreach($users as $user) {
            //status 2 -> completed step 3
            if($user->getStatus() != 2) continue;

            if($user->getDevices()->count() == 0) continue;

            foreach ($user->getPersonPerson()->getEmployer()->getEmployerHasEmployees() as $eHE ){
                if($eHE->getState() < 4) continue;
                // dump($eHE);
                $dateRegisteredToSQL = $eHE->getDateRegisterToSQL();
                if($dateRegisteredToSQL == null) continue;
                if($eHE->getActiveContract()->getDocumentDocument() != null) continue;
                $today = new DateTime();
                $difference = $today->diff($dateRegisteredToSQL);

                //day 1, 7, 15 and 30 after added to sql
                if($difference->d == 1 || $difference->d == 7 || $difference->d == 15 || $difference->d == 30) {

                    $message = "¡Hola! No olvides subir tu contrato firmado";
                    $title = "Symplifica";
                    $longMessage = "¡Hola! No olvides subir tu contrato firmado y así formalizar por completo la relación laboral. Descárgalo ahora";

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
                    $resultUsers[] = array('userId' => $user->getId(), 'resultPush' => $collect);
                    break;
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
    public function putPaymentReminderAction($isLastPayDay, $period) {

        $now = new \DateTime('now');
        $currMonth = $now->format('m');
        $currYear = $now->format('Y');

        $userRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
        $users = $userRepo->findAll();
        $resultUsers = array();
        $message = "Hola, es tiempo de reportar novedades y pagar";
        $longMessage = "¡Hola! Es momento de reportar novedades y pagar la quincena de tu empleado. Da clic para entrar";
        if($period == 4) {
            $longMessage = "¡Hola! Es momento de reportar novedades de este periodo y realizar los pagos de seguridad social y sueldo. Da clic para entrar";
        }
        if($isLastPayDay) {
            $message = "¡Último día! Realiza el pago a tu empleado";
            $longMessage = "¡Importante! Hoy es el último día para realizar el pago a tu empleado y reportar las novedades de este período. Entra ya haciendo clic";

        }
        foreach ($users as $user) {
            if($user->getStatus() < 2) continue;

            /** @var EmployerHasEmployee $eHE */
            foreach ($user->getPersonPerson()->getEmployer()->getEmployerHasEmployees() as $eHE ){
                if($eHE->getState() < 4) continue;
                $contract = $eHE->getActiveContract();
                if($contract) {
                    $activPayroll = $contract->getActivePayroll();
                    if($activPayroll && $activPayroll->getPeriod() == $period &&
                       $activPayroll->getYear() == $currYear &&
                       $activPayroll->getMonth() == $currMonth) {
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
                        $isEfectivo = false;
                        if($contract->getPayMethodPayMethod()->getPayTypePayType()->getSimpleName() == "EFE") {
                            $isEfectivo = true;
                        }
                        $smailer = $this->get('symplifica.mailer.twig_swift');
                        if ($period == 2) {
                            if($isLastPayDay) {
                                $send = $smailer->sendEmailByTypeMessage(array('emailType' => 'lastReminderPay',
                                    'toEmail' => $user->getEmail(),
                                    'userName' => $user->getPersonPerson()->getNames(),
                                    'days' => 2));
                            } else {
                                $send = $smailer->sendEmailByTypeMessage(array('emailType' => 'reminderPay',
                                    'toEmail' => $user->getEmail(),
                                    'userName' => $user->getPersonPerson()->getNames(),
                                    'isEfectivo' => $isEfectivo,
                                    'days' => 2));
                            }
                        } elseif ($period == 4) {
                            if($isLastPayDay) {
                                $send = $smailer->sendEmailByTypeMessage(array('emailType' => 'lastReminderPay',
                                    'toEmail' => $user->getEmail(),
                                    'userName' => $user->getPersonPerson()->getNames(),
                                    'days' => 3));
                            } else {
                                $send = $smailer->sendEmailByTypeMessage(array('emailType' => 'reminderPay',
                                    'toEmail' => $user->getEmail(),
                                    'userName' => $user->getPersonPerson()->getNames(),
                                    'isEfectivo' => $isEfectivo,
                                    'days' => 3));
                            }
                        }
                        $resultUsers[] = array('userId' => $user->getId(), 'resultPush' => $collect, 'resultMail' => $send);
                        break;
                    }
                }
            }
        }

        $view = View::create();
        $view->setStatusCode(200);
        return $view->setData($resultUsers);
    }

    /**
     *  Sends push notification saying that the affiliation is in progress<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Sends push notification saying that the affiliation is in progress",
     *   statusCodes = {
     *     200 = "OK"
     *   }
     * )
     *
     * @return View
     */
    public function putSocialSecurityAffiliationInProgressAction() {
        $userRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
        $users = $userRepo->findAll();
        $EmployerRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Employer");
        $resultUsers = array();
        foreach($users as $user) {
            $employer = $EmployerRepo->findOneBy(array("personPerson" => $user->getPersonPerson()));
            if(!$employer) continue;
            /** @var EmployerHasEmployee $eHE */
            foreach ( $employer->getEmployerHasEmployees() as $eHE) {

                $dateFilesUploaded = $eHE->getInfoValidatedAt();
                if(!$dateFilesUploaded) continue;
                $today = new DateTime();
                $utils = $this->get('app.symplifica_utils');
                $oneBusinessDayAfter = $utils->getWorkableDaysToDateAction($dateFilesUploaded->format("Y-m-d"), 1);
                //1 business day after
                if($oneBusinessDayAfter == $today->format("Y-m-d")) {

                    $message = "¡Estamos afiliando a tu empleada a S. social!";
                    $title = "Symplifica";
                    $longMessage = "¡Nos encontramos afiliando a tu empleada a seguridad social! Te informaremos pronto en cuento esté todo listo. Buen día";

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

                    break;
                }
            }
        }
        $view = View::create();
        $view->setStatusCode(200);
        return $view->setData($resultUsers);
    }

    /**
     * Reminder daviplata.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Send the reminder to create daviplata if not created.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when error"
     *   }
     * )
     *
     * @return View
     */
    public function putDaviplataReminderAction($period)
    {
        $resultUsers = array();
        $notifications = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository("RocketSellerTwoPickBundle:Notification")
            ->findBy(
                array(
                    "accion" => 'Crear Daviplata',
                    "status" => 1,
                )
            );
        if(!$notifications) {
            $view = View::create();
            $view->setStatusCode(200);
            return $view->setData($resultUsers);
        }

        $date = new \DateTime();
        if($period == 4) {
            /** @var Notification $notification */
            foreach ($notifications as $notification) {
                $person = $notification->getPersonPerson();
                /** @var User $user */
                $user = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:User")->findOneBy(array('personPerson'=>$person));
                if($user) {
                    $pices = explode('/',$notification->getRelatedLink());
                    if($pices) {
                        $pMethod = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:PayMethod")->find($pices[2]);
                        if($pMethod) {
                            /** @var Contract $contract */
                            $contract=$this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:Contract")->findOneBy(array('payMethodPayMethod'=>$pMethod));
                            if($contract){
                                if($contract->getActivePayroll()->getMonth() == $date->format('m') and $contract->getActivePayroll()->getPeriod()==4) {
                                    echo 'email    ' . $user->getEmail() . '      ----   ';
                                    $smailer = $this->get('symplifica.mailer.twig_swift');
                                    $context = array(
                                        'emailType'=>'reminderDaviplata',
                                        'toEmail'=>$user->getEmail(),
                                        'userName'=>$user->getPersonPerson()->getFullName(),
                                        'employeeName'=>$contract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson()->getFullName()
                                    );
                                    $send = $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);

                                    $message = "¡Hola! Recuerda activar la cuenta Daviplata";
                                    $title = "Symplifica";
                                    $longMessage = "¡Hola! Te recordamos crear y activar la cuenta Daviplata de tu empleado antes de realizar el pago de su sueldo";

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
                                    $resultUsers[] = array('userId' => $user->getId(), 'resultPush' => $collect, 'resultMail' => $send);
                                }
                            }
                        }
                    }
                }
            }
        } elseif ($period == 2) {
            /** @var Notification $notification */
            foreach ($notifications as $notification) {
                $person = $notification->getPersonPerson();
                /** @var User $user */
                $user = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:User")->findOneBy(array('personPerson' => $person));
                if ($user) {
                    $pices = explode('/', $notification->getRelatedLink());
                    if ($pices) {
                        $pMethod = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:PayMethod")->find($pices[2]);
                        if ($pMethod) {
                            /** @var Contract $contract */
                            $contract = $this->getDoctrine()->getManager()->getRepository("RocketSellerTwoPickBundle:Contract")->findOneBy(array('payMethodPayMethod' => $pMethod));
                            if ($contract) {
                                if ($contract->getActivePayroll()->getMonth() == $date->format('m') and $contract->getActivePayroll()->getPeriod() == 2) {
                                    $context = array(
                                        'emailType'=>'daviplataReminder',
                                        'toEmail'=>$user->getEmail(),
                                        'userName'=>$user->getPersonPerson()->getFullName(),
                                        'employeeName'=>$contract->getEmployerHasEmployeeEmployerHasEmployee()->getEmployeeEmployee()->getPersonPerson()->getFullName()
                                    );
                                    $send = $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage($context);

                                    $message = "¡Hola! Recuerda activar la cuenta Daviplata";
                                    $title = "Symplifica";
                                    $longMessage = "¡Hola! Te recordamos crear y activar la cuenta Daviplata de tu empleado antes de realizar el pago de su sueldo";

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
                                    $resultUsers[] = array('userId' => $user->getId(), 'resultPush' => $collect, 'resultMail' => $send);
                                }
                            }
                        }
                    }
                }
            }
        }
        $view = View::create();
        $view->setData($resultUsers)->setStatusCode(200);
        return $view;

    }

}
