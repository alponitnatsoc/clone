<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\City;
use RocketSeller\TwoPickBundle\Entity\Department;
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
            ->add('personName', 'text', array(
                'label' => 'Nombres*',
                'required' => true,
                'attr' => array(
                    'placeholder' => 'Nombres',
                    'value' => $user->getPersonPerson()->getNames(),
                )
            ))
            ->add('lastName1', 'text', array(
                'label' => 'Primer apellido*',
                'required' => true,
                'attr' => array(
                    'placeholder' => 'Primer apellido',
                    'value' => $user->getPersonPerson()->getLastName1(),
                )
            ))
            ->add('lastName2', 'text', array(
                'label' => 'Segundo apellido*',
                'required' => false,
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
                'required' => true
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
                'required' => true
            ))
            ->add('documentNumber', 'text', array(
                'label' => 'Número de documento*',
                'required' => true,
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
                'required' => true
            ))
            ->add('phoneNumber', 'text', array(
                'label' => 'Teléfono*',
                'required' => true,
                'attr' => array(
                    'placeholder' => 'Número de teléfono celular',
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
                'required' => true
            ))
            ->add('mainAddress', 'text', array(
                'label' => 'Dirección*',
                'required' => true
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
                'label' => 'Banco*',
                'required' => true,
                'class' => 'RocketSellerTwoPickBundle:Bank',
                'placeholder' => 'Seleccionar una opción',
                'empty_value' => 'Seleccionar una opción',
                'choice_label' => 'name'
            ))
            ->add('accountType', 'entity', array(
                'label' => 'Tipo de Cuenta*',
                'required' => true,
                'class' => 'RocketSellerTwoPickBundle:AccountType',
                'placeholder' => 'Seleccionar una opción',
                'empty_value' => 'Seleccionar una opción',
                'choice_label' => 'name'
            ))
            ->add('accountNumber', 'text', array(
                'label' => 'Número de la cuenta*',
                'required' => true,
                'attr' => array(
                    'placeholder' => 'Número de la cuenta',
                    'min' => 1,
                    'minlength' => 1,
                    'step' => 1,
                    'pattern' => '\d*'
                )
            ))
            ->getForm();

        $form->handleRequest($request);
        dump($form->getData());
        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $user = $this->getUser();
            /** @var Person $person */
            $methodType = $request->get('methodType');

            $person = $user->getPersonPerson();
            $person->setDocumentType($form->get("documentType")->getData());
            $person->setDocument($form->get("documentNumber")->getData());

            /* @var $department Department */
            //$department = $this->getDoctrine()
            //   ->getRepository('RocketSellerTwoPickBundle:Department')
            //   ->find($form->get("department")->getData());

            /* @var $city City */
            //$city = $this->getDoctrine()
            //   ->getRepository('RocketSellerTwoPickBundle:City')
            //  ->find($form->get("city")->getData());

            $documentExpirationDate = new \DateTime();
            $documentExpirationDate->setDate($form->get("documentExpeditionDate")->getData()->format("Y"), $form->get("documentExpeditionDate")->getData()->format("m"), $form->get("documentExpeditionDate")->getData()->format("d"));

            $person->setLastName2($form->get("lastName2")->getData());
            $person->setDocumentExpeditionDate($documentExpirationDate);
            $person->setCivilStatus($form->get("civilStatus")->getData());
            $person->setCity($form->get("city")->getData());
            $person->setDepartment($form->get("department")->getData());
            $person->setMainAddress($form->get("mainAddress")->getData());

            if ($person->getPhones()->isEmpty()) {
                $phone = new Phone();
                $phone->setPhoneNumber($form->get("phoneNumber")->getData());
                $person->addPhone($phone);
            } else {
                /* @var $phone Phone */
                $phone = $person->getPhones()->first();
                $phone->setPhoneNumber($form->get("phoneNumber")->getData());
            }
            $em->persist($phone);

            if ($person->getEmployer() == null) {
                $employer = new Employer();
                $employer->setPersonPerson($person);
                $employer->setEmployerType("Persona");
                $employer->setRegisterState(10);
                $person->setEmployer($employer);
                $em->persist($employer);
            }

            $em->persist($person);
            $em->flush();

            $expiryMonth = $form->get("expiry_month")->getData();
            if (strlen($expiryMonth) < 2) {
                $expiryMonth = "0" . $form->get("expiry_month")->getData();
            }
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
                    "accountNumber" => $form->get("accountNumber")->getData(),
                    "bankId" => $form->get("bank")->getData()->getIdBank(),
                    "accountTypeId" => $form->get("accountType")->getData()->getIdAccountType(),
                    "userId" => $user->getId(),
                ));
                // dump($request);
                // exit();
                $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:postAddDebitAccount', array('_format' => 'json'));

                //if (false) {
                if ($insertionAnswer->getStatusCode() != 201 || $insertionAnswer->getStatusCode() != 200) {
                    return $this->render('RocketSellerTwoPickBundle:legalAssistance:paymentMethod.html.twig', array(
                        'form' => $form->createView(),
                        'errno' => "Error agregando cuenta, intentalo mas tarde"
                    ));
                }
            }
            return $this->redirectToRoute('legal_payment_list');

        }
        return $this->render('RocketSellerTwoPickBundle:legalAssistance:paymentMethod.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function startPaymentListAction(Request $request)
    {
        $user = $this->getUser();
        if ($request->get('paymentMethod')) {
            //dump($request->get('paymentMethod'));
            return $this->payLegalAssistance($request->get('paymentMethod')[0]);
        } else {
            $format = array('_format' => 'json');

            //$paymentMethods = array("0-101", "1-101");
            $paymentMethods = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:getClientListPaymentMethods', array('idUser' => $user->getId()), $format);
            $paymentMethodsContent = json_decode($paymentMethods->getContent(), true);
            dump($paymentMethodsContent);
            //die();
            //if varios
            dump(count($paymentMethodsContent['payment-methods']));
            if (count($paymentMethodsContent['payment-methods']) > 1) {
                return $this->render('RocketSellerTwoPickBundle:legalAssistance:listPaymentMethod.html.twig', array(
                    'paymentMethods' => $paymentMethodsContent['payment-methods']
                ));
            } else {
                return $this->payLegalAssistance($paymentMethodsContent['payment-methods'][0]['method-id']);
            }
        }
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
            //$flag = $this->changeFlagAction();
            //$user->setLegalFlag(0);
            return $this->redirectToRoute('legal_payment_success');
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
        if ($user->getLegalFlag() != -1) {
            $urlToSend = 'edit_profile';
        } else {
            $urlToSend = 'register_employee';
        }

        $user->setLegalFlag($flag);
        $em->persist($user);
        $em->flush();


        return $this->redirectToRoute($urlToSend);
    }

    public function successPaymentAction()
    {
        return $this->successExpress();
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

