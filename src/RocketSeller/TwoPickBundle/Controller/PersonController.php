<?php 
namespace RocketSeller\TwoPickBundle\Controller;
use RocketSeller\TwoPickBundle\Entity\Person;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
class PersonController extends Controller
{	
	/**
    *
	**/
    public function newPersonAction(Request $request)
    {
        $people = new Person();

        $form = $this->createFormBuilder($people)
            ->add('fisrtName', 'text')
            ->add('surName', 'text')
            ->add('lastName', 'text')
            ->add('save', 'submit', array('label' => 'Create User'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $user=$this->getUser();
            $em = $this->getDoctrine()->getManager();
            $em->persist($people);
            $em->flush();
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
}
?>