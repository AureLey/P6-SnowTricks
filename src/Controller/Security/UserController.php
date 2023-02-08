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

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{   

    #[Route('/forgot_password', name: 'forgotpassword')]
    public function forgotPassword(): Response
    {
        return $this->render('formPage/forgotpassword.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/reset_password', name: 'resetpassword')]
    public function resetPassword(): Response
    {
        return $this->render('formPage/resetpassword.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
}
