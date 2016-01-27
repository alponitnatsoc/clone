<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Traits\InvitationMethodsTrait;

class InvitationRestController extends FOSRestController
{

    use InvitationMethodsTrait;

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

}
