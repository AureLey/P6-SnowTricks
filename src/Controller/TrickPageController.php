<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Image;
use App\Entity\Trick;
use App\Form\TrickFormType;
use App\Service\FileUploader;
use App\Repository\UserRepository;
use App\Repository\ImageRepository;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TrickPageController extends AbstractController
{
    #[Route('/trick/new', name: 'new_trick')]
    public function newTrick(Request $request, EntityManagerInterface $entityManager, UserRepository $repo, SluggerInterface $slugger, FileUploader $fileUploader): Response
    {
        //CREATE USER 
        $user = new User();
        $user = $repo->findBy(['email' => 'admin@admin.com']);
        $user = $user[0];

        $slugTrickName = new AsciiSlugger();

        $trick = new Trick();
        $form = $this->createForm(TrickFormType::class, $trick);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get UploadedListFile and add it to the trick as Image object
            $imagesListUpload = $form->get('images')->getData();
            //Featured Image traitment
            $featuredImageFile = $form->get('featuredImage')->getData();

            if ($imagesListUpload) {
                foreach ($imagesListUpload as $imageUpload) {

                    $imageName = $fileUploader->upload($imageUpload->getFile());
                    $imageUpload->setName($imageName);

                    $trick->addImage($imageUpload);
                }
            }
            //test if featured is null and give first image like default featured image to the trick
            if ($featuredImageFile) {

                $featuredImageName = $fileUploader->upload($featuredImageFile);
                $trick->setFeaturedImage($featuredImageName);
            } else {
                $trick->setFeaturedImage($trick->getImages()->first()->getName());
            }

            $now = new \DateTimeImmutable('now');
            $trick->setUser($this->getUser())
                ->setCreatedAt($now)
                ->setUpdatedAt($now)
                ->setUser($user);
            //      ->setUser($this->getUser()); 
            $slugName = $slugTrickName->slug($trick->getName());
            $trick->setSlug($slugName);

            $entityManager->persist($trick);
            //dd($trick);         
            $entityManager->flush();

            return $this->redirectToRoute('trick_detail', ['slug' => $trick->getSlug()]);
        }

        return $this->render('trickpage/newtrickpage.html.twig', [
            'controller_name' => 'TrickPageController',
            'trickform' => $form->createView(),
        ]);
    }

    #[Route('/trick/details/{slug}', name: 'trick_detail')]
    public function showTrick(Trick $trick): Response
    {
        return $this->render('trickpage/trickpage.html.twig', [
            'controller_name' => 'TrickPageController',
            'trick' => $trick,
        ]);
    }


    #[Route('trick/delete/{slug}', name: 'delete_trick')]
    public function deleteTrick(Trick $trick, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($trick);
        $entityManager->flush();
        return $this->RedirectToRoute('homepage');
    }


    #[Route('/trick/update/{slug}', name: 'update_trick')]
    public function updateTrick(Trick $trick, Request $request, EntityManagerInterface $entityManager, UserRepository $repo, TrickRepository $repoTrick, ImageRepository $repoImage, FileUploader $fileUploader): Response
    {

        //CREATE USER 
        $user = new User();
        $user = $repo->findBy(['email' => 'admin@admin.com']);
        $user = $user[0];

        //create file_path
        $slugger = new AsciiSlugger();

        $form = $this->createForm(TrickFormType::class, $trick)
            ->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            // Get UploadedListFile and add it to the trick as Image object
            $imagesList = $form->get('images')->getData();

            if ($imagesList) {
                foreach ($imagesList as $image) {

                    if ($image->getId() === null || !empty($image->getFile())) {
                        $imageName = $fileUploader->upload($image->getFile());
                        $image->setName($imageName);
                        $trick->addImage($image);
                    }
                }
            }

            //checking If Field FeaturedImage get a file, and update it
            $featuredImageFile = $form->get('featuredImage')->getData();
            if ($featuredImageFile) {
                $featuredImage = $fileUploader->upload($featuredImageFile);
                $trick->setFeaturedImage($featuredImage);
            }

            $now = new \DateTimeImmutable('now');
            $trick->setUser($this->getUser())
                ->setUpdatedAt($now)
                ->setUser($user);
            //      ->setUser($this->getUser()); 
            $slug = $slugger->slug($trick->getName());
            $trick->setSlug($slug);

            $entityManager->persist($trick);
            $entityManager->flush();

            return $this->RedirectToRoute('trick_detail', ['slug' => $trick->getSlug()]);
        }

        return $this->render('trickpage/updatetrickpage.html.twig', [
            'controller_name' => 'TrickPageController',
            'trick' => $trick,
            'trickform' => $form->createView(),
        ]);
    }
}
