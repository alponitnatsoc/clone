<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Controller\NotificationEmployerController;

class DashBoardEmployerController extends Controller
{

    /**
     * Maneja el registro de una nueva persona con los datos básicos, 
     * TODO agregar todos los campos de los wireframes
     * @param el Request que manjea el form que se imprime
     * @return La vista de el formulario de la nueva persona
     * */
    public function showDashBoardAction(Request $request)
    {
        $user = $this->getUser();
        if (empty($user)) {
            return $this->redirectToRoute('fos_user_security_login');
        }

        $notifications = $this->getNotifications($user->getPersonPerson());
        return $this->render('RocketSellerTwoPickBundle:Employer:dashBoard.html.twig', array('notifications' => $notifications));
    }

    public function getNotifications($person)
    {
        $notifications = $this->getdoctrine()
                ->getRepository('RocketSellerTwoPickBundle:NotificationEmployer')
                ->findByEmployerEmployer($person);
        return $notifications;
    }

}

?>