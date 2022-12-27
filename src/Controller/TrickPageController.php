<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrickPageController extends AbstractController
{
    #[Route('/trick/details', name: 'trickpagedetail')]
    public function trickdetail(): Response
    {        
        return $this->render('trickpage/trickpage.html.twig', [
            'controller_name' => 'TrickPageController',
        ]);
    }
    #[Route('/trick/details/update', name: 'updatetrickpagedetail')]
    public function updatetrickdetail(): Response
    {        
        return $this->render('trickpage/updatetrickpage.html.twig', [
            'controller_name' => 'TrickPageController',
        ]);
    }
    #[Route('/trick/details/new', name: 'newtrickpagedetail')]
    public function newtrickdetail(): Response
    {        
        return $this->render('trickpage/newtrickpage.html.twig', [
            'controller_name' => 'TrickPageController',
        ]);
    }
}
