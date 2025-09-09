<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use App\Repository\FigureRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/message')]
final class MessageController extends AbstractController
{
    #[Route(name: 'app_message_index', methods: ['GET'])]
    public function index(MessageRepository $messageRepository): Response
    {
        return $this->render('message/index.html.twig', [
            'messages' => $messageRepository->findAll(),
        ]);
    }

    #[Route('/to/one/figure/{figureSlug}',name: 'app_message_to_one_figure',
        methods: ['GET', 'POST'])]
    public function getMessageToOneFigure(Request $request, MessageRepository
    $messageRepository, FigureRepository $figureRepository, string
    $figureSlug): Response
    {
        if($request->isXmlHttpRequest())
        {
//            $data = json_decode($request->getContent());
//            $figureSlug = $data->figureSlug;
//            $page = intval($data->currentPage);
            $page = $request->query->get('page');
            $limit = 10;
            $messages = $messageRepository->findByFigureId
            ($page, $limit, $figureRepository->findBySlug($figureSlug)->getId
            ());
            $maxPage = ceil($messages->count() / $limit);

            return new JsonResponse([
                'content' => $this->renderView('figure/_messages.html.twig',
                    [
                        "messages"   => $messages,
                        'figureSlug' => $figureSlug,
                    ]),
                'pagination' => $this->renderView('figure/_pagination.html.twig',
                [
                    'maxPage'    => $maxPage,
                    'page'       => $page,
                    'figureSlug' => $figureSlug,
                ])
            ]);
        }

        return $this->redirectToRoute('app_welcome');
    }

    #[Route('/new', name: 'app_message_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface
    $entityManager, FigureRepository $figureRepository, MessageRepository
    $messageRepository, UserRepository $userRepository):
    Response
    {
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($message);
            $entityManager->flush();

            return $this->redirectToRoute('app_message_index', [], Response::HTTP_SEE_OTHER);
        }

        if($request->isXmlHttpRequest())
        {
            $data = json_decode($request->getContent());
            $messageContent = $data->messageContent;
            $figureSlug = $data->figureSlug;
            $userId = $this->getUser()->getId();
            $user = $userRepository->find($userId);
            $message->setUser($user);
            $message->setContent($messageContent);
            $message->setFigure($figureRepository->findBySlug($figureSlug));
            date_default_timezone_set('Europe/Paris');
            $message->setDateOfLastUpdate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
            $entityManager->persist($message);
            $entityManager->flush();

            $page = $request->query->getInt('page', 1);
            $limit = 10;
            $messages = $messageRepository->findByFigureId
            ($page, $limit, $figureRepository->findBySlug($figureSlug)->getId
            ());
            $maxPage = ceil($messages->count() / $limit);

            return new JsonResponse([
                'content' => $this->renderView('figure/_messages.html.twig',
                [
                    "messages" => $messages,
                    'figureSlug' => $figureSlug,
                ]),
                'pagination' => $this->renderView('figure/_pagination.html.twig',
                    [
                        'maxPage'    => $maxPage,
                        'page'       => $page,
                        'figureSlug' => $figureSlug,
                    ])
            ]);
        }

        return $this->render('message/new.html.twig', [
            'message' => $message,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_message_show', methods: ['GET'])]
    public function show(Message $message): Response
    {
        return $this->render('message/show.html.twig', [
            'message' => $message,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_message_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Message $message, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_message_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('message/edit.html.twig', [
            'message' => $message,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_message_delete', methods: ['POST'])]
    public function delete(Request $request, Message $message, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$message->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($message);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_message_index', [], Response::HTTP_SEE_OTHER);
    }
}
