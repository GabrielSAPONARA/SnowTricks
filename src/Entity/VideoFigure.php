<?php

namespace App\Entity;

use App\Repository\VideoFigureRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VideoFigureRepository::class)]
class VideoFigure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'videoFigures')]
    private ?Figure $figure = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $embedUrl = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFigure(): ?Figure
    {
        return $this->figure;
    }

    public function setFigure(?Figure $figure): static
    {
        $this->figure = $figure;

        return $this;
    }

    public function getEmbedUrl(): ?string
    {
        if (!$this->embedUrl) {
            return null;
        }

        if (str_contains($this->embedUrl, 'youtube.com/watch')) {
            return str_replace('watch?v=', 'embed/', $this->embedUrl);
        }

        if (str_contains($this->embedUrl, 'youtu.be/')) {
            return str_replace('youtu.be/', 'www.youtube.com/embed/', $this->embedUrl);
        }

        if (str_contains($this->embedUrl, 'dailymotion.com/video')) {
            $id = basename($this->embedUrl);
            return 'https://www.dailymotion.com/embed/video/' . $id;
        }

        return null;
    }

    public function setEmbedUrl(string $embedUrl): static
    {
        $this->embedUrl = $embedUrl;

        return $this;
    }
}
