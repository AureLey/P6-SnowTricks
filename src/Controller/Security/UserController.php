<?php

namespace App\Controller\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/signin', name: 'signin')]
    public function signin(): Response
    {
        return $this->render('formpage/signin.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/forgot_password', name: 'forgotpassword')]
    public function forgotPassword(): Response
    {
        return $this->render('formpage/forgotpassword.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/reset_password', name: 'resetpassword')]
    public function resetPassword(): Response
    {
        return $this->render('formpage/resetpassword.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/signup', name: 'signup')]
    public function signup(): Response
    {
        return $this->render('formpage/signup.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
}