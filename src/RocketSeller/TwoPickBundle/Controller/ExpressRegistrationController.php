<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Form\Type\ContactType;
use RocketSeller\TwoPickBundle\Form\PagoMembresiaForm;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrdersDescription;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Contract;
use RocketSeller\TwoPickBundle\Entity\Phone;
use RocketSeller\TwoPickBundle\Entity\Workplace;
use RocketSeller\TwoPickBundle\Form\EmployerRegistration;
use RocketSeller\TwoPickBundle\Form\PersonEmployeeRegistration;
use RocketSeller\TwoPickBundle\Form\EmployerEdit;
use RocketSeller\TwoPickBundle\Form\BasicEmployeePersonRegistration;
use RocketSeller\TwoPickBundle\Form\AddCreditCard;

class ExpressRegistrationController extends Controller
{

    public function expressPaymentAction()
    {
        //$user = $this->getDoctrine()
        //->getRepository('RocketSellerTwoPickBundle:User')
        //->find($id);
        $user = $this->getUser();
        $idUser = $user->getId();
        $person = $user->getPersonPerson();
        $format = array('_format' => 'json');
        $response = $this->forward('RocketSellerTwoPickBundle:ExpressRegistrationRest:getPayment', array(
            'id' => $idUser
                ), $format
        );
        if ($response->getStatusCode() == 201) {
            //return $this->render('RocketSellerTwoPickBundle:Registration:expressPayment.html.twig',array('user'=>$user));
            return $this->redirectToRoute('express_payment_add');
        } else {
            dump($response);
            exit();
        }
    }

    public function addCreditCardAction(Request $request)
    {
        $user = $this->getUser();
        $person = $user->getPersonPerson();

        $form = $this->createForm(new AddCreditCard(), null, array(
            'action' => $this->generateUrl('express_payment_add'),
            'method' => 'POST',
        ));

        $form->handleRequest($request);
        if ($form->isValid()) {
            $user = $this->getUser();
            /** @var Person $person */
            $person = $user->getPersonPerson();
            $data = $form->getData();

            //TODO NovoPayment
            $request->setMethod("POST");
            $request->request->add(array(
                "documentType" => $person->getDocumentType(),
                "documentNumber" => $person->getDocument(),
                "credit_card" => $form->get("credit_card")->getData(),
                "expiry_date_year" => $form->get("expiry_date_year")->getData(),
                "expiry_date_month" => $form->get("expiry_date_month")->getData(),
                "cvv" => $form->get("cvv")->getData(),
            ));

            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:postAddCreditCard', array('_format' => 'json'));
            $response = json_decode($insertionAnswer->getContent());
            $methodId = $response->{'response'}->{'method-id'};
            if ($insertionAnswer->getStatusCode() != 201) {
                return $this->render('RocketSellerTwoPickBundle:Registration:expressPaymentMethod.html.twig', array(
                            'form' => $form->createView(),
                            'errno' => "Not a valid Credit Card check the data again"
                ));
            }

            /* return $this->render('RocketSellerTwoPickBundle:Registration:cardSuccess.html.twig', array(
              'data' => $data,
              )); */
            //return $this->redirectToRoute('express_pay_start',array('id'=>$methodId));
            return $this->startExpressPayAction($methodId);
        }
        return $this->render('RocketSellerTwoPickBundle:Registration:expressPaymentMethod.html.twig', array(
                    'form' => $form->createView(),
        ));
    }

    public function startExpressPayAction($methodId)
    {
        $user = $this->getUser();
        $product = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Product')
                ->findOneBySimpleName("PRE");
        $tax = ($product->getTaxTax() != null) ? $product->getTaxTax()->getValue() : 0;
        $totalValue = $product->getPrice() * (1 + $tax);
        return $this->render('RocketSellerTwoPickBundle:Registration:payRegisterExpress.html.twig', array(
                    'product' => $product,
                    'totalValue' => $totalValue,
                    'methodId' => $methodId
                        )
        );
    }

    public function payRegisterExpressAction($id)
    {

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $format = array('_format' => 'json');
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:ExpressRegistrationRest:postPayRegisterExpress', array('id' => $user->getId(), 'idPayMethod' => $id), $format);

        if ($insertionAnswer->getStatusCode() == 200) {
            return $this->redirectToRoute('express_success');
        } elseif ($insertionAnswer->getStatusCode() == 404) {
            dump("Procesando");
            exit();
        } else {
            dump($insertionAnswer);
            exit();
        }
    }

