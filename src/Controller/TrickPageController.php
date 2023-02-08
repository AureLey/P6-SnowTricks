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

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Trick;
use App\Entity\Comment;
use App\Form\CommentFormType;
use App\Form\TrickFormType;
use App\Service\FileUploader;
use App\Repository\UserRepository;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TrickPageController extends AbstractController
{
    // Inject Service FileUploader.
    private FileUploader $fileUploader;


    public function __construct(FileUploader $fileUploader)
    {
        $this->fileUploader = $fileUploader;
    }


    #[Route('/trick/new', name: 'new_trick')]
    /**    
     * creation trick function.
     * 
     * @param  Request $request
     * @param  EntityManagerInterface $entityManager
     * @param  UserRepository $repo
     * @return Response
     */


    public function newTrick(Request $request, EntityManagerInterface $entityManager): Response
    {        

        $trick = new Trick();
        $form = $this->createForm(TrickFormType::class, $trick)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Call private function to Set and Upload images collection to the trick.
            $this->setImagesCollection($form->get('images')->getData(), $trick);

            // Call Private function  set FeaturedImage to the trick.
            $this->SetFeaturedImageFile($form->get('featuredImage')->getData(), $trick);

            // Link Trick to the trick.
            $trick->setUser($this->getUser());

            $entityManager->persist($trick);
            $entityManager->flush();

            return $this->redirectToRoute('trick_detail', ['slug' => $trick->getSlug()]);
        }

        return $this->render('trickpage/newtrickpage.html.twig', [
            'controller_name' => 'TrickPageController',
            'trick' => $trick,
            'trickform' => $form->createView(),
        ]);

    }

    #[Route('/trick/details/{slug}', name: 'trick_detail')]
    /**
     * showTrick.
     */
    public function showTrick(Trick $trick,Request $request, EntityManagerInterface $entityManager): Response
    {

        // Create comment object.
        $comment = new Comment();

        // Create the form and handle the request
        $form = $this->createForm(CommentFormType::class)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $comment = $form->getData();
            $comment->setCommentUser($this->getUser());
            $comment->setCommentTrick($trick);            
            
            $entityManager->persist($comment);
            $entityManager->flush();
            return $this->redirectToRoute('trick_detail', ['slug' => $trick->getSlug()]);
        }
        return $this->render('trickpage/trickpage.html.twig', [
            'controller_name' => 'TrickPageController',
            'trick' => $trick,
            'commentForm' => $form->createView(),
        ]);
    }

    #[Route('trick/delete/{slug}', name: 'delete_trick')]
    /**
     * deleteTrick.
     */


    public function deleteTrick(Trick $trick, EntityManagerInterface $entityManager): Response
    {
        if (null !== $trick->getImages()) {
            $this->deleteTrickRemoveImages($trick->getImages());
        }

        if (null !== $trick->getFeaturedImage()) {
            $this->removeImageFile($trick->getFeaturedImage());
        }
        $entityManager->remove($trick);
        $entityManager->flush();

        return $this->RedirectToRoute('homepage');
    }

    #[Route('/trick/update/{slug}', name: 'update_trick')]
    /**
     * updateTrick.
     */


    public function updateTrick(Trick $trick, Request $request, EntityManagerInterface $entityManager, UserRepository $repoUser, ImageRepository $repoImage): Response
    {
        // Get Images Collection to compare with new one and delete file if necessary.
        $oldImagesCollection = $repoImage->findBy(['trick' => $trick]);

        $form = $this->createForm(TrickFormType::class, $trick)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Call private function to Set and Upload images collection to the trick.
            $this->setImagesCollection($form->get('images')->getData(), $trick);

            $this->updateTrickRemovesImagesFileToFolder($oldImagesCollection, $trick->getImages());

            // Call Private function  set FeaturedImage to the trick.
            $this->SetFeaturedImageFile($form->get('featuredImage')->getData(), $trick);
            
            // Link Trick to the trick.
            $trick->setUser($this->getUser());

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

    #[Route('trick/{slug}/remove/featuredImage', name: 'delete_featuredImage')]
    /**
     * deleteFeaturedImage.
     */


    public function deleteFeaturedImage(Trick $trick, EntityManagerInterface $entityManager): Response
    {
        // Remove image file and Set to null in the trick.
        // Test if featuredImage and 1st element of collections are identical, if they are != remove the file.
        if ($trick->getImages()->first()) {
            if ($trick->getFeaturedImage() !== $trick->getImages()->first()->getName()) {
                $this->removeImageFile($trick->getFeaturedImage());
                $trick->setFeaturedImage(null);
            }
        } else {
            $this->removeImageFile($trick->getFeaturedImage());
            $trick->setFeaturedImage(null);
        }

        $entityManager->persist($trick);
        $entityManager->flush();

        return $this->RedirectToRoute('update_trick', ['slug' => $trick->getSlug()]);
    }

    /**
     * SetFeaturedImageFile
     * If form not null, upload featured image file and set it.
     */


    private function SetFeaturedImageFile(?UploadedFile $featuredFormFile, Trick $trick): Trick
    {
        if (null !== $featuredFormFile) {
            // Set Featured Image with the form element
            $featuredName = $this->fileUploader->AddFile($featuredFormFile);
            $trick->setFeaturedImage($featuredName);
        }

        return $trick;
    }

    /**
     * setImagesCollection
     * Get collection from form collection and Set images collection in trick.
     */


    private function setImagesCollection($imagesListUpload, Trick $trick): Trick
    {
        if ($imagesListUpload) {
            // Test collection if image's id is null so no registered and field File not empty, upload and set the file and name
            foreach ($imagesListUpload as $imageUpload) {
                if (null === $imageUpload->getId() || !empty($imageUpload->getFile())) {
                    $fileName = $this->fileUploader->upload($imageUpload->getFile());
                    $imageUpload->setName($fileName);
                    $trick->addImage($imageUpload);
                }
            }
        }

        return $trick;
    }
        
    /**
     * Compare oldArray and new collection and remove files unused.
     * 
     * @param  array $oldCollection
     * @param  Collection $newImagesCollection
     * @return void
     */


    private function updateTrickRemovesImagesFileToFolder(array $oldCollection, Collection $newImagesCollection)
    {
        foreach ($oldCollection as $oldImage) {
            $present = \in_array($oldImage->getName(), $newImagesCollection->toArray(), false);

            if (true !== $present) {
                $this->removeImageFile($oldImage->getName());
            }
        }
    }

    /**
     * deleteTrickRemoveImages
     * remove all images from the selected trick in delete_trick route.
     * @return void
     */


    private function deleteTrickRemoveImages($imagesCollection)
    {
        foreach ($imagesCollection as $image) {
            $this->removeImageFile($image->getName());
        }
    }

    /**
     * removeImageFile
     * get images folder hard path, and remove file by his name.
     *
     * @param string $imageName
     *
     * @return void
     */


    private function removeImageFile(?string $imageName)
    {
        if (null !== $imageName) {
            $this->fileUploader->removeFile($imageName);
        }
    }
}
