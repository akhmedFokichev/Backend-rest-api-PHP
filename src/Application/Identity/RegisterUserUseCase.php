<?php
namespace App\Application\Identity;

use App\Domain\Identity\Repository\UserRepositoryInterface;
use App\Domain\Identity\Service\PasswordService;
use App\Domain\Identity\Entity\User;

class RegisterUserUseCase
{
    private UserRepositoryInterface $userRepo;
    private PasswordService $passwordService;

    public function __construct(UserRepositoryInterface $userRepo, PasswordService $passwordService)
    {
        $this->userRepo = $userRepo;
        $this->passwordService = $passwordService;
    }

    public function execute(string $login, string $password): User
    {
        if ($this->userRepo->findByLogin($login)) {
            throw new \DomainException('User already exists');
        }
        $hash = $this->passwordService->hash($password);
        $user = new User($login, $hash);
        $this->userRepo->add($user);
        return $user;
    }
}
