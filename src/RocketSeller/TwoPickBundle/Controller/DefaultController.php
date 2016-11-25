<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Phone;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Form\Type\MailType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Form\Type\ContactType;
use RocketSeller\TwoPickBundle\Form\PagoMembresiaForm;
use RocketSeller\TwoPickBundle\Entity\BillingAddress;

class DefaultController extends Controller
{

    public function indexAction()
    {

        if ($this->isGranted('ROLE_BACK_OFFICE')) {
            return $this->redirectToRoute('back_office');
        }
        if ($this->isGranted('ROLE_SELLS')) {
            return $this->redirectToRoute('sells_dashboard');
        }
        return $this->render('RocketSellerTwoPickBundle:Default:index.html.twig', array(
        	'user' => $this->getUser()));
    }

    public function contactAction(Request $request,$subject)
    {
//        $helpCategories = $this->getDoctrine()
//            ->getRepository('RocketSellerTwoPickBundle:HelpCategory')
//            ->findAll();

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        /** @var User $user */
        $user = $this->getUser();
        $name = $user->getPersonPerson()->getFullName();
        $email = $user->getEmail();
        if (!$user->getPersonPerson()->getPhones()->isEmpty()) {
            $phone = $user->getPersonPerson()->getPhones()->first()->getPhoneNumber();
        } else {
            $phone = '';
        }
        if($subject=='default'){
            $form = $this->createForm(new ContactType($name, $email, $phone), array('method' => 'POST'));
        }else{
            $form = $this->createForm(new ContactType($name, $email, $phone,$subject), array('method' => 'POST'));
        }

        $form->handleRequest($request);

        if ($request->getMethod() == 'POST') {
            if ($form->isValid()) {
                $sub = $form->get('subject')->getData();
                switch ($sub.'') {
                    case '0':
                        $sub='Registro';
                        break;
                    case '1':
                        $sub = 'Pago de nómina y aportes';
                        break;
                    case '2':
                        $sub = 'Calculadora salarial';
                        break;
                    case '3':
                        $sub = 'Consulta jurídica';
                        break;
                    case '4':
                        $sub = 'Planes y precios';
                        break;
                    case '5':
                        $sub = 'Otros';
                        break;
                }
                $send = $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage(array('emailType'=>'helpPrivate','name'=>$form->get('name')->getData(),'fromEmail'=>$form->get('email')->getData(), 'subject'=>$sub, 'message'=>$form->get('message')->getData(),'ip'=>$request->getClientIp(),'phone'=>$form->get('phone')->getData()));

                if ($send) {
                    $this->addFlash('success_send_email', 'Tu email ha sido enviado. Nos pondremos en contacto en menos de 24 horas');
                } else {
                    $this->addFlash('fail_send_email', 'Ocurrio un error');
                }

                return $this->redirect($this->generateUrl('contact'));
            }
        }

        return $this->render('RocketSellerTwoPickBundle:General:help.html.twig', array(
            'form' => $form->createView(),
//          'helpCategories' => $helpCategories
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
//        /** @var Router $router */
//        $router = $this->get('router');
//        $routes = $router->getRouteCollection();
//
//        return $this->render("RocketSellerTwoPickBundle:Default:routes.html.twig", array(
//            'routes' => $routes
//        ));
    }
}
