<?php

namespace App\Controller;

use App\Entity\Figure;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WelcomeController extends AbstractController
{
    #[Route('/', name: 'app_welcome')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $figures = $entityManager->getRepository(Figure::class)->findAll();

        return $this->render('welcome/index.html.twig', [
            'figures' => $figures,
        ]);
    }
}
