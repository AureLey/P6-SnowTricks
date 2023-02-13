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
use App\Repository\ImageRepository;
use App\Service\FileUploader;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function showTrick(Trick $trick, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Create comment object.
        $comment = new Comment();

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
            $this->deleteTrickRemoveImages($trick->getImages());
        }

        // Check if the field FeaturedImage is not null and delete the file.
        if (null !== $trick->getFeaturedImage()) {
            $this->removeImageFile($trick->getFeaturedImage());
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
            $this->setImagesCollection($form->get('images')->getData(), $trick);

            $this->updateTrickRemovesImagesFileToFolder($oldImagesCollection, $trick->getImages());

            // Call Private function  set FeaturedImage to the trick.
            $this->SetFeaturedImageFile($form->get('featuredImage')->getData(), $trick);

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
                $this->removeImageFile($trick->getFeaturedImage());
                $trick->setFeaturedImage(null);
                $this->addFlash('danger', 'Featured Image deleted!');
            } else {
                $this->addFlash('danger', 'Featured Image is the first image of the images');
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
     *
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
