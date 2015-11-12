<?php

namespace RocketSeller\TwoPickBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Request\ParamFetcher;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Validator\ConstraintViolationList;

use Mandrill;

class SendEmailRestController extends FOSRestController
{
    /**
     * Servicio para enviar correos, recibe parametros por POST con los datos necesarios para envair el correo.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Metodo que se utiliza para actualizar el numero de la factura en una orden de compra.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher
     *
     * @RequestParam(name="template_name", description="Recibe el nombre del template del correo creado en Mandrill")
     * @RequestParam(array=true, name="to", nullable=false, strict=true, description="Array con la información de los email a donde se va a enviar el correo
     *          array(
     *              'email' => Email al que se va a enviar el correo (Requerido),
     *              'name' => Nombre del propietario del email, si no se envia se toma el email (opcional),
     *              'type' => Tipo de envio, puede ser to, cc, bcc (opcional)
     *          )")
     *
     * @throws Mandrill_Error
     * @return \FOS\RestBundle\View\View
     */
    public function postSendEmailAction(ParamFetcher $paramFetcher) {

        $template_name = ($paramFetcher->get("template_name"))?:"New Symplifica";
        $to = $paramFetcher->get("to");

        $view = View::create();

        try {
            $mandrill = new Mandrill($this->container->getParameter("mandrill_api_key"));
            $template_name = $template_name;
            $message = array(
                'subject' => 'Email enviado a *|USER|*',
                'from_email' => 'info@symplifica.com',
                'from_name' => 'Symplifica',
                'to' => $to,
                'headers' => array('Reply-To' => 'reply@symplifica.com'),
                'merge_language' => 'mailchimp',
                'global_merge_vars' => array(
                    array(
                        'name' => 'COM',
                        'content' => 'Symplifica S.A.S.'
                    )
                ),
                'merge_vars' => array(
                    array(
                        'rcpt' => 'plinio.romero@symplifica.com',
                        'vars' => array(
                            array(
                                'name' => 'USER',
                                'content' => 'Plinio Romero Symplifica'
                            )
                        )
                    ),
                    array(
                        'rcpt' => 'romero.p.mfc@gmail.com',
                        'vars' => array(
                            array(
                                'name' => 'USER',
                                'content' => 'Plinio Romero Gmail'
                            )
                        )
                    )
                ),
                'metadata' => array('website' => 'www.symplifica.com'),
                'recipient_metadata' => array(
                    array(
                        'rcpt' => 'plinio.romero@symplifica.com',
                        'values' => array('user_id' => 123456)
                    )
                )
            );
            $send = $mandrill->messages->sendTemplate($template_name, null, $message);
            $view->setData($send)->setStatusCode(200);
        } catch(Mandrill_Error $e) {
            // Mandrill errors are thrown as exceptions
            echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
            // A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
            throw $e;
        }

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