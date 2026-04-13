<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private EmailVerifier $emailVerifier,
        private SluggerInterface $slugger,
    )
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        Security $security,
        EntityManagerInterface $entityManager,
        ValidatorInterface     $validator,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            if ($form->get('plainPassword')->get('first')->getData() !==
                $form->get('plainPassword')->get('second')->getData())
            {
                $this->addFlash('danger', "Your new password isn't the same in the two field.");
                return $this->redirectToRoute('app_register');
            }
            $user->setPassword($passwordHasher->hashPassword($user, $form->get('plainPassword')
                                                                         ->get('first')
                                                                         ->getData()));
            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $avatar = $form->get('avatar')->getData();
            if ($avatar)
            {
                $violations = $validator->validate(
                    $avatar,
                    new \Symfony\Component\Validator\Constraints\Image([
                        'maxSize'          => '5M',
                        'mimeTypesMessage' => 'Merci d\'uploader une image valide (jpeg/png/webp)',
                    ])
                );

                if (count($violations) > 0)
                {
                    $this->addFlash('error', (string)$violations);
                }

                $originalFilename = pathinfo($avatar->getClientOriginalName(),
                    PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' .
                               $avatar->guessExtension();

                try
                {
                    $avatar->move(
                        $this->getParameter('user_images_directory'),
                        $newFilename
                    );
                }
                catch (FileException $e)
                {
                    $this->addFlash('error', 'Erreur lors de l’upload : ' .
                                             $e->getMessage());
                }
                $user->setAvatar($newFilename);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('gabriel.saponara@zohomail.eu', 'Snow Tricks Support'))
                    ->to((string) $user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_welcome');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository):
    Response
    {
//        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $id = $request->get('id'); // récupéré depuis l’URL signée


        if (null === $id) {
            throw $this->createNotFoundException('No user found for this id.');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            throw $this->createNotFoundException('This user doesn\'t exist.');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            /** @var User $user */
//            $user = $this->getUser();
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_login');
    }
}
