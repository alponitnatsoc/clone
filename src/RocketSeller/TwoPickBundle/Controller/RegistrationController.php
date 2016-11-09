<?php

namespace RocketSeller\TwoPickBundle\Controller;

use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Employer;
use FOS\UserBundle\Controller\RegistrationController as BaseController;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use RocketSeller\TwoPickBundle\Entity\PromotionCode;
use RocketSeller\TwoPickBundle\Entity\User;
use RocketSeller\TwoPickBundle\Entity\Phone;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Model\UserInterface;
use RocketSeller\TwoPickBundle\Entity\Invitation;
use RocketSeller\TwoPickBundle\Entity\Referred;
use RocketSeller\TwoPickBundle\Form\RegistrationExpress;

class RegistrationController extends BaseController
{

    public function registerConfirmedStartAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($user->getPersonPerson()->getEmployer() == null) {
            return $this->render('RocketSellerTwoPickBundle:Registration:confirmed.html.twig');
        } else {
            return $this->redirectToRoute('ajax');
        }
    }

    public function registerAction(Request $request)
    {
        //Redirecting if the user is already logged
        $authChecker = $this->container->get('security.authorization_checker');
        $router = $this->container->get('router');

        if ($authChecker->isGranted('ROLE_ADMIN')) {
            return new RedirectResponse($router->generate('sonata_admin_dashboard'), 307);
        }

        if ($authChecker->isGranted('ROLE_USER')) {
            return new RedirectResponse($router->generate('show_dashboard'), 307);
        }

        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.registration.form.factory');
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');
        $user = $userManager->createUser();
        //die(print_r($user));
        $user->setEnabled(true);
        $user->setUsername("atemporel_tempo_tmp");

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $formFactory->createForm();
        $form->setData($user);
        $form->handleRequest($request);
        $exists=$userManager->findUserByEmail($user->getEmail());
        $errorss="";
        /** @var ObjectManager $em */
        $em = $this->getDoctrine()->getManager();
        if ($form->isValid()&&$exists==null) {
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

            /**
             * Referidos
             */
            if ($form->has("invitation")) {
                $code = $form->get("invitation")->getData();
                if ($code) {
                    /** @var \RocketSeller\TwoPickBundle\Entity\User $userR */
                    $userR = $userManager->findUserBy(array("code" => $code)); // $userR usuario que refiere al nuevo usuario
                    $userEmail = $user->getEmail();
                    if (method_exists($userR, "getId")) {

                        $repository = $this->getDoctrine()->getRepository('RocketSellerTwoPickBundle:Invitation');
                        /** @var \RocketSeller\TwoPickBundle\Entity\Invitation $invitation */
                        $invitation = $repository->findOneBy(
                                array('userId' => $userR->getId(), 'email' => $userEmail)
                        );

                        if ($invitation) {
                            $invitation->setStatus(1);
                        } else {
                            $invitation = new Invitation();
                            $invitation->setEmail($userEmail);
                            $invitation->setUserId($userR);
                            $invitation->setSent(true);
                            $invitation->setStatus(1);
                        }
                        $em->persist($invitation);
                        /** @var Referred $referred */
                        $referred = new Referred();
                        $referred->setInvitationId($invitation);
                        $referred->setReferredUserId($user);
                        $referred->setUserId($userR);
                        $em->persist($referred);
                        $em->flush();
                    }
                }
            }

            $person = new Person();
            $person->setNames($form->get("name")->getData());
            $person->setLastName1($form->get("lastName")->getData());
            $phone= new Phone();
            $phone->setPhoneNumber($form->get("phone")->getData());
            $person->addPhone($phone);
            $user->setPersonPerson($person);
            $user->setUsername($user->getEmail());
	        
            $invitationCode=$form->get("creationCode")->getData();
	          $utils = $this->get('app.symplifica_utils');
	          $invitationCode = $utils->mb_normalize($invitationCode);
	          
            $promoCodeRepo=$this->getDoctrine()->getRepository("RocketSellerTwoPickBundle:PromotionCode");
            /** @var PromotionCode $realCode */
            $realCode=$promoCodeRepo->findOneBy(array("code"=>$invitationCode));
            if($realCode!=null&&$realCode->getPromotionCodeTypePromotionCodeType()->getStatus()!=-1&&$realCode->getPromotionCodeTypePromotionCodeType()->getUniqueness()==1){
                /** @var User $user */
                $realCode->addUser($user);
                $user->setIsFree($realCode->getPromotionCodeTypePromotionCodeType()->getDuration());
                $realCode->setStartDate(new \DateTime());
                $endDate= new DateTime(date("Y-m-d", strtotime("+".$user->getIsFree()." month", strtotime($realCode->getStartDate()->format("Y-m-d")))));
                $realCode->setEndDate($endDate);
                $em->persist($realCode);
                $userManager->updateUser($user);
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


            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('fos_user_registration_confirmed');
                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            return $response;
        }else{
            if ( $form->isSubmitted() ){
              $errorss="El usuario ya existe o la información ingresada es invalida";
            }
            $form = $formFactory->createForm();
            $form->setData($user);
            $allreque=$request->request->all();
            if(isset($allreque["nname"])){
                $form->get("name")->setData($allreque["nname"]);
                $form->get("lastName")->setData($allreque["nlast"]);
                $form->get("email")->setData($allreque["nemail"]);
                $form->get("phone")->setData($allreque["nphone"]);
            }
        }

        $queryCode = $request->query->get("c");
        if ($form->has("invitation")) {
           $form->get("invitation")->setData($queryCode);
        }

        return $this->render('FOSUserBundle:Registration:register.html.twig', array(
            'form' => $form->createView(),
            'errorss'=>$errorss
        ));
    }

     /**
      * Tell the user to check his email provider
      */
    public function checkEmailAction()
    {
        $email = $this->get('session')->get('fos_user_send_confirmation_email/email');
        if (empty($email)) {
            return new RedirectResponse($this->get('router')->generate('fos_user_registration_register'));
        }
        $this->get('session')->remove('fos_user_send_confirmation_email/email');
        $user = $this->get('fos_user.user_manager')->findUserByEmail($email);
        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', $email));
        }
        return $this->render('FOSUserBundle:Registration:checkEmail.html.twig', array(
            'user' => $user,
        ));
    }

    /**
     * Receive the confirmation token from user email provider, login the user
     */
    public function confirmAction(Request $request, $token)
    {
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserByConfirmationToken($token);
        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');
        $user->setConfirmationToken(null);
        $user->setEnabled(true);
        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRM, $event);
        $userManager->updateUser($user);
        if (null === $response = $event->getResponse()) {
            /* if ($user->getExpress()) {
              $url = $this->generateUrl('express_payment');
              }else{
              $url = $this->generateUrl('edit_profile');
              } */

            $url = $this->generateUrl('welcome_post_register');
            $response = new RedirectResponse($url);
        }
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRMED, new FilterUserResponseEvent($user, $request, $response));
        return $response;
    }

    public function chooseRegisterAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($user->getPersonPerson()->getEmployer() == null) {
            if($user->getLegalFlag()==-2){
                return $this->render('RocketSellerTwoPickBundle:Registration:chooseRegistration.html.twig');
            }else{
                return $this->redirectToRoute('welcome');
            }
        } else {
            return $this->redirectToRoute('ajax');
        }
    }

    public function welcomePostRegisterAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($user->getPersonPerson()->getEmployer() == null) {
            //return $this->render('RocketSellerTwoPickBundle:Registration:postRegistrationWelcome.html.twig');
            return $this->redirectToRoute('welcome');
        } else {
            return $this->redirectToRoute('ajax');
        }
    }

    // /**
    //  * Tell the user his account is now confirmed
    //  */
    // public function confirmedAction()
    // {
    //     $user = $this->getUser();
    //     if (!is_object($user) || !$user instanceof UserInterface) {
    //         throw new AccessDeniedException('This user does not have access to this section.');
    //     }
    //     return $this->render('FOSUserBundle:Registration:confirmed.html.twig', array(
    //         'user' => $user,
    //         'targetUrl' => $this->getTargetUrlFromSession(),
    //     ));
    // }
    // private function getTargetUrlFromSession()
    // {
    //     // Set the SecurityContext for Symfony <2.6
    //     if (interface_exists('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')) {
    //         $tokenStorage = $this->get('security.token_storage');
    //     } else {
    //         $tokenStorage = $this->get('security.context');
    //     }
    //     $key = sprintf('_security.%s.target_path', $tokenStorage->getToken()->getProviderKey());
    //     if ($this->get('session')->has($key)) {
    //         return $this->get('session')->get($key);
    //     }
    // }
    public function registerExpressAction(Request $request)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        /* @var $person Person */
        $person = $user->getPersonPerson();

        $form = $this->createForm(new RegistrationExpress(), $person, array(
            'action' => $this->generateUrl('express_start'),
            'method' => 'POST',
        ));
        //$form->setData($person);

        $form->handleRequest($request);
        $errno = null;
        if ($form->isValid()) {
            if ($person->getDocumentType() == null) {
                $person->setDocumentType($form->get("documentType")->getData());
                $person->setDocument($form->get("document")->getData());
                $person->setLastName1($form->get("lastName1")->getData());
                $person->setLastName2($form->get("lastName2")->getData());
                $employer = new Employer();
                $phone = new Phone();
                $phone->setPhoneNumber($form->get("phone")->getData()->getPhoneNumber());
                $person->addPhone($phone);
                $person->setNames($form->get("names")->getData());
                $employer->setPersonPerson($person);
                $em->persist($person);
                $employer->setEmployerType("Persona");
                $employer->setRegisterState(10);
                $employer->setRegisterExpress(1);
                $em->persist($employer);
                $em->flush();
                $user->setExpress(1);
                $em->persist($user);
                $em->flush();
            }

            //$url = $this->generateUrl('express_payment');
            //$response = new RedirectResponse($url);
            //return $response;
            //return $this->redirectToRoute('express_payment');
            if ($this->addClient($user)) {
                $methodId = $this->addCreditCard($request);
                if ($methodId) {
                    return $this->redirectToRoute('express_pay_start', array('methodId' => $methodId));

                    //return $this->forward('RocketSellerTwoPickBundle:ExpressRegistration:startExpressPay', array('methodId' => $methodId));
                } else {
                    //return $this->redirectToRoute('express_payment_add');
                    $errno = "Por favor verifique la información de la tarjeta de credito";
                }
            } else {
                $errno = "Error al crear cliente";
            }
        }
        return $this->render('FOSUserBundle:Registration:expressRegistration.html.twig', array(
                    'form' => $form->createView(),
                    'errno' => $errno
        ));
    }

    private function addClient(User $user)
    {
        $format = array('_format' => 'json');
        $response = $this->forward('RocketSellerTwoPickBundle:ExpressRegistrationRest:getPayment', array(
            'id' => $user->getId()
                ), $format
        );
        if ($response->getStatusCode() == 201) {
            return true;
        }
        return false;
    }

    private function addCreditCard(Request $request)
    {
        $user = $this->getUser();
        /** @var Person $person */
        $person = $user->getPersonPerson();
        //dump($request);
        $credit_card = $request->request->get('app_user_express_registration')['credit_card'];
        //dump($credit_card);
        $request->setMethod("POST");
        $request->request->add(array(
            "documentType" => $person->getDocumentType(),
            "documentNumber" => $person->getDocument(),
            "credit_card" => $credit_card['credit_card'],
            "expiry_date_year" => $credit_card['expiry_date_year'],
            "expiry_date_month" => $credit_card['expiry_date_month'],
            "cvv" => $credit_card['cvv'],
        ));

        $insertionAnswer = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:postAddCreditCard', array('_format' => 'json'));
        $response = json_decode($insertionAnswer->getContent());
        //dump($response);
        if (isset($response->{'response'}->{'method-id'})) {
            $methodId = $response->{'response'}->{'method-id'};
            if ($insertionAnswer->getStatusCode() != 201) {
                return false;
            }
            return $methodId;
        } else {
            $this->addFlash('error', $response->error->exception[0]->message);
            return false;
        }
    }

}
