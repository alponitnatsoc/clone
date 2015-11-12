<?php
namespace RocketSeller\TwoPickBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SendEmailController extends Controller
{
    public function testSendEmailAction() {

        return $this->render("RocketSellerTwoPickBundle:SendEmail:post-send.html.twig", array(
            'sendEmailSerivice' => $this->generateUrl("api_public_post_send_email")
        ));
    }
}