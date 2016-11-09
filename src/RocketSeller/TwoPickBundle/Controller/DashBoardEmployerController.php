<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Doctrine\Common\Collections\Collection;
use DateTime;
use Doctrine\ORM\EntityManager;
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
                    if($ehe->getDocumentStatusType()->getDocumentStatusCode()=='BOFFFF' and $ehe->getBackofficeFinishMessageAt()==null){
                        $ehe->setBackofficeFinishMessageAt($today);
                        $finished[$ehe->getIdEmployerHasEmployee().'']=true;
                        if(!$backval)$backval=true;
                        $em->persist($ehe);
                    }
                }
            }
            $em->flush();

            if($tareas){
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
                    return $this->render('@RocketSellerTwoPick/Employer/endvalidation.html.twig', array(
                        'user' => $user->getPersonPerson(),
                        'ready' => $ready,
                        'validated' => $validated,
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
