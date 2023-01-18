<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Trick;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ImagesController extends AbstractController
{
    #[Route('/images/remove/{id}', name: 'remove_image')]
    public function removeImage(Image $image, EntityManagerInterface $entityManager): Response
    {
               
    }
    #[Route('/images/update/{id}', name: 'update_image')]
    public function updateImage(Image $image): Response
    {
        
        
        
    }
}
