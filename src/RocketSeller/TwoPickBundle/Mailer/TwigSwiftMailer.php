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
use FOS\RestBundle\Controller\FOSRestController;
use FOS\UserBundle\Model\UserInterface;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\Phone;
use RocketSeller\TwoPickBundle\Entity\User;
use Swift_Attachment;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ZipArchive;

/**
 * @author Christophe Coevoet <stof@notk.org>
 */
class TwigSwiftMailer extends Controller implements MailerInterface
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

    public function sendEmailByTypeMessage($context){
        switch ($context['emailType']){
            /** tested OK */
            //$context['emailType']=='welcome'
            case 'welcome':
                /** $context must have:
                 * User $user
                 */
                $template = $this->parameters['template']['welcome'];
                $interval = new \DateInterval("P30D");
                $date = $context['user']->getDateCreated()->add($interval);
                $context['toEmail']= $context['user']->getEmail();
                $context['fechaFin']= strftime("%d de %B de %Y", $date->getTimestamp());//mas 30 dias
                $context['codigoReferidos'] = $context['user']->getCode();
                return $this->sendMessage($template,$context,$this->parameters['from_email']['confirmation'], $context['toEmail']);
                break;
            /** tested OK */
            //$context['emailType']=='confirmation'
            case 'confirmation':
                /** $context must have:
                 * User $user
                 */
                $template = $this->parameters['template']['confirmation'];
                $url = $this->router->generate('fos_user_registration_confirm', array('token' => $context['user']->getConfirmationToken()), UrlGeneratorInterface::ABSOLUTE_URL);
                $context['confirmationUrl']=$url;
                $context['toEmail']=$context['user']->getEmail();
                return $this->sendMessage($template, $context, $this->parameters['from_email']['confirmation'], $context['toEmail']);
                break;
            //$context['emailType']=='resetting'
            case 'resetting':
                /** $context must have:
                 * User $user
                 */
                $template = $this->parameters['template']['resetting'];
                $url = $this->router->generate('fos_user_resetting_reset', array('token' => $context['user']->getConfirmationToken()), UrlGeneratorInterface::ABSOLUTE_URL);
                $context['confirmationUrl']=$url;
                $context['toEmail']=$context['user']->getEmail();
                return $this->sendMessage($template, $context,$this->parameters['from_email']['confirmation'], $context['toEmail']);
                break;
            /** tested OK */
            //$context['emailType']=='reminder'
            case 'reminder':
                /** $context must have:
                 * string toEmail
                 */
                $template = $this->parameters['template']['reminder'];
                return $this->sendMessage($template, $context, 'registro@symplifica.com' ,$context['toEmail']);
                break;
            /** tested OK */
            //$context['emailType']=='help'
            case 'help':
                /** $context must have:
                 * string name
                 * string fromEmail
                 * string message
                 * string ip
                 * string phone
                 * string subject
                 */
                $template = $this->parameters['template']['help'];
                return $this->sendMessage($template, $context, 'registro@symplifica.com','contactanos@symplifica.com');
                break;
            /** tested OK */
            //$context['emailType']=='help2'
            case 'help2':
                /** $context must have:
                 * string name
                 * string fromEmail
                 * string message
                 * string ip
                 * string phone
                 * string subject
                 */
                $template = $this->parameters['template']['help'];
                return $this->sendMessage($template, $context, 'registro@symplifica.com','servicioalcliente@symplifica.com');
                break;
            /** tested OK */
            //$context['emailType']=='daviplata'
            case 'daviplata':
                /** $context must have:
                 * string toEmail
                 * User user
                 * string subject
                 */
                $template = $this->parameters['template']['daviplata'];
                return $this->sendMessage($template,$context,'registro@symplifica.com', $context['toEmail']);
                break;

            //$context['emailType']=='reminderPay'
            case 'reminderPay':
                /** $context must have:
                 * string toEmail
                 * int days
                 * string userName
                 */
                $template = $this->parameters['template']['remindNovelty'];
                return $this->sendMessage($template,$context,'registro@symplifica.com', $context['toEmail']);
                break;
            //$context['emailType']=='lastReminderPay'
            case 'lastReminderPay':
                /** $context must have:
                 * string toEmail
                 * int days
                 * string userName
                 */
                $template = $this->parameters['template']['lastRemindNovelty'];
                return $this->sendMessage($template,$context,'registro@symplifica.com', $context['toEmail']);
                break;
            case 'succesRecollect':
                /** $context must have:
                 * string toEmail
                 * string documentName
                 * string userName
                 * DateTime fechaRecaudo
                 * float value
                 */
                $template = $this->parameters['template']['succesRecollect'];

                return $this->sendMessage($template,$context,'registro@symplifica.com', $context['toEmail'],$context['path']);
                break;
            case 'failRecollect':
                /** $context must have:
                 * string userEmail
                 * string toEmail
                 * string documentName
                 * string userName
                 * DateTime rejectionDate
                 * float value
                 * string phone
                 */
                $template = $this->parameters['template']['failRecollect'];
                return $this->sendMessage($template,$context,'registro@symplifica.com', $context['toEmail']);
                break;
            case 'regectionCollect':
                /** $context must have:
                 * string userEmail
                 * string toEmail
                 * string documentName
                 * string userName
                 * DateTime rejectionDate
                 * float value
                 * string phone
                 */
                $template = $this->parameters['template']['regectionCollect'];
                return $this->sendMessage($template,$context,'registro@symplifica.com', $context['toEmail']);
                break;
            case 'regectionDispersion':
                /** $context must have:
                 * string userEmail
                 * string toEmail
                 * string documentName
                 * string userName
                 * DateTime rejectionDate
                 * string phone
                 * string rejectedProduct
                 * int idPOD
                 * float value
                 */
                $template = $this->parameters['template']['regectionDispersion'];
                return $this->sendMessage($template,$context,'registro@symplifica.com', $context['toEmail']);
                break;
            case 'succesDispersion':
                /** $context must have:
                 * string toEmail
                 * string userName
                 * if comprobante
                 * string path
                 * string documentName
                 */
                $template = $this->parameters['template']['succesDispersion'];
                if($context['comprobante']){
                    return $this->sendMessage($template,$context,'registro@symplifica.com', $context['toEmail'],$context['path']);
                }else{
                    return $this->sendMessage($template,$context,'registro@symplifica.com', $context['toEmail']);
                }
                break;
            case 'failDispersion':
                /** $context must have:
                 * string toEmail
                 * string userName
                 */
                $template = $this->parameters['template']['failDispersion'];
                return $this->sendMessage($template,$context,'registro@symplifica.com', $context['toEmail']);
                break;
            case 'validatePayMethod':
                /** $context must have:
                 * string toEmail
                 * string userName
                 * string payMethod
                 * DateTime startDate
                 */
                $template = $this->parameters['template']['validatePayMethod'];
                return $this->sendMessage($template,$context,'registro@symplifica.com', $context['toEmail']);
                break;
            case 'backWarning':
                /** $context must have:
                 * string toEmail
                 * int idPod
                 */
                $template = $this->parameters['template']['backoffice_warning'];
                return $this->sendMessage($template,$context,'registro@symplifica.com', $context['toEmail']);
                break;
            /** tested OK */
            //$context['emailType']=='liquidation'
            case 'liquidation':
                /** $context must have:
                 * string toEmail
                 * string userName
                 * string employerSociety
                 * string documentNumber
                 * string userEmail
                 * string phone
                 * string employeeName
                 * string sqlNumber
                 */
                $template = $this->parameters['template']['liquidation'];
                return $this->sendMessage($template,$context,'registro@symplifica.com', $context['toEmail']);
                break;
            //$context['emailType']=='transactionAcepted'
            case 'transactionAcepted':
                /** $context must have:
                 * string toEmail
                 * string userName
                 */
                $template = $this->parameters['template']['transactionAcepted'];
                return $this->sendMessage($template,$context,'registro@symplifica.com', $context['toEmail']);
                break;
            //$context['emailType']=='transactionRejected'
            case 'transactionRejected':
                /** $context must have:
                 * string toEmail
                 * string userName
                 * DateTime rejectionDate
                 */
                $template = $this->parameters['template']['transactionRejected'];
                return $this->sendMessage($template,$context,'registro@symplifica.com', $context['toEmail']);
                break;
            //$context['emailType']=='reminderDaviplata'
            case 'reminderDaviplata':
                /** $context must have:
                 * string toEmail
                 * string userName
                 * string employeeName
                 */
                $template = $this->parameters['template']['reminderDaviplata'];
                return $this->sendMessage($template,$context,'registro@symplifica.com', $context['toEmail']);
                break;
        }

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

//    public function sendDaviplataReminderMessage(User $user , $employeeName)
//    {
//        $to = $user->getEmail();
//        $template = $this->parameters['template']['reminderDaviplata'];
//        $context = array(
//            'toEmail' => $user->getEmail(),
//            'user' => $user,
//            'employeeName' => $employeeName,
//            'subject'=> "Recordatorio Crear Daviplata",
//            'userName' => $user->getPersonPerson()->getFullName(),
//        );
//        return $this->sendMessage($template,$context,'registro@symplifica.com', $to);
//    }
//    public function sendHelpEmailMessage($name, $fromEmail,$subject,$message,$ip,$phone)
//    {
//        $template = $this->parameters['template']['help'];
//        $context = array(
//            'name' => $name,
//            'fromEmail' =>$fromEmail,
//            'subject' =>$subject,
//            'message' =>$message,
//            'ip'=> $ip,
//            'phone'=>$phone
//        );
//
//        return $this->sendMessage($template, $context, $fromEmail ,'contactanos@symplifica.com');
//    }

//    public function sendReminderEmailMessage(UserInterface $user, $toEmail)
//    {
//        $template = $this->parameters['template']['reminder'];
//        $context = array(
//            'user' => $user,
//            'subject' => 'Documentos necesarios para el registro',
//            'toEmail' => $toEmail
//        );
//
//        return $this->sendMessage($template, $context, 'registro@symplifica.com' ,$toEmail);
//    }

//    public function sendReminderPayEmailMessage(User $user , $days)
//    {
//        $to = $user->getEmail();
//        $template = $this->parameters['template']['remindNovelty'];
//        $context = array(
//            'toEmail' => $user->getEmail(),
//            'subject'=> "Recordatorio Agregar Novedades",
//            'dias' => $days,
//            'userName' => $user->getPersonPerson()->getFullName(),
//        );
//        return $this->sendMessage($template,$context,'registro@symplifica.com', $to);
//    }

//    public function sendLastReminderPayEmailMessage(User $user , $days)
//    {
//        $to = $user->getEmail();
//        $template = $this->parameters['template']['lastRemindNovelty'];
//        $context = array(
//            'toEmail' => $user->getEmail(),
//            'user' => $user,
//            'subject'=> "Recordatorio Agregar Novedades",
//            'dias' => $days,
//            'userName' => $user->getPersonPerson()->getFullName(),
//        );
//        return $this->sendMessage($template,$context,'registro@symplifica.com', $to);
//    }

//        public function sendDaviplataMessage(UserInterface $user){
//        $to = $user->getEmail();
//        $template = $this->parameters['template']['daviplata'];
//        $context = array(
//            'toEmail' => $user->getEmail(),
//            'user' => $user,
//            'subject'=> "Informacion Daviplata"
//        );
//        $this->sendMessage($template,$context,$this->parameters['from_email']['confirmation'], $to);
//    }

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

    public function sendEmail(UserInterface $user, $templateName, $fromEmail, $toEmail, $path = null)
    {
        $context = array(
            'toEmail' => $toEmail,
            'user' => $user
        );

        return $this->sendMessage($templateName, $context, $fromEmail, $toEmail, $path);
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
//    public function sendBackOfficeWarningMessage( $idPayroll ){
//        $template = $this->parameters['template']['backoffice_warning'];
//        $context = array(
//            'toEmail' => $to,
//            'idPod'=>$idPayroll
//        );
//        return $this->sendMessage($template,$context,"cagaste_guebon@symplifica.com", "johonson.aguirre@symplifica.com");
//    }

    public function sendLogMessage($content){
        $to = "andres.ramirez@symplifica.com";
        $template=$this->parameters['template']['log'];
        $context = array(
            'toEmail' => "andres.ramirez@symplifica.com",
            'htmlBody' => $content
        );
        return $this->sendMessage($template,$context,'log@symplifica.com', $to);
    }


//    public function sendSuccesRecollectMessage(User $user,$fechaRecaudo,$value,$dis,$path)
//    {
//        $to = $user->getEmail();
//        $template = $this->parameters['template']['succesRecollect'];
//        $context = array(
//            'document'=>'Factura'.date_format($fechaRecaudo,'d-m-y H:i:s').'.pdf',
//            'toEmail' => $user->getEmail(),
//            'user' => $user,
//            'subject'=> "Exito Recaudo",
//            'userName' => $user->getPersonPerson()->getFullName(),
//            'fechaRecaudo' => $fechaRecaudo,
//            'value'=>$value
//        );
//        return $this->sendMessage($template,$context,'registro@symplifica.com', $to,$path);
//    }

//    public function sendFailRecollectMessage(User $user,$fechaRecaudo,$value)
//    {
//        $to = $user->getEmail();
//
//        $context = array(
//            'toEmail' => $user->getEmail(),
//            'user' => $user,
//            'subject'=> "Fallo Recaudo",
//            'userName' => $user->getPersonPerson()->getFullName(),
//            'fechaRecaudo' => $fechaRecaudo,
//            'value'=>$value,
//            'phone'=>$user->getPersonPerson()->getPhones()->first()->getPhoneNumber()
//
//        );
//        return $this->sendMessage($template,$context,'registro@symplifica.com', $to);
//    }

    public function sendTestEmailsMessage($context)
    {
        $path = null;
        $template = $this->parameters['template'][$context['template']];
        if($context['path']){
            $path = $context['path'];
        }
        $to = $context['toEmail'];
        return $this->sendMessage($template,$context,'registro@symplifica.com', $to,$path);
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

        if($fromEmail==$this->parameters['from_email']['confirmation']){
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

        if (!empty($htmlBody)) {
            $message->setBody($htmlBody, 'text/html')
                ->addPart($textBody, 'text/plain');
        } else {
            $message->setBody($textBody, 'text/plain');
        }
        if ($path) {
            $message->attach(Swift_Attachment::fromPath(getcwd().'/'.$path)->setFilename($context['documentName']));
        }
        return $this->mailer->send($message);
    }

    
}
