<?php

namespace App\Domain\User;

use App\Enum\Role;
use PDO;
use PDOException;

class User
{
    private static ?PDO $pdo = null;

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

    public static function setPdo(PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    private static function pdo(): PDO
    {
        if (self::$pdo === null) {
            throw new \RuntimeException('User::setPdo() was not called');
        }
        return self::$pdo;
    }

    public static function findByLogin(string $login): ?self
    {
        $stmt = self::pdo()->prepare('SELECT id, login, password_hash, role, created_at, updated_at FROM users WHERE login = :login LIMIT 1');
        $stmt->execute([':login' => $login]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? self::hydrate($row) : null;
    }

    public static function findById(int $id): ?self
    {
        $stmt = self::pdo()->prepare('SELECT id, login, password_hash, role, created_at, updated_at FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
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
        $stmt = self::pdo()->prepare('INSERT INTO users (login, password_hash, role) VALUES (:login, :hash, :role)');
        try {
            $stmt->execute([':login' => $this->login, ':hash' => $this->passwordHash, ':role' => $this->role->value]);
        } catch (PDOException $e) {
            if ((int) $e->getCode() === 23000) {
                throw new \RuntimeException('user already exists');
            }
            throw $e;
        }
        $this->id = (int) self::pdo()->lastInsertId();
        $stmt = self::pdo()->prepare('SELECT created_at, updated_at FROM users WHERE id = :id');
        $stmt->execute([':id' => $this->id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->createdAt = $row['created_at'] ?? null;
        $this->updatedAt = $row['updated_at'] ?? null;
    }

    private function update(): void
    {
        $stmt = self::pdo()->prepare('UPDATE users SET login = :login, password_hash = :hash, role = :role, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute([
            ':login' => $this->login,
            ':hash'  => $this->passwordHash,
            ':role'  => $this->role->value,
            ':id'    => $this->id,
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
        $stmt = self::pdo()->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute([':id' => $this->id]);
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
