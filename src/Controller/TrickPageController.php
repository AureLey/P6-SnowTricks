<?php

namespace App\Controller;
use App\Entity\User;
use App\Entity\Trick;
use App\Form\TrickFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TrickPageController extends AbstractController
{
    #[Route('/trick/new', name: 'newtrickpage')]
    public function newTrick(Request $request, EntityManagerInterface $manager,UserRepository $repo): Response
    {  
        //CREATE USER 
        $user = new User();
        $user = $repo->findBy(['email'=>'admin@admin.com']);
        $user = $user[0];

        
        $slugger = new AsciiSlugger();
              
        $trick = new Trick();   
        $formTrick = $this->createForm(TrickFormType::class, $trick );
        
        $formTrick->handleRequest($request);

        if($formTrick->isSubmitted() && $formTrick->isValid())
        {   
            
            dd($formTrick);
            dd($trick);
            $now = new \DateTimeImmutable('now');
            $trick->setUser($this->getUser());
            $trick->setCreatedAt($now);
            $trick->setUpdatedAt($now);
            $slug = $slugger->slug($trick->getName());
            $trick->setSlug($slug);
            //$trick->setUser($this->getUser());
            $trick->setUser($user);

            $manager->persist($trick);
            $manager->flush();

            return $this->redirectToRoute('trickpagedetail',['slug'=>$trick->getSlug()]);
        }
        
        return $this->render('trickpage/newtrickpage.html.twig', [
            'controller_name' => 'TrickPageController',
            'trickform' => $formTrick->createView(),
        ]);
    }

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
        $form = $this->createForm(TrickFormType::class, $trick );
        
        return $this->render('trickpage/updatetrickpage.html.twig', [
            'controller_name' => 'TrickPageController',
            'trick'=> $trick,
            'trickform' => $form->createView(),
        ]);
    }
    
}
