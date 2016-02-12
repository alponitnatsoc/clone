<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Form\Type\ContactType;
use RocketSeller\TwoPickBundle\Form\PagoMembresiaForm;
use RocketSeller\TwoPickBundle\Entity\Notification;

class ExpressRegistrationController extends Controller
{
    public function expressPaymentAction($id)
    {
        $user = $this->getDoctrine()
        ->getRepository('RocketSellerTwoPickBundle:User')
        ->find($id);
        return $this->render('RocketSellerTwoPickBundle:Registration:expressPayment.html.twig',array('user'=>$user));
    }
    public function successExpressAction($id)
    {
        $user = $this->getDoctrine()
        ->getRepository('RocketSellerTwoPickBundle:User')
        ->find($id);
        $role = $this->getDoctrine()
        ->getRepository('RocketSellerTwoPickBundle:Role')
        ->findByName("ROLE_BACK_OFFICE");
        
        $notification = new Notification();
        $notification->setPersonPerson($user->getPersonPerson());
        $notification->setType("Registro express");
        $notification->setAccion("Registrar usuario");
        $notification->setRoleRole($role[0]);
        $em = $this->getDoctrine()->getManager();
        $em->persist($notification);
        $em->flush();

        return $this->render('RocketSellerTwoPickBundle:Registration:expressSuccess.html.twig');
    }    

}
