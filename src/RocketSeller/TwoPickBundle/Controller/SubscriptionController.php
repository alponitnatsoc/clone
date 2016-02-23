<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use RocketSeller\TwoPickBundle\Form\PagoMembresiaForm;
use RocketSeller\TwoPickBundle\Entity\BillingAddress;
use Symfony\Component\HttpFoundation\Request;

class SubscriptionController extends Controller
{

    private function findPriceByNumDays($productos, $days)
    {
        if ($days > 0 && $days <= 10) {
            $key = 'PS1';
        } elseif ($days >= 11 && $days <= 19) {
            $key = 'PS2';
        } elseif ($days >= 20) {
            $key = 'PS3';
        } else {
            $key = 'PS3';
        }
        foreach ($productos as $value) {
            if ($value->getSimpleName() == $key) {
                return $value;
            }
        }
    }

    public function getEmployees($person)
    {
        try {
            $employerHasEmployee = $this->getdoctrine()
                    ->getRepository('RocketSellerTwoPickBundle:EmployerHasEmployee')
                    ->findByEmployerEmployer($person);

            $productos = $this->getdoctrine()
                    ->getRepository('RocketSellerTwoPickBundle:Product')
                    ->findBy(array('simpleName' => array('PS1', 'PS2', 'PS3')));

            $employees = array();
            foreach ($employerHasEmployee as $keyEmployee => $employee) {
                $contracts = $employee->getContracts();
                foreach ($contracts as $keyContract => $contract) {
                    if ($contract->getState() > 0) {
                        $employees[$keyEmployee]['contrato'] = $contract;
                        $employees[$keyEmployee]['employee'] = $employee;
                        $employees[$keyEmployee]['product'] = $this->findPriceByNumDays($productos, ($contract->getWorkableDaysMonth() * 4.34524));
                        break;
                    }
                }
            }
            return array('employees' => $employees, 'productos' => $productos);
        } catch (Exception $ex) {
            $logger = $this->get('logger');
            $logger->error(json_encode($ex));
            return false;
        }
    }

    private function orderProducts($productos)
    {
        $products = array();
        foreach ($productos as $key => $value) {
            $products[$value->getSimpleName()] = $value->getPrice();
        }
        return $products;
    }

    public function subscriptionChoicesAction()
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        $user = $this->getUser();
        $employees = $this->getEmployees($user->getPersonPerson()->getEmployer());
        return $this->render('RocketSellerTwoPickBundle:Subscription:subscriptionChoices.html.twig', array(
                    'employees' => $employees['employees'],
                    'productos' => $this->orderProducts($employees['productos']),
                    'user' => $user
        ));
    }

    public function activarSuscripcionAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {

            $user = $this->getUser();
            $person = $user->getPersonPerson();
            $billingAdress = $person->getBillingAddress();
            $documentNumber = $person->getDocument();
            $employees = $this->getEmployees($user->getPersonPerson()->getEmployer());

            $form = $this->createForm(new PagoMembresiaForm(), new BillingAddress(), array(
                'action' => $this->generateUrl('api_public_post_pay_membresia', array('format' => 'json')),
                'method' => 'POST',
            ));

            return $this->render('RocketSellerTwoPickBundle:Subscription:active.html.twig', array(
                        'form' => $form->createView(),
                        'employer' => $person,
                        'employees' => $employees,
                        'billingAdress' => (count($billingAdress) > 0) ? $billingAdress : false
            ));
        } else {
            return $this->redirectToRoute("subscription_choices");
        }
    }

    public function suscripcionInactivaAction()
    {
        $user = $this->getUser();
        return $this->render('RocketSellerTwoPickBundle:Subscription:inactive.html.twig');
    }

}