    public function successExpressAction()
    {
        $user = $this->getUser();
        $role = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Role')
                ->findOneByName("ROLE_BACK_OFFICE");

        $notification = new Notification();
        $notification->setPersonPerson($user->getPersonPerson());
        $notification->setType("Registro express");
        $notification->setAccion("Registrar usuario");
        $notification->setRoleRole($role);
        $em = $this->getDoctrine()->getManager();
        $em->persist($notification);
        $em->flush();

        return $this->render('RocketSellerTwoPickBundle:Registration:expressSuccess.html.twig');
    }

    public function menuEmployerAction($id)
    {
        $suma = 0;
        $em = $this->getDoctrine()->getManager();
        $person = $em->getRepository('RocketSellerTwoPickBundle:Person')->find($id);
        $user = $em->getRepository('RocketSellerTwoPickBundle:User')->findByPersonPerson($person);
        $employer = $em->getRepository('RocketSellerTwoPickBundle:Employer')->findOneByPersonPerson($person);
    
        if (sizeof($employer->getEmployerHasEmployees())>0 ) {
            foreach ($employer->getEmployerHasEmployees() as $employerHasEmployee) {
                $suma += $employerHasEmployee->getEmployeeEmployee()->getRegisterState();
            }

            $promedio = $suma / sizeof($employer->getEmployerHasEmployees());
        } else {
            $promedio = -1;
        }

        return $this->render('RocketSellerTwoPickBundle:BackOffice:menuEmployer.html.twig', array('employer' => $employer, 'employeesState' => $promedio));
    }

    public function registrationAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $person = $em->getRepository('RocketSellerTwoPickBundle:Person')->find($id);
        $user = $em->getRepository('RocketSellerTwoPickBundle:User')->findByPersonPerson($person);

        $entityTypeRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:EntityType");
        $entityTypes = $entityTypeRepo->findAll();
        $severances = null;
        $arls = null;


        /** @var EntityType $entityType */
        foreach ($entityTypes as $entityType) {
            if ($entityType->getName() == (isset($configData['ARL']) ? $configData['ARL'] : "ARL")) {
                $arls = $entityType->getEntities();
            }
            if ($entityType->getName() == (isset($configData['CC Familiar']) ? $configData['CC Familiar'] : "CC Familiar")) {
                $severances = $entityType->getEntities();
            }
        }

        $employer = $em->getRepository('RocketSellerTwoPickBundle:Employer')->findByPersonPerson($person);
        $workplace = new Workplace();
        $employer[0]->addWorkplace($workplace);

        if (!$person) {
            throw $this->createNotFoundException(
                    'No news found for id ' . $id
            );
        }
        $form = $this->createForm(new EmployerRegistration($severances,$arls), $employer[0]);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $employer[0]->setRegisterState(100);
            $em->flush();
            return $this->redirectToRoute('back_employer_menu', array('id' => $employer[0]->getPersonPerson()->getIdPerson()));
        }
        return $this->render(
            'RocketSellerTwoPickBundle:BackOffice:registerExpress.html.twig', array('form' => $form->createView())
        );
    }

    public function employeeCreateAction($id, $idEmployee, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $personEmployer = $em->getRepository('RocketSellerTwoPickBundle:Person')->find($id);
        $user = $em->getRepository('RocketSellerTwoPickBundle:User')->findOneByPersonPerson($personEmployer);
        $employerSearch = $em->getRepository('RocketSellerTwoPickBundle:Employer')->findOneByPersonPerson($personEmployer);
        $employer = $employerSearch;
        $workplaces = $employer->getWorkplaces();
        if ($idEmployee == -1) {
            $employerHasEmployee = new EmployerHasEmployee();
            $contract = new Contract();
            $employerHasEmployee->addContract($contract);
            $employee = new Employee();
            $person = new Person();
            // $phone = new Phone();
            // $person->addPhone($phone);
            $employee->setPersonPerson($person);
        }
        $form = $this->createForm(new BasicEmployeePersonRegistration(), $person);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $employee->setRegisterState(20);
            $em->persist($employee);
            $em->flush();
            $employerHasEmployee->setEmployerEmployer($employer);
            $employerHasEmployee->setEmployeeEmployee($employee);
            $em->persist($employerHasEmployee);
            $em->flush();

            return $this->redirectToRoute('back_employer_menu', array('id' => $employer->getPersonPerson()->getIdPerson()));
        }
        return $this->render(
                        'RocketSellerTwoPickBundle:BackOffice:ExpressEmployeeRegister.html.twig', array('form' => $form->createView())
        );
    }

}
