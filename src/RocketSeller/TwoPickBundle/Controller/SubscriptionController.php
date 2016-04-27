<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use RocketSeller\TwoPickBundle\Form\PagoMembresiaForm;
use RocketSeller\TwoPickBundle\Entity\Bank;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use RocketSeller\TwoPickBundle\Traits\SubscriptionMethodsTrait;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SubscriptionController extends Controller {

    use SubscriptionMethodsTrait;

    public function subscriptionChoicesAction() {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /* @var $user User */
        $user = $this->getUser();

        $day = $this->getDaysSince($user->getLastPayDate(), date_create(date('Y-m-d')));

        if ($day === true || ($day->d >= 28) || ($day->m >= 1)) {

            $date = new \DateTime();
            $date->add(new \DateInterval('P1M'));
            $startDate = $date->format('Y-m-d');

            $data = $this->getSubscriptionCost($user, false);
            $constrains = $this->forward('RocketSellerTwoPickBundle:CalculatorRest:getCalculatorConstraints', array('_format' => 'json'));
            $constrains = json_decode($constrains->getContent(), true);

            return $this->render('RocketSellerTwoPickBundle:Subscription:subscriptionChoices.html.twig', array(
                        'employees' => $data['employees'],
                        'productos' => $data['productos'], //$this->orderProducts($employees['productos']),
                        'constrains' => isset($constrains['response']['smmlv']) ? $constrains['response'] : false,
                        'total' => $data['total_sin_descuentos'],
                        'total_sin_descuentos' => $data['total_sin_descuentos'],
                        'descuento_3er' => $data['descuento_3er'],
                        'descuento_isRefered' => $data['descuento_isRefered'],
                        'total_con_descuentos' => $data['total_con_descuentos'],
                        'descuento_haveRefered' => $data['descuento_haveRefered'],
                        'user' => $user,
                        'startDate' => $startDate
            ));
        } else {

            $date = new \DateTime(date_format($user->getLastPayDate(), "Y-m-d H:i:s"));
            $date->add(new \DateInterval('P1M'));
            $startDate = $date->format('Y-m-d');

            return $this->render('RocketSellerTwoPickBundle:Subscription:subscriptionNoPay.html.twig', array(
                        'user' => $user,
                        'startDate' => $startDate
            ));
        }
    }

    public function suscripcionConfirmAction(Request $request) {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /* @var $user User */
        $user = $this->getUser();

        $day = $this->getDaysSince($user->getLastPayDate(), date_create(date('Y-m-d')));


        if ($day === true || ($day->d >= 28) || ($day->m >= 1)) {

            if ($request->isMethod('POST')) {

                $user = $this->getUser();
                $person = $user->getPersonPerson();
                $billingAdress = $person->getBillingAddress();
                $data = $this->getSubscriptionCost($user, true);

                //dump($data);
                $date = new \DateTime();
                $date->add(new \DateInterval('P1M'));
                $startDate = $date->format('Y-m-d');
                $minMont = $date->format('m');
                $minYear = $date->format('Y');

                $form = $this->get('form.factory')->createNamedBuilder('pagoMembresia', 'form', array(), array(
                            'action' => $this->generateUrl('subscription_pay'),
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
                                'readonly' => true,
                                'value' => $person->getNames() . ' ' . $person->getLastName1() . ' ' . $person->getLastName2()
                            )
                        ))
                        ->add('documentType', 'text', array(
                            'label' => 'Tipo de Documento',
                            'required' => true,
                            'attr' => array(
                                'placeholder' => 'Tipo de Documento',
                                'readonly' => true,
                                'value' => $person->getDocumentType()
                            )
                        ))
                        ->add('documentNumber', 'text', array(
                            'label' => 'Número de documento',
                            'required' => true,
                            'attr' => array(
                                'placeholder' => 'Número de documento',
                                'readonly' => true,
                                'value' => $person->getDocument()
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
                        ->add('numberAccount', 'text', array(
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

                return $this->render('RocketSellerTwoPickBundle:Subscription:subscriptionConfirm.html.twig', array(
                            'form' => $form->createView(),
                            'employer' => $person,
                            'employees' => $data['employees'],
                            'billingAdress' => $billingAdress,
                            'total_sin_descuentos' => $data['total_sin_descuentos'],
                            'total_con_descuentos' => $data['total_con_descuentos'],
                            'descuento_3er' => $data['descuento_3er'],
                            'descuento_isRefered' => $data['descuento_isRefered'],
                            'descuento_haveRefered' => $data['descuento_haveRefered'],
                            'startDate' => $startDate
                ));
            } else {
                return $this->redirectToRoute("subscription_choices");
            }
        } else {

            $date = new \DateTime(date_format($user->getLastPayDate(), "Y-m-d H:i:s"));
            $date->add(new \DateInterval('P1M'));
            $startDate = $date->format('Y-m-d');

            return $this->render('RocketSellerTwoPickBundle:Subscription:subscriptionNoPay.html.twig', array(
                        'user' => $user,
                        'startDate' => $startDate
            ));
        }
    }

    public function suscripcionPayAction(Request $requestIn) {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        //dump($requestIn);
        /* @var $user User */
        $user = $this->getUser();

        $day = $this->getDaysSince($user->getLastPayDate(), date_create(date('Y-m-d')));

        if ($day === true || ($day->d >= 28) || ($day->m >= 1)) {

            if ($requestIn->isMethod('POST')) {
                $em = $this->getDoctrine()->getManager();
                /* @var $user User */
                $user = $this->getUser();
                $typeMethod = $requestIn->get('typeMethod');
                $pagoMembresia = $requestIn->get('pagoMembresia');
                $tos = $requestIn->get('tos');
                if ($tos == 'on') {

                    if ($typeMethod == 'creditCard') {

                        if ($this->addToNovo($user)) {
                            //$request = new Request ();
                            $request = $this->container->get('request');
                            $request->setMethod('POST');
                            $request->request->set('credit_card', $pagoMembresia['credit_card']);
                            $request->request->set('expiry_date_year', $pagoMembresia['expiry_year']);
                            $request->request->set('expiry_date_month', strlen($pagoMembresia['expiry_month']) < 2 ? '0' . $pagoMembresia['expiry_month'] : $pagoMembresia['expiry_month']);
                            $request->request->set('cvv', $pagoMembresia['cvv']);
                            $request->request->set('name_on_card', $pagoMembresia['name_on_card']);
                            $postAddCreditCard = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:postAddCreditCard', array('request' => $request), array('_format' => 'json'));
                            if ($postAddCreditCard->getStatusCode() != Response::HTTP_CREATED) {
                                dump($postAddCreditCard->getContent());
                                return $this->redirectToRoute("subscription_error");
                                //throw $this->createNotFoundException($data->getContent());
                            } else {
                                $this->procesosLuegoPagoExitoso($user);
                                return $this->redirectToRoute("subscription_success");
                                /*
                                  $methodId = json_decode($postAddCreditCard->getContent(), true);
                                  $purchaseOrder = $this->createPurchaceOrder($user, 'novo', isset($methodId['response']['method-id']) ? $methodId['response']['method-id'] : false);

                                  if ($purchaseOrder) {
                                  return $this->redirectToRoute("subscription_success");
                                  }
                                  return $this->redirectToRoute("subscription_error");
                                 */
                            }
                        } else {
                            dump('Error al insertar en novopayment');
                            return $this->redirectToRoute("subscription_error");
                        }
                    } elseif ($typeMethod == 'debito') {

                        if ($this->addToHighTech($user)) {
                            //$request = new Request ();
                            $request = $this->container->get('request');
                            $request->setMethod('POST');
                            $request->request->set('accountNumber', $pagoMembresia['numberAccount']);
                            $request->request->set('bankId', $pagoMembresia['bank']);
                            $request->request->set('accountTypeId', $pagoMembresia['accountType']);
                            $request->request->set('userId', $user->getId());
                            $postAddCreditCard = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:postAddDebitAccount', array('request' => $request), array('_format' => 'json'));
                            if ($postAddCreditCard->getStatusCode() != Response::HTTP_CREATED) {
                                dump($postAddCreditCard->getContent());
                                return $this->redirectToRoute("subscription_error");
                                //throw $this->createNotFoundException($data->getContent());
                            } else {
                                $this->procesosLuegoPagoExitoso($user);
                                return $this->redirectToRoute("subscription_success");
                                /*
                                  $methodId = json_decode($postAddCreditCard->getContent(), true);
                                  $purchaseOrder = $this->createPurchaceOrder($user, 'hightec', isset($methodId['response']['method-id']) ? $methodId['response']['method-id'] : false);

                                  if ($purchaseOrder) {
                                  return $this->redirectToRoute("subscription_success");
                                  }
                                  return $this->redirectToRoute("subscription_error");
                                 */
                            }
                        } else {
                            dump('Error al insertar en hightec');
                            return $this->redirectToRoute("subscription_error");
                        }
                    } else {
                        dump('La opcion enviada es diferente a las opciones permitidas');
                        return $this->redirectToRoute("subscription_error");
                    }
                } else {
                    dump('No se aceptaron terminos y condiciones');
                    return $this->redirectToRoute("subscription_error");
                }
            } else {
                return $this->redirectToRoute("subscription_choices");
            }
        } else {

            $date = new \DateTime(date_format($user->getLastPayDate(), "Y-m-d H:i:s"));
            $date->add(new \DateInterval('P1M'));
            $startDate = $date->format('Y-m-d');

            return $this->render('RocketSellerTwoPickBundle:Subscription:subscriptionNoPay.html.twig', array(
                        'user' => $user,
                        'startDate' => $startDate
            ));
        }
    }

    public function suscripcionSuccessAction(Request $request) {
        return $this->render('RocketSellerTwoPickBundle:Subscription:subscriptionSuccess.html.twig', array(
                    'user' => $this->getUser(),
                    'date' => \date('Y-m-d')
        ));
    }

    public function suscripcionErrorAction(Request $request) {
        return $this->render('RocketSellerTwoPickBundle:Subscription:subscriptionError.html.twig', array(
                    'user' => $this->getUser()
        ));
    }

    public function suscripcionInactivaAction() {
        $user = $this->getUser();
        return $this->render('RocketSellerTwoPickBundle:Subscription:inactive.html.twig');
    }

}
