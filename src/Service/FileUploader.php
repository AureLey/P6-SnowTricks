<?php

declare(strict_types=1);

/*
 * This file is part of ...
 *
 * (c)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    private string $targetDirectory;
    private $slugger;
    private $fileSystem;

    public function __construct(string $targetDirectory, SluggerInterface $slugger, Filesystem $fileSystem)
    {
        $this->targetDirectory = $targetDirectory;
        $this->slugger = $slugger;
        $this->fileSystem = $fileSystem;
    }

    public function upload(UploadedFile $file)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), \PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }

        return $fileName;
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    /**
     * removeFile,function who remove the file (image) from the images folder
     * FileSystem injected in FileUploader Class.
     *
     * @return void
     */
    public function removeFile(string $pathFileName)
    {
        $this->fileSystem->remove($pathFileName);
    }
}
