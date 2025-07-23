<?php

namespace App\Controller;

use App\Entity\Figure;
use App\Entity\PictureFigure;
use App\Entity\VideoFigure;
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
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): Response {
        $figure = new Figure();
        $form = $this->createForm(FigureForm::class, $figure);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Slug
            $slug = $this->slugger->slug($figure->getName())->lower();
            $figure->setSlug($slug);

            // 📷 Gestion des images uploadées
            $images = $form->get('images')->getData();
            if ($images) {
                foreach ($images as $image) {
                    $violations = $validator->validate(
                        $image,
                        new \Symfony\Component\Validator\Constraints\Image([
                            'maxSize' => '5M',
                            'mimeTypesMessage' => 'Merci d\'uploader une image valide (jpeg/png/webp)',
                        ])
                    );

                    if (count($violations) > 0) {
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
                        $this->addFlash('error', 'Erreur lors de l’upload : ' . $e->getMessage());
                        continue;
                    }

                    $picture = new PictureFigure();
                    $picture->setName($newFilename);
                    $picture->setFigure($figure);
                    $entityManager->persist($picture);
                }
            }

            // 📹 Gestion des URLs de vidéos (validation stricte)
            $videoUrls = $request->request->all('videoUrls');

            foreach ($videoUrls as $url) {
                if (!preg_match('/(youtube\.com|youtu\.be|dailymotion\.com)/', $url)) {
                    $this->addFlash('error', 'L’URL "' . $url . '" n’est pas une URL de vidéo valide.');
                    continue;
                }

                $video = new VideoFigure();
                $video->setName($url);
                $video->setFigure($figure);
                $entityManager->persist($video);
            }

            $entityManager->persist($figure);
            $entityManager->flush();

            $this->addFlash('success', 'La figure a bien été créée.');

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

//        dd($figure);
        return $this->render('figure/show.html.twig', [
            'figure' => $figure,
        ]);
    }

    #[Route('/edit/{slug}', name: 'app_figure_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Figure $figure,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        $form = $this->createForm(FigureForm::class, $figure);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 🔥 SUPPRESSION D'IMAGES
            $removeImageIds = $request->request->all('remove_images');
            if ($removeImageIds) {
                foreach ($figure->getPictureFigures() as $picture) {
                    if (in_array($picture->getId(), $removeImageIds)) {
                        $filePath = $this->getParameter('figures_images_directory') . '/' . $picture->getName();
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                        $entityManager->remove($picture);
                    }
                }
            }

            // 🖼️ AJOUT D'IMAGES
            $images = $form->get('images')->getData();
            if ($images) {
                foreach ($images as $image) {
                    $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();

                    try {
                        $image->move(
                            $this->getParameter('figures_images_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Erreur lors de l’upload d’image : ' . $e->getMessage());
                        continue;
                    }

                    $picture = new PictureFigure();
                    $picture->setName($newFilename);
                    $picture->setFigure($figure);
                    $entityManager->persist($picture);
                }
            }

            // 🎬 SUPPRESSION DE VIDÉOS
            $removeVideoIds = $request->request->all('remove_videos');
            if ($removeVideoIds) {
                foreach ($figure->getVideoFigures() as $video) {
                    if (in_array($video->getId(), $removeVideoIds)) {
                        $entityManager->remove($video);
                    }
                }
            }

            // 🎬 AJOUT DE NOUVELLES VIDÉOS AVEC VALIDATION
            $videoUrls = $form->get('videoUrls')->getData();
            foreach ($videoUrls as $url) {
                if (!preg_match('/(youtube\.com|youtu\.be|dailymotion\.com)/', $url)) {
                    $this->addFlash('error', 'L’URL "' . $url . '" n’est pas une URL de vidéo valide.');
                    continue;
                }

                $video = new VideoFigure();
                $video->setName($url);
                $video->setFigure($figure);
                $entityManager->persist($video);
            }

            // 🧠 SLUG
            $slug = $slugger->slug($figure->getName())->lower();
            $figure->setSlug($slug);

            $entityManager->flush();

            $this->addFlash('success', 'Figure mise à jour avec succès.');
            return $this->redirectToRoute('app_figure_index');
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
