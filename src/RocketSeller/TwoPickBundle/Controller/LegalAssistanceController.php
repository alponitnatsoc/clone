<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Employer;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Phone;
use RocketSeller\TwoPickBundle\Entity\Notification;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use RocketSeller\TwoPickBundle\Form\AddCreditCard;

class LegalAssistanceController extends Controller
{
	public function indexAction(){

		$totalValue = $this->getPaymentValue();
		return $this->render('RocketSellerTwoPickBundle:legalAssistance:index.html.twig',
			array(
				'totalValue'=>floor($totalValue)
				)
			);
	}
	public function legalAcceptanceAction($state){

		return $this->render('RocketSellerTwoPickBundle:legalAssistance:legalAcceptance.html.twig',
            array('state'=>$state));
	}
	public function startPaymentAction(Request $request){
        $user = $this->getUser();
        $person = $user->getPersonPerson();
        $date = new \DateTime();
                $date->add(new \DateInterval('P1M'));
                $startDate = $date->format('Y-m-d');
                $minMont = $date->format('m');
                $minYear = $date->format('Y');

        $form = $this->get('form.factory')->createNamedBuilder('legalAssistancePay', 'form', array(), array(
                            'action' => $this->generateUrl('legal_payment'),
                            //'action' => $this->generateUrl('subscription_confirm'),
                            'method' => 'POST'))
                        ->add('name_on_card', 'text', array(
                            'label' => 'Nombre en la tarjeta',
                            'required' => true,
                            'attr' => array('placeholder' => 'Nombre en la tarjeta')
                        ))
                        ->add('credit_card', 'integer', array(
                            'label' => 'Número tarjeta de crédito',
                            'required' => true,
                            'attr' => array(
                                'placeholder' => '1234 5678 9012 3456',
                                'min' => 1,
                                'step' => 1
                            )
                        ))
                        ->add('expiry_month', 'integer', array(
                            'label' => 'Fecha de vencimiento',
                            'required' => true,
                            'attr' => array(
                                'placeholder' => 'Mes',
                                'min' => 01,
                                'max' => 12,
                                'maxlength' => 2,
                                'minlength' => 1,
                                'step' => 1
                            )
                        ))
                        ->add('expiry_year', 'integer', array(
                            'label' => 'Fecha de vencimiento',
                            'required' => true,
                            'attr' => array(
                                'placeholder' => 'Año',
                                'min' => $minYear,
                                'max' => 9999,
                                'maxlength' => 4,
                                'minlength' => 4,
                                'step' => 1
                            )
                        ))
                        ->add('cvv', 'integer', array(
                            'label' => 'Código de seguridad:',
                            'required' => true,
                            'attr' => array(
                                'placeholder' => '123',
                                'min' => 1,
                                'max' => 9999,
                                'maxlength' => 4,
                                'minlength' => 3,
                                'step' => 1
                            )
                        ))
                        ->add('titularName', 'text', array(
                            'label' => 'Titular de la cuenta',
                            'required' => true,
                            'attr' => array(
                                'placeholder' => 'Nombre titular de la cuenta',
                            )
                        ))
                        ->add('documentType', 'choice', array(
                                'choices' => array(
                                    'CC'   => 'Cédula de ciudadanía',
                                    'CE' => 'Cedula de extranjería',
                                    'TI' => 'Tarjeta de identidad'
                                ),
                                'multiple' => false,
                                'expanded' => false,
                                'label' => 'Tipo de documento*',
                                'placeholder' => 'Seleccionar una opción',
                                'required' => true
                            ))
                        ->add('documentNumber', 'text', array(
                            'label' => 'Número de documento',
                            'required' => true,
                            'attr' => array(
                                'placeholder' => 'Número de documento',
                                'value' => $person->getDocument()
                            )
                        ))
                        ->add('phoneNumber', 'text', array(
                            'label' => 'Teléfono',
                            'required' => true,
                            'attr' => array(
                                'placeholder' => 'Número de teléfono',
                            )
                        ))
                        ->add('bank', 'entity', array(
                            'label' => 'Banco',
                            'required' => false,
                            'class' => 'RocketSellerTwoPickBundle:Bank',
                            'empty_value' => 'Seleccione',
                            'choice_label' => 'name'
                        ))
                        ->add('accountType', 'entity', array(
                            'label' => 'Tipo de Cuenta',
                            'required' => false,
                            'class' => 'RocketSellerTwoPickBundle:AccountType',
                            'empty_value' => 'Seleccione',
                            'choice_label' => 'name'
                        ))
                        ->add('numberAccount', 'integer', array(
                            'label' => 'Número de la cuenta',
                            'required' => false,
                            'attr' => array(
                                'placeholder' => 'Número de la cuenta',
                                'min' => 1,
                                'minlength' => 1,
                                'step' => 1
                            )
                        ))
                        ->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
        	$em = $this->getDoctrine()->getManager();
            $user = $this->getUser();
            /** @var Person $person */
            $methodType = $request->get('methodType');
            if ($methodType == "cre") {
                dump("se quiere pagar con credito");
                exit();
            }else if($methodType == "deb"){
                dump("se quiere pagar con debito");
                exit();
            }
            $person = $user->getPersonPerson();
            $person->setDocumentType($form->get("documentType")->getData());
            $person->setDocument($form->get("documentNumber")->getData());
            $phone = new Phone();
            $phone->setPhoneNumber($form->get("phoneNumber")->getData());
            $person->setDocument($form->get("documentNumber")->getData());
            $person->addPhone($phone);
            $employer = new Employer();
            $employer->setPersonPerson($person);
            $employer->setEmployerType("Persona");
            $employer->setRegisterState(10);
            $em->persist($employer);
            $em->persist($phone);
            $em->persist($person);
            $em->flush();

            $data = $form->getData();

            //TODO NovoPayment
            $request->setMethod("POST");
            $request->request->add(array(
                "documentType" => $person->getDocumentType(),
                "documentNumber" => $person->getDocument(),
                "credit_card" => $form->get("credit_card")->getData(),
                "expiry_date_year" => $form->get("expiry_year")->getData(),
                "expiry_date_month" => $form->get("expiry_month")->getData(),
                "cvv" => $form->get("cvv")->getData(),
            ));

            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:postAddCreditCard', array('_format' => 'json'));

            $response = json_decode($insertionAnswer->getContent());
            $methodId = $response->{'response'}->{'method-id'};

            if ($insertionAnswer->getStatusCode() != 201) {

                return $this->render('RocketSellerTwoPickBundle:legalAssistance:paymentMethod.html.twig', array(
                            'form' => $form->createView(),
                            'errno' => "Not a valid Credit Card check the data again"
                ));
            }


            return $this->payLegalAssistance($methodId);
        }
        return $this->render('RocketSellerTwoPickBundle:legalAssistance:paymentMethod.html.twig', array(
                    'form' => $form->createView(),
        ));
	}
	public function getPaymentValue()
	{
		$user = $this->getUser();
        $product = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Product')
                ->findOneBySimpleName("PAL");
        $tax = ($product->getTaxTax() != null) ? $product->getTaxTax()->getValue() : 0;
        $totalValue = $product->getPrice() * (1 + $tax);
        return $totalValue;
	}
    public function payLegalAssistance($id)
    {

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $format = array('_format' => 'json');
        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:ExpressRegistrationRest:postPayRegisterExpress', array('id' => $user->getId(), 'idPayMethod' => $id), $format);

        if ($insertionAnswer->getStatusCode() == 200) {
        	$flag = $this->changeFlagAction();
            return $this->successExpress();
            exit();

            //return $this->redirectToRoute('DashBoard');
        } elseif ($insertionAnswer == 404) {
            dump("Procesando");
            exit();
        } else {
            dump($insertionAnswer);
            exit();
        }
    }
    public function changeFlagAction($flag)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var User $user */
        $user = $this->getUser();
        if($user->getLegalFlag()!=-1){
            $urlToSend='edit_profile';
        }else{
            $urlToSend='register_employee';
        }
        $user->setLegalFlag($flag);
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute($urlToSend);
    }
    public function successExpress()
    {
        $user = $this->getUser();
        $role = $this->getDoctrine()
                ->getRepository('RocketSellerTwoPickBundle:Role')
                ->findByName("ROLE_BACK_OFFICE");

        $notification = new Notification();
        $notification->setPersonPerson($user->getPersonPerson());
        $notification->setType("Asistencia legal");
        $notification->setAccion("Asistir a usuario");
        $notification->setRoleRole($role[0]);
        $em = $this->getDoctrine()->getManager();
        $em->persist($notification);
        $em->flush();

        return $this->render('RocketSellerTwoPickBundle:legalAssistance:paymentSuccess.html.twig');
    }


}

