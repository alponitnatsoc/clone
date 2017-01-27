<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\LandingRegistration;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\UserHasConfig;
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
                $landRes->setType("mesgratis");
                $em= $this->getDoctrine()->getEntityManager();
                $em->persist($landRes);
                $em->flush();
                $request->request->add(array(
                    "nname"=>$result['firstname'],
                    "nlast"=>$result['lastname'],
                    "nemail"=>$result['email'],
                    "nphone"=>$result['cellphone'],
                ));
                return $this->forward('RocketSellerTwoPickBundle:Registration:register', array('request' => $request), array('_format' => 'json'));
            }
            return $this->render('RocketSellerTwoPickBundle:Public:home.html.twig', array(
                'form' => $form->createView()));
        } else {
            return $this->redirectToRoute('welcome_post_register');
        }

    }
	public function salarioAction(Request $request) {
		return $this->redirectToRoute("salario_min_actual");
	}
	public function salario2017Action(Request $request) {
		return $this->render('RocketSellerTwoPickBundle:Public:salario.html.twig');
	}
	public function landingAction(Request $request) {
		return $this->render('RocketSellerTwoPickBundle:Public:campana.html.twig');
        $user=$this->getUser();
        if (empty($user)) {
            $form = $this->createFormBuilder()
                ->add('save', 'submit', array('label' => '¡Quiero mis $150.000!'))
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
                $landRes->setType("lohagobien");
                //TODO-Andres Enviar un correo diciendole que lso dátos fueron recibidos y que todo bien que si quiere continuar puede seguir en la página del registro
                //email to send
                $email=$landRes->getEmail();

                $em= $this->getDoctrine()->getEntityManager();
                $em->persist($landRes);
                $em->flush();
                $request->request->add(array(
                    "nname"=>$result['firstname'],
                    "nlast"=>$result['lastname'],
                    "nemail"=>$result['email'],
                    "nphone"=>$result['cellphone'],
                ));
                return $this->forward('RocketSellerTwoPickBundle:Registration:register', array('request' => $request), array('_format' => 'json'));
            }
            return $this->render('RocketSellerTwoPickBundle:Public:campana.html.twig', array(
                'form' => $form->createView()));
        } else {
            return $this->redirectToRoute('welcome_post_register');
        }

    }
	public function expressLandingAction(Request $request) {
        $user=$this->getUser();
        if (empty($user)) {
            $answer = $request->request->all();

            if (isset($answer["firstname"])&&isset($answer["email"])&&isset($answer["cellphone"])&&isset($answer["lastname"])) {

                $landRes = new LandingRegistration();
                $landRes->setEmail($answer['email']);
                $landRes->setName($answer['firstname']);
                $landRes->setLastName($answer['lastname']);
                $landRes->setCreatedAt(new \DateTime());
                $landRes->setPhone($answer['cellphone']);
                $landRes->setEntityType("persona");
                $landRes->setType("0Esfuezo");
                $mailer = $this->get("symplifica.mailer.twig_swift");
                $context = array(
                    "emailType"=>"sendBackLandingInfo",
                    "email"=>$landRes->getEmail(),
                    "name"=>$landRes->getName()." ".$landRes->getLastName(),
                    "phone"=>$landRes->getPhone(),
                    "createdAt"=>$landRes->getCreatedAt()->format("d-m-Y"),
                );
                $mailer->sendEmailByTypeMessage($context);
                $em= $this->getDoctrine()->getEntityManager();
                $em->persist($landRes);
                $em->flush();
            }
        }
        return $this->redirectToRoute('welcome_post_register');

    }
	public function maintenanceAction(Request $request) {
        return $this->render('RocketSellerTwoPickBundle:Public:mantenimiento.html.twig');
    }
    public function beneficiosAction() {
        return $this->render('RocketSellerTwoPickBundle:Public:beneficios.html.twig', array(
            "breadcrumbs" => array("Inicio" => "/", "Beneficios" => "")
        ));
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
                $send = $this->get('symplifica.mailer.twig_swift')->sendEmailByTypeMessage(array('emailType'=>'help','name'=>$form->get('name')->getData(),'fromEmail'=>$form->get('email')->getData(), 'subject'=>$sub, 'message'=>$form->get('message')->getData(),'ip'=>$request->getClientIp(),'phone'=>$form->get('phone')->getData()));
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

    public function authorizeAutomatedPaymentAction($hash) {
        $uRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
        /** @var User $user */
        $user = $uRepo->findOneBy(array('sHash'=>$hash));
        if($user != null){
            $user->setSHash(null);
            $uhc = new UserHasConfig();
            $configSev = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Configuration")->findOneBy(array('value'=>'Auth-SeverancesPayment'));
            $uhc->setAcceptedAt(new \DateTime());
            $uhc->setUserUser($user);
            $uhc->setConfigurationConfiguration($configSev);
            $em = $this->getDoctrine()->getManager();
            $em->persist($uhc);
            $em->persist($user);
            $em->flush();
        }
        return $this->render('RocketSellerTwoPickBundle:Public:thankYou.html.twig', array());
    }
}
