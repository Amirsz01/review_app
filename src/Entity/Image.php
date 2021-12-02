<?php

namespace App\Entity;

use App\Repository\ImageRepository;
use App\Service\FileManagerService;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\String_;

/**
 * @ORM\Entity(repositoryClass=ImageRepository::class)
 */
class Image
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $slug;

    /**
     * @ORM\ManyToOne(targetEntity=Review::class, inversedBy="images")
     * @ORM\JoinColumn(nullable=true)
     */
    private Review $review;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $sharedUri;

    private FileManagerService $fms;
    public function __construct($slug, FileManagerService $fms)
    {
        $this->slug = $slug;
        $this->fms = $fms;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

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

    public function getSharedUri(): ?string
    {
        return $this->sharedUri;
    }

    public function setSharedUri(string $sharedUri): self
    {
        $this->sharedUri = $sharedUri;

        return $this;
    }

    public function getFms(): FileManagerService
    {
        return $this->fms;
    }
}
