<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    #[IsGranted('ROLE_VERIFIED')]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/my/account/{id}', name: 'app_my_account', methods: ['GET'])]
    #[IsGranted('ROLE_VERIFIED')]
    public function seeMyAccount(User $user): Response
    {
        return $this->render('user/see_my_account.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/edit/{id}', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
//            if (!$passwordHasher->isPasswordValid($user, $form->get
//            ('password')->getData()))
//            {
//                $this->addFlash('danger', 'Your password is invalid.');
//                return $this->redirectToRoute('app_user_edit', ['id' => $user->getId()]);
//            }
//
//            if ($form->get('newPassword')->get('first')->getData() !==
//                $form->get('newPassword')->get('second')->getData())
//            {
//                $this->addFlash('danger', "Your new password isn't the same in the two field.");
//                return $this->redirectToRoute('app_user_edit', ['id' => $user->getId()]);
//            }

            $entityManager->flush();

            return $this->redirectToRoute('app_user_show', ['id' => $user->getId()],
                Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/change/password/{id}', name: 'app_user_change_password',
        methods: [
        'GET', 'POST'
    ])]
    public function changePassword(Request $request, User $user,
                                   EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            if (!$passwordHasher->isPasswordValid($user, $form->get
            ('password')->getData()))
            {
                $this->addFlash('danger', 'Your password is invalid.');
                return $this->redirectToRoute('app_user_edit', ['id' => $user->getId()]);
            }

            if ($form->get('newPassword')->get('first')->getData() !==
                $form->get('newPassword')->get('second')->getData())
            {
                $this->addFlash('danger', "Your new password isn't the same in the two field.");
                return $this->redirectToRoute('app_user_edit', ['id' => $user->getId()]);
            }
            $user->setPassword($passwordHasher->hashPassword($user, $form->get('newPassword')->get('first')->getData()));
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', "Your password has been changed with success !");
            return $this->redirectToRoute('app_my_account', ['id' => $user->getId()],
                Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/change_password.html.twig',
            [
                'user' => $user,
                'form' => $form,
            ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' .
                                    $user->getId(), $request->getPayload()
                                                            ->getString('_token')))
        {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
