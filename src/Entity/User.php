<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use League\OAuth2\Client\Provider\FacebookUser;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private string $uuid;

    /**
     * @ORM\Column(type="json")
     */
    private array $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $password;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private string $surname;

    /**
     * @ORM\OneToMany(targetEntity=Review::class, mappedBy="author", orphanRemoval=true)
     */
    private Collection $reviews;

    /**
     * @ORM\OneToMany(targetEntity=Like::class, mappedBy="user")
     */
    private Collection $likes;


    public function __construct()
    {
        $this->reviews = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->password = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->uuid;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->uuid;
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

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname(): string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getFullName(): string
    {
        return '<span class="badge bg-success text-light">'.count($this->likes).'</span> '.$this->surname . ' ' . $this->name;
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    /**
     * @return Collection|Review[]
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): self
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews[] = $review;
            $review->setAuthor($this);
        }

        return $this;
    }

    public function removeReview(Review $review): self
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getAuthor() === $this) {
                $review->setAuthor(null);
            }
        }

        return $this;
    }

    public function setDataFromGoogle(GoogleUser $googleUser)
    {
        $this->setName($googleUser->getFirstName());
        $this->setSurname($googleUser->getLastName());
        $this->setUuid($googleUser->getId());
    }

    public function setDataFromFacebook(FacebookUser $facebookUser)
    {
        $this->setName($facebookUser->getFirstName());
        $this->setSurname($facebookUser->getLastName());
        $this->setSurname($facebookUser->getLastName());
        $this->setUuid($facebookUser->getId());
    }

    /**
     * @return Collection|Review[]
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }
}
