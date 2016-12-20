<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Doctrine\Common\Collections\Collection;
use DateTime;
use Doctrine\ORM\EntityManager;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\DocumentType;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Traits\EmployeeMethodsTrait;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Controller\NotificationController;

class DashBoardEmployerController extends Controller {

    use EmployeeMethodsTrait;
    /**
     * Maneja el registro de una nueva persona con los datos básicos,
     * TODO agregar todos los campos de los wireframes
     * @param el Request que manjea el form que se imprime
     * @return La vista de el formulario de la nueva persona
     * */
    public function showDashBoardAction(Request $request) {
        /** @var User $user */
        $user = $this->getUser();
        //checking user exist
        if (empty($user)) {
            //if not redirect to login
            return $this->redirectToRoute('fos_user_security_login');
        }
        //checking user is payed
        if($user->getStatus()<2 ) {
            return $this->forward('RocketSellerTwoPickBundle:DashBoard:showDashBoard');
        }
        try {
            $orderBy = ($request->query->get('orderBy')) ? $request->query->get('orderBy') : 'deadline';
            //getting the notifications
            /** @var Collection $notifications */
            $notifications = $this->getNotifications($user->getPersonPerson(), $orderBy);
            //setting flag tareas to false
            $tareas=false;
            //crossing notifications
            /** @var Notification $notification */
            foreach ($notifications as $notification){
                //if at least one notification status is 1 change tareas flag to true
                if($notification->getStatus()==1){
                    $tareas=true;
                    break;
                }
            }
            //getting the user
            /** @var User $user */
            $user = $this->getUser();
            $employer=$user->getPersonPerson()->getEmployer();
            $today = new DateTime();
            $em = $this->getDoctrine()->getManager();
            $state = $this->allDocumentsReady($user);
            $ready=array();
            $validated = array();
            $finished = array();
            $valContract = false;
            $valid = false;
            $backval = false;
            $fin = array();
            if($state ==1){
                if($user->getPersonPerson()->getEmployer()->getDashboardMessage()==null){
                    $employer->setDashboardMessage($today);
                    $welcome = true;
                    $em->persist($employer);
                }else{
                    $welcome = false;
                }
                /** @var EmployerHasEmployee $ehe */
                foreach ($user->getPersonPerson()->getEmployer()->getActiveEmployerHasEmployees() as $ehe) {
                    if($ehe->getAllEmployeeDocsReadyAt()!= null and $ehe->getAllDocsReadyMessageAt()==null){
                        $ehe->setAllDocsReadyMessageAt($today);
                        $ready[$ehe->getIdEmployerHasEmployee().'']=true;
                        $smailer = $this->get('symplifica.mailer.twig_swift');
                        $smailer->sendOneDayMessage($this->getUser(),$ehe);
                        $pushNotificationService = $this->get('app.symplifica_push_notification');
                        $pushNotificationService->sendMessageValidatingDocuments($this->getUser()->getId());
                        $em->persist($ehe);
                    }
                    if($ehe->getDocumentStatusType()->getDocumentStatusCode()=='ALDCVM' and $ehe->getAllDocsValidatedMessageAt()==null){
                        $ehe->setAllDocsValidatedMessageAt($today);
                        $ehe->setDocumentStatusType($this->getDocumentStatusByCode('ALDCVA'));
                        $validated[$ehe->getIdEmployerHasEmployee().'']=true;
                        if(!$valid)$valid=true;
                        $em->persist($ehe);
                    }
                    if($ehe->getDocumentStatusType()->getDocumentStatusCode()=='BOFFFF' and $ehe->getBackofficeFinishMessageAt()==null and $ehe->getExistentSQL()==1){
                        $ehe->setBackofficeFinishMessageAt($today);
                        $finished[$ehe->getIdEmployerHasEmployee().'']=true;
                        $fin[$ehe->getIdEmployerHasEmployee()]=true;
                        if(!$backval)$backval=true;
                        $valid=true;
                        $em->persist($ehe);
                    }elseif($ehe->getDocumentStatusType()->getDocumentStatusCode()=='BOFFFF' and $ehe->getExistentSQL()==1){
                        $fin[$ehe->getIdEmployerHasEmployee()]=true;
                        $valid=true;
                    }else{
                        $fin[$ehe->getIdEmployerHasEmployee()]=false;
                    }

                }
            }

            $em->flush();

            if($tareas and !$backval){
                return $this->render('RocketSellerTwoPickBundle:Employer:dashBoard.html.twig', array(
                    'notifications' => $notifications,
                    'user' => $user->getPersonPerson(),
                    'welcome'=>$welcome,
                    'ready'=>$ready,
                    'validated'=>$validated,
                    'finished'=>$finished,
                ));
            }else{

                if($valid and !$backval) {
                    return $this->render('@RocketSellerTwoPick/Employer/cleanDashboard.html.twig', array(
                        'user' => $user->getPersonPerson(),
                        'ready' => $ready,
                        'validated' => $valid,
                        'backval'=>$backval,
                        'finished'=>$finished,
                        'fin'=>$fin,
                    ));
                }elseif($valid and $backval){
                    return $this->render('@RocketSellerTwoPick/Employer/endvalidation.html.twig',array(
                        'user' => $user->getPersonPerson(),
                        'ready'=>$ready,
                        'validated'=>$validated,
                        'backval'=>$backval,
                    ));
                }elseif(!$valid and $backval){
                    return $this->render('@RocketSellerTwoPick/Employer/endvalidation.html.twig',array(
                        'user' => $user->getPersonPerson(),
                        'ready'=>$ready,
                        'validated'=>$validated,
                        'backval'=>$backval,
                    ));
                }else{
                    return $this->render('@RocketSellerTwoPick/Employer/cleanDashboard.html.twig',array(
                        'user' => $user->getPersonPerson(),
                        'ready' => $ready,
                        'validated'=>$valid,
                    ));
                }
            }
        } catch (Exception $ex) {
            return $this->render('RocketSellerTwoPickBundle:Employer:dashBoard.html.twig', array(
                        'notifications' => false,
                        'user' => $user->getPersonPerson(),

            ));
        }
    }
    /**
     * Function to upload documents from the notification
     * @param Integer $idContract id of the contract
     * @param Integer $idNotification id of the notification to change status
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function solveContractStatusAction($idContract, $idNotification, Request $request){
        $em = $this->getDoctrine()->getManager();
        /** @var Contract $contract */
        $contract = $em->getRepository("RocketSellerTwoPickBundle:Contract")->find($idContract);
        /** @var Notification $notification */
        $notification = $em->getRepository("RocketSellerTwoPickBundle:Notification")->find($idNotification);
        $ehe = $contract->getEmployerHasEmployeeEmployerHasEmployee();
        /** @var DocumentType $docType */
        $docType = $em->getRepository("RocketSellerTwoPickBundle:DocumentType")->findOneBy(array('docCode'=>'CTC'));
        $endContractForm = $this->get("form.factory")->createNamedBuilder("end_contract_form")
            ->add('submit','submit',array('label'=>'aceptar','attr'=>array('class'=>"btn btn-azul")))
            ->getForm();
        $endContractForm->handleRequest($request);

