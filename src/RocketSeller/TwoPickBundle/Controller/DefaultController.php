<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Form\Type\ContactType;
use RocketSeller\TwoPickBundle\Form\PagoMembresiaForm;
use RocketSeller\TwoPickBundle\Entity\BillingAddress;

class DefaultController extends Controller
{

    public function indexAction()
    {
        if($this->isGranted('ROLE_BACK_OFFICE')){
            return $this->redirectToRoute('back_office');
        }
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
                                    'andres.ramirez@symplifica.com' => 'Andres Ramirez',
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


    public function introSinVerificarAction()
    {
        return $this->render("RocketSellerTwoPickBundle:Default:intro-sin-verificar.html.twig", array(
                    'dateCreated' => $this->getRequest()->query->get("dc"),
                    'ct' => $this->getRequest()->query->get("q"),
                    'id' => $this->getRequest()->query->get("ui")
        ));
    }

    public function getAllRoutesAction()
    {
        /** @var Router $router */
        $router = $this->get('router');
        $routes = $router->getRouteCollection();

        return $this->render("RocketSellerTwoPickBundle:Default:routes.html.twig", array(
                    'routes' => $routes
        ));
    }
}
