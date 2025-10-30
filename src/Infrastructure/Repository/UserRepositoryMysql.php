<?php
namespace App\Infrastructure\Repository;

use App\Domain\Identity\Entity\User;
use App\Domain\Identity\Repository\UserRepositoryInterface;
use PDO;

class UserRepositoryMysql implements UserRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function findByLogin(string $login): ?User
    {
        $stm = $this->pdo->prepare('SELECT * FROM identity_users WHERE login = :login LIMIT 1');
        $stm->bindValue(':login', $login);
        $stm->execute();
        $row = $stm->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        $user = new User($row['login'], $row['pass_hash']);
        $user->id = (int)$row['id'];
        $user->accessLevel = (int)$row['access_level'];
        $user->createdAt = new \DateTimeImmutable($row['created_at']);
        $user->updatedAt = new \DateTimeImmutable($row['updated_at']);
        return $user;
    }

    public function add(User $user): void
    {
        $stm = $this->pdo->prepare(
            'INSERT INTO identity_users (login, pass_hash, access_level, created_at, updated_at) VALUES (:login, :pass_hash, :access_level, :created_at, :updated_at)'
        );
        $stm->execute([
            ':login' => $user->login,
            ':pass_hash' => $user->passwordHash,
            ':access_level' => $user->accessLevel,
            ':created_at' => $user->createdAt->format('Y-m-d H:i:s'),
            ':updated_at' => $user->updatedAt->format('Y-m-d H:i:s')
        ]);
        $user->id = (int)$this->pdo->lastInsertId();
    }
}
