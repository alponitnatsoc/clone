<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Entity\User;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RequestParam;

class SubscriptionRestController extends FOSRestController
{

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
        $lasDayOfMonth = intval(date("t", strtotime("2015-02-23")));
        $dayNow = intval(date("d", strtotime("2015-02-28")));
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
        if ($user->getId() == 3) {
            return false;
        }
        //realizar pago, enviar correo con factura al usuario
        //en caso que el pago no se pueda realizar que hay que hacer? 

        return true;
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
     * @param string $idUser id del usuario
     * 
     *
     * @return View
     */
    public function postPaySubscriptionAction($idUser)
    {
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

            $view->setData('OK')->setStatusCode(200);
        } else {
            $view->setData('Usuario no encontrado:' . $id)->setStatusCode(400);
        }
        return $view;
    }

    /**
     * 
     * @param int $idUser id del usuario a buscar
     * @return User|null
     */
    private function getUserById($idUser)
    {
        return $this->getDoctrine()->getRepository('RocketSeller\TwoPickBundle\Entity\User')->findOneBy(
                        array('id' => $idUser)
        );
    }

    /**
     * enviar email de pago exitoso.
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
     * @param string $idUser id del usuario
     * @param string $idPurchaseOrder id de la orden de compra
     * 
     *
     * @return View
     */
    public function postSendEmailPaySuccessAction($idUser, $idPurchaseOrder)
    {
        $view = View::create();
        /* @var $user User */
        $user = $this->getUserById($idUser);
        $path = null;
        if ($user) {
            $response = $this->forward('RocketSellerTwoPickBundle:Document:downloadDocuments', array(
                'ref' => 'factura', 'id' => $idPurchaseOrder, 'type' => 'pdf', 'attach' => 1
            ));

            $response = json_decode($response->getContent(), true);

            if (isset($response['name-path'])) {
                $path = $this->get('kernel')->getRootDir() . "/../web/public/docs/tmp/invoices/" . $response['name-path'];
                //D:\drive\Multiplica\symplifica\app/../web/public/docs/tmp/invoices/8061777-123.pdf 
            } else {
                $path = null;
            }
            $toEmail = $user->getEmail();

            $fromEmail = "servicioalcliente@symplifica.com";

            $tsm = $this->get('symplifica.mailer.twig_swift');

            $response = $tsm->sendEmail($user, "RocketSellerTwoPickBundle:Subscription:paySuccess.txt.twig", $fromEmail, $toEmail, $path);

            $view->setData($response)->setStatusCode(200);
        } else {
            $view->setData('Usuario ' . $id . ' no encontrado')->setStatusCode(400);
        }
        if ($path != null) {
            //unlink($path);
        }
        return $view;
    }

}
