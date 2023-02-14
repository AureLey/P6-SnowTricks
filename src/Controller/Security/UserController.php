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

namespace App\Controller\Security;

use App\Entity\User;
use App\Form\ForgotPasswordFormType;
use App\Form\ResetPasswordFormType;
use App\Repository\UserRepository;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private MailerService $mailer;

    public function __construct(MailerService $mailer)
    {
        $this->mailer = $mailer;
    }

    #[Route('/forgot_password', name: 'forgotpassword')]
    public function forgotPassword(Request $request, UserRepository $userRepo, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        // Form creation and handleRequest.
        $formForgot = $this->createForm(ForgotPasswordFormType::class)->handleRequest($request);

        if ($formForgot->isSubmitted() && $formForgot->isValid()) {
            // Check if user exist in DB.
            $user = $userRepo->findOneBy(['username' => $formForgot->get('username')->getData()]);

            // If user exist continue
            if ($user) {
                // Token Creation method in User.
                $token = $user->creationTokenReset($user->getId());
                // set TokenValidation time.
                $date = new \DateTime('now');

                // Set Token parameters in user.
                $user->setTokenValidation($date);
                $user->setToken($token);

                // Call service MailerService.
                $this->mailer->sendReset($user);

                // Persist and flush.
                $entityManager->persist($user);
                $entityManager->flush();
            }
            $this->addFlash('success', 'Check your Mailbox!');

            return $this->redirectToRoute('homepage');
        }

        return $this->render('form_user/forgotpassword.html.twig', [
            'controller_name' => 'UserController',
            'formForgot' => $formForgot->createView(),
        ]);
    }

    #[Route('/reset_password/{token}', name: 'resetpassword')]
    public function resetPassword(
        string $token,
        Request $request,
        UserRepository $userRepo,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager): Response
    {
        // Query with username parameters and set existingUser var.
        $user = $userRepo->findOneBy(['token' => $token]);

        // Test if null and return to homepage.
        if (null === $user) {
            // Should return message dont' exist.
            return $this->redirectToRoute('homepage');
        }

        if ($user) {
            // set TokenValidation time.
            $date = new \DateTime('now');
            if ($user->verificationTokenTime($date, $user->getTokenValidation())) {
                // Form creation and handleRequest.
                $formReset = $this->createForm(ResetPasswordFormType::class)->handleRequest($request);

                if ($formReset->isSubmitted() && $formReset->isValid()) {
                    // Get password
                    $plaintextPassword = $formReset->get('password')->getData();
                    // Hash the password (based on the security.yaml config for the $user class).
                    $hashedPassword = $passwordHasher->hashPassword($user, $plaintextPassword);

                    $user->setToken(null)
                        ->setTokenValidation(null)
                        ->setPassword($hashedPassword);

                    $entityManager->persist($user);
                    $entityManager->flush();

                    $this->addFlash('success', 'Password is reset!');

                    return $this->redirectToRoute('homepage');
                }

                return $this->render('form_user/resetpassword.html.twig', [
                    'controller_name' => 'UserController',
                    'formReset' => $formReset->createView(),
                    ]);
            }
            $this->addFlash('danger', 'Token not valid');
            // Should return message dont' exist.
            return $this->redirectToRoute('homepage');
        }
    }
}
