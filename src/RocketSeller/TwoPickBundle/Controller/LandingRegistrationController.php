<?php
// src/AppBundle/Controller/RegistrationController.php
namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use RocketSeller\TwoPickBundle\Form\LandingRegistrationForm;
use RocketSeller\TwoPickBundle\Entity\LandingRegistration;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class LandingRegistrationController extends Controller
{
    /**
     * @Route("/", name="prueba_landing")
     */
    public function registerAction(Request $request)
    {
        // 1) build the form
        $user = new LandingRegistration();
        $form = $this->createForm(new LandingRegistrationForm(), $user);
               //$this->createForm(new EmployeeBeneficiaryRegistration(), $beneficiary);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // 3) Encode the password (you could also do this via Doctrine listener)

            // 4) save the User!
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirect($this->generateUrl('home_landing_form') . '#thanks');
        }

        return $this->render(
            'RocketSellerTwoPickBundle:General:landing_with_form.html.twig',
            array('form' => $form->createView())
        );
    }
}
