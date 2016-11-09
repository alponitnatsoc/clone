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
     * @param User $user
     * @param EmployerHasEmployee $eHE
     *
     * @return void
     */
    public function sendBackValidatedMessage(User $user,EmployerHasEmployee $eHE);


    /**
     * Send email tester
     *
     * @param array $context with all the parameters needed
     * @return Void
     */
    public function sendTestEmailsMessage($context);

    }
