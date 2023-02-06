<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\MailerService;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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

        $formRegistration = $this->createForm(RegistrationFormType::class, $user);
        $formRegistration->handleRequest($request);
        $plaintextPassword = $formRegistration->get('password')->getData();
        

        if ($formRegistration->isSubmitted() && $formRegistration->isValid()) {
            // hash the password (based on the security.yaml config for the $user class)
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $plaintextPassword
            );
            $user->setPassword($hashedPassword);
            // Token Creation method in User
            $token= $user->tokenCreation($user->getUsername());
            // set TokenValidation time;
            $date = new \Datetime("now");
            

            // Set Token paramters in user
            $user->setTokenValidation($date);
            $user->setToken($token);           
            
            $this->mailer->sendConfirmation($user);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('homepage');
        }
        
        return $this->render('registration/signup.html.twig', [            
            'formRegistration' => $formRegistration->createView(),            
        ]);
    }
    #[Route('/email-verified', name: 'app_verified_mail')]
    public function verifiedEmail(Request $request, UserRepository $userRepo): Response
    {
        // get username from URL request
        $name = $request->get('user');
        $token = $request->get('token');
        
        // test if null and return to homepage
        if(null === $name)
        {
            dd($name);
            return $this->redirectToRoute('homepage');
        }

        // query with username parameters and set existingUser var       
        $existingUser = $userRepo->findOneBy(['username'=>$name,
                                              'token' => $token,
                                            ]);
        
        // test if null and return to homepage
        if(null === $existingUser)
        {   
            // should return message dont' exist
            return $this->redirectToRoute('homepage');
        }
        // convertir le tableau existing en object User
        
        $dateValidation = new \DateTime("now");
                
        if($existingUser->verificationTokenTime($existingUser->getTokenValidation(),$dateValidation))
        {
            return $this->redirectToRoute('signin');  
        }
        else
        {
            return $this->redirectToRoute('signup');
        }
        

        
    }
}
