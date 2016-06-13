<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\User;
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

    public function contactAction(Request $request)
    {
        $helpCategories = $this->getDoctrine()
            ->getRepository('RocketSellerTwoPickBundle:HelpCategory')
            ->findAll();

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        /** @var User $user */
        $user = $this->getUser();
        $name = $user->getPersonPerson()->getFullName();
        $email = $user->getEmail();

        $form = $this->createForm(new ContactType($name,$email), array('method'=> 'POST'));
        

        $form->handleRequest($request);

        if ($request->getMethod() == 'POST') {
            if ($form->isValid()) {
                /*$smailer = $this->get('symplifica.mailer.twig_swift');
                $send = $smailer->sendEmail($this->getUser(),
                    'RocketSellerTwoPickBundle:Mail:contact.html.twig', array(
                        'ip' => $request->getClientIp(),
                        'name' => $form->get('name')->getData(),
                        'email' => $form->get('email')->getData(),
                        'message' => $form->get('message')->getData())
                    ,$form->get('email'), 'andres.ramirez@symplifica.com');
                */

                $mailer = $this->get('mailer');
                $sub='';  
                switch($form->get('subject')->getData()){
                    case 0:
                        $sub = 'Preguntas del Registro';
                        break;
                    case 1:
                        $sub ='Preguntas de pago de nómina y aportes';
                        break;
                    case 2:
                        $sub = 'Preguntas sobre la calculadora salarial';
                        break;
                    case 3:
                        $sub = 'Consulta jurídica';
                        break;
                    case 4:
                        $sub = 'Consulta de planes y precios';
                        break;
                    case 5:
                        $sub = 'Otros';
                        break;
                }
                
                $message = $mailer->createMessage()
                    ->setSubject($sub)
                    ->setFrom(
                        array($form->get('email')->getData() => $form->get('name')->getData())
                    )
                    ->setTo(
                        array('andres.ramirez@symplifica.com')
                    )
                    ->setCc(
                        array(
                            'andres.ramirez@symplifica.com' => 'Andres Felipe Ramirez',
                            $form->get('email')->getData() => $form->get('name')->getData()
                        )
                    )
                    ->setBody(
                        $this->renderView(
                            'RocketSellerTwoPickBundle:Mail:contact.html.twig', array(
                                'ip' => $request->getClientIp(),
                                'name' => $form->get('name')->getData(),
                                'email' => $form->get('email')->getData(),
                                'message' => $form->get('message')->getData(),
                                'subject' => $form->get('subject')->getData()
                            )
                        )
                    );

                $mailer->send($message);

                $request->getSession()->getFlashBag()->add('success', 'Tu email ha sido enviado. Gracias');

                return $this->redirect($this->generateUrl('contact'));
            }
        }

        return $this->render('RocketSellerTwoPickBundle:General:help.html.twig', array(
                    'form' => $form->createView(),
                    'helpCategories'=>$helpCategories
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
