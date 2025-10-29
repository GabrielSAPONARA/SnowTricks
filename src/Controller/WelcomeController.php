<?php

namespace App\Controller;

use App\Entity\Figure;
use App\Repository\FigureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WelcomeController extends AbstractController
{
    #[Route('/', name: 'app_welcome')]
    public function index(FigureRepository $figureRepository, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 2;
        $figures = $figureRepository->paginateFigures($page, $limit);
        $maxPage = ceil(count($figures) / $limit);

        if($request->get('ajax'))
        {
            return new JsonResponse([
                'content' => $this->renderView('welcome/_figures.html.twig',
                    [
                        'figures' => $figures,
                    ]),
                'pagination' => $this->renderView('welcome/_pagination.html.twig',
                    [
                        'figures' => $figures,
                        'maxPage' => $maxPage,
                        'page' => $page,
                    ]),
                'pages' => $maxPage,
            ]);
        }

        return $this->render('welcome/index.html.twig', [
            'figures' => $figures,
            'maxPage' => $maxPage,
            'page' => $page,
        ]);
    }
}
