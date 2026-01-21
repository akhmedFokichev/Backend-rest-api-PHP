<?php
namespace App\Infrastructure\Storage;

use App\Domain\Storage\Entity\File;
use App\Domain\Storage\Repository\FileRepositoryInterface;
use PDO;

class FileRepositoryMysql implements FileRepositoryInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function add(File $file): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO storage_files (uuid, original_name, storage_name, mime_type, size, path, created_at, updated_at) 
             VALUES (:uuid, :original_name, :storage_name, :mime_type, :size, :path, :created_at, :updated_at)'
        );
        
        $stmt->execute([
            ':uuid' => $file->uuid,
            ':original_name' => $file->originalName,
            ':storage_name' => $file->storageName,
            ':mime_type' => $file->mimeType,
            ':size' => $file->size,
            ':path' => $file->path,
            ':created_at' => $file->createdAt->format('Y-m-d H:i:s'),
            ':updated_at' => $file->updatedAt->format('Y-m-d H:i:s'),
        ]);

        $file->id = (int)$this->pdo->lastInsertId();
    }

    public function findByUuid(string $uuid): ?File
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM storage_files WHERE uuid = :uuid LIMIT 1'
        );
        $stmt->execute([':uuid' => $uuid]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT * FROM storage_files ORDER BY created_at DESC'
        );
        
        $files = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $files[] = $this->hydrate($row);
        }

        return $files;
    }

    public function delete(string $uuid): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM storage_files WHERE uuid = :uuid'
        );
        $stmt->execute([':uuid' => $uuid]);
    }

    private function hydrate(array $row): File
    {
        $file = new File(
            $row['uuid'],
            $row['original_name'],
            $row['storage_name'],
            $row['mime_type'],
            (int)$row['size'],
            $row['path']
        );
        
        $file->id = (int)$row['id'];
        $file->createdAt = new \DateTimeImmutable($row['created_at']);
        $file->updatedAt = new \DateTimeImmutable($row['updated_at']);

        return $file;
    }
}
