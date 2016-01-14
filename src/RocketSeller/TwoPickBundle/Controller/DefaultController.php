<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Form\Type\ContactType;

class DefaultController extends Controller
{

    public function indexAction()
    {
        return $this->render('RocketSellerTwoPickBundle:Default:index.html.twig');
    }

    public function contactAction(Request $query)
    {
        $form = $this->createForm(new ContactType());

        if ($query->isMethod('POST')) {
//             $form->handleRequest($query);
            $form->bind($query);
            if ($form->isValid()) {
                $mailer = $this->get('mailer');
                $message = $mailer->createMessage()
                        ->setSubject($form->get('subject')->getData())
                        ->setFrom(
                                array($form->get('email')->getData() => $form->get('name')->getData())
                        )
                        ->setTo(
                                array(
                                    $form->get('topic')->getData() => $form->get('subject')->getData()
                                )
                        )
                        ->setCc(
                                array(
                                    'plinio.romero@symplifica.com' => 'Plinio Romero',
                                    $form->get('email')->getData() => $form->get('name')->getData()
                                )
                        )
                        ->setBody(
                        $this->renderView(
                                'RocketSellerTwoPickBundle:Mail:contact.html.twig', array(
                            'ip' => $query->getClientIp(),
                            'name' => $form->get('name')->getData(),
                            'email' => $form->get('email')->getData(),
                            'message' => $form->get('message')->getData(),
                            'subject' => $form->get('subject')->getData()
                                )
                        )
                );

                $mailer->send($message);

                $query->getSession()->getFlashBag()->add('success', 'Tu email ha sido enviado. Gracias');

                return $this->redirect($this->generateUrl('contact'));
            }
        }

        return $this->render('RocketSellerTwoPickBundle:Default:contact.html.twig', array(
                    'form' => $form->createView()
        ));
    }

    public function getEmployees($person)
    {
        try {
            $employees = $this->getdoctrine()
                    ->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')
                    ->findByEmployerEmployer($this->getEmployer($person));
            return $employees;
        } catch (Exception $ex) {
            $logger = $this->get('logger');
            $logger->error(json_encode($ex));
            return false;
        }
    }

    public function getEmployer($person)
    {
        try {
            $employer = $this->getdoctrine()
                    ->getRepository('RocketSellerTwoPickBundle:Employer')
                    ->findByPersonPerson($person);
            return $employer;
        } catch (Exception $ex) {
            $logger = $this->get('logger');
            $logger->error(json_encode($ex));
            return false;
        }
    }

    public function subscriptionChoicesAction()
    {
        $user = $this->getUser();
        $employees = $this->getEmployees($user->getPersonPerson());
        return $this->render('RocketSellerTwoPickBundle:Default:subscriptionChoices.html.twig', array(
                    'employees' => $employees,
                    'user' => $user
        ));
    }

    public function activarSuscripcionAction()
    {
        $user = $this->getUser();
        return $this->render('RocketSellerTwoPickBundle:Default:active.html.twig');
    }

    public function introSinVerificarAction()
    {
        return $this->render("RocketSellerTwoPickBundle:Default:intro-sin-verificar.html.twig", array(
                    'dateCreated' => $this->getRequest()->query->get("dc"),
                    'ct' => $this->getRequest()->query->get("q"),
                    'id' => $this->getRequest()->query->get("ui")
        ));
    }

}
