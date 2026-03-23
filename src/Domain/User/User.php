<?php

namespace App\Domain\User;

use App\Enum\Role;
use Medoo\Medoo;
use PDOException;

class User
{
    private static ?Medoo $db = null;

    public ?int $id = null;
    public string $login = '';
    /** @var string внутреннее поле, не отдавать в API */
    private string $passwordHash = '';
    public Role $role;
    public ?string $createdAt = null;
    public ?string $updatedAt = null;

    public function __construct()
    {
        $this->role = Role::User;
    }

    public static function setDb(Medoo $db): void
    {
        self::$db = $db;
    }

    private static function db(): Medoo
    {
        if (self::$db === null) {
            throw new \RuntimeException('User::setDb() was not called');
        }
        return self::$db;
    }

    public static function findByLogin(string $login): ?self
    {
        $row = self::db()->get('users', [
            'id',
            'login',
            'password_hash',
            'role',
            'created_at',
            'updated_at',
        ], ['login' => $login]);
        return $row ? self::hydrate($row) : null;
    }

    public static function findById(int $id): ?self
    {
        $row = self::db()->get('users', [
            'id',
            'login',
            'password_hash',
            'role',
            'created_at',
            'updated_at',
        ], ['id' => $id]);
        return $row ? self::hydrate($row) : null;
    }

    private static function hydrate(array $row): self
    {
        $user = new self();
        $user->id = (int) $row['id'];
        $user->login = $row['login'];
        $user->passwordHash = $row['password_hash'];
        $user->role = Role::tryFrom((int) ($row['role'] ?? Role::User->value)) ?? Role::User;
        $user->createdAt = $row['created_at'] ?? null;
        $user->updatedAt = $row['updated_at'] ?? null;
        return $user;
    }

    public function setPassword(string $plainPassword): void
    {
        $this->passwordHash = password_hash($plainPassword, PASSWORD_DEFAULT);
    }

    public function verifyPassword(string $plainPassword): bool
    {
        return $this->passwordHash !== '' && password_verify($plainPassword, $this->passwordHash);
    }

    /**
     * Сохранить в БД. Новый пользователь — INSERT, существующий — UPDATE.
     * @throws \RuntimeException при дубликате login (уже существует)
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
            self::db()->insert('users', [
                'login' => $this->login,
                'password_hash' => $this->passwordHash,
                'role' => $this->role->value,
            ]);
        } catch (PDOException $e) {
            if ((int) $e->getCode() === 23000) {
                throw new \RuntimeException('user already exists');
            }
            throw $e;
        }
        $this->id = (int) self::db()->id();
        $row = self::db()->get('users', ['created_at', 'updated_at'], ['id' => $this->id]);
        $this->createdAt = $row['created_at'] ?? null;
        $this->updatedAt = $row['updated_at'] ?? null;
    }

    private function update(): void
    {
        self::db()->update('users', [
            'login' => $this->login,
            'password_hash' => $this->passwordHash,
            'role' => $this->role->value,
            'updated_at' => Medoo::raw('CURRENT_TIMESTAMP'),
        ], [
            'id' => $this->id,
        ]);
    }

    /**
     * Удалить запись из БД.
     */
    public function delete(): void
    {
        if ($this->id === null) {
            return;
        }
        self::db()->delete('users', ['id' => $this->id]);
    }

    /**
     * Данные для ответа API (без пароля).
     */
    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'login'     => $this->login,
            'role'      => $this->role->value,
            'roleLabel' => $this->role->label(),
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
        ];
    }
}
