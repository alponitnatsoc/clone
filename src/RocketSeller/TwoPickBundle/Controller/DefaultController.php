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

    public function getEmployerHasEmployee($person)
    {
        try {
            $employerHasEmployee = $this->getdoctrine()
                    ->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')
                    ->findByEmployerEmployer($this->getEmployer($person));
            $contratos = array();
            foreach ($employerHasEmployee as $key => $employee) {
                $contracts = $employee->getContracts();
                foreach ($contracts as $key => $contract) {
                    if ($contract->getState() == 'Active') {
                        $contratos[$employee->getIdEmployerHasEmployee()] = $contract;
                        break;
                    }
                }
            }
            return array($employerHasEmployee, $contratos);
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
        $employees = $this->getEmployerHasEmployee($user->getPersonPerson());
        return $this->render('RocketSellerTwoPickBundle:Default:subscriptionChoices.html.twig', array(
                    'employerHasEmployee' => $employees[0],
                    'contratos' => $employees[1],
                    'user' => $user
        ));
    }

    public function activarSuscripcionAction()
    {
        $user = $this->getUser();
        $person = $user->getPersonPerson();
        $billingAdress = $person->getBillingAddress();
        $documentNumber = $person->getDocument();
        $employees = $this->getEmployerHasEmployee($user->getPersonPerson());

        $clientListPaymentmethods = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:getClientListPaymentmethods', array('documentNumber' => $documentNumber), array('_format' => 'json'));
        $responcePaymentsMethods = json_decode($clientListPaymentmethods->getContent(), true);

        $form = $this->createForm(new PagoMembresiaForm(), new BillingAddress(), array(
            'action' => $this->generateUrl('api_public_post_pay_membresia', array('format' => 'json')),
            'method' => 'POST',
        ));

        return $this->render('RocketSellerTwoPickBundle:Default:active.html.twig', array(
                    'form' => $form->createView(),
                    'employer' => $person,
                    'employerHasEmployee' => $employees[0],
                    'contratos' => $employees[1],
                    'paymentMethods' => isset($responcePaymentsMethods['payments']) ? $responcePaymentsMethods['payments'] : false,
                    'billingAdress' => (count($billingAdress) > 0) ? $billingAdress : false
        ));
    }

    public function suscripcionInactivaAction()
    {
        $user = $this->getUser();
        return $this->render('RocketSellerTwoPickBundle:Default:inactive.html.twig');
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
