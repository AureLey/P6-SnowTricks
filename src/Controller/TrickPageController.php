<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Image;
use App\Entity\Trick;
use App\Form\TrickFormType;
use App\Service\FileUploader;
use Symfony\Component\Form\Form;
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
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

        // $slugTrickName = new AsciiSlugger();

        $trick = new Trick();
        $form = $this->createForm(TrickFormType::class, $trick)->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            //call private function to Set and Upload images collection to the trick
            $this->SetFilesCollection($form, $trick, $fileUploader);

            //call Private function to choose and set FeaturedImage ( and Upload if necessary )to the new trick
            $this->PickFeaturedImage($form, $trick, $fileUploader);

            // $oldvideos = $form->get('videos')->getData();
            // dump($oldvideos);
            // $videos = $this->URLChanger($form->get('videos')->getData());
            // dd($videos);
            $now = new \DateTime('now');
            $trick->setUser($this->getUser())
                // ->setCreatedAt($now)
                // ->setUpdatedAt($now)
                ->setUser($user);
            //      ->setUser($this->getUser()); 
            // $slugName = $slugTrickName->slug($trick->getName());
            // $trick->setSlug($slugName);

            $entityManager->persist($trick);
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
    public function deleteTrick(Trick $trick, EntityManagerInterface $entityManager, FileUploader $fileUploader): Response
    {
        if ($trick->getImages() !== null) {
            $this->deleteTrickRemoveImages($trick->getImages(), $fileUploader);
        }

        if ($trick->getFeaturedImage() !== null) {

            $this->removeImage($trick->getFeaturedImage(), $fileUploader);
        }
        $entityManager->remove($trick);
        $entityManager->flush();
        return $this->RedirectToRoute('homepage');
    }


    #[Route('/trick/update/{slug}', name: 'update_trick')]
    public function updateTrick(Trick $trick, Request $request, EntityManagerInterface $entityManager, UserRepository $repoUser, TrickRepository $repoTrick, ImageRepository $repoImage, FileUploader $fileUploader): Response
    {
        //Set a var to compare if Featured change and then delete the file if needed
        $oldFeaturedImage = $trick->getFeaturedImage();
        $oldImagesCollection = $repoImage->findBy(['trick' => $trick]);

        //CREATE USER 
        $user = new User();
        $user = $repoUser->findBy(['email' => 'admin@admin.com']);
        $user = $user[0];

        //create file_path
        $slugger = new AsciiSlugger();

        $form = $this->createForm(TrickFormType::class, $trick)->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {

            //call private function to Set and Upload images collection to the trick
            $this->SetFilesCollection($form, $trick, $fileUploader);

            //call Private function to choose and set FeaturedImage to the new trick
            $this->PickFeaturedImage($form, $trick, $fileUploader);

            $now = new \DateTime('now');
            $trick->setUser($this->getUser())
                ->setUpdatedAt($now)
                ->setUser($user);
            //      ->setUser($this->getUser()); 
            $slug = $slugger->slug($trick->getName());
            $trick->setSlug($slug);



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
     * AddFile import the file from the trick form, return string filename 
     * @param  FileUploader $fileUploader
     * @param  UploadedFile $file     
     * @return String fileName
     */
    private function AddFile(FileUploader $fileUploader, UploadedFile $file): string
    {
        $fileName = $fileUploader->upload($file);

        return $fileName;
    }

    /**
     * PickFeaturedImage
     * Choose which name will be set to the featured Image. Featured image field OR 1st images collection element  OR default in twig 
     * @param  Form $form
     * @param  Trick $trick
     * @param  FileUploader $fileUploader
     * @return Trick
     */
    private function PickFeaturedImage(Form $form, Trick $trick, FileUploader $fileUploader): Trick
    {

        $featuredFormFile = $form->get('featuredImage')->getData();

        //Test if FeaturedImage is empty not already in DB and form File field is empty
        if (($trick->getFeaturedImage()) == null && $featuredFormFile == null) {

            //Then Test is trick images Collection is not empty/null
            if ($trick->getImages()->first() != null) {
                //Set Featured Image with 1st element en images collection
                $trick->setFeaturedImage($trick->getImages()->first()->getName());
            }
            //If all element are empty, twig will set a default Featured Image

        } else {

            if ($featuredFormFile != null) {

                //Set Featured Image with the form element
                $featuredName = $this->AddFile($fileUploader, $featuredFormFile);
                $trick->setFeaturedImage($featuredName);
            }
        }
        return $trick;
    }

    /**
     * SetFilesCollection
     * Get collection from form collection and Set images collection in trick
     * @param  Form $form
     * @param  Trick $trick
     * @param  FileUploader $fileUploader
     * @return Trick
     */
    private function SetFilesCollection(Form $form, Trick $trick, FileUploader $fileUploader): Trick
    {
        // Get UploadedListFile and add it to the trick as Image object
        $imagesListUpload = $form->get('images')->getData();

        if ($imagesListUpload) {
            //Test collection if image's id is null so no registered and field File not empty, upload and set the file and name
            foreach ($imagesListUpload as $imageUpload) {

                if ($imageUpload->getId() === null || !empty($imageUpload->getFile())) {
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
     * get images folder hard path, and remove file by his name
     * @param  string $imageName
     * @param  FileUploader $fileUploader
     * @return void
     */
    private function removeImage(string $imageName, FileUploader $fileUploader)
    {
        //Set images Folder
        $path = $this->getParameter('kernel.project_dir');
        $path = $path . '/public/images/' . $imageName;
        $fileUploader->removeFile($path);
    }

    private function updateTrickRemovesImagesFileToFolder($oldCollection, $newImagesCollection, FileUploader $fileUploader)
    {
        //$intersectArray = array_intersect($oldCollection, $newImagesCollection->toArray());

        foreach ($oldCollection as $oldImage) {
            if (!in_array($oldImage->getName(), $newImagesCollection->toArray())) {

                $this->removeImage($oldImage, $fileUploader);
            }
        }
    }

    /**
     * deleteTrickRemoveImages
     * remove all images from the selected trick in delete_trick route 
     *
     * @param  ArrayCollection $imagesCollection
     * @param  FileUploader $fileUploader
     * @return void
     */
    private function deleteTrickRemoveImages(Collection $imagesCollection, FileUploader $fileUploader)
    {
        foreach ($imagesCollection as $image) {

            $this->removeImage($image, $fileUploader);
        }
    }

    private function URLChanger(ArrayCollection $videos): ArrayCollection
    {
        $pattern = '%^ (?:https?://)? (?:www\.)? (?: youtu\.be/ | youtube\.com (?: /embed/ | /v/ | /watch\?v= ) ) ([\w-]{10,12}) $%x';
        foreach ($videos as $video) {
            //matches[1] return code, 0 return full url
            preg_match($pattern, $video->getName(), $matches);
            $newUrl = 'https://www.youtube.com/embed/' . $matches[1];
            $video->setName($newUrl);
        }
        return $videos;
    }
}
