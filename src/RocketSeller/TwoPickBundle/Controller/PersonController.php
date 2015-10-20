<?php 
namespace RocketSeller\TwoPickBundle\Controller;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\Workplace;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Form\PersonRegistration;
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
        $people = $user->getPersonPerson();
        if ($people==null) {
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

            return $this->redirectToRoute('rocket_seller_two_pick_homepage');
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
        $workplaces = $employer->getWorkplaces();
        if ($workplaces==null) {
            $workplaces = new Workplace();
            $employer->addWorkplace($workplace);
            $people->setEmployer($employer);
        }

        $form = $this->createForm(new PersonRegistration(), $people);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($people);
            $em->flush();
            $user->setPersonPerson($people);
            $em->persist($people);
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('rocket_seller_two_pick_homepage');
        }

        return $this->render(
            'RocketSellerTwoPickBundle:Registration:newPerson.html.twig',
            array('form' => $form->createView())
        );
    }

}
?>