<?php

namespace App\Controller;

use App\Entity\Trick;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TrickPageController extends AbstractController
{
    #[Route('/trick/details/{slug}', name: 'trickpagedetail')]
    public function showTrick(Trick $trick): Response
    {
        return $this->render('trickpage/trickpage.html.twig', [
            'controller_name' => 'TrickPageController',
            'trick'=> $trick,            
        ]);
    }


    #[Route('/trick/details/{slug}/update', name: 'updatetrickpage')]
    public function updateTrick(Trick $trick): Response
    {        
        return $this->render('trickpage/updatetrickpage.html.twig', [
            'controller_name' => 'TrickPageController',
            'trick'=> $trick,
        ]);
    }
    #[Route('/trick/details/new', name: 'newtrickpage')]
    public function newTrick(): Response
    {        
        return $this->render('trickpage/newtrickpage.html.twig', [
            'controller_name' => 'TrickPageController',
        ]);
    }
}
