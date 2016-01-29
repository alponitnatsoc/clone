<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Traits\InvitationMethodsTrait;
use RocketSeller\TwoPickBundle\Traits\UserMethodsTrait;

class InvitationRestController extends FOSRestController
{

    use InvitationMethodsTrait,
        UserMethodsTrait;

    /**
     * Valida si el codigo ingresado pertenece a algun usuario.<br/>
     * 
     * /api/public/v1/validates/{code}/code
     * api_public_get_validate_code  
     * 
     * @ApiDoc(
     *   resource = true,
     *   description = "Valida si el codigo ingresado pertenece a algun usuario",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param integer $email - email al que se envio la invitacion
     *
     * @return View
     */
    public function getInvitationByEmailAction($email)
    {
        $data = $this->findInvitationByEmail($email);
        $view = View::create();
        $view->setData($data);
        if ($data) {
            $view->setStatusCode(200);
        } else {
            $view->setStatusCode(400);
        }
        return $view;
    }

    /**
     * Valida si el codigo ingresado pertenece a algun usuario.<br/>
     * 
     * /api/public/v1/validates/{code}/code
     * api_public_get_validate_code  
     * 
     * @ApiDoc(
     *   resource = true,
     *   description = "Valida si el codigo ingresado pertenece a algun usuario",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     * 
     * @RequestParam(name="userId", nullable=false, strict=true, description="Codigo a validar")
     * @RequestParam(name="email", nullable=false, strict=true, description="Codigo a validar")
     * @RequestParam(name="status", nullable=false, strict=true, description="Codigo a validar")
     * @RequestParam(name="sent", nullable=false, strict=true, description="Codigo a validar")
     * 
     *
     * @return View
     */
    public function putInvitationAction(ParamFetcher $paramFetcher)
    {
        $view = View::create();
        $view->setStatusCode(200);

        $user = $this->getUserById($paramFetcher->get('userId'));
        $email = $paramFetcher->get('email');
        $status = $paramFetcher->get('status');
        $sent = $paramFetcher->get('sent');
        if ($user && $email && $status && $sent) {
            $invitation = new Invitation();
            $invitation->setUserId($user);
            $invitation->setEmail($email);
            $invitation->setStatus($status);
            $invitation->setSent($sent);

            $em = $this->getDoctrine()->getManager();
            $em->persist($invitation);
            $em->flush();

            if ($invitation->getId()) {
                $view->setStatusCode(200);
                $view->setData($invitation);
            } else {
                $view->setStatusCode(400);
                $view->setData(false);
            }
        } else {
            $view->setStatusCode(400);
            $view->setData(false);
        }
        return $view;
    }

}
