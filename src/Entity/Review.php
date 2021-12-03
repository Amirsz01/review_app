<?php

namespace App\Entity;

use App\Repository\ReviewRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ReviewRepository::class)
 */
class Review
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
    private string $title;

    /**
     * @ORM\Column(type="text")
     */
    private string $text;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="review", orphanRemoval=true)
     */
    private Collection $comments;

    /**
     * @ORM\Column(type="datetime")
     */
    private DateTimeInterface $date_create;

    /**
     * @ORM\Column(type="datetime")
     */
    private DateTimeInterface $date_update;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="reviews")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $author;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="reviews")
     * @ORM\JoinColumn(nullable=false)
     */
    private Category $category;

    /**
     * @ORM\ManyToMany(targetEntity=Tag::class)
     */
    private Collection $tags;

    /**
     * @ORM\OneToMany(targetEntity=Like::class, mappedBy="review", orphanRemoval=true)
     */
    private Collection $likes;

    /**
     * @ORM\OneToMany(targetEntity=Image::class, mappedBy="review", orphanRemoval=true)
     */
    private Collection $images;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\Range(
     *      min = 0,
     *      max = 10,
     *      notInRangeMessage = "You must be between {{ min }} point and {{ max }} point tall to enter",
     * )
     */
    private int $score;

    public function __construct()
    {
        $this->setDateCreate(new \DateTime());
        $this->setDateUpdate(new \DateTime());
        $this->comments = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
        }

        return $this;
    }

    public function getDateCreate(): DateTimeInterface
    {
        return $this->date_create;
    }

    public function setDateCreate(DateTimeInterface $date_create): self
    {
        $this->date_create = $date_create;

        return $this;
    }

    public function getDateUpdate(): DateTimeInterface
    {
        return $this->date_update;
    }

    public function setDateUpdate(DateTimeInterface $date_update): self
    {
        $this->date_update = $date_update;

        return $this;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function setAuthor(User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * @return Collection|Like[]
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Like $like): self
    {
        if (!$this->likes->contains($like)) {
            $this->likes[] = $like;
        }

        return $this;
    }

    public function removeLike(Like $like): self
    {
        $this->likes->removeElement($like);
        return $this;
    }

    /**
     * @return Collection|Image[]
     */
    public function getImages(): ?Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setReview($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        $image->getFms()->imageRemove($image->getSlug());
        $this->images->removeElement($image);

        return $this;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function setScore(int $score): self
    {
        $this->score = $score;

        return $this;
    }
}
