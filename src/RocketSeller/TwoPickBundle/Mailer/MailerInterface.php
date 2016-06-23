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

/**
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 */
interface MailerInterface
{
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
     * Send an reminder email
     *
     * @param String $toEmail
     *
     * @return void
     */
    public function sendReminderEmailMessage($toEmail);

    /**
     * Sen email
     *
     * @param UserInterface $user
     *
     * @return void
     */
    public function sendEmail(UserInterface $user, $templateName, $fromEmail, $toEmail);
}
