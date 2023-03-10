<?php

declare(strict_types=1);

/*
 * This file is part of Snowtricks
 *
 * (c)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class MailerService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendConfirmation(User $user)
    {
        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('Registration Confirmation')
            ->htmlTemplate('registration/_mailVerified.html.twig')
            ->context([
                'user' => $user,
            ]);

        // On envoie le mail
        $this->mailer->send($email);
    }

    public function sendReset(User $user)
    {
        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('Reset password')
            ->htmlTemplate('form_user/_mail_reset.html.twig')
            ->context([
                'user' => $user,
            ]);

        // On envoie le mail
        $this->mailer->send($email);
    }
}
