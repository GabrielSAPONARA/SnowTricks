<?php

namespace App\Entity;

use App\Repository\FigureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FigureRepository::class)]
class Figure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $creationDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateOfLastUpdate = null;

    /**
     * @var Collection<int, VideoFigure>
     */
    #[ORM\OneToMany(targetEntity: VideoFigure::class, mappedBy: 'figure')]
    private Collection $videoFigures;

    /**
     * @var Collection<int, PictureFigure>
     */
    #[ORM\OneToMany(targetEntity: PictureFigure::class, mappedBy: 'figure')]
    private Collection $pictureFigures;

    /**
     * @var Collection<int, Group>
     */
    #[ORM\ManyToMany(targetEntity: Group::class, inversedBy: 'figures')]
    private Collection $groupes;

    public function __construct()
    {
        $this->videoFigures = new ArrayCollection();
        $this->pictureFigures = new ArrayCollection();
        $this->groupes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getCreationDate(): ?\DateTime
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTime $creationDate): static
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getDateOfLastUpdate(): ?\DateTime
    {
        return $this->dateOfLastUpdate;
    }

    public function setDateOfLastUpdate(\DateTime $dateOfLastUpdate): static
    {
        $this->dateOfLastUpdate = $dateOfLastUpdate;

        return $this;
    }

    /**
     * @return Collection<int, VideoFigure>
     */
    public function getVideoFigures(): Collection
    {
        return $this->videoFigures;
    }

    public function addVideoFigure(VideoFigure $videoFigure): static
    {
        if (!$this->videoFigures->contains($videoFigure)) {
            $this->videoFigures->add($videoFigure);
            $videoFigure->setFigure($this);
        }

        return $this;
    }

    public function removeVideoFigure(VideoFigure $videoFigure): static
    {
        if ($this->videoFigures->removeElement($videoFigure)) {
            // set the owning side to null (unless already changed)
            if ($videoFigure->getFigure() === $this) {
                $videoFigure->setFigure(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, PictureFigure>
     */
    public function getPictureFigures(): Collection
    {
        return $this->pictureFigures;
    }

    public function addPictureFigure(PictureFigure $pictureFigure): static
    {
        if (!$this->pictureFigures->contains($pictureFigure)) {
            $this->pictureFigures->add($pictureFigure);
            $pictureFigure->setFigure($this);
        }

        return $this;
    }

    public function removePictureFigure(PictureFigure $pictureFigure): static
    {
        if ($this->pictureFigures->removeElement($pictureFigure)) {
            // set the owning side to null (unless already changed)
            if ($pictureFigure->getFigure() === $this) {
                $pictureFigure->setFigure(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Group>
     */
    public function getGroupes(): Collection
    {
        return $this->groupes;
    }

    public function addGroupe(Group $groupe): static
    {
        if (!$this->groupes->contains($groupe)) {
            $this->groupes->add($groupe);
        }

        return $this;
    }

    public function removeGroupe(Group $groupe): static
    {
        $this->groupes->removeElement($groupe);

        return $this;
    }
}
