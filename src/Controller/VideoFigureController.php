<?php

namespace App\Controller;

use App\Entity\VideoFigure;
use App\Form\VideoFigureFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/video/figure')]
final class VideoFigureController extends AbstractController
{
    #[Route('/edit/{id}', name: 'app_video_figure_edit')]
    public function edit
    (
        VideoFigure $videoFigure,
        Request $request,
        EntityManagerInterface $entityManager,
    )
    {
        if($request->isXmlHttpRequest())
        {
            if (is_array($request->request->all()) &&
                isset($request->request->all()["video_figure_form"]["_token"]))
            {
                $token = $request->request->all()["video_figure_form"]["_token"];
            }
            else
            {
                return new JsonResponse([
                    'error' => 'Token CSRF is missing or disabled
                .'
                ], 403);
            }
        }
        $videoFigure->setEmbedUrl($request->request->all()['video_figure_form']["url"]);
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
        ]);
    }

    #[Route('/form/to/edit/{id}', name: 'app_video_figure_form_to_edit')]
    public function getFormVideoFigureToEdit(
        VideoFigure $videoFigure,
        Request $request,
        EntityManagerInterface $entityManager,

    ): Response
    {
        if($request->isXmlHttpRequest())
        {
            $form = $this->createForm(VideoFigureFormType::class, $videoFigure);

            return new JsonResponse([
                'content' => $this->renderView('video_figure/_form.html.twig',
                    [
                        'form' => $form,
                    ])
            ]);
        }
    }

    #[Route('/delete/{id}', name: 'app_video_figure_to_delete')]
    public function delete
    (
        Request                $request,
        EntityManagerInterface $entityManager,
        VideoFigure          $videoFigure,
    ): Response
    {
        $token = $request->headers->get('X-CSRF-TOKEN') ?? $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete' . $videoFigure->getId(), $token)) {
            return $this->json([
                'success' => false,
                'message' => "Invalid CSRF token.",
            ], 400);
        }

        $entityManager->remove($videoFigure);
        $entityManager->flush();

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => true,
                'message' => "The figure was successfully deleted.",
            ]);
        } else {
            return $this->redirectToRoute('app_welcome', [], Response::HTTP_SEE_OTHER);
        }
    }
}
