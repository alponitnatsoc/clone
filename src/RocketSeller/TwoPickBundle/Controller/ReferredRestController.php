<?php

namespace RocketSeller\TwoPickBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use RocketSeller\TwoPickBundle\Traits\ReferredMethodsTrait;

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
        $data = $this->validateCode($code);
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
     * @RequestParam(name="code", nullable=false, strict=true, description="Codigo a validar")
     * 
     *
     * @return View
     */
    public function postValidateCodeAction(ParamFetcher $paramFetcher)
    {
        $data = $this->validateCode($paramFetcher->get('code'));
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
