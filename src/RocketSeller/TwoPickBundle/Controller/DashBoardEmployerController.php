<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Doctrine\Common\Collections\Collection;
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
            //setting flags for end validation, clean dashboard and contract validated
            $endValid = false;
            $cleanDash = false;
            $valContract = false;
            //

            foreach ($this->allDocumentsReady($user) as $docStat ){
                //se asigna la bandera ready para cada id de empleado con su respectivo status
                $ready[$docStat['idEHE']]=$docStat['docStatus'];
                // Se envia el Email diahabil y se muestra el modal de documentos en validación
                if($tareas and $docStat['docStatus']==2){
                    $em = $this->getDoctrine()->getManager();
                    $eHE = $em->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')->find($docStat['idEHE']);
                    $smailer = $this->get('symplifica.mailer.twig_swift');
                    $smailer->sendOneDayMessage($this->getUser(),$eHE);
                }elseif(!$tareas and ($docStat['docStatus']==2)){
                    $cleanDash =true;
                }elseif(!$tareas and $docStat['docStatus']==3){
                    $cleanDash =true;
                }

                //si el usuario no tiene tareas pendientes y algun empleado fue completamente validado por backoffice
                if(!$tareas and $docStat['docStatus']==13){
                    $endValid = true;
                }



                if($tareas and $docStat['docStatus']==11){
                    $eHE = $this->getDoctrine()->getManager()->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')->find($docStat['idEHE']);
                    return $this->redirectToRoute('employer_completion_documents',array('idEHE'=>$eHE->getIdEmployerHasEmployee()));
                }
                if(!$tareas and $docStat['docStatus']>13){
                }
            }
            if($endValid){
                return $this->render('@RocketSellerTwoPick/Employer/endvalidation.html.twig',array(
                    'user' => $user->getPersonPerson(),
                    'ready'=>$ready,
                ));
            }
            if($cleanDash){
                return $this->render('@RocketSellerTwoPick/Employer/cleanDashboard.html.twig',array(
                    'user' => $user->getPersonPerson(),
                    'ready' => $ready
                ));
            }
            return $this->render('RocketSellerTwoPickBundle:Employer:dashBoard.html.twig', array(
                    'notifications' => $notifications,
                    'user' => $user->getPersonPerson(),
                    'ready'=>$ready,
            ));
            
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