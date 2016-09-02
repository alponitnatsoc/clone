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

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\UserInterface;
use RocketSeller\TwoPickBundle\Entity\EmployerHasEmployee;
use RocketSeller\TwoPickBundle\Entity\PurchaseOrders;
use RocketSeller\TwoPickBundle\Entity\User;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
interface MailerInterface
{
    /**
     * Send an email by tipe of message
     * @param array $context
     * @return void
     */
    public function sendEmailByTypeMessage($context);

    /**
     * Send an email to a user to confirm the account creation
     *
     * @param UserInterface $user
     *
     * @return void
     */
    public function sendConfirmationEmailMessage(UserInterface $user);

    /**
     * Send an email to a user to confirm the password reset
     *
     * @param UserInterface $user
     *
     * @return void
     */
    public function sendResettingEmailMessage(UserInterface $user);

    /**
     * Send an welcome email
     *
     * @param UserInterface $user
     *
     * @return void
     */
    public function sendWelcomeEmailMessage(UserInterface $user);

//    /**
//     * Send an reminder email
//     *
//     * @param String $toEmail
//     *
//     * @return void
//     */
//    public function sendReminderEmailMessage(UserInterface $user,$toEmail);

//    /**
//     * Send a reminder email
//     *
//     * @param User $user usuario al que se le envia el email
//     * @param int $days tipo de recordatorio para el usuario ultimo dia habil
//     * @return void
//     */
//    public function sendLastReminderPayEmailMessage(User $user , $days);
    
//    /**
//     * Send a reminder email
//     *
//     * @param User $user usuario al que se le envia el email
//     * @param int $days tipo de recordatorio para el usuario 2 o 3 dias habiles
//     * @return void
//     */
//    public function sendReminderPayEmailMessage(User $user, $days);

//    /**
//     * Send help message
//     * @param String $name
//     * @param String $fromEmail
//     * @param String $subject
//     * @param String $ip
//     * @param number $phone
//     *
//     * @return void
//     */
//    public function sendHelpEmailMessage($name, $fromEmail,$subject,$message,$ip,$phone);

//    /**
//     * Send an reminder DaviPlata email
//     *
//     * @param UserInterface $user
//     *
//     * @return void
//     */
//    public function sendDaviplataMessage(UserInterface $user);

    /**
     * Send an reminder OneDay email
     *
     * @param UserInterface $user
     * @param EmployerHasEmployee $eHE 
     *
     * @return void
     */
    public function sendOneDayMessage(UserInterface $user,EmployerHasEmployee $eHE);
    /**
     * Send an reminder DiasHabilesemail
     *
     * @param User $user
     * @param EmployerHasEmployee $eHE
     *
     * @return void
     */
    public function sendDiasHabilesMessage(User $user,EmployerHasEmployee $eHE);

    /**
     * Send log status
     *
     * @param String $content
     *
     * @return void
     */
    public function sendLogMessage($content);
    /**
     * Send an reminder Backoffice validation
     *
     * @param UserInterface $user
     * @param EmployerHasEmployee $eHE
     *
     * @return void
     */
    public function sendBackValidatedMessage(UserInterface $user,EmployerHasEmployee $eHE);

//    /**
//     * Send a reminder create daviplata email
//     *
//     * @param User $user usuario al que se le envia el email
//     * @param String $employeName nombre del empleado
//     * @return void
//     */
//    public function sendDaviplataReminderMessage(User $user, $employeeName);

//    /**
//     * Send the succes Collect Email
//     *
//     * @param User $user
//     * @param DateTime $fechaRecaudo
//     * @param float $value
//     * @param PurchaseOrders $dis
//     * @param string $path
//     * @return Void
//     */
//    public function sendSuccesRecollectMessage(User $user, $fechaRecaudo, $value,$dis,$path);

    /**
     * Send email tester
     *
     * @param array $context with all the parameters needed
     * @return Void
     */
    public function sendTestEmailsMessage($context);

//    /**
//     * Send a remind backoffice to check payrolls
//     *
//     * @param int $idPayroll
//     */
//    public function sendBackOfficeWarningMessage( $idPayroll);

    }
