<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user
            ->setUsername('johnDoe')
            ->setEmail('johnDoe@gmail.com')
            ->setPassword($this->passwordHasher->hashPassword($user, 'johnDoe'))
            ->setIsVerified(true)
            ->setRoles(['ROLE_VERIFIED'])
        ;

        $manager->persist($user);

        $user = new User();
        $user
            ->setUsername('katleenDoe')
            ->setEmail('katleenDoe@gmail.com')
            ->setPassword($this->passwordHasher->hashPassword($user, 'katleenDoe'))
            ->setIsVerified(true)
            ->setRoles(['ROLE_VERIFIED'])
        ;

        $manager->persist($user);

        $user = new User();
        $user
            ->setUsername('georgeDoe')
            ->setEmail('georgeDoe@gmail.com')
            ->setPassword($this->passwordHasher->hashPassword($user, 'georgeDoe'))
            ->setIsVerified(true)
            ->setRoles(['ROLE_VERIFIED'])
        ;

        $manager->persist($user);

        $user = new User();
        $user
            ->setUsername('julieDoe')
            ->setEmail('julieDoe@gmail.com')
            ->setPassword($this->passwordHasher->hashPassword($user, 'julieDoe'))
            ->setIsVerified(true)
            ->setRoles(['ROLE_VERIFIED'])
        ;

        $manager->persist($user);



        $manager->flush();
    }
}
