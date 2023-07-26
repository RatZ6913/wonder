<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QuestionRepository::class)]
class Question
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /**
     * Summary of id
     * @var 
     */
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuillez renseigner un titre')]
    #[Assert\Length(min: 20, minMessage: 'Veuillez détailler votre titre', max: 255, maxMessage: 'Le titre de votre question est trop long')]
    /**
     * Summary of title
     * @var 
     */
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Veuillez détailler votre question')]
    #[Assert\Length(min: 100, minMessage: 'Veuillez détailler votre question')]
    /**
     * Summary of content
     * @var 
     */
    private ?string $content = null;

    #[ORM\Column]
    /**
     * Summary of createdAt
     * @var 
     */
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    /**
     * Summary of rating
     * @var 
     */
    private ?int $rating = null;

    #[ORM\Column]
    /**
     * Summary of nbrOfReponse
     * @var 
     */
    private ?int $nbrOfReponse = null;

    #[ORM\OneToMany(mappedBy: 'question', targetEntity: Comment::class, orphanRemoval: true)]
    /**
     * Summary of comments
     * @var Collection
     */
    private Collection $comments;

    #[ORM\ManyToOne(inversedBy: 'questions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    /**
     * Summary of __construct
     */
    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    /**
     * Summary of getId
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Summary of getTitle
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Summary of setTitle
     * @param string $title
     * @return Question
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Summary of getContent
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Summary of setContent
     * @param string $content
     * @return Question
     */
    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Summary of getCreatedAt
     * @return \DateTimeImmutable|null
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Summary of setCreatedAt
     * @param \DateTimeImmutable $createdAt
     * @return Question
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Summary of getRating
     * @return int|null
     */
    public function getRating(): ?int
    {
        return $this->rating;
    }

    /**
     * Summary of setRating
     * @param int $rating
     * @return Question
     */
    public function setRating(int $rating): static
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Summary of getNbrOfReponse
     * @return int|null
     */
    public function getNbrOfReponse(): ?int
    {
        return $this->nbrOfReponse;
    }

    /**
     * Summary of setNbrOfReponse
     * @param int $nbrOfReponse
     * @return Question
     */
    public function setNbrOfReponse(int $nbrOfReponse): static
    {
        $this->nbrOfReponse = $nbrOfReponse;

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
     * Summary of addComment
     * @param \App\Entity\Comment $comment
     * @return Question
     */
    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setQuestion($this);
        }

        return $this;
    }

    /**
     * Summary of removeComment
     * @param \App\Entity\Comment $comment
     * @return Question
     */
    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getQuestion() === $this) {
                $comment->setQuestion(null);
            }
        }

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }
}
