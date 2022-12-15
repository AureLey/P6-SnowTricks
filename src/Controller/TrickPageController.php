<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrickPageController extends AbstractController
{
    #[Route('/trick', name: 'trickpage')]
    public function index(): Response
    {        
        return $this->render('trickpage/trickpage.html.twig', [
            'controller_name' => 'TrickPageController',
        ]);
    }
}
