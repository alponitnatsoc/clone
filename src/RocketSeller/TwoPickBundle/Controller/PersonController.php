<?php 
namespace RocketSeller\TwoPickBundle\Controller;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\Employee;
use RocketSeller\TwoPickBundle\Entity\Workplace;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Form\PersonRegistration;
use RocketSeller\TwoPickBundle\Form\PersonEmployeeRegistration;
use Doctrine\Common\Collections\ArrayCollection;
class PersonController extends Controller
{	
	/**
    * Maneja el registro de una nueva persona con los datos básicos, 
    * TODO agregar todos los campos de los wireframes
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

        $form = $this->createForm(new PersonRegistration(), $people);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            foreach ($workplaces as $work) {
                if (false === $people->getEmployer()->getWorkplaces()->contains($work)) {
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
    public function newEmployeeAction(Request $request)
    {
        $user=$this->getUser();
        $employee= new Employee();
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
            return $this->redirectToRoute('show_dashboard');
        }
        
        return $this->render(
            'RocketSellerTwoPickBundle:Registration:EmployeeForm.html.twig',
            array('form' => $form->createView())
        );
    }

}
?>