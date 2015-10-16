<?php 
namespace RocketSeller\TwoPickBundle\Controller;
use RocketSeller\TwoPickBundle\Entity\Person;
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
    /*
    public function newPersonAction(Request $request)
    {
        $people = new Person();

        $form = $this->createFormBuilder($people)
            ->add('fisrtName', 'text')
            ->add('surName', 'text')
            ->add('lastName', 'text')
            ->add('save', 'submit', array('label' => 'Create'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($people);
            $em->flush();
            $user=$this->getUser();
            $realUser = $this->getDoctrine()->getRepository('RocketSeller\TwoPickBundle\Entity\User')->findOneBy( array('username' => $user->getUsername()) );
            $realUser->setPersonPerson($people);
            $em->persist($people);
            $em->persist($realUser);
            $em->flush();


            return $this->redirectToRoute('rocket_seller_two_pick_homepage');
        }
        return $this->render('RocketSellerTwoPickBundle:Registration:newPerson.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    */
    public function newPersonAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $people = new Person();
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
            'RocketSellerTwoPickBundle:Registration:testNewPerson.html.twig',
            array('form' => $form->createView())
        );
    }

}
?>