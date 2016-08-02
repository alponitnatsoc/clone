<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\User;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;
use RocketSeller\TwoPickBundle\Traits\SubscriptionMethodsTrait;

class SubscriptionRestController extends FOSRestController
{

    use SubscriptionMethodsTrait;

    private function paySubscriptionToDays($daysToPay)
    {
        $users = $this->getDoctrine()->getRepository('RocketSeller\TwoPickBundle\Entity\User')->findBy(
            array('dayToPay' => $daysToPay)
        );
        $responses = array();

        if ($users) {
            /* @var $user User */
            foreach ($users as $user) {

                $response = $this->paySubscriptionUser($user);
                if ($response) {
                    $responses['OK'][$user->getId()] = $response;
                } else {
                    $responses['ERROR'][$user->getId()] = $response;
                }
            }
            return $responses;
        } else {

        }
        return false;
    }

    /**
     * Pago de membresia de un usuario en especifico
     * @param User $user
     * @return boolean
     */
    private function paySubscriptionUser(User $user)
    {
        $day = $this->getDaysSince($user->getLastPayDate(), date_create(date('Y-m-d')));
        if ($day === true || ($day->d >= 28) || ($day->m >= 1)) {
            if ($this->createPurchaceOrder($user)) {
                return true;
            }
        }
//realizar pago, enviar correo con factura al usuario
//en caso que el pago no se pueda realizar que hay que hacer? 

        return false;
    }

    /**
     * crear tramites para backOffice
     *
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "crear tramites para backOffice",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the mail no send"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher
     *
     * @RequestParam(name="idUser", description="Recibe el id del usuario")
     *
     *
     *
     * @return View
     */
    public function postCrearTramitesAction(ParamFetcher $paramFetcher)
    {
        $idUser = ($paramFetcher->get('idUser'));
        /* @var $user User */
        $user = $this->getUserById($idUser);
        $tramites = $this->crearTramites($user);

        $view = View::create();
        $view->setData(array('tramites' => $tramites->getContent()));
        $view->setStatusCode(200);
        return $view;
    }

    /**
     * crear notificaciones iniciales
     *
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "crear tramites para backOffice",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the mail no send"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher
     *
     * @RequestParam(name="idUser", description="Recibe el id del usuario")
     *
     *
     *
     * @return View
     */
    public function postCrearNotificacionesAction(ParamFetcher $paramFetcher)
    {
        $idUser = ($paramFetcher->get('idUser'));
        /* @var $user User */
        $user = $this->getUserById($idUser);
        $documents = $this->validateDocuments($user);
        $view = View::create();
        $view->setData(array('documents' => $documents));
        $view->setStatusCode(200);
        return $view;
    }

    /**
     * pagar membresia de un usuario especifico
     *
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "pagar membresia de un usuario especifico",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the mail no send"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher
     *
     * @RequestParam(name="idUser", description="Recibe el id del usuario")
     *
     *
     *
     * @return View
     */
    public function postSubscriptionPayAction(ParamFetcher $paramFetcher)
    {
        $idUser = ($paramFetcher->get('idUser'));
        /* @var $user User */
        $user = $this->getUserById($idUser);
        $responses = array();
        $view = View::create();
        if ($user) {
            if ($response = $this->paySubscriptionUser($user)) {
                $responses['OK'][$user->getId()] = $response;
            } else {
                $responses['ERROR'][$user->getId()] = $response;
            }
            $view->setData($responses)->setStatusCode(200);
        } else {
            $view->setData('Usuario no encontrado:' . $idUser)->setStatusCode(400);
        }
        return $view;
    }

    /**
     * pagar subscripcion de un usuario
     *
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "enviar email de pago exitoso",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the mail no send"
     *   }
     * )
     *
     *
     * @return View
     */
    public function putSubscriptionPayAction()
    {
        $daysToPay = array(28, 29, 30, 31);
        //$lasDayOfMonth = intval(date("t", strtotime("2015-03-23")));
        $lasDayOfMonth = intval(date("t"));
        //$dayNow = intval(date("d", strtotime("2015-03-28")));
        $dayNow = intval(date("d"));
        if ($dayNow === $lasDayOfMonth && in_array($dayNow, $daysToPay)) {
            foreach ($daysToPay as $key => $value) {
                if ($value < $dayNow) {
                    unset($daysToPay[$key]);
                }
            }
        } else {
            $daysToPay = array($dayNow);
        }
        $view = View::create();
        $response = $this->paySubscriptionToDays($daysToPay);
        if ($response) {
            $view->setData($response)->setStatusCode(200);
        } else {

            $view->setData('Usuarios no encontrados')->setStatusCode(400);
        }
        return $view;
    }

}
