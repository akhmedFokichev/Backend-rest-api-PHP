<?php

namespace App\Domain\Reference\Country;

use App\Domain\Shared\BaseModel;

class Country extends BaseModel
{
    public ?string $parentUuid = null;
    public bool $isCatalog = false;
    public string $code = '';
    public string $name = '';
    public int $sortOrder = 0;

    protected static function tableName(): string
    {
        return 'ref_country';
    }

    protected static function hydrate(array $r): static
    {
        $e = new static();
        $e->id = (int)$r['id'];
        $e->uuid = $r['uuid'];
        $e->parentUuid = $r['parent_uuid'] ?? null;
        $e->isCatalog = (bool)($r['is_catalog'] ?? false);
        $e->code = $r['code'];
        $e->name = $r['name'];
        $e->sortOrder = (int)($r['sort_order'] ?? 0);
        $e->createdAt = new \DateTimeImmutable($r['created_at']);
        $e->updatedAt = new \DateTimeImmutable($r['updated_at']);
        return $e;
    }

    protected function toDbRow(): array
    {
        return [
            'parent_uuid' => $this->parentUuid,
            'is_catalog' => $this->isCatalog,
            'code' => $this->code,
            'name' => $this->name,
            'sort_order' => $this->sortOrder,
        ];
    }

    /**
     * @return static[]
     */
    public static function list(?string $parentUuid = null, ?bool $isCatalog = null): array
    {
        $w = [];
        $p = [];
        if ($parentUuid === '') {
            $w[] = 'parent_uuid IS NULL';
        } elseif ($parentUuid !== null) {
            $w[] = 'parent_uuid = :p';
            $p[':p'] = $parentUuid;
        }
        if ($isCatalog !== null) {
            $w[] = 'is_catalog = :ic';
            $p[':ic'] = $isCatalog ? 1 : 0;
        }
        return static::query(implode(' AND ', $w), $p, 'sort_order ASC, id ASC');
    }

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
            'createdAt' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
