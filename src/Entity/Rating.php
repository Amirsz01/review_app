<?php

namespace App\Entity;

use App\Repository\RatingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RatingRepository::class)
 */
class Rating
{

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity=Review::class, inversedBy="ratings")
     * @ORM\JoinColumn(nullable=false)
     */
    private Review $review;

    /**
     * @ORM\Column(type="smallint")
     */
    private int $value;


    public function __construct(Review $review, User $user)
    {
        $this->review = $review;
        $this->user = $user;
        $this->value = 1;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getReview(): Review
    {
        return $this->review;
    }

    public function setReview(Review $review): self
    {
        $this->review = $review;

        return $this;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;

        return $this;
    }
}
