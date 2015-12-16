<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

//use Symfony\Component\HttpFoundation\Response;
//use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
//use RocketSeller\TwoPickBundle\Entity\Notification;

class NotificationEmployerController extends Controller
{

    public function indexAction()
    {
        $user = $this->getUser();
        $notifications = $this->getNotifications($user->getPersonPerson());
        return $this->render('RocketSellerTwoPickBundle:Employer:notifications.html.twig', array(
                    'notifications' => $notifications,
        ));
    }

    public function getNotifications($person)
    {
        $notifications = $this->getdoctrine()
                ->getRepository('RocketSellerTwoPickBundle:NotificationEmployer')
                ->findByEmployerEmployer($person);
        return $notifications;
    }

    public function loadClassById($parameter, $entity)
    {
        $loadedClass = $this->getdoctrine()
                ->getRepository('RocketSellerTwoPickBundle:' . $entity)
                ->find($parameter);
        return $loadedClass;
    }

    public function loadClassByArray($array, $entity)
    {
        $loadedClass = $this->getdoctrine()
                ->getRepository('RocketSellerTwoPickBundle:' . $entity)
                ->findOneBy($array);
        return $loadedClass;
    }

}
