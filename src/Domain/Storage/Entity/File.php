<?php
namespace App\Domain\Storage\Entity;

class File
{
    public ?int $id = null;
    public string $uuid;
    public string $originalName;
    public string $storageName;
    public string $mimeType;
    public int $size;
    public string $path;
    public ?\DateTimeImmutable $createdAt = null;
    public ?\DateTimeImmutable $updatedAt = null;

    public function __construct(
        string $uuid,
        string $originalName,
        string $storageName,
        string $mimeType,
        int $size,
        string $path
    ) {
        $this->uuid = $uuid;
        $this->originalName = $originalName;
        $this->storageName = $storageName;
        $this->mimeType = $mimeType;
        $this->size = $size;
        $this->path = $path;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
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
