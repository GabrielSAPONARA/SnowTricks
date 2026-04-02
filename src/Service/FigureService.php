<?php

namespace App\Service;

use App\Controller\FigureController;
use Twig\Environment;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\JsonResponse;

class FigureService
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

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
}