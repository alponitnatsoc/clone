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
        return $this->render('RocketSellerTwoPickBundle:Default:index.html.twig');
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
                switch ($form->get('subject')->getData()) {
                    case 0:
                        $sub = 'Registro';
                        break;
                    case 1:
                        $sub = 'Pago de nómina y aportes';
                        break;
                    case 2:
                        $sub = 'Calculadora salarial';
                        break;
                    case 3:
                        $sub = 'Consulta jurídica';
                        break;
                    case 4:
                        $sub = 'Planes y precios';
                        break;
                    case 5:
                        $sub = 'Otros';
                        break;
                    $sub = $form->get('subject')->getData();
                }
                $send = $this->get('symplifica.mailer.twig_swift')->sendHelpEmailMessage($form->get('name')->getData(),$form->get('email')->getData(), $sub, $form->get('message')->getData(), $request->getClientIp(), $form->get('phone')->getData());

                if ($send) {
                    $this->addFlash('success', 'Tu email ha sido enviado. Nos pondremos en contacto en menos de 24 horas');
                } else {
                    $this->addFlash('fail', 'Ocurrio un error');
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
