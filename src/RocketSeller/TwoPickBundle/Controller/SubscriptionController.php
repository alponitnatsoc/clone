<?php

namespace RocketSeller\TwoPickBundle\Controller;

use DateTime;
use RocketSeller\TwoPickBundle\Entity\Campaign;
use RocketSeller\TwoPickBundle\Entity\PromotionCode;
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

class SubscriptionController extends Controller
{

    use SubscriptionMethodsTrait;

    public function subscriptionChoicesAction()
    {
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
	        
	          $isFreeUntil =  $user->getDateCreated();
	          $isFreeMonths = $user->getIsFree();
	
	          $utils = $this->get('app.symplifica_utils');
	        
	          if($isFreeMonths == 0){
	          	$isFreeUntil = "";
	          }
	          else{
		          $isFreeUntil->modify("+" . $isFreeMonths . " months");
		          $isFreeUntil = "Gratis hasta " . $utils->month_number_to_name($isFreeUntil->format('m')) . " " . $isFreeUntil->format('d Y');
	          }
	        
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
                'startDate' => $startDate,
	              'isFreeUntil' => $isFreeUntil
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

    public function suscripcionConfirmAction(Request $request)
    {
        $inData = $request->request->all();
        if(isset($inData["referredCode"])){
            /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
            $userManager = $this->get('fos_user.user_manager');
            /** @var User $user */
            $user = $this->getUser();
            $em=$this->getDoctrine()->getManager();
            $invitationCode=$inData["referredCode"];
            $utils = $this->get('app.symplifica_utils');
            $invitationCode = $utils->mb_normalize($invitationCode);

            $promoCodeRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PromotionCode");
            /** @var PromotionCode $realCode */
            $realCode=$promoCodeRepo->findOneBy(array("code"=>$invitationCode));
            if($realCode!=null&&$realCode->getPromotionCodeTypePromotionCodeType()->getStatus()!=-1&&$realCode->getPromotionCodeTypePromotionCodeType()->getUniqueness()==1){
                $users = $realCode->getUsers();
                $flag = false;
                /** @var User $tUser */
                foreach ($users as $tUser) {
                    if($user->getId() == $tUser->getId()){
                        $flag = true;
                        break;
                    }
                }
                if(!$flag){
                    $realCode->addUser($user);
                    $user->setIsFree($realCode->getPromotionCodeTypePromotionCodeType()->getDuration());
                    $realCode->setStartDate(new \DateTime());
                    $endDate= new DateTime(date("Y-m-d", strtotime("+".$user->getIsFree()." month", strtotime($realCode->getStartDate()->format("Y-m-d")))));
                    $realCode->setEndDate($endDate);
                    $em->persist($realCode);
                    $userManager->updateUser($user);
                }
            }else{
                if($realCode!=null&&$realCode->getPromotionCodeTypePromotionCodeType()->getUniqueness()=="-1"&&$realCode->getUsers()->count()==0){
                    /** @var User $user */
                    $realCode->addUser($user);
                    $user->setIsFree($realCode->getPromotionCodeTypePromotionCodeType()->getDuration());
                    $realCode->setStartDate(new \DateTime());
                    $endDate= new DateTime(date("Y-m-d", strtotime("+".$user->getIsFree()." month", strtotime($realCode->getStartDate()->format("Y-m-d")))));
                    $realCode->setEndDate($endDate);
                    $em->persist($realCode);
                }
                $userManager->updateUser($user);
            }
        }
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /* @var $user User */
        $user = $this->getUser();

        $day = $this->getDaysSince($user->getLastPayDate(), date_create(date('Y-m-d')));


        if ($day === true || ($day->d >= 28) || ($day->m >= 1)) {

            //if ($request->isMethod('POST')) {

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
                    'label' => 'Tipo de documento',
                    'required' => true,
                    'attr' => array(
                        'placeholder' => 'Tipo de documento',
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
            //} else {
            //    return $this->redirectToRoute("subscription_choices");
            //}
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

    public function askDataCreditQuestionsAction($userId, Request $request)
    {
        $debitData = $request->request->all();
        /** @var Request $request */
        $request = $this->container->get('request');
        $userRepo = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:User");
        /** @var User $user */
        $user = $userRepo->find($userId);
        if ($user == null) {
            $this->redirectToRoute("show_dashboard");
        }
        $person = $user->getPersonPerson();
        $documentNumber = $person->getDocument();
        $documentType = $person->getDocumentType();
        if ($user->getDataCreditStatus() == 0) {
            $lastName1 = $person->getLastName1();
            $firstName = explode(" ", $person->getNames())[0];
            $expeditionDate = $person->getDocumentExpeditionDate()->format("d-m-Y");
            $request->setMethod("GET");
            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:DataCreditoRest:getClientIdentificationServiceExperianPreguntas', array(
                'documentNumber' => $documentNumber,
                'identificationType' => $documentType,
                'surname' => $lastName1,
                'names' => $firstName,
                'documentExpeditionDate' => $expeditionDate,
            ), array('_format' => 'json'));
            $em = $this->getDoctrine()->getManager();

            if ($insertionAnswer->getStatusCode() != 200) {
                if ($insertionAnswer->getStatusCode() == 429) {
                    $user->setDataCreditStatus(4);
                    $em->persist($user);
                    $em->flush();
                }
                return $this->redirectToRoute("subscription_error");
            }
            $user->setDataCreditStatus(1);
            $em->persist($user);
            $em->flush();
            $form = $this->createFormBuilder();
            $questions = json_decode($insertionAnswer->getContent(), true);
            for ($i = 0; $i < count($questions); $i++) {
                $p = "" . $i;
                if (isset($questions[$p])) {
                    $j = 0;
                    $choices = array();
                    while (true) {
                        if (isset($questions[$p][$j . ""])) {
                            $choices[$questions[$p][$j . ""]["texto"]] = $questions[$p][$j . ""]["id"];
                        } else {
                            break;
                        }
                        $j++;
                    }
                    $form->add('' . $questions[$p]["orden"], 'choice', array(
                        'label' => $questions[$p]["texto"],
                        'choices' => $choices,
                        'choices_as_values' => true,
                    ));
                } else {
                    break;
                }
            }

            $form
                ->setAction($this->generateUrl('data_credit_questions', array('userId' => $user->getId())))
                ->add('idQuestionnaire', 'hidden', array('data' => $questions["id"]))
                ->add('numberAccount', 'hidden', array('data' => $debitData['pagoMembresia']["numberAccount"]))
                ->add('bank', 'hidden', array('data' => $debitData['pagoMembresia']["bank"]))
                ->add('accountType', 'hidden', array('data' => $debitData['pagoMembresia']["accountType"]))
                ->add('register', 'hidden', array('data' => $questions["registro"]))
                ->add('save', 'submit', array('label' => 'Validar preguntas', 'attr' => array('class' => 'btn btn-orange')));
            $realForm = $form->getForm();

            return $this->render('RocketSellerTwoPickBundle:Registration:generalFormRenderDatacredito.html.twig', array(
                'form' => $realForm->createView(),
            ));
        } elseif ($user->getDataCreditStatus() == 1) {
            $k = 1;
            $formdone = $request->request->get("form");

            $toSend = array();
            while (true) {
                if (isset($formdone[$k])) {
                    $toSend[$k] = $formdone[$k];
                } else {
                    break;
                }
                $k++;
            }
            $request->setMethod("GET");
            $requestToSend = new Request();
            $requestToSend->setMethod("GET");
            $requestToSend->query->set('documentNumber', $documentNumber);
            $requestToSend->query->set('documentType', $documentType);
            $requestToSend->query->set('idQuestions', $formdone["idQuestionnaire"]);
            $requestToSend->query->set('regQuestions', $formdone["register"]);
            $requestToSend->query->set('answers', $toSend);
            $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:DataCreditoRest:getClientIdentificationServiceExperianVerifyPreguntas', array('request' => $requestToSend), array('_format' => 'json'));

            $dataCreditAnswer = json_decode($insertionAnswer->getContent(), true);
            $em = $this->getDoctrine()->getManager();
            if ($insertionAnswer->getStatusCode() != 200) {
                $user->setDataCreditStatus(5);
                $em->persist($user);
                $em->flush();
            } elseif ($dataCreditAnswer["aprobacion"] == "true") {
                $user->setDataCreditStatus(2);
                $em->persist($user);
                $em->flush();
                if ($this->addToHighTech($user)) {
                    //$request = new Request ();
                    $request = $this->container->get('request');
                    $request->setMethod('POST');
                    $request->request->set('accountNumber', $formdone['numberAccount']);
                    $request->request->set('bankId', $formdone['bank']);
                    $request->request->set('accountTypeId', $formdone['accountType']);
                    $request->request->set('userId', $user->getId());
                    $postAddCreditCard = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:postAddDebitAccount', array('request' => $request), array('_format' => 'json'));
                    if ($postAddCreditCard->getStatusCode() != Response::HTTP_CREATED) {
                        return $this->redirectToRoute("subscription_error");
                        //throw $this->createNotFoundException($data->getContent());
                    } else {
                        $this->procesosLuegoPagoExitoso($user);
                        return $this->redirectToRoute("subscription_success");
                    }
                } else {
                    return $this->redirectToRoute("subscription_error");
                }

            } else {
                $user->setDataCreditStatus(3);
                $em->persist($user);
                $em->flush();
            }
            return $this->redirectToRoute("subscription_error");

        } else {
            return $this->redirectToRoute("subscription_error");
        }

    }

    public function suscripcionPayAction(Request $requestIn)
    {

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
	                          $request->request->set('userId', $user->getId());
                            $postAddCreditCard = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:postAddCreditCard', array('request' => $request), array('_format' => 'json'));
                            if ($postAddCreditCard->getStatusCode() != Response::HTTP_CREATED) {
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
                            return $this->redirectToRoute("subscription_error");
                        }
                    } elseif ($typeMethod == 'debito') {
                        if($user->getPersonPerson()->getDocumentType()=="PASAPORTE"){
                            $user->setDataCreditStatus(2);
                        }
                        if ($user->getDataCreditStatus() == 0) {
                            return $this->forward('RocketSellerTwoPickBundle:Subscription:askDataCreditQuestions', array('userId' => $user->getId(), 'request' => $requestIn));
                        } elseif ($user->getDataCreditStatus() == 2) {//this is for bypass purposes
                            if ($this->addToHighTech($user)) {
                                $debitData = $requestIn->request->all();

                                //$request = new Request ();
                                $request = $this->container->get('request');
                                $request->setMethod('POST');
                                $request->request->set('accountNumber', $debitData['pagoMembresia']["numberAccount"]);
                                $request->request->set('bankId', $debitData['pagoMembresia']["bank"]);
                                $request->request->set('accountTypeId', $debitData['pagoMembresia']["accountType"]);
                                $request->request->set('userId', $user->getId());
                                $postAddCreditCard = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:postAddDebitAccount', array('request' => $request), array('_format' => 'json'));
                                if ($postAddCreditCard->getStatusCode() != Response::HTTP_CREATED) {
                                    return $this->redirectToRoute("subscription_error");
                                    //throw $this->createNotFoundException($data->getContent());
                                } else {
                                    $this->procesosLuegoPagoExitoso($user);
                                    return $this->redirectToRoute("subscription_success");
                                }
                            } else {
                                return $this->redirectToRoute("subscription_error");
                            }
                        } else {
                            $user->setDataCreditStatus(0);
                            $em->persist($user);
                            $em->flush();
                            return $this->forward('RocketSellerTwoPickBundle:Subscription:askDataCreditQuestions', array('userId' => $user->getId(), 'request' => $requestIn));
                        }

                    } else {
                        return $this->redirectToRoute("subscription_error");
                    }
                } else {
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

    public function suscripcionSuccessAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $promoTypeRef = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PromotionCodeType")->findOneBy(array('shortName'=>'RF'));
        /** @var Campaign $campaing */
        $campaing = $this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:Campaign")->findOneBy(array('description'=>'RefCamp'));
        /** @var User $user */
        $user = $this->getUser();
        if(!$user->getPromoCodeClaimedByReferidor()) {
            /** @var PromotionCode $promoCode */
            foreach ($user->getPromoCodes() as $promoCode) {
                if ($promoCode->getPromotionCodeTypePromotionCodeType() == $promoTypeRef) {
                    /** @var User $userReferidor */
                    $userReferidor = $promoCode->getUserUser();
                    if($campaing->getEnabled()==1){
                        //stock in this campaing is used to have a database value of the campaing
                        $userReferidor->setMoney($userReferidor->getMoney()+$campaing->getStock());
                    }else{
                        $userReferidor->setIsFree($userReferidor->getIsFree() + 3);
                    }
                    $em->persist($userReferidor);
                    $user->setPromoCodeClaimedByReferidor(true);
                    $em->persist($user);
                    $em->flush();
                    break;
                }
            }
        }

        return $this->render('RocketSellerTwoPickBundle:Subscription:subscriptionSuccess.html.twig', array(
            'user' => $user,
            'date' => \date('Y-m-d')
        ));
    }

    public function suscripcionErrorAction(Request $request)
    {
        return $this->render('RocketSellerTwoPickBundle:Subscription:subscriptionError.html.twig', array(
            'user' => $this->getUser()
        ));
    }

    public function suscripcionInactivaAction()
    {
        $user = $this->getUser();
        return $this->render('RocketSellerTwoPickBundle:Subscription:inactive.html.twig');
    }

}
