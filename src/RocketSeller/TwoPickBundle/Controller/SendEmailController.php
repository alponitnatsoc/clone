<?php
namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class SendEmailController extends Controller
{
    public function testSendEmailAction() {

        return $this->render("RocketSellerTwoPickBundle:SendEmail:post-send.html.twig", array(
            'sendEmailSerivice' => $this->generateUrl("api_public_post_send_email")
        ));
    }

    public function sendEmailAction() {
        $destinatario = "plinio.romero@symplifica.com";
        $asunto = "Este mensaje es de prueba";
        $cuerpo = '
<html>
<head>
   <title>Prueba de correo</title>
</head>
<body>
<h1>Hola amigos!</h1>
<p>
<b>Bienvenidos a mi correo electrónico de prueba</b>. Estoy encantado de tener tantos lectores. Este cuerpo del mensaje es del artículo de envío de mails por PHP. Habría que cambiarlo para poner tu propio cuerpo. Por cierto, cambia también las cabeceras del mensaje.
</p>
</body>
</html>
';

        //para el envío en formato HTML
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";

        //dirección del remitente
        $headers .= "From: Plinio Romero Test <plinio.romero@symplifica.com>\r\n";

        //dirección de respuesta, si queremos que sea distinta que la del remitente
        $headers .= "Reply-To: plinio.romero@symplifica.com\r\n";

        //ruta del mensaje desde origen a destino
        $headers .= "Return-path: plinio.romero@symplifica.com\r\n";

        //direcciones que recibián copia
        $headers .= "Cc: plinio.romero@symplifica.com\r\n";

        //direcciones que recibirán copia oculta
        $headers .= "Bcc: plinio.romero@symplifica.com,plinio.romero@symplifica.com\r\n";

        $res = mail($destinatario,$asunto,$cuerpo,$headers);

        return new JsonResponse($res);
    }
}