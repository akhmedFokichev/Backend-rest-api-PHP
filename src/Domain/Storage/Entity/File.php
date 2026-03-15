<?php

namespace App\Domain\Storage\Entity;

use App\Domain\Shared\BaseModel;

class File extends BaseModel
{
    public string $originalName = '';
    public string $storageName = '';
    public string $mimeType = '';
    public int $size = 0;
    public string $path = '';

    private static string $storageDir = '';

    public static function setStorageDir(string $dir): void
    {
        self::$storageDir = $dir;
    }

    protected static function tableName(): string
    {
        return 'storage_files';
    }

    protected static function hydrate(array $r): static
    {
        $e = new static();
        $e->id = (int)$r['id'];
        $e->uuid = $r['uuid'];
        $e->originalName = $r['original_name'];
        $e->storageName = $r['storage_name'];
        $e->mimeType = $r['mime_type'];
        $e->size = (int)$r['size'];
        $e->path = $r['path'];
        $e->createdAt = new \DateTimeImmutable($r['created_at']);
        $e->updatedAt = new \DateTimeImmutable($r['updated_at']);
        return $e;
    }

    protected function toDbRow(): array
    {
        return [
            'original_name' => $this->originalName,
            'storage_name' => $this->storageName,
            'mime_type' => $this->mimeType,
            'size' => $this->size,
            'path' => $this->path,
        ];
    }

    public function getFullPath(): string
    {
        return self::$storageDir . '/' . $this->storageName;
    }

    /**
     * Process an uploaded file: move to storage, fill properties, save to DB.
     */
    public function uploadFrom(array $uploadedFile): void
    {
        if (!isset($uploadedFile['tmp_name']) || !is_uploaded_file($uploadedFile['tmp_name'])) {
            throw new \RuntimeException('Invalid uploaded file');
        }
        if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('File upload error: ' . $uploadedFile['error']);
        }

        $this->originalName = $uploadedFile['name'] ?? 'unnamed';
        $extension = pathinfo($this->originalName, PATHINFO_EXTENSION);
        if ($this->uuid === '') {
            $this->uuid = static::uuidv4();
        }
        $this->storageName = $this->uuid . ($extension ? '.' . $extension : '');
        $this->mimeType = $uploadedFile['type'] ?? 'application/octet-stream';
        $this->size = (int)($uploadedFile['size'] ?? 0);
        $this->path = $this->storageName;

        if (!is_dir(self::$storageDir)) {
            mkdir(self::$storageDir, 0755, true);
        }

        if (!move_uploaded_file($uploadedFile['tmp_name'], $this->getFullPath())) {
            throw new \RuntimeException('Failed to move uploaded file');
        }

        $this->save();
    }

    /**
     * Delete both the physical file and the DB record.
     */
    public function deleteWithFile(): void
    {
        $fullPath = $this->getFullPath();
        if (file_exists($fullPath)) {
            if (!unlink($fullPath)) {
                throw new \RuntimeException('Failed to delete physical file');
            }
        }
        $this->delete();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'originalName' => $this->originalName,
            'mimeType' => $this->mimeType,
            'size' => $this->size,
            'createdAt' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
