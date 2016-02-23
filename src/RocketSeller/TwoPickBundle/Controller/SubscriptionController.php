<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use RocketSeller\TwoPickBundle\Form\PagoMembresiaForm;
use RocketSeller\TwoPickBundle\Entity\BillingAddress;

class SubscriptionController extends Controller
{

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
        return $this->render('RocketSellerTwoPickBundle:Subscription:subscriptionChoices.html.twig', array(
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

        return $this->render('RocketSellerTwoPickBundle:Subscription:active.html.twig', array(
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
        return $this->render('RocketSellerTwoPickBundle:Subscription:inactive.html.twig');
    }

}
