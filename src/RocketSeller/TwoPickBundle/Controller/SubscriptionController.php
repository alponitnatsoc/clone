<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use RocketSeller\TwoPickBundle\Form\PagoMembresiaForm;
use RocketSeller\TwoPickBundle\Entity\BillingAddress;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Referred;
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

    public function getEmployees($person, $activeEmployee = false)
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
                if ($activeEmployee) {
                    if ($employee->getState() == 0) {
                        break;
                    }
                }
                $contracts = $employee->getContracts();
                foreach ($contracts as $keyContract => $contract) {
                    if ($contract->getState() > 0) {
                        $employees[$keyEmployee]['contrato'] = $contract;
                        $employees[$keyEmployee]['employee'] = $employee;
                        $employees[$keyEmployee]['product'] = $this->findPriceByNumDays($productos, ($contract->getWorkableDaysMonth()));
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

    public function addToNovo()
    {
        $request = $this->container->get('request');
        $request->setMethod("POST");
        $request->request->add(array(
            "documentType" => $person->getDocumentType(),
            "documentNumber" => $person->getDocument(),
            "name" => $person->getNames(),
            "lastName" => $person->getLastName1() . " " . $person->getLastName2(),
            "year" => $person->getBirthDate()->format("Y"),
            "month" => $person->getBirthDate()->format("m"),
            "day" => $person->getBirthDate()->format("d"),
            "phone" => $person->getPhones()->get(0)->getPhoneNumber(),
            "email" => $user->getEmail()
        ));
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:postClient', array('_format' => 'json'));
        //echo "Status Code Employer: " . $person->getNames() . " -> " . $insertionAnswer->getStatusCode();

        if ($insertionAnswer->getStatusCode() == 406 || $insertionAnswer->getStatusCode() == 201) {
            $eHEes = $employer->getEmployerHasEmployees();
            /** @var EmployerHasEmployee $employeeC */
            foreach ($eHEes as $employeeC) {
                if ($employeeC->getState() > 1) {
                    $contracts = $employeeC->getContracts();
                    /** @var Contract $cont */
                    $contract = null;
                    foreach ($contracts as $cont) {
                        if ($cont->getState() == 1)
                            $contract = $cont;
                    }
                    $payMC = $contract->getPayMethodPayMethod();
                    $paymentMethodId = $payMC->getAccountTypeAccountType()->getName() == "Ahorros" ? 4 :
                            $payMC->getAccountTypeAccountType()->getName() == "Corriente" ? 5 : 6;
                    $paymentMethodAN = $payMC->getAccountNumber() == null ? $payMC->getCellPhone() : $payMC->getAccountNumber();
                    $employeePerson = $employeeC->getEmployeeEmployee()->getPersonPerson();
                    $request->setMethod("POST");
                    $request->request->add(array(
                        "documentType" => $employeePerson->getDocumentType(),
                        "beneficiaryId" => $employeePerson->getDocument(),
                        "documentNumber" => $person->getDocument(),
                        "name" => $employeePerson->getNames(),
                        "lastName" => $employeePerson->getLastName1() . " " . $employeePerson->getLastName2(),
                        "yearBirth" => $employeePerson->getBirthDate()->format("Y"),
                        "monthBirth" => $employeePerson->getBirthDate()->format("m"),
                        "dayBirth" => $employeePerson->getBirthDate()->format("d"),
                        "phone" => $employeePerson->getPhones()->get(0)->getPhoneNumber(),
                        "email" => $employeePerson->getEmail() == null ? $employeePerson->getDocumentType() . $person->getDocument() .
                                "@" . $employeePerson->getNames() . ".com" : $employeePerson->getEmail(),
                        "companyId" => $person->getDocument(), //TODO ESTO CAMBIA CUANDO TENGAMOS EMPRESAS
                        "companyBranch" => "0", //TODO ESTO CAMBIA CUANDO TENGAMOS EMPRESAS
                        "paymentMethodId" => $paymentMethodId,
                        "paymentAccountNumber" => $paymentMethodAN,
                        "paymentBankNumber" => 0, //THIS SHOULD HAVE THE NOVO ID BANK TABLE
                        "paymentType" => $payMC->getAccountTypeAccountType()->getName(),
                    ));
                    $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentsRest:postBeneficiary', array('_format' => 'json'));
                    //echo "Status Code Employee: " . $employeePerson->getNames() . " -> " . $insertionAnswer->getStatusCode() . " content" . $insertionAnswer->getContent();
                }
            }
        }
    }

    public function activarSuscripcionAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {

            $data = $this->forward('RocketSellerTwoPickBundle:EmployerRest:getEmployeeFree', array(
                'idEmployer' => $this->getUser()->getPersonPerson()->getEmployer()->getIdEmployer(),
                'freeTime' => $this->getUser()->getStatus() > 1 ? $this->getUser()->getStatus() - 1 : 1
                    ), array('_format' => 'json')
            );

            $user = $this->getUser();
            $person = $user->getPersonPerson();
            $billingAdress = $person->getBillingAddress();
            $employees = $this->getEmployees($user->getPersonPerson()->getEmployer(), true);

            $form = $this->createForm(new PagoMembresiaForm(), new BillingAddress(), array(
                'action' => $this->generateUrl('api_public_post_pay_membresia', array('format' => 'json')),
                'method' => 'POST',
            ));

            return $this->render('RocketSellerTwoPickBundle:Subscription:active.html.twig', array(
                        'form' => $form->createView(),
                        'employer' => $person,
                        'employees' => $employees['employees'],
                        'billingAdress' => $billingAdress,
                        'isRefered' => $this->userIsRefered(),
                        'haveRefered' => $this->userHaveValidRefered()
            ));
        } else {
            return $this->redirectToRoute("subscription_choices");
        }
    }

    /**
     * Buscar si el usuario fue referido por alguien
     * @param User $user
     * @return Referred
     */
    private function userIsRefered(User $user = null)
    {
        /* @var $isRefered Referred */
        $isRefered = $this->getdoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Referred')
                ->findOneBy(array('referredUserId' => $user != null ? $user : $this->getUser()));
        return $isRefered;
    }

    /**
     * Buscar referidos que tiene el usuario y que ya tengan subscripcion 
     * @param User $user
     * @return array
     */
    private function userHaveValidRefered(User $user = null)
    {
        /* @var $haveRefered Referred */
        $haveRefered = $this->getdoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Referred')
                ->findBy(array('userId' => $user != null ? $user : $this->getUser(), 'status' => 0));
        $responce = array();
        foreach ($haveRefered as $key => $referedUser) {
            if ($referedUser->getReferredUserId()->getPaymentState() > 0) {
                array_push($responce, $referedUser);
            }
        }
        return $responce;
    }

    public function suscripcionInactivaAction()
    {
        $user = $this->getUser();
        return $this->render('RocketSellerTwoPickBundle:Subscription:inactive.html.twig');
    }

}
