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
    #[Route('/trick/new', name: 'new_trick')]
    /**
     * newTrick
     * creation trick function.
     *
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $repo
     * @param FileUploader $fileUploader
     */
    public function newTrick(Request $request, EntityManagerInterface $entityManager, UserRepository $repo, FileUploader $fileUploader): Response
    {
        // CREATE USER
        $user = new User();
        $user = $repo->findBy(['email' => 'admin@admin.com']);
        $user = $user[0];

        // $slugTrickName = new AsciiSlugger();

        $trick = new Trick();
        $form = $this->createForm(TrickFormType::class, $trick)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // call private function to Set and Upload images collection to the trick
            $this->SetFilesCollection($form, $trick, $fileUploader);

            // call Private function to choose and set FeaturedImage ( and Upload if necessary )to the new trick
            $this->PickFeaturedImage($form, $trick, $fileUploader);
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
     *
     * @param Trick $trick
     * @param EntityManagerInterface $entityManager
     * @param FileUploader $fileUploader
     */
    public function deleteTrick(Trick $trick, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        if (null !== $trick->getImages()) {
            $this->deleteTrickRemoveImages($trick->getImages(), $fileUploader);
        }

        if (null !== $trick->getFeaturedImage()) {
            $this->removeImage($trick->getFeaturedImage(), $fileUploader);
        }
        $entityManager->remove($trick);
        $entityManager->flush();

        return $this->RedirectToRoute('homepage');
    }

    #[Route('/trick/update/{slug}', name: 'update_trick')]
    /**
     * updateTrick.
     *
     * @param Trick $trick
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param UserRepository $repoUser
     * @param ImageRepository $repoImage
     * @param FileUploader $fileUploader
     */
    public function updateTrick(Trick $trick, Request $request, EntityManagerInterface $entityManager, UserRepository $repoUser, ImageRepository $repoImage, FileUploader $fileUploader): Response
    {
        // Set a var to compare if Featured change and then delete the file if needed
        $oldFeaturedImage = $trick->getFeaturedImage();
        $oldImagesCollection = $repoImage->findBy(['trick' => $trick]);

        // CREATE USER
        $user = new User();
        $user = $repoUser->findBy(['email' => 'admin@admin.com']);
        $user = $user[0];

        $form = $this->createForm(TrickFormType::class, $trick)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // call private function to Set and Upload images collection to the trick
            $this->SetFilesCollection($form, $trick, $fileUploader);

            // call Private function to choose and set FeaturedImage to the new trick
            $this->PickFeaturedImage($form, $trick, $fileUploader);
            $trick->setUser($user);
            // $trick->setUser($this->getUser());

            $entityManager->persist($trick);
            $entityManager->flush();

            $this->updateTrickRemovesImagesFileToFolder($oldImagesCollection, $trick->getImages(), $fileUploader);

            if ($oldFeaturedImage !== $trick->getFeaturedImage()) {
                $this->removeImage($oldFeaturedImage, $fileUploader);
            }

            return $this->RedirectToRoute('trick_detail', ['slug' => $trick->getSlug()]);
        }

        return $this->render('trickpage/updatetrickpage.html.twig', [
            'controller_name' => 'TrickPageController',
            'trick' => $trick,
            'trickform' => $form->createView(),
        ]);
    }

    /**
     * AddFile import the file from the trick form, return string filename.
     *
     * @return string fileName
     */
    private function AddFile(FileUploader $fileUploader, UploadedFile $file): string
    {
        $fileName = $fileUploader->upload($file);

        return $fileName;
    }

    /**
     * PickFeaturedImage
     * Choose which name will be set to the featured Image. Featured image field OR 1st images collection element  OR default in twig.
     */
    private function PickFeaturedImage(Form $form, Trick $trick, FileUploader $fileUploader): Trick
    {
        $featuredFormFile = $form->get('featuredImage')->getData();

        // Test if FeaturedImage is empty not already in DB and form File field is empty
        if (null === $trick->getFeaturedImage() && null === $featuredFormFile) {
            // Then Test is trick images Collection is not empty/null
            if (null !== $trick->getImages()->first()) {
                // Set Featured Image with 1st element en images collection
                $trick->setFeaturedImage($trick->getImages()->first()->getName());
            }
        // If all element are empty, twig will set a default Featured Image
        } else {
            if (null !== $featuredFormFile) {
                // Set Featured Image with the form element
                $featuredName = $this->AddFile($fileUploader, $featuredFormFile);
                $trick->setFeaturedImage($featuredName);
            } else {
                $trick->setFeaturedImage(null);
            }
        }

        return $trick;
    }

    /**
     * SetFilesCollection
     * Get collection from form collection and Set images collection in trick.
     */
    private function SetFilesCollection(Form $form, Trick $trick, FileUploader $fileUploader): Trick
    {
        // Get UploadedListFile and add it to the trick as Image object
        $imagesListUpload = $form->get('images')->getData();

        if ($imagesListUpload) {
            // Test collection if image's id is null so no registered and field File not empty, upload and set the file and name
            foreach ($imagesListUpload as $imageUpload) {
                if (null === $imageUpload->getId() || !empty($imageUpload->getFile())) {
                    $FileNamne = $fileUploader->upload($imageUpload->getFile());
                    $imageUpload->setName($FileNamne);
                    $trick->addImage($imageUpload);
                }
            }
        }

        return $trick;
    }

    /**
     * removeImage
     * get images folder hard path, and remove file by his name.
     *
     * @param string $imageName
     *
     * @return void
     */
    private function removeImage(?string $imageName, FileUploader $fileUploader)
    {
        // Set images Folder
        $path = $this->getParameter('kernel.project_dir');
        $path = $path.'/public/images/'.$imageName;
        if (null !== $imageName) {
            $fileUploader->removeFile($path);
        }
    }

    private function updateTrickRemovesImagesFileToFolder($oldCollection, $newImagesCollection, FileUploader $fileUploader)
    {
        foreach ($oldCollection as $oldImage) {
            if (!\in_array($oldImage->getName(), $newImagesCollection->toArray(), true)) {
                $this->removeImage($oldImage, $fileUploader);
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
    private function deleteTrickRemoveImages(Collection $imagesCollection, FileUploader $fileUploader)
    {
        foreach ($imagesCollection as $image) {
            $this->removeImage($image, $fileUploader);
        }
    }
}
