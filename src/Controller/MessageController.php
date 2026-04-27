<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Form\Type\MessageType;
use App\Repository\FigureRepository;
use App\Repository\MessageRepository;
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

    #[Route('/to/one/figure/{figureSlug}', name: 'app_message_to_one_figure', methods: [
        'GET', 'POST'
    ])]
    public function getMessageToOneFigure(
        Request           $request,
        MessageRepository $messageRepository,
        FigureRepository  $figureRepository,
        string            $figureSlug
    ): Response
    {
        if ($request->isXmlHttpRequest())
        {
            $page = $request->query->get('page', 1);
            $limit = 10;

            $figure = $figureRepository->findBySlug($figureSlug);
            if (!$figure)
            {
                return new JsonResponse(['error' => 'Figure not found'], 404);
            }

            $messages = $messageRepository->findByFigureId($page, $limit, $figure->getId());
            $maxPage = (int)ceil($messages->count() / $limit);

            return new JsonResponse([
                'content'    => $this->renderView('figure/_messages.html.twig', [
                    "messages"   => $messages,
                    'figureSlug' => $figureSlug,
                    'figure'     => $figure,// Pass figure if needed in template
                ]),
                'pagination' => $this->renderView('figure/_pagination.html.twig', [
                    'maxPage'    => $maxPage,
                    'page'       => $page,
                    'figureSlug' => $figureSlug,
                ]),
                'pages'      => $maxPage,
            ]);
        }

        return $this->redirectToRoute('app_welcome');
    }

    #[Route('/new', name: 'app_message_new', methods: ['GET', 'POST'])]
    public function new(
        Request                $request,
        EntityManagerInterface $entityManager,
        FigureRepository       $figureRepository,
        MessageRepository      $messageRepository
    ): Response
    {
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);

        // ✅ AJAX handling
        if ($request->isXmlHttpRequest())
        {

            $data = json_decode($request->getContent(), true);

            if (!$data || !isset($data['messageContent']) ||
                !isset($data['figureSlug']))
            {
                return new JsonResponse(['error' => 'Invalid data received.'], 400);
            }

            // ✅ Get CSRF token from header
            $csrfToken = $request->headers->get('X-CSRF-TOKEN');

            // ✅ Submit form INCLUDING token
            $form->submit([
                'content' => $data['messageContent'],
                '_token'  => $csrfToken
            ]);

            // ❌ If invalid → includes CSRF failure automatically
            if (!$form->isValid())
            {
                $errors = [];
                foreach ($form->getErrors(true) as $error)
                {
                    $errors[] = $error->getMessage();
                }

                return new JsonResponse([
                    'error' => implode(', ', $errors) ?: 'Invalid CSRF token or form data.'
                ], 400);
            }

            // ✅ Fetch Figure
            $figure = $figureRepository->findBySlug($data['figureSlug']);
            if (!$figure)
            {
                return new JsonResponse(['error' => 'Figure not found.'], 404);
            }

            // ✅ Set entity data
            $message->setContent($data['messageContent']);
            $message->setFigure($figure);

            if (!$this->getUser())
            {
                return new JsonResponse(['error' => 'You must be logged in.'], 401);
            }

            /** @var \App\Entity\User $currentUser */
            $currentUser = $this->getUser();

            $user = $entityManager->getReference(User::class, $currentUser->getId());

            $message->setUser($user);
            $message->setDateOfLastUpdate(new \DateTime());

            // ✅ Save
            $entityManager->persist($message);
            $entityManager->flush();

            // ✅ Reload messages
            $page = 1;
            $limit = 10;
            $messages = $messageRepository->findByFigureId($page, $limit, $figure->getId());
            $maxPage = (int)ceil($messages->count() / $limit);

            return new JsonResponse([
                'content'    => $this->renderView('figure/_messages.html.twig', [
                    'messages'   => $messages,
                    'figureSlug' => $figure->getSlug(),
                    'figure'     => $figure,
                ]),
                'pagination' => $this->renderView('figure/_pagination.html.twig', [
                    'maxPage'    => $maxPage,
                    'page'       => $page,
                    'figureSlug' => $figure->getSlug(),
                ])
            ]);
        }

        // ✅ Standard (non-AJAX)
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $entityManager->persist($message);
            $entityManager->flush();

            return $this->redirectToRoute('app_message_index');
        }

        return $this->render('message/new.html.twig', [
            'message' => $message,
            'form'    => $form,
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

        if ($form->isSubmitted() && $form->isValid())
        {
            $entityManager->flush();
            return $this->redirectToRoute('app_message_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('message/edit.html.twig', [
            'message' => $message,
            'form'    => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_message_delete', methods: ['POST'])]
    public function delete(Request $request, Message $message, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' .
                                    $message->getId(), $request
            ->getPayload()
            ->getString('_token')))
        {
            $entityManager->remove($message);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_message_index', [], Response::HTTP_SEE_OTHER);
    }
}