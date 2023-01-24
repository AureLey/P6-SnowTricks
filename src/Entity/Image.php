<?php
//src/Entity/Image.php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ImageRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
class Image
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'images')]
    #[ORM\JoinColumn(name: 'trick_id', referencedColumnName: 'id')]
    private ?Trick $trick;

    // #[ORM\Column(type: 'string', length: 255)]
    // private ?string $imageFilename;

    /**
     * @var UploadedFile
     */
    protected $file;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTrick(): Trick
    {
        return $this->trick;
    }

    public function setTrick(?Trick $trick): self
    {
        $this->trick = $trick;

        return $this;
    }

    // public function getImageFilename(): string
    // {
    //     return $this->imageFilename;
    // }

    // public function setImageFilename($imageFilename): self
    // {
    //     $this->imageFilename = $imageFilename;

    //     return $this;
    // }



    /**
     * Get the value of file
     *
     * @return  UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set the value of file
     *
     * @param  UploadedFile  $file
     *
     * @return  self
     */
    public function setFile(UploadedFile $file)
    {
        $this->file = $file;

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
