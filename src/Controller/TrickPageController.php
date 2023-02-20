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

use App\Entity\Comment;
use App\Entity\Trick;
use App\Entity\User;
use App\Form\CommentFormType;
use App\Form\TrickFormType;
use App\Repository\CommentRepository;
use App\Repository\ImageRepository;
use App\Service\FileManager;
use App\Service\Media\MediaManager;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrickPageController extends AbstractController
{
    // Inject Service Service\FileManager.
    private FileManager $fileManager;
    // Inject Service Media\MediaManager
    private MediaManager $mediaManager;

    public function __construct(FileManager $fileManager, MediaManager $mediaManager)
    {
        $this->fileManager = $fileManager;
        $this->mediaManager = $mediaManager;
    }

    #[Route('/trick/new', name: 'new_trick')]
    /**
     * creation trick function.
     */
    public function newTrick(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Check permission to delete via Voter function.
        $this->denyAccessUnlessGranted('ROLE_USER');
        $trick = new Trick();
        $form = $this->createForm(TrickFormType::class, $trick)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Call private function to Set and Upload images collection to the trick.
            $this->mediaManager->setImagesCollection($form->get('images')->getData(), $trick);

            // Call Private function  set FeaturedImage to the trick.
            $this->mediaManager->SetFeaturedImageFile($form->get('featuredImage')->getData(), $trick);

            // Link Trick to the trick.
            $trick->setUser($this->getUser());

            $entityManager->persist($trick);
            $entityManager->flush();
            $this->addFlash('success', 'Trick created!');

            return $this->redirectToRoute('homepage');
        }

        return $this->render('trickpage/edit_trickpage.html.twig', [
            'controller_name' => 'TrickPageController',
            'trick' => $trick,
            'trickform' => $form->createView(),
        ]);
    }

    #[Route('/trick/details/{slug}', name: 'trick_detail')]
    /**
     * showTrick.
     */
    public function showTrick(Trick $trick, Request $request, EntityManagerInterface $entityManager, CommentRepository $commentRepo): Response
    {
        // Create comment object.
        $comment = new Comment();

        // Get the page number in the request, 1 is the default value.
        $page = $request->query->getInt('page', 1);

        // Call pagination function, send id trick and currentPage
        $comments = $commentRepo->loadCommentPaginated($trick->getId(), $page);

        // Create the form and handle the request about the comment.
        $form = $this->createForm(CommentFormType::class)->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Set link with User and Trick
            $comment = $form->getData();
            $comment->setCommentUser($this->getUser());
            $comment->setCommentTrick($trick);

            $entityManager->persist($comment);
            $entityManager->flush();
            $this->addFlash('success', 'Comment posted!');

            return $this->redirectToRoute('trick_detail', ['slug' => $trick->getSlug()]);
        }

        return $this->render('trickpage/trickpage.html.twig', [
            'controller_name' => 'TrickPageController',
            'trick' => $trick,
            'commentForm' => $form->createView(),
            'comments' => $comments,
        ]);
    }

    #[Route('trick/delete/{id}', name: 'delete_trick')]
    /**
     * deleteTrick.
     */
    public function deleteTrick(Trick $trick, EntityManagerInterface $entityManager): Response
    {
        // Check permission to delete via Voter function.
        $this->denyAccessUnlessGranted('TRICK_DELETE', $trick);

        // Check if the collection is not null and delete all files.
        if (null !== $trick->getImages()) {
            $this->mediaManager->deleteTrickRemoveImages($trick->getImages());
        }

        // Check if the field FeaturedImage is not null and delete the file.
        if (null !== $trick->getFeaturedImage()) {
            $this->mediaManager->removeImageFile($trick->getFeaturedImage());
        }
        $entityManager->remove($trick);
        $entityManager->flush();
        $this->addFlash('danger', 'Trick deleted!');

        return $this->RedirectToRoute('homepage');
    }

    #[Route('/trick/update/{slug}', name: 'update_trick')]
    /**
     * updateTrick.
     */
    public function updateTrick(Trick $trick, Request $request, EntityManagerInterface $entityManager, ImageRepository $repoImage): Response
    {
        // Check permission to edit via Voter function.
        $this->denyAccessUnlessGranted('TRICK_EDIT', $trick);
        // Get Images Collection to compare with new one and delete file if necessary.
        $oldImagesCollection = $repoImage->findBy(['trick' => $trick]);

        $form = $this->createForm(TrickFormType::class, $trick)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Call private function to Set and Upload images collection to the trick.
            $this->mediaManager->setImagesCollection($form->get('images')->getData(), $trick);

            $this->mediaManager->updateTrickRemovesImagesFileToFolder($oldImagesCollection, $trick->getImages());

            // Call Private function  set FeaturedImage to the trick.
            $this->mediaManager->SetFeaturedImageFile($form->get('featuredImage')->getData(), $trick);

            // Link Trick to the trick.
            $trick->setUser($this->getUser());

            $entityManager->persist($trick);
            $entityManager->flush();
            $this->addFlash('success', 'Trick updated!');

            return $this->RedirectToRoute('trick_detail', ['slug' => $trick->getSlug()]);
        }

        return $this->render('trickpage/edit_trickpage.html.twig', [
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
                $this->mediaManager->removeImageFile($trick->getFeaturedImage());
                $trick->setFeaturedImage(null);
                $this->addFlash('danger', 'Featured Image deleted!');
            } else {
                $this->addFlash('danger', 'Featured Image is the first image of the images');
            }
        } else {
            $this->mediaManager->removeImageFile($trick->getFeaturedImage());
            $trick->setFeaturedImage(null);
        }

        $entityManager->persist($trick);
        $entityManager->flush();

        return $this->RedirectToRoute('update_trick', ['slug' => $trick->getSlug()]);
    }
}
