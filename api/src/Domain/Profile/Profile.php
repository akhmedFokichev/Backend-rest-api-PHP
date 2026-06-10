<?php

namespace App\Domain\Profile;

use Medoo\Medoo;
use PDOException;

class Profile
{
    private static ?Medoo $db = null;

    public ?int $id = null;
    public int $userId;
    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $phone = null;
    public ?string $avatarUrl = null;
    public ?string $createdAt = null;
    public ?string $updatedAt = null;

    public static function setDb(Medoo $db): void
    {
        self::$db = $db;
    }

    private static function db(): Medoo
    {
        if (self::$db === null) {
            throw new \RuntimeException('Profile::setDb() was not called');
        }
        return self::$db;
    }

    public static function findByUserId(int $userId): ?self
    {
        $row = self::db()->get('profile', [
            'id',
            'user_id',
            'first_name',
            'last_name',
            'phone',
            'avatar_url',
            'created_at',
            'updated_at',
        ], ['user_id' => $userId]);
        return $row ? self::hydrate($row) : null;
    }

    public static function findById(int $id): ?self
    {
        $row = self::db()->get('profile', [
            'id',
            'user_id',
            'first_name',
            'last_name',
            'phone',
            'avatar_url',
            'created_at',
            'updated_at',
        ], ['id' => $id]);
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
        try {
            self::db()->insert('profile', [
                'user_id' => $this->userId,
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
                'phone' => $this->phone,
                'avatar_url' => $this->avatarUrl,
            ]);
        } catch (PDOException $e) {
            if ((int) $e->getCode() === 23000) {
                throw new \RuntimeException('profile already exists for this user');
            }
            throw $e;
        }
        $this->id = (int) self::db()->id();
        $row = self::db()->get('profile', ['created_at', 'updated_at'], ['id' => $this->id]);
        $this->createdAt = $row['created_at'] ?? null;
        $this->updatedAt = $row['updated_at'] ?? null;
    }

    private function update(): void
    {
        self::db()->update('profile', [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'phone' => $this->phone,
            'avatar_url' => $this->avatarUrl,
            'updated_at' => Medoo::raw('CURRENT_TIMESTAMP'),
        ], [
            'id' => $this->id,
        ]);
    }

    public function delete(): void
    {
        if ($this->id === null) {
            return;
        }
        self::db()->delete('profile', ['id' => $this->id]);
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
