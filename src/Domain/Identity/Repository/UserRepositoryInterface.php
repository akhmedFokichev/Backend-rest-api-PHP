<?php
namespace App\Domain\Identity\Repository;

use App\Domain\Identity\Entity\User;

interface UserRepositoryInterface
{
    public function findByLogin(string $login): ?User;
    public function add(User $user): void;
}
