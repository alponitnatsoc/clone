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
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Phone;
use RocketSeller\TwoPickBundle\Entity\User;
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

    

    public function sendHelpEmailMessage($name, $fromEmail,$subject,$message,$ip,$phone)
    {
        $template = $this->parameters['template']['help'];
        $context = array(
            'name' => $name,
            'fromEmail' =>$fromEmail,
            'subject' =>$subject,
            'message' =>$message,
            'ip'=> $ip,
            'phone'=>$phone
        );
        
        return $this->sendMessage($template, $context, $fromEmail ,'contactanos@symplifica.com');
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

    public function sendReminderPayEmailMessage(User $user , $days)
    {
        $to = $user->getEmail();
        $template = $this->parameters['template']['remindNovelty'];
        $context = array(
            'toEmail' => $user->getEmail(),
            'user' => $user,
            'subject'=> "Recordatorio Agregar Novedades",
            'dias' => $days,
            'userName' => $user->getPersonPerson()->getFullName(),
        );
        return $this->sendMessage($template,$context,'registro@symplifica.com', $to);
    }

    public function sendLastReminderPayEmailMessage(User $user , $days)
    {
        $to = $user->getEmail();
        $template = $this->parameters['template']['lastRemindNovelty'];
        $context = array(
            'toEmail' => $user->getEmail(),
            'user' => $user,
            'subject'=> "Recordatorio Agregar Novedades",
            'dias' => $days,
            'userName' => $user->getPersonPerson()->getFullName(),
        );
        return $this->sendMessage($template,$context,'registro@symplifica.com', $to);
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
    
    public function sendDaviplataMessage(UserInterface $user){
        $to = $user->getEmail();
        $template = $this->parameters['template']['daviplata'];
        $context = array(
            'toEmail' => $user->getEmail(),
            'user' => $user,
            'subject'=> "Informacion Daviplata"
        );
        $this->sendMessage($template,$context,$this->parameters['from_email']['confirmation'], $to);
    }

    public function sendOneDayMessage(UserInterface $user,EmployerHasEmployee $eHE){
        $to = $user->getEmail();
        $template = $this->parameters['template']['oneday'];
        $context = array(
            'toEmail' => $user->getEmail(),
            'user' => $user,
            'subject'=> "Inicio de proceso de validación",
            'employeeName'=>$eHE->getEmployeeEmployee()->getPersonPerson()->getNames(),
        );
        return $this->sendMessage($template,$context,$this->parameters['from_email']['confirmation'], $to);
    }
    public function sendDiasHabilesMessage(User $user,EmployerHasEmployee $eHE){
        $to = $user->getEmail();
        $template = $this->parameters['template']['diashabiles'];
        $context = array(
            'toEmail' => $user->getEmail(),
            'user' => $user,
            'userName'=> $user->getPersonPerson()->getFullName(),
            'subject'=> "Inicio de proceso de afiliación",
            'employeeName'=>$eHE->getEmployeeEmployee()->getPersonPerson()->getNames(),
        );
        return $this->sendMessage($template,$context,$this->parameters['from_email']['confirmation'], $to);
    }

    public function sendBackValidatedMessage(UserInterface $user,EmployerHasEmployee $eHE){
        $to = $user->getEmail();
        $template = $this->parameters['template']['backval'];
        $context = array(
            'toEmail' => $user->getEmail(),
            'user' => $user,
            'subject'=> "Confirmacíon de afiliación",
            'employeeName'=>$eHE->getEmployeeEmployee()->getPersonPerson()->getNames(),
        );
        return $this->sendMessage($template,$context,$this->parameters['from_email']['confirmation'], $to);
    }
    public function sendBackOfficeWarningMessage( $idPayroll){
        $to = "johonson.aguirre@symplifica.com";
        $template = $this->parameters['template']['backoffice_warning'];
        $context = array(
            'toEmail' => $to,
            'idPod'=>$idPayroll
        );
        return $this->sendMessage($template,$context,"cagaste_guebon@symplifica.com", $to);
    }

    public function sendLogMessage($content){
        $to = "andres.ramirez@symplifica.com";
        $template=$this->parameters['template']['log'];
        $context = array(
            'toEmail' => "andres.ramirez@symplifica.com",
            'htmlBody' => $content
        );
        return $this->sendMessage($template,$context,'log@symplifica.com', $to);
    }

    public function sendDaviplataReminderMessage(User $user , $employeeName)
    {
        $to = $user->getEmail();
        $template = $this->parameters['template']['reminderDaviplata'];
        $context = array(
            'toEmail' => $user->getEmail(),
            'user' => $user,
            'employeeName' => $employeeName,
            'subject'=> "Recordatorio Crear Daviplata",
            'userName' => $user->getPersonPerson()->getFullName(),
        );
        return $this->sendMessage($template,$context,'registro@symplifica.com', $to);
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
        $message ="";
        if($fromEmail==$this->parameters['from_email']['confirmation'] or $fromEmail ==$this->parameters['from_email']['resetting']){
            $message = \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom($fromEmail)
                ->setTo($toEmail)
                ->setPriority(1);
        }else{
            $message = \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom(array($fromEmail=>'Equipo Symplifica'))
                ->setTo($toEmail)
                ->setReplyTo("contactanos@symplifica.com")
                ->setPriority(1);
        }

        if ($path) {
            $message->attach(\Swift_Attachment::fromPath($path));
        }
        if (!empty($htmlBody)) {
            $message->setBody($htmlBody, 'text/html')
                ->addPart($textBody, 'text/plain');
        } else {
            $message->setBody($textBody, 'text/plain');
        }

        return $this->mailer->send($message);
    }

    
}
