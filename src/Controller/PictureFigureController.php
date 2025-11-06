<?php

namespace App\Controller;

use App\Entity\PictureFigure;
use App\Form\PictureFigureFormType;
use App\Repository\PictureFigureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/picture/figure')]
final class PictureFigureController extends AbstractController
{
    #[Route(name: 'app_picture_figure')]
    public function index(): Response
    {
        return $this->render('picture_figure/index.html.twig', [
            'controller_name' => 'PictureFigureController',
        ]);
    }

    #[Route('/edit/{id}', name: 'app_picture_figure_edit')]
    public function edit
    (
        PictureFigure          $pictureFigure,
        Request                $request,
        EntityManagerInterface $entityManager,
        SluggerInterface       $slugger
    ): Response
    {
        if ($request->isXmlHttpRequest())
        {
            if (is_array($request->request->all()) &&
                isset($request->request->all()["picture_figure_form"]["_token"]))
            {
                $token = $request->request->all()["picture_figure_form"]["_token"];
            }
            else
            {
                return new JsonResponse([
                    'error' => 'Token CSRF is missing or disabled.'
                ], 403);
            }
            $file = $request->files->get('picture_figure_form')['image'];
            $newFilename = $file->getClientOriginalName();
            $newFilename = explode('.', $newFilename)[0];
            $oldPictureName = explode("-", $pictureFigure->getName())[0];
            if ($oldPictureName !== $newFilename)
            {
                $filePath = $this->getParameter('figures_images_directory') .
                            '/' . $pictureFigure->getName();
                if (file_exists($filePath))
                {
                    unlink($filePath);
                }
                $pictureFigure->setName($newFilename);

                try
                {
                    $file->move(
                        $this->getParameter('figures_images_directory'),
                        $newFilename
                    );
                }
                catch (FileException $e)
                {
                    $this->addFlash('error', 'Erreur lors de l’upload d’image : ' .
                                             $e->getMessage());
                }

                $entityManager->flush();

                return new JsonResponse([
                    'success' => true,
                ]);
            }
            else
            {
                return new JsonResponse([
                    'error' => 'It is the same file.'
                ], 403);
            }
        }
        else
        {
            return $this->redirectToRoute('app_welcome', [], Response::HTTP_SEE_OTHER);
        }
    }

    #[Route('/form/to/edit/{id}', name: 'app_picture_figure_form_to_edit')]
    public function getFormPictureFigureToEdit
    (
        Request                $request,
        EntityManagerInterface $entityManager,
        PictureFigure          $pictureFigure,
    ): Response
    {
        if ($request->isXmlHttpRequest())
        {
            $data = json_decode($request->getContent());
            $form = $this->createForm(PictureFigureFormType::class, $pictureFigure);

            return new JsonResponse([
                'content' => $this->renderView('picture_figure/_form.html.twig',
                    [
                        'form'        => $form,
                        'pictureName' => $pictureFigure->getName(),
                    ])
            ]);
        }
        else
        {
            return $this->redirectToRoute('app_welcome', [], Response::HTTP_SEE_OTHER);
        }
    }

    #[Route('/delete/{id}', name: 'app_picture_figure_to_delete')]
    public function delete
    (
        Request                $request,
        EntityManagerInterface $entityManager,
        PictureFigure          $pictureFigure,
    ): Response
    {
        $token = $request->headers->get('X-CSRF-TOKEN') ?? $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete' . $pictureFigure->getId(), $token)) {
            return $this->json([
                'success' => false,
                'message' => "Invalid CSRF token.",
            ], 400);
        }

        $entityManager->remove($pictureFigure);
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
