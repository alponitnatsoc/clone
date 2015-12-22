<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use RocketSeller\TwoPickBundle\Entity\Notification;

class NotificationController extends Controller {

    public function indexAction() {
        $user = $this->getUser();
        $notifications = $this->getNotifications($user->getPersonPerson());
        return $this->render('RocketSellerTwoPickBundle:Employer:notifications.html.twig', array(
                    'notifications' => $notifications,
        ));
    }

    public function changeStatusAction($idNotification, $status) {
        $user = $this->getUser();
        /** @var Notification $notification */
        $notification = $this->getdoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Notification')
                ->find($idNotification);
        $notification->setStatus($status);
        $em = $this->getDoctrine()->getManager();
        $em->persist($notification);
        $em->flush();
        $notifications = $this->getNotifications($user->getPersonPerson());
        return $this->render('RocketSellerTwoPickBundle:Employer:notifications.html.twig', array(
                    'notifications' => $notifications,
        ));
    }

    public function getNotifications($person) {
        $notifications = $this->getdoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Notification')
                ->findByPersonPerson($person);
        return $notifications;
    }

    public function loadClassById($parameter, $entity) {
        $loadedClass = $this->getdoctrine()
                ->getRepository('RocketSellerTwoPickBundle:' . $entity)
                ->find($parameter);
        return $loadedClass;
    }

    public function loadClassByArray($array, $entity) {
        $loadedClass = $this->getdoctrine()
                ->getRepository('RocketSellerTwoPickBundle:' . $entity)
                ->findOneBy($array);
        return $loadedClass;
    }

}