        if($endContractForm->isSubmitted() and $endContractForm->isValid()){
            /** @var UtilsController $utils */
            $utils = $this->get('app.symplifica_utils');
            $contract->setToLiquidate(1);
            $contract->setDateToEnd($notification->getDeadline());
            $em->persist($contract);
            $notification2 = $em->getRepository("RocketSellerTwoPickBundle:Notification")->findOneBy(array(
                "documentTypeDocumentType"=>$docType,
                "personPerson"=>$ehe->getEmployerEmployer()->getPersonPerson(),
            ));
            if(!$notification2)
                $notification2 = new Notification();
            $notification2->setPersonPerson($ehe->getEmployerEmployer()->getPersonPerson());
            $notification2->activate();
            $notification2->setDocumentTypeDocumentType($docType);
            $notification2->setType('alert');
            $notification2->setDescription("Subir Carta de Terminación de Contrato de ".$utils->mb_capitalize(explode(" ",$ehe->getEmployeeEmployee()->getPersonPerson()->getNames())[0].
                    " " . $ehe->getEmployeeEmployee()->getPersonPerson()->getLastName1()));
            $notification2->setDeadline($notification->getDeadline());
            $notification2->setRelatedLink("/document/add/Contract/".$contract->getIdContract()."/CTC");
            $notification2->setAccion('Subir');
            $notification2->setDownloaded(0);
            $notification2->setDownloadAction("Bajar");
            $notification2->setDownloadLink("/documents/downloads/".$docType->getRefPdf()."/".$contract->getIdContract()."/pdf");
            $em->persist($notification2);
            $notification->disable();
            $em->persist($notification);
            $em->flush();
            return $this->redirectToRoute('show_dashboard');
        }
        return $this->render(
            'RocketSellerTwoPickBundle:Contract:solve_contract_end_status.html.twig', array(
                'ehe'=>$ehe,
                'contract'=>$contract,
                'notification'=>$notification,
                'end_contract_form'=>$endContractForm->createView(),
                )
        );
    }

    public function getNotifications($person, $orderBy = 'deadline') {
        try {
            $notifications = $this->getdoctrine()
                    ->getRepository('RocketSellerTwoPickBundle:Notification')
                    ->findByPersonPerson($person, array($orderBy => 'ASC'));
            return $notifications;
        } catch (Exception $ex) {
            return false;
        }
    }

}

?>
