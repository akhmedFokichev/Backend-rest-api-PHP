<?php
namespace App\Domain\Reference\Country;

class Country
{
    public ?int $id = null;
    public string $uuid;
    public ?string $parentUuid = null; // для иерархии (Adjacency List)
    public bool $isCatalog = false;    // true = каталог, false = объект
    public string $code;
    public string $name;
    public int $sortOrder = 0;         // для сортировки внутри одного уровня
    public \DateTimeImmutable $createdAt;
    public \DateTimeImmutable $updatedAt;

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'parentUuid' => $this->parentUuid,
            'isCatalog' => $this->isCatalog,
            'code' => $this->code,
            'name' => $this->name,
            'sortOrder' => $this->sortOrder,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}

