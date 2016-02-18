<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Form\Type\ContactType;
use RocketSeller\TwoPickBundle\Form\PagoMembresiaForm;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\Workplace;
use RocketSeller\TwoPickBundle\Form\EmployerRegistration;
use RocketSeller\TwoPickBundle\Form\PersonEmployeeRegistration;
use RocketSeller\TwoPickBundle\Form\EmployerEdit;

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
        $em = $this->getDoctrine()->getManager();
        $person = $em->getRepository('RocketSellerTwoPickBundle:Person')->find($id);
        $user  = $em->getRepository('RocketSellerTwoPickBundle:User')->findByPersonPerson($person);
        $employer  = $em->getRepository('RocketSellerTwoPickBundle:Employer')->findByPersonPerson($person);
        if (sizeof($employer[0]->getEmployerHasEmployees())>0) {
            foreach ($employer[0]->getEmployerHasEmployees() as $employerHasEmployee) {
                $suma = $suma + $employerHasEmployee->getemployeeEmployee()->getRegisterState();                
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
    public function employeeCreateAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $person = $em->getRepository('RocketSellerTwoPickBundle:Person')->find($id);
        $user  = $em->getRepository('RocketSellerTwoPickBundle:User')->findByPersonPerson($person);
        $employer  = $em->getRepository('RocketSellerTwoPickBundle:Employer')->findByPersonPerson($person);
        $employee = new Employee();
        if (!$person) {
          throw $this->createNotFoundException(
                  'No news found for id ' . $id
          );
        }
        $workplaces = $employer[0]->getWorkplaces();
        $form = $this->createForm(new PersonEmployeeRegistration($employee->getIdEmployee(),$workplaces),$employee);
        $form->handleRequest($request);

        if ($form->isValid()) {                            
            $em->flush();
            return $this->redirectToRoute('express_info');
        }
        return $this->render(
            'RocketSellerTwoPickBundle:BackOffice:ExpressEmployeeRegister.html.twig',
            array('form' => $form->createView())
        );
    }

}
