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

namespace App\Service\Media;

use App\Entity\Trick;
use App\Service\FileManager;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaManager
{
    // Inject Service FileManager.
    private FileManager $fileManager;

    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    /**
     * SetFeaturedImageFile
     * If form not null, upload featured image file and set it.
     */
    public function SetFeaturedImageFile(?UploadedFile $featuredFormFile, Trick $trick): Trick
    {
        if (null !== $featuredFormFile) {
            // Set Featured Image with the form element
            $featuredName = $this->fileManager->AddFile($featuredFormFile);
            $trick->setFeaturedImage($featuredName);
        }

        return $trick;
    }

    /**
     * setImagesCollection
     * Get collection from form collection and Set images collection in trick.
     */
    public function setImagesCollection($imagesListUpload, Trick $trick): Trick
    {
        if ($imagesListUpload) {
            // Test collection if image's id is null so no registered and field File not empty, upload and set the file and name
            foreach ($imagesListUpload as $imageUpload) {
                if (null === $imageUpload->getId() || !empty($imageUpload->getFile())) {
                    $fileName = $this->fileManager->upload($imageUpload->getFile());
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
    public function updateTrickRemovesImagesFileToFolder(array $oldCollection, Collection $newImagesCollection)
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
    public function deleteTrickRemoveImages($imagesCollection)
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
    public function removeImageFile(?string $imageName)
    {
        if (null !== $imageName) {
            $this->fileManager->removeFile($imageName);
        }
    }
}
