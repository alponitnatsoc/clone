<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\LandingRegistration;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Form\PublicCalculator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Form\Type\ContactType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class PublicController extends Controller
{
	public function homeAction(Request $request) {
        $user=$this->getUser();
        if (empty($user)) {
            $form = $this->createFormBuilder()
                ->add('save', 'submit', array('label' => 'Obtén 1 mes gratis'))
                ->getForm();
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $result = $request->request->all();
                $landRes = new LandingRegistration();
                $landRes->setEmail($result['email']);
                $landRes->setName($result['firstname']." ".$result['lastname']);
                $landRes->setCreatedAt(new \DateTime());
                $landRes->setPhone($result['cellphone']);
                $landRes->setEntityType("persona");
                $em= $this->getDoctrine()->getEntityManager();
                $em->persist($landRes);
                $em->flush();
                $request->request->add(array(
                    "nname"=>$result['firstname'],
                    "nlast"=>$result['lastname'],
                    "nemail"=>$result['email'],
                ));
                return $this->forward('RocketSellerTwoPickBundle:Registration:register', array('request' => $request), array('_format' => 'json'));
            }
            return $this->render('RocketSellerTwoPickBundle:Public:home.html.twig', array(
                'form' => $form->createView()));
        } else {
            return $this->redirectToRoute('welcome_post_register');
        }

    }

    public function beneficiosAction() {
        return $this->render('RocketSellerTwoPickBundle:Public:beneficios.html.twig', array(
            "breadcrumbs" => array("Inicio" => "/", "Beneficios" => "")
        ));
    }

    public function calculadoraAction() {
	      $timeCommitments = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:ContractType")->findAll();
		    $form = $this->createForm(new PublicCalculator($timeCommitments));
        return $this->render('RocketSellerTwoPickBundle:Public:calculadora.html.twig');
    }

    public function preciosAction() {
        return $this->render('RocketSellerTwoPickBundle:Public:precios.html.twig', array(
            "breadcrumbs" => array("Inicio" => "/", "Precios" => "")
        ));
    }

    public function nosotrosAction() {
        return $this->render('RocketSellerTwoPickBundle:Public:nosotros.html.twig', array(
            "breadcrumbs" => array("Inicio" => "/", "Nosotros" => "")
        ));
    }

    public function ayudaAction() {
        return $this->render('RocketSellerTwoPickBundle:Public:ayuda.html.twig');
    }

    public function blogAction() {
        return $this->render('RocketSellerTwoPickBundle:Public:blog.html.twig');
    }

    
    public function contactenosAction(Request $request) {
        
        $form = $this->createForm(new ContactType('','',''), array('method'=> 'POST'));
        $form->handleRequest($request);
        if ($request->getMethod() == 'POST') {
            if ($form->isValid()) {
                switch($form->get('subject')->getData()){
                    case 0:
                        $sub = 'Preguntas del Registro';
                        break;
                    case 1:
                        $sub ='Preguntas de pago de nómina y aportes';
                        break;
                    case 2:
                        $sub = 'Preguntas sobre la calculadora salarial';
                        break;
                    case 3:
                        $sub = 'Consulta jurídica';
                        break;
                    case 4:
                        $sub = 'Consulta de planes y precios';
                        break;
                    case 5:
                        $sub = 'Otros';
                        break;
                }
                $send = $this->get('symplifica.mailer.twig_swift')->sendHelpEmailMessage($form->get('name')->getData(),$form->get('email')->getData(),$sub,$form->get('message')->getData(),$request->getClientIp(),$form->get('phone')->getData());
                if($send){
                    $this->addFlash('success', 'Tu email ha sido enviado. Nos pondremos en contacto en menos de 24 horas');
                }else{
                    $this->addFlash('fail','Ocurrio un error');
                }
                
                return $this->redirect($this->generateUrl('contactenos'));
            }
        }
        return $this->render('RocketSellerTwoPickBundle:Public:contactenos.html.twig', array(
            'form' => $form->createView()
        ));

    }

    public function faqAction() {
        return $this->render('RocketSellerTwoPickBundle:Public:FAQ.html.twig', array(
            "breadcrumbs" => array("Inicio" => "/", "Contáctenos" => "")
        ));
    }


    public function productoAction() {
        return $this->render('RocketSellerTwoPickBundle:Public:producto.html.twig', array(
            "breadcrumbs" => array("Inicio" => "/", "Contáctenos" => "")
        ));
    }

    /**
     * @Route("/categories/{redirectedBy}", name="called_by")
     */
    public function categoriesAction($redirectedBy) {

        return $this->render('RocketSellerTwoPickBundle:Public:categories.html.twig', array(
            "called_from" => $redirectedBy));
    }
}
