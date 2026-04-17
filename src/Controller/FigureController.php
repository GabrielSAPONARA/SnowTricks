<?php

namespace App\Controller;

use App\Entity\Figure;
use App\Entity\Message;
use App\Entity\PictureFigure;
use App\Entity\VideoFigure;
use App\Form\FigureForm;
use App\Form\FigureForm2;
use App\Form\MessageType;
use App\Repository\FigureRepository;
use App\Repository\GroupRepository;
use App\Repository\MessageRepository;
use App\Service\FigureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\Image as ImageConstraint;


#[Route('/figure')]
final class FigureController extends AbstractController
{

    public function __construct(
        private SluggerInterface $slugger,
        private FigureService    $figureService,
    ) {}

    #[Route(name: 'app_figure_index', methods: ['GET'])]
    public function index(FigureRepository $figureRepository, Request $request):
    Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 2;
        $figures = $figureRepository->paginateFigures($page, $limit);
        $maxPage = ceil(count($figures) / 2);

        if ($request->get('ajax'))
        {
            return $this->figureService->getJsonResponse($figures, $maxPage,
                $page);
        }

        return $this->render('figure/index.html.twig', [
            'figures' => $figures,
            'maxPage' => $maxPage,
            'page'    => $page,
        ]);
    }

    #[Route('/new', name: 'app_figure_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_VERIFIED')]
    public function new(
        Request                $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface     $validator
    ): Response
    {
        $figure = new Figure();
        $form = $this->createForm(FigureForm::class, $figure);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            // Slug
            $slug = $this->slugger->slug($figure->getName())->lower();
            $figure->setSlug($slug);

            // 📷 Gestion des images uploadées
            $images = $form->get('images')->getData();
            if ($images)
            {
                $result = $this->figureService->recordImages($images, $figure);

                if (!$result['valid'])
                {
                    foreach ($result['errors'] as $error)
                    {
                        $this->addFlash('error', $error);
                    }
                    return $this->render('figure/new.html.twig', ['form' => $form->createView()]);
                }
            }

            // 📹 Gestion des URLs de vidéos (validation stricte)
            $videoUrls = $form->get('videoFigures')->getData();

            foreach ($videoUrls as $videoUrl)
            {
                $url = trim($videoUrl->getEmbedUrl());

                if (empty($url))
                {
                    continue;
                }

                if (!preg_match('/(youtube\.com|youtu\.be|dailymotion\.com)/', $url))
                {
                    $this->addFlash('error', 'L’URL "' . $url .
                                             '" n’est pas une URL de vidéo valide.');
                    continue;
                }

                $video = new VideoFigure();
                $video->setEmbedUrl($url);
                $video->setFigure($figure);
                $entityManager->persist($video);
            }

            $figure->setCreationDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
            $figure->setDateOfLastUpdate(new \DateTime('now', new
            \DateTimeZone('Europe/Paris')));

            $entityManager->persist($figure);
            $entityManager->flush();

            $this->addFlash('success', 'La figure a bien été créée.');

            return $this->redirectToRoute('app_figure_show', [
                'groupName' =>
                    $figure->getGroup()->getName()
                , 'slug'    => $figure->getSlug()
            ]);
        }
        else
        {
            $this->addFlash('error', 'The figure was not valid.');
            $figureRepository = $entityManager->getRepository(Figure::class);
            if ($figureRepository->findBy(['name' => $figure->getName()]))
            {
                $this->addFlash('error', 'A figure with the same name already exists.');
            }
        }

        return $this->render('figure/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/modal/{slug}', name: 'app_figure_edit_modal', methods: [
        'GET', 'POST'
    ])]
    public function getEditModal(
        Figure  $figure,
        Request $request,
    ): Response
    {
        $figureVideos = $figure->getVideoFigures();
        $figurePictures = $figure->getPictureFigures();

        if ($request->isXmlHttpRequest())
        {
            $form = $this->createForm(FigureForm2::class, $figure);

            return new JsonResponse([
                'content' => $this->renderView('figure/_modal_to_edit_figure.html.twig',
                    [
                        'figure'   => $figure,
                        'videos'   => $figureVideos,
                        'pictures' => $figurePictures,
                        'form'     => $form,
                    ])
            ]);
        }

        return new Response();
    }


    #[Route('/edit/{slug}', name: 'app_figure_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_VERIFIED')]
    public function edit(
        Request                $request,
        Figure                 $figure,
        EntityManagerInterface $entityManager,
        SluggerInterface       $slugger
    ): Response
    {
        $form = $this->createForm(FigureForm::class, $figure);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            // 🔥 SUPPRESSION D'IMAGES
            $removeImageIds = $request->request->all('remove_images');
            if ($removeImageIds)
            {
                foreach ($figure->getPictureFigures() as $picture)
                {
                    if (in_array($picture->getId(), $removeImageIds))
                    {
                        $filePath = $this->getParameter('figures_images_directory') .
                                    '/' . $picture->getName();
                        if (file_exists($filePath))
                        {
                            unlink($filePath);
                        }
                        $entityManager->remove($picture);
                    }
                }
            }

            // 🖼️ AJOUT D'IMAGES
            $images = $form->get('images')->getData();
            if ($images)
            {
                foreach ($images as $image)
                {
                    $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' .
                                   $image->guessExtension();

                    try
                    {
                        $image->move(
                            $this->getParameter('figures_images_directory'),
                            $newFilename
                        );
                    }
                    catch (FileException $e)
                    {
                        $this->addFlash('error', 'Error while picture upload : ' .
                                                 $e->getMessage());
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
            if ($removeVideoIds)
            {
                foreach ($figure->getVideoFigures() as $video)
                {
                    if (in_array($video->getId(), $removeVideoIds))
                    {
                        $entityManager->remove($video);
                    }
                }
            }

            // 🎬 AJOUT DE NOUVELLES VIDÉOS AVEC VALIDATION
            $videoUrlForms = $form->get('videoFigures')->getData();
            foreach ($videoUrlForms as $videoUrlForm)
            {
                $url = trim($videoUrlForm->getEmbedUrl());

                if (empty($url))
                {
                    continue;
                }

                if (!preg_match('/(youtube\.com|youtu\.be|dailymotion\.com)/', $url))
                {
                    $this->addFlash('error', 'L’URL "' . $url .
                                             '" n’est pas une URL de vidéo valide.');
                    continue;
                }

                $video = new VideoFigure();
                $video->setEmbedUrl($url);
                $video->setFigure($figure);
                $entityManager->persist($video);
            }


            // 🧠 SLUG
            $slug = $slugger->slug($figure->getName())->lower();
            $figure->setSlug($slug);
            $figure->setDateOfLastUpdate(new \DateTime('now', new
            \DateTimeZone('Europe/Paris')));

            $entityManager->flush();

            $this->addFlash('success', 'Figure mise à jour avec succès.');
            return $this->redirectToRoute('app_figure_show', [
                'groupName' =>
                    $figure->getGroup()->getName()
                , 'slug'    => $figure->getSlug()
            ]);
        }

        return $this->render('figure/edit.html.twig', [
            'figure' => $figure,
            'form'   => $form,
        ]);
    }

    #[Route('/update/{slug}', name: 'app_figure_update', methods: ['POST'])]
    #[IsGranted('ROLE_VERIFIED')]
    public function update(
        Request                $request,
        Figure                 $figure,
        EntityManagerInterface $entityManager,
        GroupRepository        $groupRepository,
        SluggerInterface       $slugger
    ): Response
    {
        if ($request->isXmlHttpRequest())
        {
            $allData = $request->request->all();

            // DEBUG: Log exactly what Symfony received
            // Check var/log/dev.log or docker logs symfony_php
            error_log("📥 UPDATE REQUEST RECEIVED. Slug: " . $figure->getSlug());
            error_log("📦 Raw Data: " . json_encode($allData));

            // Try to find the form data. It could be 'figure_form2', 'figure_form', etc.
            $formData = null;

            if (isset($allData['figure_form2']))
            {
                $formData = $allData['figure_form2'];
                error_log("✅ Found key: figure_form2");
            }
            elseif (isset($allData['figure_form']))
            {
                $formData = $allData['figure_form'];
                error_log("✅ Found key: figure_form");
            }
            else
            {
                // If keys are at the root (rare with form_start, but possible)
                if (isset($allData['name']))
                {
                    $formData = $allData;
                    error_log("✅ Using root level data");
                }
            }

            if (!$formData)
            {
                return $this->json([
                    'success' => false,
                    'message' => 'Form data not found. Received keys: ' .
                                 implode(', ', array_keys($allData)),
                ], 400);
            }

            // Update Entity
            $figure->setName($formData['name']);
            $figure->setDescription($formData['description']);

            $slug = $slugger->slug($formData['name'])->lower();
            $figure->setSlug($slug);
            $figure->setDateOfLastUpdate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
            $figure->setGroup(null);
            if (isset($formData['group']))
            {
                $group = $groupRepository->find($formData['group']);
                if ($group)
                {
                    $figure->setGroup($group);
                }
            }

            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'La figure a été mise à jour avec succès !',
            ]);
        }

        return $this->json([
            'success' => false, 'message' => 'Not an AJAX request'
        ], 400);
    }

    #[Route('/delete/{slug}', name: 'app_figure_delete', methods: [
        'GET', 'POST', 'DELETE'
    ])]
    #[IsGranted('ROLE_VERIFIED')]
    public function delete(Request $request, Figure $figure, EntityManagerInterface $entityManager): Response
    {
        $token = $request->headers->get('X-CSRF-TOKEN') ??
                 $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete' . $figure->getId(), $token))
        {
            return $this->json([
                'success' => false,
                'message' => "Invalid CSRF token.",
            ], 400);
        }

        foreach ($figure->getPictureFigures() as $picture)
        {
            $entityManager->remove($picture);
        }
        foreach ($figure->getVideoFigures() as $video)
        {
            $entityManager->remove($video);
        }
        $entityManager->remove($figure);
        $entityManager->flush();

        if ($request->isXmlHttpRequest())
        {
            return $this->json([
                'success' => true,
                'message' => "The figure was successfully deleted.",
            ]);
        }
        else
        {
            return $this->redirectToRoute('app_figure_index', [], Response::HTTP_SEE_OTHER);
        }
    }

    #[Route('/{groupName}/{slug}', name: 'app_figure_show', methods: ['GET'])]
    public function show(string            $groupName, Figure $figure, Request $request,
                         MessageRepository $messageRepository): Response
    {
        $figureVideos = $figure->getVideoFigures();
        $figurePictures = $figure->getPictureFigures();

        if ($request->isXmlHttpRequest())
        {
            $message = new Message();
            $formMessage = $this->createForm(MessageType::class, $message);
            $page = $request->query->getInt('page', 1);
            $limit = 10;
            $messages = $messageRepository->findByFigureId($page, $limit, $figure->getId());
            $maxPage = (int)ceil($messages->count() / $limit);

            $html = $this->renderView('figure/_figure.html.twig', [
                'figure'      => $figure,
                'videos'      => $figureVideos,
                'pictures'    => $figurePictures,
                'formMessage' => $formMessage,
                'messages'    => $messages,
                'maxPage'     => $maxPage,
                'page'        => $page,
                'figureSlug'  => $figure->getSlug(),
            ]);

            // encoder explicitement le payload, en évitant l'échappement des slashs
            $payload = json_encode(['content' => $html], JSON_UNESCAPED_SLASHES);

            // On passe true pour indiquer que $payload est déjà une chaîne JSON
            return new JsonResponse($payload, 200, [], true);
        }

        return $this->render('figure/show.html.twig', [
            'figure'   => $figure,
            'videos'   => $figureVideos,
            'pictures' => $figurePictures,
        ]);
    }
}
