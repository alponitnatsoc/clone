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
use RocketSeller\TwoPickBundle\Traits\SubscriptionMethodsTrait;

class LegalAssistanceController extends Controller
{
    use SubscriptionMethodsTrait;

    public function indexAction()
    {

        $totalValue = $this->getPaymentValue();
        return $this->render('RocketSellerTwoPickBundle:legalAssistance:index.html.twig',
            array(
                'totalValue' => floor($totalValue)
            )
        );
    }

    public function legalAcceptanceAction($state)
    {

        return $this->render('RocketSellerTwoPickBundle:legalAssistance:legalAcceptance.html.twig',
            array('state' => $state));
    }

    public function startPaymentAction(Request $request)
    {
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
                // 'required' => true,
                'attr' => array('placeholder' => 'Nombre en la tarjeta')
            ))
            ->add('credit_card', 'integer', array(
                'label' => 'Número tarjeta de crédito',
                // 'required' => true,
                'attr' => array(
                    'placeholder' => '1234 5678 9012 3456',
                    'min' => 1,
                    'step' => 1
                )
            ))
            ->add('expiry_month', 'integer', array(
                'label' => 'Fecha de vencimiento',
                // 'required' => true,
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
                // 'required' => true,
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
                // 'required' => true,
                'attr' => array(
                    'placeholder' => '123',
                    'min' => 1,
                    'max' => 9999,
                    'maxlength' => 4,
                    'minlength' => 3,
                    'step' => 1
                )
            ))
            ->add('personName', 'text', array(
                'label' => 'Nombres',
                // 'required' => true,
                'attr' => array(
                    'placeholder' => $user->getPersonPerson()->getNames(),
                )
            ))
            ->add('lastName1', 'text', array(
                'label' => 'Primer apellido',
                'required' => true,
                'attr' => array(
                    'placeholder' => $user->getPersonPerson()->getLastName1(),
                )
            ))
            ->add('lastName2', 'text', array(
                'label' => 'segundo apellido',
                // 'required' => true,
                'attr' => array(
                    'placeholder' => 'Segundo apellido',
                )
            ))
            ->add('civilStatus', 'choice', array(
                'choices' => array(
                    'soltero' => 'Soltero(a)',
                    'casado' => 'Casado(a)',
                    'unionLibre' => 'Union Libre',
                    'viudo' => 'Viudo(a)'
                ),
                'multiple' => false,
                'expanded' => false,
                'label' => 'Estado civil*',
                'placeholder' => 'Seleccionar una opción',
                // 'required' => true
            ))
            ->add('documentType', 'choice', array(
                'choices' => array(
                    'CC' => 'Cédula de ciudadanía',
                    'CE' => 'Cedula de extranjería',
                    'TI' => 'Tarjeta de identidad'
                ),
                'multiple' => false,
                'expanded' => false,
                'label' => 'Tipo de documento*',
                'placeholder' => 'Seleccionar una opción',
                // 'required' => true
            ))
            ->add('documentNumber', 'text', array(
                'label' => 'Número de documento',
                // 'required' => true,
                'attr' => array(
                    'placeholder' => 'Número de documento',
                    'value' => $person->getDocument()
                )
            ))
            ->add('documentExpeditionDate', 'date', array(
                'placeholder' => array(
                    'year' => 'Año', 'month' => 'Mes', 'day' => 'Dia'
                ),
                'years' => range(2015, 1900),
                'label' => 'Fecha de expedición de documento de identidad*',
                // 'required' => true
            ))
            ->add('phoneNumber', 'text', array(
                'label' => 'Teléfono',
                // 'required' => true,
                'attr' => array(
                    'placeholder' => 'Número de teléfono',
                )
            ))
            ->add('department', 'entity', array(
                'label' => 'Departamento*',
                'translation_domain' => 'messages',
                'class' => 'RocketSellerTwoPickBundle:Department',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,

                'placeholder' => 'Seleccionar una opción',
                // 'required' => true
            ))
            ->add('mainAddress', 'text', array(
                'label' => 'Dirección*'
            ))
            ->add('city', 'entity', array(
                'label' => 'Ciudad*',
                'translation_domain' => 'messages',
                'class' => 'RocketSellerTwoPickBundle:City',
                'property' => 'name',
                'multiple' => false,
                'expanded' => false,

                'placeholder' => 'Seleccionar una opción',
                'required' => true
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
            $person->setEmployer($employer);

            $expiryMonth = $form->get("expiry_month")->getData();
            if (strlen($expiryMonth) < 2) {
                $expiryMonth = "0" . $form->get("expiry_month")->getData();
            }
            $data = $form->getData();
            if ($methodType == "cre") {
                $request->setMethod("POST");
                $request->request->add(array(
                    "documentType" => $person->getDocumentType(),
                    "documentNumber" => $person->getDocument(),
                    "credit_card" => $form->get("credit_card")->getData(),
                    "expiry_date_year" => $form->get("expiry_year")->getData(),

                    "expiry_date_month" => $expiryMonth,
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
                dump("se quiere pagar con credito");
                //return $this->payLegalAssistance($methodId);
            } else if ($methodType == "deb") {
                $department = $this->getDoctrine()
                    ->getRepository('RocketSellerTwoPickBundle:Department')
                    ->find($form->get("department")->getData());
                $city = $this->getDoctrine()
                    ->getRepository('RocketSellerTwoPickBundle:City')
                    ->find($form->get("city")->getData());
                $documentExpirationDate = new \DateTime();
                $documentExpirationDate->setDate($form->get("documentExpeditionDate")->getData()->format("Y"), $form->get("documentExpeditionDate")->getData()->format("m"), $form->get("documentExpeditionDate")->getData()->format("d"));
                $person = $user->getPersonPerson();
                $person->setLastName2($form->get("lastName2")->getData());
                $person->setDocument($form->get("lastName2")->getData());
                $person->setDocumentExpeditionDate($documentExpirationDate);
                $person->setCivilStatus($form->get("civilStatus")->getData());
                $person->setCity($city);
                $person->setDepartment($department);
                $person->setMainAddress($form->get("mainAddress")->getData());

                // if ($this->addToHighTech($user)) 
                // {
                //    return true;
                // }else{
                //     return $this->render('RocketSellerTwoPickBundle:legalAssistance:paymentMethod.html.twig', array(
                //                 'form' => $form->createView(),
                //                 'errno' => "Tenemos un errar en el sistema, intenta mas tarde"
                //     ));
                // } 
                $request->request->add(array(
                    "accountNumber" => $form->get("numberAccount")->getData(),
                    "bankId" => $form->get("bank")->getData()->getIdBank(),
                    "accountTypeId" => $form->get("accountType")->getData()->getIdAccountType(),
                    "userId" => $user->getId(),
                ));
                // dump($request);
                // exit();
                $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:postAddDebitAccount', array('_format' => 'json'));
                // if ($insertionAnswer->getStatusCode() != 201) {
                dump($insertionAnswer);
                exit();
                if (false) {
                    return $this->render('RocketSellerTwoPickBundle:legalAssistance:paymentMethod.html.twig', array(
                        'form' => $form->createView(),
                        'errno' => "Error agregando cuenta, intentalo mas tarde"
                    ));
                }

                dump("se quiere pagar con debito");

            }
            //descomentar ojo  
            // $em->persist($person);
            // $em->persist($employer);
            // $em->persist($phone);
            // $em->flush();
            $format = array('_format' => 'json');

            $paymentMethods = array("0-101", "1-101");
            // $paymentMethods = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:getClientListPaymentMethods',array('idUser' => $user->getId()), $format);

            //if varios
            if (true) {
                return $this->render('RocketSellerTwoPickBundle:legalAssistance:listPaymentMethod.html.twig', array(
                    'paymentMethods' => $paymentMethods
                ));
            } else {
                return $this->payLegalAssistance($methodId);
            }
            //TODO NovoPayment
            //cambiar los campos requeridos.

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
        } elseif ($insertionAnswer->getStatusCode() == 404) {
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
        $user->setLegalFlag($flag);
        $em->persist($user);
        $em->flush();

        if ($user->getLegalFlag() == 0) {
            $urlToSend = 'edit_profile';
        } else if ($user->getLegalFlag() == 1) {
            $urlToSend = 'legal';
        } else {
            $urlToSend = 'register_employee';
        }

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

