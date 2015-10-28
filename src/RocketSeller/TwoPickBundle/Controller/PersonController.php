<?php 
namespace RocketSeller\TwoPickBundle\Controller;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\Beneficiary;
use RocketSeller\TwoPickBundle\Entity\Workplace;
use RocketSeller\TwoPickBundle\Entity\Entity;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Form\EmployerRegistration;
use RocketSeller\TwoPickBundle\Form\PersonEmployeeRegistration;
use RocketSeller\TwoPickBundle\Form\PersonBeneficiaryRegistration;
use Doctrine\Common\Collections\ArrayCollection;

class PersonController extends Controller
{	
	/**
    * Maneja el registro de una nueva persona con los datos básicos, 
    * @param el Request que manjea el form que se imprime
    * @return La vista de el formulario de la nueva persona
	**/
    public function newPersonAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();
        $people = $user->getPersonPerson();
        if ($people!=null) {
            return $this->forward("RocketSellerTwoPickBundle:Person:editPerson", array('request' => $request));
        }
        $people = new Person();
        $employer = new Employer();
        $workplace = new Workplace();
        $employer->addWorkplace($workplace);
        $people->setEmployer($employer);
        $form = $this->createForm(new PersonRegistration(), $people);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($people);
            $em->flush();
            $user=$this->getUser();
            $user->setPersonPerson($people);
            $em->persist($people);
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('show_dashboard');
        }

        return $this->render(
            'RocketSellerTwoPickBundle:Registration:newPerson.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
    * Maneja el registro de un beneficiario a un empleado con los datos básicos,
    * @param el Request que maneja el form que se imprime
    * @return La vista de el formulario de la nueva persona
    **/
    public function addBeneficiaryAction(Request $request, Employee $employee, Entity $entity=null) {

        if(is_null($entity)) {
            $entities = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:EmployeeHasEntity')
                ->findByEmployeeEmployee($employee);
            return $this->render(
                'RocketSellerTwoPickBundle:Registration:addBeneficiarySelectEntity.html.twig',
                array(
                    'entities' => $entities,
                    'employee' => $employee
                )
            );
        } else {
            $beneficiary = new Beneficiary();
            $form = $this->createForm(new PersonBeneficiaryRegistration(), $beneficiary);
            return $this->render(
                'RocketSellerTwoPickBundle:Registration:addBeneficiary.html.twig',
                array('form' => $form->createView())
            );
        }
    }
    /**
    * Maneja la edición de una  persona con los datos básicos, 
    * @param el Request que manjea el form que se imprime
    * @return La vista de el formulario de editar persona
    **/
    public function editPersonAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user=$this->getUser();
        $people = $user->getPersonPerson();
        if ($people==null) {
            return $this->forward("RocketSellerTwoPickBundle:Person:newPerson", array('request' => $request));
        }
        $employer = $people->getEmployer();
        if ($employer==null) {
            $employer=new Employer();
        }
        $workplaces = new ArrayCollection();

        foreach ($employer->getWorkplaces() as $work) {
            $workplaces->add($work);
        }
        if (count($workplaces)==0) {
            $workplace = new Workplace();
            $employer->addWorkplace($workplace);
            $people->setEmployer($employer);
        }

        $form = $this->createForm(new EmployerRegistration(), $employer);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            foreach ($workplaces as $work) {
                if (false === $employer->getWorkplaces()->contains($work)) {
                    // remove the Task from the Tag
                    $work->setEmployerEmployer(null);
                    $em->persist($work);
                    $em->remove($work);
                }
            }
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('show_dashboard');
        }

        return $this->render(
            'RocketSellerTwoPickBundle:Registration:newPerson.html.twig',
            array('form' => $form->createView())
        );
    }
    /**
    * Maneja el formulario de un nuevo empleado
    * @param el Request y el Id del empleado, si lo desean editar
    * @return La vista de el formulario de la nuevo empleado
    **/
    public function newEmployeeAction(Request $request, $id)
    {
        $user=$this->getUser();
        $employee;
        if ($id==-1) {
            $employee= new Employee();
        }else{
            $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Employee');
            $employee= $repository->find($id);
        }
        
        $form = $this->createForm(new PersonEmployeeRegistration(), $employee);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $employerEmployee = new EmployerHasEmployee();
            $employerEmployee->setEmployerEmployer($user->getPersonPerson()->getEmployer());
            $employerEmployee->setEmployeeEmployee($employee);
            $em = $this->getDoctrine()->getManager();
            $em->persist($employee);
            $em->flush();
            $em->persist($employerEmployee);
            $em->flush();
            return $this->redirectToRoute('manage_employees');
        }
        
        return $this->render(
            'RocketSellerTwoPickBundle:Registration:EmployeeForm.html.twig',
            array('form' => $form->createView())
        );
    }
    /**
    * el dashboard de los empleados de cada empleador que le permite editar la información
    * y agregar nuevos empleados
    * TODO eliminar empleados
    * @param el Request que manjea el form que se imprime
    * @return La vista de el formulario manager
    **/
    public function manageEmployeesAction(Request $request)
    {
        $user=$this->getUser();
        $employeesData=$user->getPersonPerson()->getEmployer()->getEmployerHasEmployees();
        return $this->render(
            'RocketSellerTwoPickBundle:Employee:employeeManager.html.twig',array(
                'employees'=>$employeesData)
        );
    }
}
?>