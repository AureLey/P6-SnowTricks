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

namespace App\Entity;

use App\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;
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

    /**
     * Get the value of file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set the value of file.
     *
     * @return self
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
