<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Validator\ConstraintViolationList;

class RegistrationRestController extends FOSRestController
{
    /**
     * Enviar correo para confirmar cuenta<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Enviar correo para confirmar cuenta.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher
     *
     * @RequestParam(name="id", description="Recibe el id del usuario")
     *
     * @return View
     */
    public function postSendConfirmationEmailAction(ParamFetcher $paramFetcher)
    {

        $id = $paramFetcher->get("id");
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');

        /** @var \RocketSeller\TwoPickBundle\Entity\User $user */
        $user = $userManager->findUserBy(array("id" => $id));
        //         md0eqx-wrrF6e4fs3Z9T86a2_cpmcTrgy5wK6VzwYOc
        if ($user->getConfirmationToken() !== null) {

            $tsm = $this->get('symplifica.mailer.twig_swift');
            $response = $tsm->sendConfirmationEmailMessage($user);
        } else {
            $response = "Esta cuenta ya fue verificada";
        }

        $view = View::create();
        $view->setData($response)->setStatusCode(200);

        return $view;
    }

    /**
     * Get the validation errors
     *
     * @param ConstraintViolationList $errors Validator error list
     *
     * @return View
     */
    protected function getErrorsView(ConstraintViolationList $errors)
    {
        $msgs = array();
        $errorIterator = $errors->getIterator();
        foreach ($errorIterator as $validationError) {
            $msg = $validationError->getMessage();
            $params = $validationError->getMessageParameters();
            $msgs[$validationError->getPropertyPath()][] = $this->get('translator')->trans($msg, $params, 'validators');
        }
        $view = View::create($msgs);
        $view->setStatusCode(400);

        return $view;
    }

}