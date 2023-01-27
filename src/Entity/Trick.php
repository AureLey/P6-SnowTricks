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

namespace App\Entity;

use App\Repository\TrickRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: TrickRepository::class)]
#[UniqueEntity(
    fields: ['name', 'slug'],
    message: 'Trick already exist',
)]
class Trick
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column]
    private \DateTime $createdAt;

    #[ORM\Column]
    private \DateTime $updatedAt;

    #[ORM\ManyToOne(inversedBy: 'groupTrick')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'tricks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Group $groupTrick = null;

    #[ORM\OneToMany(mappedBy: 'commentTrick', targetEntity: Comment::class, orphanRemoval: true)]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: 'trick', targetEntity: Image::class, cascade: ['all'], orphanRemoval: true)]
    private Collection $images;

    #[ORM\OneToMany(mappedBy: 'trick', targetEntity: Video::class, cascade: ['all'], orphanRemoval: true)]
    private Collection $videos;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $featuredImage = null; // Represent 1st Picture of the trick

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->images = new ArrayCollection();
        $this->videos = new ArrayCollection();
    }
    
    /**
     * getId
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }
    
    /**
     * getName
     *
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }
    
    /**
     * setName
     *
     * @param  string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
    
    /**
     * getSlug
     *
     * @return string
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }
    
    /**
     * setSlug
     *
     * @param  string $slug
     * @return self
     */
    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }
    
    /**
     * getContent
     *
     * @return string
     */
    public function getContent(): ?string
    {
        return $this->content;
    }
    
    /**
     * setContent
     *
     * @param  string $content
     * @return self
     */
    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }
    
    /**
     * getCreatedAt
     *
     * @return DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
    
    /**
     * setCreatedAt
     *
     * @param  DateTime $createdAt
     * @return self
     */
    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
    
    /**
     * getUpdatedAt
     *
     * @return DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }
    
    /**
     * setUpdatedAt
     *
     * @param  DateTime $updatedAt
     * @return self
     */
    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
    
    /**
     * getUser
     *
     * @return user
     */
    public function getUser(): ?user
    {
        return $this->user;
    }
    
    /**
     * setUser
     *
     * @param  User $user
     * @return self
     */
    public function setUser(?user $user): self
    {
        $this->user = $user;

        return $this;
    }
    
    /**
     * getGroupTrick
     *
     * @return group
     */
    public function getGroupTrick(): ?group
    {
        return $this->groupTrick;
    }
    
    /**
     * setGroupTrick
     *
     * @param  group $groupTrick
     * @return self
     */
    public function setGroupTrick(?group $groupTrick): self
    {
        $this->groupTrick = $groupTrick;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }
    
    /**
     * addComment
     *
     * @param  Comment $comment
     * @return self
     */
    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setCommentTrick($this);
        }

        return $this;
    }
    
    /**
     * removeComment
     *
     * @param  Comment $comment
     * @return self
     */
    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getCommentTrick() === $this) {
                $comment->setCommentTrick(null);
            }
        }

        return $this;
    }
    
    /**
     * __toString
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }
    
    /**
     * addImage
     *
     * @param  Image $image
     * @return self
     */
    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setTrick($this);
        }

        return $this;
    }
    
    /**
     * removeImage
     *
     * @param  Image $image
     * @return self
     */
    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getTrick() === $this) {
                $image->setTrick(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Video>
     */
    public function getVideos(): Collection
    {
        return $this->videos;
    }
    
    /**
     * addVideo
     *
     * @param  Video $video
     * @return self
     */
    public function addVideo(Video $video): self
    {
        if (!$this->videos->contains($video)) {
            $this->videos->add($video);
            $video->setTrick($this);
        }

        return $this;
    }
    
    /**
     * removeVideo
     *
     * @param  Video $video
     * @return self
     */
    public function removeVideo(Video $video): self
    {
        if ($this->videos->removeElement($video)) {
            // set the owning side to null (unless already changed)
            if ($video->getTrick() === $this) {
                $video->setTrick(null);
            }
        }

        return $this;
    }
    
    /**
     * getFeaturedImage
     *
     * @return string
     */
    public function getFeaturedImage(): ?string
    {
        if (null !== $this->featuredImage) {
            return $this->featuredImage;
        }

        return null;
    }
    
    /**
     * setFeaturedImage
     *
     * @param  string $featuredImage
     * @return self
     */
    public function setFeaturedImage(?string $featuredImage): self
    {
        $this->featuredImage = $featuredImage;

        return $this;
    }
}
