<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use RocketSeller\TwoPickBundle\Form\PagoMembresiaForm;
use RocketSeller\TwoPickBundle\Entity\BillingAddress;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use RocketSeller\TwoPickBundle\Traits\SubscriptionMethodsTrait;

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

            return $this->render('RocketSellerTwoPickBundle:Subscription:subscriptionChoices.html.twig', array(
                        'employees' => $data['employees'],
                        'productos' => $data['productos'], //$this->orderProducts($employees['productos']),
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

    public function suscripcionConfirmAction(Request $request)
    {
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
                $form = $this->createForm(new PagoMembresiaForm(), new BillingAddress(), array(
                    'action' => $this->generateUrl('subscription_pay'),
                    'method' => 'POST',
                ));

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

    public function suscripcionPayAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        /* @var $user User */
        $user = $this->getUser();

        $day = $this->getDaysSince($user->getLastPayDate(), date_create(date('Y-m-d')));

        if ($day === true || ($day->d >= 28) || ($day->m >= 1)) {

            if ($request->isMethod('POST')) {
                $em = $this->getDoctrine()->getManager();
                /* @var $user User */
                $user = $this->getUser();
                if ($this->addToNovo($user)) {
                    $request = new Request();
                    $request->setMethod('POST');
                    $request->request->set('credit_card', $request->get('credit_card'));
                    $request->request->set('expiry_date_year', $request->get('expiry_date_year'));
                    $request->request->set('expiry_date_month', $request->get('expiry_date_month'));
                    $request->request->set('cvv', $request->get('cvv'));
                    $request->request->set('name_on_card', $request->get('name_on_card'));
                    $postAddCreditCard = $this->forward('RocketSellerTwoPickBundle:PaymentMethodRest:postAddCreditCard', array('request' => $request), array('_format' => 'json'));
                    if ($postAddCreditCard->getStatusCode() != Response::HTTP_CREATED) {
                        $this->addFlash('error', $postAddCreditCard->getContent());
                        return $this->redirectToRoute("subscription_error");
                        //throw $this->createNotFoundException($data->getContent());
                    } else {
                        $methodId = json_decode($postAddCreditCard->getContent(), true);
                        $purchaseOrder = $this->createPurchaceOrder($user, isset($methodId['response']['method-id']) ? $methodId['response']['method-id'] : false);

                        if ($purchaseOrder) {
                            return $this->redirectToRoute("subscription_success");
                        }
                        return $this->redirectToRoute("subscription_error");
                    }
                } else {
                    $this->addFlash('error', 'Error al insertar en novopayment');
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
        return $this->render('RocketSellerTwoPickBundle:Subscription:subscriptionSuccess.html.twig', array(
                    'user' => $this->getUser(),
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
