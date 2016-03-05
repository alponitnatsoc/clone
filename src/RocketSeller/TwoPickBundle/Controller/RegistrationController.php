<?php

namespace RocketSeller\TwoPickBundle\Controller;

use RocketSeller\TwoPickBundle\Entity\Person;
use RocketSeller\TwoPickBundle\Entity\Employer;
use FOS\UserBundle\Controller\RegistrationController as BaseController;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
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
        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.registration.form.factory');
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user = $userManager->createUser();
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
        if ($form->isValid()) {
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

            /**
             * Referidos
             */
            if ($form->has("invitation")) {
                $code = $form->get("invitation")->getData();
                if ($code) {
                    $em = $this->getDoctrine()->getManager();
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
            $user->setPersonPerson($person);
            $user->setUsername($user->getEmail());
            $userManager->updateUser($user);

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('fos_user_registration_confirmed');
                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            return $response;
        }

        //$queryCode = $request->query->get("c");
        //if ($form->has("invitation")) {
        //    $form->get("invitation")->setData($queryCode);
        //}

        return $this->render('FOSUserBundle:Registration:register.html.twig', array(
                    'form' => $form->createView()
        ));
    }

    // /**
    //  * Tell the user to check his email provider
    //  */
    // public function checkEmailAction()
    // {
    //     $email = $this->get('session')->get('fos_user_send_confirmation_email/email');
    //     $this->get('session')->remove('fos_user_send_confirmation_email/email');
    //     $user = $this->get('fos_user.user_manager')->findUserByEmail($email);
    //     if (null === $user) {
    //         throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', $email));
    //     }
    //     return $this->render('FOSUserBundle:Registration:checkEmail.html.twig', array(
    //         'user' => $user,
    //     ));
    // }
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
            if ($user->getExpress()) {
                $url = $this->generateUrl('express_payment');   
            }else{
                $url = $this->generateUrl('edit_profile');
            }            
            $response = new RedirectResponse($url);
        }
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRMED, new FilterUserResponseEvent($user, $request, $response));
        return $response;
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
        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.registration.form.factory');
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user = $userManager->createUser();
        $user->setEnabled(true);
        $user->setExpress(1);
        $user->setUsername("atemporel_tempo_tmp");
        $em = $this->getDoctrine()->getManager();

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);
            $person = new Person();
            $person->setDocumentType($request->get("documentType"));
            $person->setDocument($request->get("document"));
            $person->setLastName1($request->get("lastName1"));
            $person->setLastName2($request->get("lastName2"));
            $employer = new Employer();            
            $phone = new Phone();            
            $phone->setPhoneNumber($request->get("phone"));
            $person->addPhone($phone);
            $person->setNames($form->get("name")->getData());
            $employer->setPersonPerson($person);
            $employer->setEmployerType("Persona");
            $employer->setRegisterState(10);
            $employer->setRegisterExpress(1);
            $em->persist($employer);
            $em->flush();
            $user->setPersonPerson($person);
            $user->setUsername($user->getEmail());
            $userManager->updateUser($user);



            return $this->render('RocketSellerTwoPickBundle:Registration:checkEmail.html.twig');
        }
        return $this->render('FOSUserBundle:Registration:expressRegistration.html.twig', array(
                    'form' => $form->createView()
        ));
    }    
}
