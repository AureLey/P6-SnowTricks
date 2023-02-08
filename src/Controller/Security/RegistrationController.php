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
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    private MailerService $mailer;

    public function __construct(MailerService $mailer)
    {
        $this->mailer = $mailer;
    }

    #[Route('/signup', name: 'app_signup')]
    public function signup(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();

        // Form creation and handleRequest.
        $formRegistration = $this->createForm(RegistrationFormType::class, $user);
        $formRegistration->handleRequest($request);

        // Get password
        $plaintextPassword = $formRegistration->get('password')->getData();

        if ($formRegistration->isSubmitted() && $formRegistration->isValid()) {
            // Hash the password (based on the security.yaml config for the $user class).
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $plaintextPassword
            );
            $user->setPassword($hashedPassword);
            // Token Creation method in User.
            $token = $user->tokenCreation($user->getUsername());
            // set TokenValidation time.
            $date = new \DateTime('now');

            // Set Token parameters in user.
            $user->setTokenValidation($date);
            $user->setToken($token);

            // Call service MailerService.
            $this->mailer->sendConfirmation($user);

            // Persist and flush.
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('homepage');
        }

        return $this->render('registration/signup.html.twig', [
            'formRegistration' => $formRegistration->createView(),
        ]);
    }

    #[Route('/email-verified', name: 'app_verified_mail')]
    /**
     * verifiedEmail.
     * Function who check if User exist then set to 1 User field isVerified by the mail confirmation.
     * 
     * @param  Request $request
     * @param  UserRepository $userRepo
     * @param  EntityManagerInterface $entityManager
     * @return Response
     */
    public function verifiedEmail(Request $request, UserRepository $userRepo, EntityManagerInterface $entityManager): Response
    {
        // Get username from URL request.
        $name = $request->get('user');
        $token = $request->get('token');

        // Test if null and return to homepage.
        if (null === $name) {
            return $this->redirectToRoute('homepage');
        }

        // Query with username parameters and set existingUser var.
        $existingUser = $userRepo->findOneBy(['username' => $name,
                                              'token' => $token,
                                            ]);

        // Test if null and return to homepage.
        if (null === $existingUser) {
            // Should return message dont' exist.
            return $this->redirectToRoute('homepage');
        }

        // Set a datatime to compare it with the datetime's token.
        $dateValidation = new \DateTime('now');

        if ($existingUser->verificationTokenTime($existingUser->getTokenValidation(), $dateValidation)) {
            // Set and persist new status of isVerified in User ExistingUser.
            $existingUser->setIsVerified(true);

            $entityManager->persist($existingUser);
            $entityManager->flush();

            // Return to the login page if everything works.
            return $this->redirectToRoute('app_login');
        }

        // Back to signup page if something wrong.
        return $this->redirectToRoute('app_signup');
    }
}
