<?php

namespace App\Domain\Profile;

use PDO;
use PDOException;

class Profile
{
    private static ?PDO $pdo = null;

    public ?int $id = null;
    public int $userId;
    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $phone = null;
    public ?string $avatarUrl = null;
    public ?string $createdAt = null;
    public ?string $updatedAt = null;

    public static function setPdo(PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    private static function pdo(): PDO
    {
        if (self::$pdo === null) {
            throw new \RuntimeException('Profile::setPdo() was not called');
        }
        return self::$pdo;
    }

    public static function findByUserId(int $userId): ?self
    {
        $stmt = self::pdo()->prepare('SELECT id, user_id, first_name, last_name, phone, avatar_url, created_at, updated_at FROM profile WHERE user_id = :uid LIMIT 1');
        $stmt->execute([':uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? self::hydrate($row) : null;
    }

    public static function findById(int $id): ?self
    {
        $stmt = self::pdo()->prepare('SELECT id, user_id, first_name, last_name, phone, avatar_url, created_at, updated_at FROM profile WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? self::hydrate($row) : null;
    }

    private static function hydrate(array $row): self
    {
        $p = new self();
        $p->id = (int) $row['id'];
        $p->userId = (int) $row['user_id'];
        $p->firstName = $row['first_name'] ?? null;
        $p->lastName = $row['last_name'] ?? null;
        $p->phone = $row['phone'] ?? null;
        $p->avatarUrl = $row['avatar_url'] ?? null;
        $p->createdAt = $row['created_at'] ?? null;
        $p->updatedAt = $row['updated_at'] ?? null;
        return $p;
    }

    /**
     * Сохранить в БД. Новый профиль — INSERT, существующий — UPDATE.
     */
    public function save(): void
    {
        if ($this->id === null) {
            $this->insert();
        } else {
            $this->update();
        }
    }

    private function insert(): void
    {
        $stmt = self::pdo()->prepare(
            'INSERT INTO profile (user_id, first_name, last_name, phone, avatar_url) VALUES (:uid, :fn, :ln, :phone, :avatar)'
        );
        try {
            $stmt->execute([
                ':uid'   => $this->userId,
                ':fn'    => $this->firstName,
                ':ln'    => $this->lastName,
                ':phone' => $this->phone,
                ':avatar'=> $this->avatarUrl,
            ]);
        } catch (PDOException $e) {
            if ((int) $e->getCode() === 23000) {
                throw new \RuntimeException('profile already exists for this user');
            }
            throw $e;
        }
        $this->id = (int) self::pdo()->lastInsertId();
        $stmt = self::pdo()->prepare('SELECT created_at, updated_at FROM profile WHERE id = :id');
        $stmt->execute([':id' => $this->id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->createdAt = $row['created_at'] ?? null;
        $this->updatedAt = $row['updated_at'] ?? null;
    }

    private function update(): void
    {
        $stmt = self::pdo()->prepare(
            'UPDATE profile SET first_name = :fn, last_name = :ln, phone = :phone, avatar_url = :avatar, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
        );
        $stmt->execute([
            ':fn'    => $this->firstName,
            ':ln'    => $this->lastName,
            ':phone' => $this->phone,
            ':avatar'=> $this->avatarUrl,
            ':id'    => $this->id,
        ]);
    }

    public function delete(): void
    {
        if ($this->id === null) {
            return;
        }
        $stmt = self::pdo()->prepare('DELETE FROM profile WHERE id = :id');
        $stmt->execute([':id' => $this->id]);
    }

    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'userId'    => $this->userId,
            'firstName' => $this->firstName,
            'lastName'  => $this->lastName,
            'phone'     => $this->phone,
            'avatarUrl' => $this->avatarUrl,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
