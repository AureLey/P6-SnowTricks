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

use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\User;
use App\Form\TrickFormType;
use App\Repository\ImageRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrickPageController extends AbstractController
{
    private FileUploader $fileUploader;

    public function __construct(FileUploader $fileUploader)
    {
        $this->fileUploader = $fileUploader;
    }

    #[Route('/trick/new', name: 'new_trick')]
    /**
     * newTrick
     * creation trick function.
     */
    public function newTrick(Request $request, EntityManagerInterface $entityManager, UserRepository $repo, FileUploader $fileUploader): Response
    {
        // CREATE USER
        $user = new User();
        $user = $repo->findBy(['email' => 'admin@admin.com']);
        $user = $user[0];

        $trick = new Trick();
        $form = $this->createForm(TrickFormType::class, $trick)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // call private function to Set and Upload images collection to the trick
            $this->setImagesCollection($form->get('images')->getData(), $trick);

            // call Private function  set FeaturedImage to the trick
            $this->SetFeaturedImageFile($form->get('featuredImage')->getData(), $trick);

            $trick->setUser($user);
            // $trick  ->setUser($this->getUser());

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
    public function showTrick(Trick $trick): Response
    {
        return $this->render('trickpage/trickpage.html.twig', [
            'controller_name' => 'TrickPageController',
            'trick' => $trick,
        ]);
    }

    #[Route('trick/delete/{slug}', name: 'delete_trick')]
    /**
     * deleteTrick.
     */
    public function deleteTrick(Trick $trick, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        if (null !== $trick->getImages()) {
            $this->deleteTrickRemoveImages($trick->getImages(), $fileUploader);
        }

        if (null !== $trick->getFeaturedImage()) {
            $this->removeImageFile($trick->getFeaturedImage(), $fileUploader);
        }
        $entityManager->remove($trick);
        $entityManager->flush();

        return $this->RedirectToRoute('homepage');
    }

    #[Route('/trick/update/{slug}', name: 'update_trick')]
    /**
     * updateTrick.
     */
    public function updateTrick(Trick $trick, Request $request, EntityManagerInterface $entityManager, UserRepository $repoUser, ImageRepository $repoImage, FileUploader $fileUploader): Response
    {
        // get Images Collection to compare with new one and delete file if necessary
        $oldImagesCollection = $repoImage->findBy(['trick' => $trick]);

        // CREATE USER
        $user = new User();
        $user = $repoUser->findBy(['email' => 'admin@admin.com']);
        $user = $user[0];

        $form = $this->createForm(TrickFormType::class, $trick)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // call private function to Set and Upload images collection to the trick
            $this->setImagesCollection($form->get('images')->getData(), $trick);

            $this->updateTrickRemovesImagesFileToFolder($oldImagesCollection, $trick->getImages());

            // call Private function  set FeaturedImage to the trick
            $this->SetFeaturedImageFile($form->get('featuredImage')->getData(), $trick);

            $trick->setUser($user);
            // $trick->setUser($this->getUser());

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
        // remove image file and Set to null in the trick
        // test if featuredImage and 1st element of collections are identical, if they are != remove the file
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
     * updateTrickRemovesImagesFileToFolder.
     * Compare oldArray and new collection and remove files unused.
     */
    private function updateTrickRemovesImagesFileToFolder(array $oldCollection, Collection $newImagesCollection)
    {
        foreach ($oldCollection as $oldImage) {
            $present = \in_array($oldImage->getName(), $newImagesCollection->toArray(), true);

            if (true !== $present) {
                $this->removeImageFile($oldImage->getName());
            }
        }
    }

    /**
     * deleteTrickRemoveImages
     * remove all images from the selected trick in delete_trick route.
     *
     * @param ArrayCollection $imagesCollection
     *
     * @return void
     */
    private function deleteTrickRemoveImages(Collection $imagesCollection)
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
