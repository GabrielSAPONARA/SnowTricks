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
        if (empty($this->embedUrl)) {
            return null;
        }

        $url = $this->embedUrl;

        // Si l'URL est déjà au format embed
        if (str_contains($url, 'youtube.com/embed') || str_contains($url, 'dailymotion.com/embed')) {
            return $url;
        }

        // YouTube (inclut shorts, watch, youtu.be, live, etc.)
        if (preg_match(
            '#(?:youtube\.com/(?:watch\?v=|embed/|v/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})#',
            $url,
            $matches
        )) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }

        // Dailymotion
        if (preg_match('#dailymotion\.com/video/([a-zA-Z0-9]+)#', $url, $matches)) {
            return 'https://www.dailymotion.com/embed/video/' . $matches[1];
        }

        // Aucun format reconnu
        return null;
    }

    public function setEmbedUrl(string $embedUrl): static
    {
        $this->embedUrl = $embedUrl;

        return $this;
    }
}
