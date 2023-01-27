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

use App\Repository\CommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
/**
 * Comment
 */
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?trick $commentTrick = null;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?user $commentUser = null;
    
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
    public function getCreatedAt(): ?\DateTime
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
     * getCommentTrick
     *
     * @return trick
     */
    public function getCommentTrick(): ?trick
    {
        return $this->commentTrick;
    }
    
    /**
     * setCommentTrick
     *
     * @param  Trick $commentTrick
     * @return self
     */
    public function setCommentTrick(?trick $commentTrick): self
    {
        $this->commentTrick = $commentTrick;

        return $this;
    }
    
    /**
     * getCommentUser
     *
     * @return user
     */
    public function getCommentUser(): ?user
    {
        return $this->commentUser;
    }
    
    /**
     * setCommentUser
     *
     * @param  User $commentUser
     * @return self
     */
    public function setCommentUser(?user $commentUser): self
    {
        $this->commentUser = $commentUser;

        return $this;
    }
}
