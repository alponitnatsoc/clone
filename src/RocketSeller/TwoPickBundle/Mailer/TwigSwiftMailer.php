<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RocketSeller\TwoPickBundle\Mailer;

use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @author Christophe Coevoet <stof@notk.org>
 */
class TwigSwiftMailer implements MailerInterface
{
    protected $mailer;
    protected $router;
    protected $twig;
    protected $parameters;

    public function __construct(\Swift_Mailer $mailer, UrlGeneratorInterface $router, \Twig_Environment $twig, array $parameters)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->twig = $twig;
        $this->parameters = $parameters;
    }

    public function sendConfirmationEmailMessage(UserInterface $user)
    {
        $template = $this->parameters['template']['confirmation'];
        $url = $this->router->generate('fos_user_registration_confirm', array('token' => $user->getConfirmationToken()), UrlGeneratorInterface::ABSOLUTE_URL);

        $context = array(
            'user' => $user,
            'confirmationUrl' => $url
        );

        return $this->sendMessage($template, $context, $this->parameters['from_email']['confirmation'], $user->getEmail());
    }

    

    public function helpEmail($name, $templateName, $fromEmail, $toEmail,$subject,$message,$ip,$phone, $path = null)
    {
        $msg = array(
            'name' => $name,
            'toEmail' => $toEmail,
            'fromEmail' =>$fromEmail,
            'subject' =>$subject,
            'message' =>$message,
            'ip'=> $ip,
            'phone'=>$phone
        );
        
        $context = $this->twig->mergeGlobals($msg);
        $template = $this->twig->loadTemplate($templateName);
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail);
        
        if ($path) {
            $message->attach(\Swift_Attachment::fromPath($path));
        }

        return $this->mailer->send($message);
    }
    

    public function sendResettingEmailMessage(UserInterface $user)
    {
        $template = $this->parameters['template']['resetting'];
        $url = $this->router->generate('fos_user_resetting_reset', array('token' => $user->getConfirmationToken()), UrlGeneratorInterface::ABSOLUTE_URL);

        $context = array(
            'user' => $user,
            'confirmationUrl' => $url
        );

        return $this->sendMessage($template, $context, $this->parameters['from_email']['resetting'], $user->getEmail());
    }

    public function sendReminderEmailMessage(UserInterface $user, $toEmail)
    {
        $template = $this->parameters['template']['reminder'];
        $context = array(
            'user' => $user,
            'subject' => 'Documentos necesarios para el registro',
            'toEmail' => $toEmail
        );

        return $this->sendMessage($template, $context, 'registro@symplifica.com' ,$toEmail);
    }
    
    public function sendWelcomeEmailMessage(UserInterface $user)
    {
        $template = $this->parameters['template']['welcome'];

        $interval = new \DateInterval("P30D");
        $date = $user->getDateCreated()->add($interval);

        $context = array(
            'fechaFin' => strftime("%d de %B de %Y", $date->getTimestamp()), //mas 30 dias
            'codigoReferidos' => $user->getCode(),
            'user' => $user
        );

        return $this->sendMessage($template, $context, $this->parameters['from_email']['confirmation'], $user->getEmail());
    }

    public function sendEmail(UserInterface $user, $templateName, $fromEmail, $toEmail, $path = null)
    {
        $context = array(
            'toEmail' => $toEmail,
            'user' => $user
        );

        return $this->sendMessage($templateName, $context, $fromEmail, $toEmail, $path);
    }
    
    /**
     * @param string $templateName
     * @param array  $context
     * @param string $fromEmail
     * @param string $toEmail
     * @param string $path - Path donde se encuentra el archivo a enviar como adjunto en el correo
     */
    protected function sendMessage($templateName, $context, $fromEmail, $toEmail, $path = null)
    {
        $context = $this->twig->mergeGlobals($context);
        $template = $this->twig->loadTemplate($templateName);
        $subject = $template->renderBlock('subject', $context);
        $textBody = $template->renderBlock('body_text', $context);
        $htmlBody = $template->renderBlock('body_html', $context);
        

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail);

        if ($path) {
            $message->attach(\Swift_Attachment::fromPath($path));
        }
        dump($htmlBody);
        if (!empty($htmlBody)) {
            $message->setBody($htmlBody, 'text/html')
                ->addPart($textBody, 'text/plain');
        } else {
            $message->setBody($textBody, 'text/plain');
        }

        return $this->mailer->send($message);
    }
    
}
