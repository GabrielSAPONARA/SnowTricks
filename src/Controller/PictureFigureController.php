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

final class PictureFigureController extends AbstractController
{
    #[Route('/picture/figure', name: 'app_picture_figure')]
    public function index(): Response
    {
        return $this->render('picture_figure/index.html.twig', [
            'controller_name' => 'PictureFigureController',
        ]);
    }

    #[Route('/picture/figure/edit/{id}', name: 'app_picture_figure_edit')]
    public function edit
    (
        PictureFigure          $pictureFigure,
        Request                $request,
        EntityManagerInterface $entityManager,
        SluggerInterface       $slugger
    ): Response
    {
        $form = $this->createForm(PictureFigureFormType::class, $pictureFigure);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $image = $form->get('image')->getData();
            $originalFilename = pathinfo($image
                ->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' .
                           $image->guessExtension();
            $oldPictureName = explode("-", $pictureFigure->getName())[0];
            if ($oldPictureName !== $originalFilename)
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
                    $image->move(
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

            }
            $entityManager->flush();
            return $this->redirectToRoute('app_welcome');
        }

        if($request->isXmlHttpRequest())
        {
            $data = json_decode($request->getContent());
            dd($data);
        }

        return $this->render('picture_figure/edit.html.twig',
            [
                'form' => $form,
            ]);
    }

    #[Route('/picture/figure/form/to/edit/{id}', name: 'app_picture_figure_form_to_edit')]
    public function getFormPictureFigureToEdit
    (
        Request                $request,
        EntityManagerInterface $entityManager,
        PictureFigureRepository $pictureFigureRepository,
    ): Response
    {
        if($request->isXmlHttpRequest())
        {
            $data = json_decode($request->getContent());
            $pictureFigure = $pictureFigureRepository->find($data->id);
            $form = $this->createForm(PictureFigureFormType::class, $pictureFigure);

            return new JsonResponse([
               'content' => $this->renderView('picture_figure/_form.html.twig',
               [
                    'form' => $form,
                    'pictureName' => $pictureFigure->getName(),
               ])
            ]);
        }
    }
}
