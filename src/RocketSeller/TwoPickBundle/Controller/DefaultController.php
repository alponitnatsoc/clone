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
                        'RocketSellerTwoPickBundle:Mail:contact.html.twig',
                        array(
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
            'form'   => $form->createView()
        ));
    }
}