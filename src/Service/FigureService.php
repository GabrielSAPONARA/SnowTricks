<?php

namespace App\Service;

use App\Controller\FigureController;
use App\Entity\Figure;
use App\Entity\PictureFigure;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\JsonResponse;

class FigureService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator, // This ensures $this->validator exists
        private string $figuresImagesDirectory,
        private Environment $twig,
    ){}

    /**
     * @param \Doctrine\ORM\Tools\Pagination\Paginator $figures
     * @param float $maxPage
     * @param int $page
     * @param FigureController $instance
     * @return JsonResponse
     */
    public function getJsonResponse(Paginator $figures, float $maxPage, int $page): JsonResponse
    {
        return new JsonResponse([
            'content'    => $this->twig->render('figure/_figures.html.twig',
                [
                    'figures' => $figures,
                ]),
            'pagination' => $this->twig->render('figure/_pagination_figures.html.twig',
                [
                    'figures' => $figures,
                    'maxPage' => $maxPage,
                    'page'    => $page,
                ]),
            'pages'      => $maxPage,
        ]);
    }

    /**
     * @param UploadedFile[] $images
     * @param Figure $figure
     * @return array{valid: bool, errors: string[]}
     */
    public function recordImages(array $images, Figure $figure): array
    {
        $errors = [];

        foreach ($images as $image) {
            $violations = $this->validator->validate(
                $image,
                new \Symfony\Component\Validator\Constraints\Image([
                    'maxSize'          => '5M',
                    'mimeTypesMessage' => 'Merci d\'uploader une image valide (jpeg/png/webp)',
                ])
            );

            if (count($violations) > 0) {
                $errors[] = (string)$violations;
                continue;
            }

            $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();

            try {
                $image->move($this->figuresImagesDirectory, $newFilename);
            } catch (FileException $e) {
                $errors[] = "Erreur lors de l'upload : " . $e->getMessage();
                continue;
            }

            $picture = new PictureFigure();
            $picture->setName($newFilename);
            $picture->setFigure($figure);
            $this->entityManager->persist($picture);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    private function slug(string $text): string
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $text));
    }
}