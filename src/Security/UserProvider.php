<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<User>
 */
class UserProvider implements UserProviderInterface
{
    public function __construct(private readonly UserRepository $userRepository) {}

    public function loadUserByIdentifier(string $identifier): User
    {
        $user = $this->userRepository->findOneBy(['username' => $identifier]);

        if (!$user)
        {
            $user = $this->userRepository->findOneBy(['email' => $identifier]);
        }

        if (!$user)
        {
            throw new UserNotFoundException(sprintf('User "%s" not found.', $identifier));
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): User
    {
        if (!$user instanceof User)
        {
            throw new \InvalidArgumentException('Unsupported user class.');
        }

        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return $class === User::class || is_subclass_of($class, User::class);
    }
}
