<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolation;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use Symfony\Component\Validator\Constraints\Date;
use RocketSeller\TwoPickBundle\Entity\User;
use FOS\RestBundle\EventListener\ParamFetcherListener;
use FOS\RestBundle\Request\ParamReader;
use FOS\RestBundle\Tests\Request\ParamFetcherTest;

class SubscriptionRestController extends FOSRestController
{

    /**
     * enviar email de pago exitoso.
     *
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "enviar email de pago exitoso",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the mail no send"
     *   }
     * )
     *
     * @param string $id username or email
     * 
     * @RequestParam(name="username", nullable=false, strict=true, description="Username.")
     * @RequestParam(name="email", nullable=false, strict=true, description="Email.")
     * @RequestParam(name="name", nullable=false, strict=true, description="Name.")
     * @RequestParam(name="lastname", nullable=false, strict=true, description="Lastname.")
     * @RequestParam(name="password", nullable=false, strict=true, description="Plain Password.")
     *
     * @return View
     */
    public function postSendEmailPaySuccessAction($id)
    {

        $toEmail = $this->getUser()->getEmail();

        $fromEmail = "servicioalcliente@symplifica.com";

        $tsm = $this->get('symplifica.mailer.twig_swift');

        $response = $tsm->sendEmail($this->getUser(), "RocketSellerTwoPickBundle:Liquidation:paySuccess.txt.twig", $fromEmail, $toEmail);

        //(UserInterface $user, $templateName, $fromEmail, $toEmail, $path = null)

        $view = View::create();
        $view->setData($response)->setStatusCode(200);

        return $view;
    }

}
