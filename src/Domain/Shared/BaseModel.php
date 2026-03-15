<?php

namespace App\Domain\Shared;

use PDO;

abstract class BaseModel implements DatabaseModel
{
    public ?int $id = null;
    public string $uuid = '';
    public ?\DateTimeImmutable $createdAt = null;
    public ?\DateTimeImmutable $updatedAt = null;

    abstract protected static function tableName(): string;

    abstract protected static function hydrate(array $row): static;

    /**
     * Column-value pairs for DB operations (excluding id, uuid, created_at, updated_at).
     * Boolean values will be auto-converted to 0/1 for MySQL.
     */
    abstract protected function toDbRow(): array;

    private static PDO $pdo;

    public static function setPdo(PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    protected static function pdo(): PDO
    {
        return self::$pdo;
    }

    public static function findByUuid(string $uuid): ?static
    {
        $table = static::tableName();
        $stm = self::$pdo->prepare("SELECT * FROM `{$table}` WHERE uuid = :u LIMIT 1");
        $stm->execute([':u' => $uuid]);
        $row = $stm->fetch(PDO::FETCH_ASSOC);
        return $row ? static::hydrate($row) : null;
    }

    public static function findAll(string $orderBy = 'id ASC'): array
    {
        $table = static::tableName();
        $stm = self::$pdo->query("SELECT * FROM `{$table}` ORDER BY {$orderBy}");
        $items = [];
        foreach ($stm->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $items[] = static::hydrate($row);
        }
        return $items;
    }

    /**
     * Filtered query helper for subclasses.
     */
    protected static function query(string $where = '', array $params = [], string $orderBy = 'id ASC'): array
    {
        $table = static::tableName();
        $sql = "SELECT * FROM `{$table}`";
        if ($where !== '') {
            $sql .= " WHERE {$where}";
        }
        $sql .= " ORDER BY {$orderBy}";
        $stm = self::$pdo->prepare($sql);
        $stm->execute($params);
        $items = [];
        foreach ($stm->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $items[] = static::hydrate($row);
        }
        return $items;
    }

    public function save(): void
    {
        if ($this->id !== null) {
            $this->performUpdate();
        } else {
            $this->performInsert();
        }
    }

    public function delete(): void
    {
        $table = static::tableName();
        self::$pdo->prepare("DELETE FROM `{$table}` WHERE uuid = :u")
            ->execute([':u' => $this->uuid]);
    }

    private function performInsert(): void
    {
        if ($this->uuid === '') {
            $this->uuid = self::uuidv4();
        }

        $data = $this->toDbRow();

        $cols = ['uuid'];
        $placeholders = [':uuid'];
        $params = [':uuid' => $this->uuid];

        foreach ($data as $col => $val) {
            $cols[] = $col;
            $ph = ':' . str_replace(['.', '-'], '_', $col);
            $placeholders[] = $ph;
            $params[$ph] = is_bool($val) ? ($val ? 1 : 0) : $val;
        }

        $cols[] = 'created_at';
        $cols[] = 'updated_at';
        $placeholders[] = 'NOW()';
        $placeholders[] = 'NOW()';

        $sql = sprintf(
            "INSERT INTO `%s` (%s) VALUES (%s)",
            static::tableName(),
            implode(', ', $cols),
            implode(', ', $placeholders)
        );

        self::$pdo->prepare($sql)->execute($params);
        $this->id = (int)self::$pdo->lastInsertId();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    private function performUpdate(): void
    {
        $data = $this->toDbRow();
        $sets = [];
        $params = [':uuid' => $this->uuid];

        foreach ($data as $col => $val) {
            $ph = ':' . str_replace(['.', '-'], '_', $col);
            $sets[] = "{$col} = {$ph}";
            $params[$ph] = is_bool($val) ? ($val ? 1 : 0) : $val;
        }

        $sets[] = 'updated_at = NOW()';

        $sql = sprintf(
            "UPDATE `%s` SET %s WHERE uuid = :uuid",
            static::tableName(),
            implode(', ', $sets)
        );

        self::$pdo->prepare($sql)->execute($params);
        $this->updatedAt = new \DateTimeImmutable();
    }

    protected static function uuidv4(): string
    {
        $d = random_bytes(16);
        $d[6] = chr((ord($d[6]) & 0x0f) | 0x40);
        $d[8] = chr((ord($d[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($d), 4));
    }
}
