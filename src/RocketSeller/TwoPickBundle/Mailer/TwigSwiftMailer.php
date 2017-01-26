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

    public function sendMultipleRecipientsEmailByType($context){
        $fromEmail = array('hola@symplifica.com'=>'Equipo Symplifica');
        $toEmail = $context["toEmail"];
        $replyEmail = array('hola@symplifica.com'=>'Equipo Symplifica');
        $template = $this->parameters['template'][$context['emailType']];
        switch ($context["emailType"]){
            case 'twoMonthsRegistration':
                return $this->sendMessage2($template,$context,$fromEmail,$replyEmail,$toEmail);
                break;
            case 'noRegisterFacebook':
                return $this->sendMessage2($template,$context,$fromEmail,$replyEmail,$toEmail);
                break;
            case 'noRegisterLanding':
                return $this->sendMessage2($template,$context,$fromEmail,$replyEmail,$toEmail);
                break;
            case 'testAnalytics':
                return $this->sendMessage2($template,$context,$fromEmail,$replyEmail,$toEmail);
                break;
        }
    }

    public function sendEmailByTypeMessage($context){
        $registerFromEmail = array('registro@symplifica.com'=>'Registro Symplifica');
        $teamFromEmail=array('registro@symplifica.com'=>'Equipo Symplifica');
        $contactPublicFromEmail=array('registro@symplifica.com'=>'Contacto Público');
        $contactPrivateFromEmail=array('registro@symplifica.com'=>'Contacto Privado');
        $contactPublic = 'contactanos@symplifica.com';
        $contactPrivate = 'servicioalcliente@symplifica.com';
        $testEmail = 'andres.ramirez@symplifica.com';
        $registrationStuck = 'salua.garcia@symplifica.com';
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
                return $this->sendMessage($template,$context,$registerFromEmail, $context['toEmail']);
                break;
            /** tested OK */
            //$context['emailType']=='confirmation'
            case 'confirmation':
                /** $context must have:
                 * User $user
                 */
                return $this->sendConfirmationEmailMessage($context['user']);
                break;
            /** tested OK */
            //$context['emailType']=='resetting'
            case 'resetting':
                /** $context must have:
                 * User $user
                 */
                $template = $this->parameters['template']['resetting'];
                $url = $this->router->generate('fos_user_resetting_reset', array('token' => $context['user']->getConfirmationToken()), UrlGeneratorInterface::ABSOLUTE_URL);
                $context['confirmationUrl']=$url;
                $context['toEmail']=$context['user']->getEmail();
                return $this->sendMessage($template, $context,$registerFromEmail, $context['toEmail']);
                break;
            /** tested OK */
            //$context['emailType']=='reminder'
            case 'reminder':
                /** $context must have:
                 * string toEmail
                 */
                $template = $this->parameters['template']['reminder'];
                return $this->sendMessage($template, $context, $teamFromEmail ,$context['toEmail']);
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
                return $this->sendMessage($template, $context,$contactPublicFromEmail,$contactPublic);
                break;
            /** tested OK */
            //$context['emailType']=='helpPrivate'
            case 'helpPrivate':
                /** $context must have:
                 * string name
                 * string fromEmail
                 * string message
                 * string ip
                 * string phone
                 * string subject
                 */
                $template = $this->parameters['template']['help'];
                return $this->sendMessage($template, $context, $contactPrivateFromEmail,$contactPrivate);
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
                return $this->sendMessage($template,$context,$teamFromEmail, $context['toEmail']);
                break;
            /** tested OK */
            //$context['emailType']=='reminderPay'
            case 'reminderPay':
                /** $context must have:
                 * string toEmail
                 * int days
                 * string userName
                 * boolean isEfectivo
                 */
                $template = $this->parameters['template']['remindNovelty'];
                return $this->sendMessage($template,$context,$teamFromEmail, $context['toEmail']);
                break;
            /** tested OK */
            //$context['emailType']=='lastReminderPay'
            case 'lastReminderPay':
                /** $context must have:
                 * string toEmail
                 * int days
                 * string userName
                 */
                $template = $this->parameters['template']['lastRemindNovelty'];
                return $this->sendMessage($template,$context,$teamFromEmail, $context['toEmail']);
                break;
            /** tested OK */
            //$context['emailType']=='succesRecollect'
            case 'succesRecollect':
                /** $context must have:
                 * string toEmail
                 * string documentName
                 * string userName
                 * DateTime fechaRecaudo
                 * float value
                 */
                $template = $this->parameters['template']['succesRecollect'];
                return $this->sendMessage($template,$context,$teamFromEmail, $context['toEmail'],$context['path']);
                break;
            /** tested OK */
            //$context['emailType']=='failRecollect'
            case 'failRecollect':
                /** $context must have:
                 * string toEmail
                 * string userName
                 * DateTime rejectionDate
                 * float value
                 */
                $template = $this->parameters['template']['failRecollect'];
                return $this->sendMessage($template,$context,$teamFromEmail, $context['toEmail']);
                break;
            /** tested OK */
            //$context['emailType']=='regectionCollect'
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
                return $this->sendMessage($template,$context,$teamFromEmail, $context['toEmail']);
                break;
            /** tested OK */
            //$context['emailType']=='regectionDispersion'
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
                return $this->sendMessage($template,$context,$teamFromEmail, $context['toEmail']);
                break;
            /** tested OK */
            //$context['emailType']=='succesDispersion'
            case 'succesDispersion':
                /** $context must have:
                 * string toEmail
                 * string userName
                 * if comprobante
                 * string path
                 * string documentName
                 * boolean pagoPila
                 */
                $template = $this->parameters['template']['succesDispersion'];
                if($context['comprobante']){
                    return $this->sendMessage($template,$context,$teamFromEmail, $context['toEmail'],$context['path']);
                }else{
                    return $this->sendMessage($template,$context,$teamFromEmail, $context['toEmail']);
                }
                break;
            /** tested OK */
            //$context['emailType']=='failDispersion'
            case 'failDispersion':
                /** $context must have:
                 * string toEmail
                 * string userName
                 */
                $template = $this->parameters['template']['failDispersion'];
                return $this->sendMessage($template,$context,$teamFromEmail, $context['toEmail']);
                break;
            /** tested OK */
            //$context['emailType']=='validatePayMethod'
            case 'validatePayMethod':
                /** $context must have:
                 * string toEmail
                 * string userName
                 * string payMethod
                 * DateTime starDate
                 */
                $template = $this->parameters['template']['validatePayMethod'];
                return $this->sendMessage($template,$context,$teamFromEmail, $context['toEmail']);
                break;
            /** tested OK */
            //$context['emailType']=='backWarning'
            case 'backWarning':
                /** $context must have:
                 * string toEmail
                 * int idPod
                 */
                $template = $this->parameters['template']['backoffice_warning'];
                return $this->sendMessage($template,$context,$teamFromEmail, $context['toEmail']);
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
                return $this->sendMessage($template,$context,$teamFromEmail, $context['toEmail']);
                break;
            /** tested OK */
            //$context['emailType']=='transactionAcepted'
            case 'transactionAcepted':
                /** $context must have:
                 * string toEmail
                 * string userName
                 */
                $template = $this->parameters['template']['transactionAcepted'];
                return $this->sendMessage($template,$context,$teamFromEmail, $context['toEmail']);
                break;
            //$context['emailType']=='transactionRejected'
            case 'transactionRejected':
                /** $context must have:
                 * string toEmail
                 * string userName
                 * DateTime rejectionDate
                 */
                $template = $this->parameters['template']['transactionRejected'];
                return $this->sendMessage($template,$context,$teamFromEmail, $context['toEmail']);
                break;
            //$context['emailType']=='reminderDaviplata'
            case 'reminderDaviplata':
                /** $context must have:
                 * string toEmail
                 * string userName
                 * string employeeName
                 */
                $template = $this->parameters['template']['reminderDaviplata'];
                return $this->sendMessage($template,$context,$teamFromEmail, $context['toEmail']);
                break;
            case 'appDownload':
                    /** $context must have:
                     * string toEmail
                     */
                    $template = $this->parameters['template']['appDownload'];
                    return $this->sendMessage($template, $context, $teamFromEmail ,$context['toEmail']);
                    break;
            case 'descubrir':
                /** $context must have:
                 * string toEmail
                 */
                $template = $this->parameters['template']['descubrir'];
                return $this->sendMessage($template,$context,$teamFromEmail,$context['toEmail']);
            case 'supplies':
                /** $context must have:
                 * string toEmail
                 * string userName
                 */
                $template = $this->parameters['template']['supplies'];
                return $this->sendMessage($template,$context,$teamFromEmail,$context['toEmail']);
            case 'waiting':
                /** $context must have:
                 * string toEmail
                 * string userName
                 */
                $template = $this->parameters['template']['waiting'];
                return $this->sendMessage($template,$context,$teamFromEmail,$context['toEmail']);
            case 'contractFinishReminder':
                /** $context must have:
                 * string toEmail
                 * string userName
                 * string employeeName
                 */
                $template = $this->parameters['template']['contractFinishReminder'];
                return $this->sendMessage($template,$context,$teamFromEmail,$context['toEmail']);
            case 'cesantCharges':
                /** $context must have:
                 * string toEmail
                 * string userName
                 * string employeeName
                 */
                $template = $this->parameters['template']['cesantCharges'];
                return $this->sendMessage($template,$context,$teamFromEmail,$context['toEmail']);
            case 'cesantPayment':
                /** $context must have:
                 * string toEmail
                 * string userName
                 * string employeeName
                 */
                $template = $this->parameters['template']['cesantPayment'];
                return $this->sendMessage($template,$context,$teamFromEmail,$context['toEmail']);
            case 'bonus':
                /** $context must have:
                 * string toEmail
                 * string userName
                 * string employeeName
                 */
                $template = $this->parameters['template']['bonus'];
                return $this->sendMessage($template,$context,$teamFromEmail,$context['toEmail']);
            case 'clientRecovery':
                /** $context must have:
                 * string toEmail
                 * string userName
                 */
                $template = $this->parameters['template']['clientRecovery'];
                return $this->sendMessage($template,$context,$teamFromEmail,$context['toEmail']);
            case 'risks':
                /** $context must have:
                 * string toEmail
                 * string userName
                 */
                $template = $this->parameters['template']['risks'];
                return $this->sendMessage($template,$context,$teamFromEmail,$context['toEmail']);
            case 'contractFinish':
                /** $context must have:
                 * string toEmail
                 * string userName
                 */
                $template = $this->parameters['template']['contractFinish'];
                return $this->sendMessage($template,$context,$teamFromEmail,$context['toEmail']);
            case 'docsValidated':
                /** $context must have:
                 * string toEmail
                 * string userName
                 */
                $template = $this->parameters['template']['docsValidated'];
                return $this->sendMessage($template,$context,$teamFromEmail,$context['toEmail']);
            case 'docsError':
                /** $context must have:
                 * string toEmail
                 * string userName
                 * array errors
                 */
                $template = $this->parameters['template']['docsError'];
                return $this->sendMessage($template,$context,$teamFromEmail,$context['toEmail']);
            case 'employeeDocsError':
                /** $context must have:
                 * string toEmail
                 * string userName
                 * string employeeName
                 * array errors
                 */
                $template = $this->parameters['template']['employeeDocsError'];
                return $this->sendMessage($template,$context,$teamFromEmail,$context['toEmail']);
                break;
            case 'employeeDocsValidated':
                /** $context must have:
                 * string toEmail
                 * string userName
                 * string employeeName
                 */
                $template = $this->parameters['template']['employeeDocsValidated'];
                return $this->sendMessage($template,$context,$teamFromEmail,$context['toEmail']);
                break;
            case 'contractAttachmentEmail':
                /** $context must have:
                 * string toEmail
                 * string userName
                 * strubg docType (contrato o mandato)
                 * string path
                 */
                $template = $this->parameters['template']['contractAttachmentEmail'];
                return $this->sendMessage($template, $context, $teamFromEmail, $context['toEmail'], $context['path']);
                break;
            case 'notRegisteredUserApp':
                /** $context must have:
                 * string name
                 * string userEmail
                 * string phone
                 */
                $template = $this->parameters['template']['notRegisteredUserApp'];
                return $this->sendMessage($template, $context,$contactPublicFromEmail,$contactPublic);
                break;
            case 'helpTransaction':
                /** $context must have:
                 * string name
                 * string userEmail
                 * string phone
                 * string userID
                 * string username
                 * string idPod
                 * string idNovoPay
                 * string statusName
                 * string statusDescription
                 */
                $template = $this->parameters['template']['helpTransaction'];
                return $this->sendMessage($template, $context,$contactPublicFromEmail,$contactPrivate);
                break;

            case 'stuckRegistration':
                /** $context must have:
                 * string name
                 * string userEmail
                 * string phone
                 * string message
                 * string subject
                   */
                $template = $this->parameters['template']['stuckRegistration'];
                return $this->sendMessage($template, $context, $teamFromEmail, $registrationStuck);
                break;
            case 'primaReminder':
                /** $context must have:
                 * string name
                 * string userEmail
                 * string employeeName
                 */
                $template = $this->parameters['template']['primaReminder'];
                return $this->sendMessage($template, $context, $contactPublicFromEmail, $contactPublic);
                break;
            case 'reportVacacionesXD':
                /** $context must have:
                 * string payrollId
                 * string contractId
                 * string startDate
                 * string endDate
                 * string employerName
                 * string employeeName
                 */
                $template = $this->parameters['template']['reportVacacionesXD'];
                return $this->sendMessage($template, $context, $contactPublicFromEmail, $contactPrivate);
                break;
            case 'minimumSalaryAdjust':
                /** $context must have:
                 * string toEmail
                 */
                 $template = $this->parameters['template']['minimumSalaryAdjust'];
                 return $this->sendMessage($template, $context, $teamFromEmail, $context["toEmail"]);
                break;
            case "sendBackLandingInfo":
                /** $context must have:
                 * string email
                 * string name
                 * string phone
                 * string createdAt
                 */
                $template = $this->parameters['template']['sendBackLandingInfo'];
                return $this->sendMessage($template, $context, $contactPublicFromEmail, $registrationStuck);
                break;
            case "promoReferred5days":
                    /** $context must have:
                     * string toEmail
                     * string referredCode
                     */
                    $template = $this->parameters['template']['promoReferred5days'];
                  //TODO (a-santamaria cambiar el form a $mercadeoFromEmail)
                    return $this->sendMessage($template, $context, $contactPublicFromEmail, $context["toEmail"]);
                    break;
            case "promoReferred15days":
                    /** $context must have:
                     * string toEmail
                     * string referredCode
                     */
                    $template = $this->parameters['template']['promoReferred15days'];
                            //TODO (a-santamaria cambiar el form a $mercadeoFromEmail)
                    return $this->sendMessage($template, $context, $contactPublicFromEmail, $context["toEmail"]);
                    break;
            case "severancesAdvice":
                /** $context must have:
                 * string toEmail
                 * string redirectUrl
                 */
                $template = $this->parameters['template']['severancesAdvice'];
                return $this->sendMessage($template, $context, $teamFromEmail, $context["toEmail"]);
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


    public function sendOneDayMessage(UserInterface $user,EmployerHasEmployee $eHE){
        $to = $user->getEmail();
        $template = $this->parameters['template']['oneday'];
        $context = array(
            'toEmail' => $user->getEmail(),
            'user' => $user,
            'subject'=> "Inicio de proceso de validación",
            'employeeName'=>$eHE->getEmployeeEmployee()->getPersonPerson()->getNames(),
        );
        return $this->sendMessage($template,$context,array('registro@symplifica.com'=>'Equipo Symplifica'), $to);
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
        return $this->sendMessage($template,$context,array('registro@symplifica.com'=>'Equipo Symplifica'), $to);
    }

    public function sendBackValidatedMessage(User $user,EmployerHasEmployee $eHE){
        $to = $user->getEmail();
        $template = $this->parameters['template']['backval'];
        $context = array(
            'toEmail' => $user->getEmail(),
            'user' => $user,
            'subject'=> "Confirmacíon de afiliación",
            'employeeName'=>$eHE->getEmployeeEmployee()->getPersonPerson()->getNames(),
        );
        return $this->sendMessage($template,$context,array('registro@symplifica.com'=>'Equipo Symplifica'), $to);
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

    protected function sendMessage2($templateName,$context,$fromEmail,$replyEmail,$toEmail,$path=null)
    {
        $context = $this->twig->mergeGlobals($context);
        $template = $this->twig->loadTemplate($templateName);
        $subject = $template->renderBlock('subject', $context);
        $textBody = $template->renderBlock('body_text', $context);
        $htmlBody = $template->renderBlock('body_html', $context);
        $message ="";
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo(array("Clientes@symplifica.com"=>"Clientes Symplifica"))
            ->setBcc($toEmail)
            ->setReplyTo($replyEmail)
            ->setPriority(1);
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
                ->setFrom($fromEmail)
                ->setTo($toEmail)
                ->setReplyTo("servicioalcliente@symplifica.com")
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
