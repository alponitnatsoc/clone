<?php
namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SendEmailController extends Controller
{
    public function indexAction()
    {
        $subject = 'Hello from Mandrill, PHP!';
        $from = array('test@swiftmailer.mandrill' =>'Plinio Test Mandrill From');
        $to = array(
            'plinio.romero@symplifica.com'  => 'Plinio Symplifica',
            'romero.p.mfc@gmail.com' => 'Plinio Gmail'
        );

        $text = "Mandrill speaks plaintext";
        $html = "<em>Mandrill speaks <strong>HTML</strong></em>";

        $message2 = \Swift_Message::newInstance();
        $message2->setSubject($subject);
        $message2->setFrom($from);
        $message2->setTo($to);
        $message2->setBody(
//             $this->renderView(
//                 // app/Resources/views/Emails/registration.html.twig
//                 'Emails/registration.html.twig',
//                 array('name' => $name)
//             ),
            $html,
            "text/html"
        );
         $message2->addPart(
//             $this->renderView(
//                 'Emails/registration.txt.twig',
//                 array('name' => $name)
//             ),
            $text,
            'text/plain'
         );

        try{
            $send = $this->get("mailer")->send($message2);
        } catch(\Exception $e) {
            echo "OUCH";
        }
        if ($send) {
            echo 'Message successfully sent!';
        } else {
            echo "There was an error:\n";
        }

        return $this->render('RocketSellerTwoPickBundle:General:mandril-test.html.twig', array("send" => $send));

    }
}