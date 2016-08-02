<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Traits\ReferredMethodsTrait;
use RocketSeller\TwoPickBundle\Entity\Invitation;
use RocketSeller\TwoPickBundle\Entity\Referred;
use RocketSeller\TwoPickBundle\Entity\User;

class ReferredRestController extends FOSRestController
{

    use ReferredMethodsTrait;

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
     * @param integer $code - Codigo de referido a buscar
     *
     * @return View
     */
    public function getValidateCodeAction($code)
    {
        return $this->validateCode($code);
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
     * @RequestParam(name="code", nullable=false, strict=true, description="Codigo a validar")
     * 
     *
     * @return View
     */
    public function postValidateCodeAction(ParamFetcher $paramFetcher)
    {
        $code = $paramFetcher->get('code') ? $paramFetcher->get('code') : false;
        return $this->validateCode($code);
    }

    private function validateCode($code)
    {
        $view = View::create();
        if ($this->getUser()) {
            /* @var $user_dueno User */
            $user_dueno = $this->userValidateCode($code);
            if ($user_dueno) {
                if ($user_dueno->getPaymentState() > 0) {
                    $refered = $this->referedValidateCode($user_dueno->getId(), $this->getUser()->getId());
                    if (!$refered) {

                        $em = $this->getDoctrine()->getManager();

                        $invitation = new Invitation();
                        $invitation->setUserId($user_dueno);
                        $invitation->setEmail($this->getUser()->getEmail());
                        $invitation->setStatus(1);
                        $invitation->setSent(1);
                        $em->persist($invitation);
                        $em->flush();

                        $refered = new Referred();
                        $refered->setUserId($user_dueno);
                        $refered->setReferredUserId($this->getUser());
                        $refered->setStatus(0);
                        $refered->setInvitationId($invitation);

                        $em->persist($refered);
                        $em->flush();

                        $view->setData(true);
                        $view->setStatusCode(Response::HTTP_OK);
                    } else {
                        $view->setStatusCode(Response::HTTP_CREATED);
                        $view->setData('codigo valido, ya redimido');
                    }
                } else {
                    $view->setStatusCode(Response::HTTP_CREATED);
                    $view->setData('codigo valido, pero sin membresia');
                }
            } else {
                $view->setStatusCode(Response::HTTP_CREATED);
                $view->setData('codigo no valido');
            }
        } else {
            $view->setStatusCode(Response::HTTP_CREATED);
            $view->setData('no hay usuario autenticado');
        }
        return $view;
    }

}
