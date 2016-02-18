<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Form\Type\ContactType;
use RocketSeller\TwoPickBundle\Form\PagoMembresiaForm;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Phone;
use RocketSeller\TwoPickBundle\Entity\Workplace;
use RocketSeller\TwoPickBundle\Form\EmployerRegistration;
use RocketSeller\TwoPickBundle\Form\PersonEmployeeRegistration;
use RocketSeller\TwoPickBundle\Form\EmployerEdit;
use RocketSeller\TwoPickBundle\Form\BasicEmployeePersonRegistration;

class ExpressRegistrationController extends Controller
{
    public function expressPaymentAction($id)
    {
        $user = $this->getDoctrine()
        ->getRepository('RocketSellerTwoPickBundle:User')
        ->find($id);
        return $this->render('RocketSellerTwoPickBundle:Registration:expressPayment.html.twig',array('user'=>$user));
    }
    public function successExpressAction($id)
    {
        $user = $this->getDoctrine()
        ->getRepository('RocketSellerTwoPickBundle:User')
        ->find($id);
        $role = $this->getDoctrine()
        ->getRepository('RocketSellerTwoPickBundle:Role')
        ->findByName("ROLE_BACK_OFFICE");
        
        $notification = new Notification();
        $notification->setPersonPerson($user->getPersonPerson());
        $notification->setType("Registro express");
        $notification->setAccion("Registrar usuario");
        $notification->setRoleRole($role[0]);
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
        $user  = $em->getRepository('RocketSellerTwoPickBundle:User')->findByPersonPerson($person);
        $employer  = $em->getRepository('RocketSellerTwoPickBundle:Employer')->findByPersonPerson($person);
        if (sizeof($employer[0]->getEmployerHasEmployees())>0) {
            foreach ($employer[0]->getEmployerHasEmployees() as $employerHasEmployee) {
                $suma += $employerHasEmployee->getEmployeeEmployee()->getRegisterState();                
            }
            
            $promedio = $suma/sizeof($employer[0]->getEmployerHasEmployees());
        }else{
            $promedio = -1;
        }

        return $this->render('RocketSellerTwoPickBundle:BackOffice:menuEmployer.html.twig',array('employer'=>$employer[0],'employeesState'=>$promedio)); 
    }
    public function registrationAction($id, Request $request)
    {   
        $em = $this->getDoctrine()->getManager();
        $person = $em->getRepository('RocketSellerTwoPickBundle:Person')->find($id);
        $user  = $em->getRepository('RocketSellerTwoPickBundle:User')->findByPersonPerson($person);
        $employer  = $em->getRepository('RocketSellerTwoPickBundle:Employer')->findByPersonPerson($person);
        $workplace = new Workplace();
        $employer[0]->addWorkplace($workplace);

        if (!$person) {
          throw $this->createNotFoundException(
                  'No news found for id ' . $id
          );
        }
        $form = $this->createForm(new EmployerRegistration(),$employer[0]);
        $form->handleRequest($request);

        if ($form->isValid()) {                    
            $employer[0]->setRegisterState(100);                    
            $em->flush();
            return $this->redirectToRoute('back_employer_menu',array('id'=>$employer[0]->getPersonPerson()->getIdPerson()));
        }
        return $this->render(
            'RocketSellerTwoPickBundle:BackOffice:startExpressRegister.html.twig',
            array('form' => $form->createView())
        );
    }
    public function employeeCreateAction($id,$idEmployee, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $personEmployer = $em->getRepository('RocketSellerTwoPickBundle:Person')->find($id);
        $user[0]  = $em->getRepository('RocketSellerTwoPickBundle:User')->findByPersonPerson($personEmployer);
        $employerSearch  = $em->getRepository('RocketSellerTwoPickBundle:Employer')->findByPersonPerson($personEmployer);
        $employer = $employerSearch[0];        
        $workplaces = $employer->getWorkplaces();
        if ($idEmployee == -1) {
            $employerHasEmployee = new EmployerHasEmployee();
            $employee = new Employee();                    
            $person = new Person();                        
            $phone = new Phone();            
            $person->addPhone($phone);
            $employee->setPersonPerson($person);            
        }
        $form = $this->createForm(new BasicEmployeePersonRegistration(),$person);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $employee->setRegisterState(20);            
            $em->persist($employee);                                                        
            $em->flush();
            $employerHasEmployee->setEmployerEmployer($employer);
            $employerHasEmployee->setEmployeeEmployee($employee);
            $em->persist($employerHasEmployee);
            $em->flush();

            return $this->redirectToRoute('back_employer_menu',array('id'=>$employer->getPersonPerson()->getIdPerson()));
        }
        return $this->render(
            'RocketSellerTwoPickBundle:BackOffice:ExpressEmployeeRegister.html.twig',
            array('form' => $form->createView())
        );

        

    }

}
