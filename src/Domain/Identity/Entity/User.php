<?php
namespace App\Domain\Identity\Entity;

class User
{
    public ?int $id = null;
    public string $login;
    public string $passwordHash;
    public int $accessLevel = 0;
    public \DateTimeImmutable $createdAt;
    public \DateTimeImmutable $updatedAt;

    public function __construct(string $login, string $passwordHash)
    {
        $this->login = $login;
        $this->passwordHash = $passwordHash;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }
}
