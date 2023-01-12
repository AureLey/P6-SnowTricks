<?php

namespace App\Controller;
use App\Entity\User;
use App\Entity\Trick;
use App\Form\TrickFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TrickPageController extends AbstractController
{
    #[Route('/trick/new', name: 'new_trick')]
    public function newTrick(Request $request, EntityManagerInterface $entityManager,UserRepository $repo): Response
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
            $now = new \DateTimeImmutable('now');
            $trick  ->setUser($this->getUser())
                    ->setCreatedAt($now)
                    ->setUpdatedAt($now)
                    ->setUser($user);
            //      ->setUser($this->getUser()); 
            $slug = $slugger->slug($trick->getName());
            $trick->setSlug($slug);          
            
            $entityManager->persist($trick);           
            $entityManager->flush();

            return $this->redirectToRoute('trick_detail',['slug'=>$trick->getSlug()]);
        }       
            
        return $this->render('trickpage/newtrickpage.html.twig', [
            'controller_name' => 'TrickPageController',
            'trickform' => $formTrick->createView(),
        ]);
    }

    #[Route('/trick/details/{slug}', name: 'trick_detail')]
    public function showTrick(Trick $trick): Response
    {
        return $this->render('trickpage/trickpage.html.twig', [
            'controller_name' => 'TrickPageController',
            'trick'=> $trick,            
        ]);
    }


    #[Route ('trick/delete/{slug}', methods: ['GET', 'DELETE'], name :'delete_trick')]
    public function deleteTrick(Trick $trick,EntityManagerInterface $entityManager):Response
    {
        $entityManager->remove($trick);
        $entityManager->flush();
        return $this->RedirectToRoute('homepage');
    }


    #[Route('/trick/update/{slug}', name: 'update_trick')]
    public function updateTrick(Trick $trick,Request $request,EntityManagerInterface $entityManager,UserRepository $repo): Response
    {  
        //CREATE USER 
        $user = new User();
        $user = $repo->findBy(['email'=>'admin@admin.com']);
        $user = $user[0];

        $slugger = new AsciiSlugger();

        $form = $this->createForm(TrickFormType::class, $trick );

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {  
            $now = new \DateTimeImmutable('now');
            $trick  ->setUser($this->getUser())                    
                    ->setUpdatedAt($now)
                    ->setUser($user);
            //      ->setUser($this->getUser()); 
            $slug = $slugger->slug($trick->getName());
            $trick->setSlug($slug);          
            
            $entityManager->persist($trick);           
            $entityManager->flush();

            return $this->RedirectToRoute('trick_detail',['slug'=>$trick->getSlug()]);
        }
        
        return $this->render('trickpage/updatetrickpage.html.twig', [
            'controller_name' => 'TrickPageController',
            'trick'=> $trick,
            'trickform' => $form->createView(),
        ]);
    }
    
}
