<?php

namespace App\Controller;

use App\Entity\Figure;
use App\Entity\PictureFigure;
use App\Form\FigureForm;
use App\Repository\FigureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\Image as ImageConstraint;


#[Route('/figure')]
final class FigureController extends AbstractController
{

    public function __construct(
        private SluggerInterface $slugger,
    )
    {

    }

    #[Route(name: 'app_figure_index', methods: ['GET'])]
    public function index(FigureRepository $figureRepository): Response
    {
        return $this->render('figure/index.html.twig', [
            'figures' => $figureRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_figure_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface
    $entityManager, ValidatorInterface $validator): Response
    {
        $figure = new Figure();
        $form = $this->createForm(FigureForm::class, $figure);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $images = $form->get('images')->getData();

            if ($images) {
                foreach ($images as $image) {
                    // Valider chaque fichier
                    $violations = $validator->validate(
                        $image,
                        new ImageConstraint([
                            'maxSize' => '5M',
                            'mimeTypesMessage' => 'Merci d\'uploader une image valide (jpeg/png/webp)',
                        ])
                    );

                    if (count($violations) > 0) {
                        // Handle the violation (show error, redirect, etc.)
                        $this->addFlash('error', (string) $violations);
                        continue;
                    }

                    $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $this->slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();

                    try {
                        $image->move(
                            $this->getParameter('figures_images_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        dd('Erreur lors de l’upload : ' . $e->getMessage());
                    }

                    $picture = new PictureFigure();
                    $picture->setName($newFilename);
                    $picture->setFigure($figure);
                    $entityManager->persist($picture);
                }
            }

            $slug = $this->slugger->slug($figure->getName())->lower();
            $figure->setSlug($slug);
            $entityManager->persist($figure);
            $entityManager->flush();

            return $this->redirectToRoute('app_figure_index');
        }

        return $this->render('figure/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{slug}', name: 'app_figure_show', methods: ['GET'])]
    public function show(Figure $figure, Request $request): Response
    {
        if($request->isXmlHttpRequest())
        {
            return new JsonResponse([
               'content' => $this->renderView('figure/_figure.html.twig', [ 'figure' => $figure ])
            ]);
        }
        return $this->render('figure/show.html.twig', [
            'figure' => $figure,
        ]);
    }

    #[Route('/edit/{slug}', name: 'app_figure_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Figure $figure, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(FigureForm::class, $figure);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->flush();

            return $this->redirectToRoute('app_figure_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('figure/edit.html.twig', [
            'figure' => $figure,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}', name: 'app_figure_delete', methods: ['POST'])]
    public function delete(Request $request, Figure $figure, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$figure->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($figure);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_figure_index', [], Response::HTTP_SEE_OTHER);
    }
}
