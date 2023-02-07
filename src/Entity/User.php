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

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: 'email', message: 'Email already used', )]
#[UniqueEntity(fields: 'username', message: 'Username already used', )]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    // Value in hours, represents the token validation time
    public const TOKEN_DURATION = 24;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picturePath = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $token = null;

    #[ORM\Column]
    private ?\DateTime $tokenValidation = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Trick::class)]
    private Collection $tricks;

    #[ORM\OneToMany(mappedBy: 'commentUser', targetEntity: Comment::class)]
    private Collection $comments;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isVerified = false;

    public function __construct()
    {
        $this->tricks = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    /**
     * getId.
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * getUsername.
     *
     * @return string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * setUsername.
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * getEmail.
     *
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * setEmail.
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * getPassword.
     *
     * @return string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * setPassword.
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * getPicturePath.
     *
     * @return string
     */
    public function getPicturePath(): ?string
    {
        return $this->picturePath;
    }

    /**
     * setPicturePath.
     *
     * @param string $picturePath
     */
    public function setPicturePath(?string $picturePath): self
    {
        $this->picturePath = $picturePath;

        return $this;
    }

    /**
     * getToken.
     *
     * @return string
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * setToken.
     *
     * @param string $token
     */
    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * getTokenValidation.
     *
     * @return \DateTime
     */
    public function getTokenValidation(): ?\DateTime
    {
        return $this->tokenValidation;
    }

    /**
     * setTokenValidation.
     *
     * @param \DateTime $tokenValidation
     */
    public function setTokenValidation(?\DateTime $tokenValidation): self
    {
        $this->tokenValidation = $tokenValidation;

        return $this;
    }

    /**
     * @return Collection<int, Trick>
     */
    public function getTricks(): Collection
    {
        return $this->tricks;
    }

    /**
     * addTrick.
     */
    public function addTrick(Trick $tricks): self
    {
        if (!$this->tricks->contains($tricks)) {
            $this->tricks->add($tricks);
            $tricks->setUser($this);
        }

        return $this;
    }

    /**
     * removeTrick.
     */
    public function removeTrick(Trick $tricks): self
    {
        if ($this->tricks->removeElement($tricks)) {
            // set the owning side to null (unless already changed)
            if ($tricks->getUser() === $this) {
                $tricks->setUser(null);
            }
        }

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
     * addComment.
     */
    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setCommentUser($this);
        }

        return $this;
    }

    /**
     * removeComment.
     */
    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getCommentUser() === $this) {
                $comment->setCommentUser(null);
            }
        }

        return $this;
    }

    public function isIsVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @see UserInterface
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * getUserIdentifier.
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * Returning a salt is only needed if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
    
    /**
     * tokenCreation
     *
     * @param  int $id
     * @return string
     */
    public function tokenCreation(int $id): string
    {
        $date = new \DateTime('now');
        $date = $date->format('Y-m-d H:i:s');
        $key = $date.$id;
        $key = md5($key);

        return $key;
    }

    public function verificationTokenTime(\DateTime $maildatetime, \DateTime $tokenDatime): bool
    {
        // Calc value between account creation and mail validation
        $diff = date_diff($tokenDatime, $maildatetime);

        // Format in hours
        $hours = $diff->format('%h');
        $days = $diff->format('%d');

        $duration = $days * 24 + $hours;

        if ($duration <= self::TOKEN_DURATION) {
            return true;
        }

        return false;
    }
}
