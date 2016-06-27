<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Doctrine\ORM\EntityManager;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
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
        $contractType = $this->getdoctrine()
                    ->getRepository('RocketSellerTwoPickBundle:DocumentType')
                    ->findOneByName("Contrato");
        if (empty($user)) {
            return $this->redirectToRoute('fos_user_security_login');
        }
        if($user->getStatus()<2 ) {
            return $this->forward('RocketSellerTwoPickBundle:DashBoard:showDashBoard');
        }
        try {
            $orderBy = ($request->query->get('orderBy')) ? $request->query->get('orderBy') : 'deadline';
            $notifications = $this->getNotifications($user->getPersonPerson(), $orderBy);
            /** @var User $user */
            $user = $this->getUser();
            $employerHasEmployees=$user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
            $index= 0;
            /** @var EmployerHasEmployee $employerHasEmployee */
            foreach ($employerHasEmployees as $employerHasEmployee){
                if($this->allDocumentsReady($user,$employerHasEmployee)){
                    $ready[$index]=$employerHasEmployee->getIdEmployerHasEmployee();
                }else{
                    $ready[$index]=-1;
                }
                $index++;
            }
            
            return $this->render('RocketSellerTwoPickBundle:Employer:dashBoard.html.twig', array(
                        'notifications' => $notifications,
                        'user' => $user->getPersonPerson(),
                        'contractType' => $contractType,
                        'ready'=> $ready
                        
            ));
        } catch (Exception $ex) {
            return $this->render('RocketSellerTwoPickBundle:Employer:dashBoard.html.twig', array(
                        'notifications' => false,
                        'user' => $user->getPersonPerson(),
                        'contractType' => $contractType
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