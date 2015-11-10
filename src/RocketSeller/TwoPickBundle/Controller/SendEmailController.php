<?php
namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Mandrill;
use Symfony\Component\HttpFoundation\JsonResponse;

class SendEmailController extends Controller
{
    const MANDRILL_API_KEY = "yHsfD-NztDn4JOPbhD2VYg";

    /**
     * Servicio para enviar correos, recibe parametros por POST con los datos necesarios para envair el correo.
     *
     * @param Request $request
     * Parametros recibidos por POST
     * @param $template_name - Recibe el nombre del template del correo creado en Mandrill
     * @param $to - Array con la información de los email a donde se va a enviar el correo, debe tener la siguiente estructura:
     *          array(
     *              'email' => Email al que se va a enviar el correo (Requerido),
     *              'name' => Nombre del propietario del email, si no se envia se toma el email (opcional),
     *              'type' => Tipo de envio, puede ser to, cc, bcc (opcional)
     *          )
     *
     * @throws Mandrill_Error
     */
    public function indexAction(Request $request)
    {
        $req = $request->request;
        if($req->all()) {
            $template_name = ($req->get("template_name"))?:"New Symplifica";
            $to = $req->get("to");
        }

        try {
            $mandrill = new Mandrill(self::MANDRILL_API_KEY);
            $template_name = $template_name;
            $message = array(
                'subject' => 'Email enviado a *|USER|*',
                'from_email' => 'info@symplifica.com',
                'from_name' => 'Symplifica',
                'to' => $to,
//                 array(
//                     array(
//                         'email' => 'plinio.romero@symplifica.com',
//                         'name' => 'Plinio Romero Symplifica',
//                         'type' => 'to'
//                     ),
//                     array(
//                         'email' => 'romero.p.mfc@gmail.com',
//                         'name' => 'Plinio Romero Gmail',
//                         'type' => 'to'
//                     )
//                 ),
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
        } catch(Mandrill_Error $e) {
            // Mandrill errors are thrown as exceptions
            echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
            // A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
            throw $e;
        }

        return new JsonResponse($send);
    }

    public function testSendEmailAction() {
        return $this->render("RocketSellerTwoPickBundle:SendEmail:post-send.html.twig");
    }
}