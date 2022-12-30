<?php

namespace App\Controller;

use App\Repository\TrickRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomepageController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(TrickRepository $repo): Response
    {
        $tricks = $repo->findAll();
        return $this->render('homepage/homepage.html.twig', [
            'controller_name' => 'HomepageController',
            'tricks' => $tricks,
        ]);
    }
}
