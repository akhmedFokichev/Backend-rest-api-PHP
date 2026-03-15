<?php

namespace App\Domain\Product\Entity;

use App\Domain\Shared\BaseModel;

class Product extends BaseModel
{
    public ?string $parentUuid = null;
    public bool $isCatalog = false;
    public string $name = '';
    public string $code = '';
    public ?string $description = null;
    public ?string $barcode = null;
    public string $price = '0';
    public string $wholesalePrice = '0';
    public string $superWholesalePrice = '0';
    public ?string $unit = null;
    public string $vatRate = '0';
    public int $quantity = 0;
    public int $sortOrder = 0;
    public bool $isActive = true;

    protected static function tableName(): string
    {
        return 'product';
    }

    protected static function hydrate(array $r): static
    {
        $e = new static();
        $e->id = (int)$r['id'];
        $e->uuid = $r['uuid'];
        $e->parentUuid = $r['parent_uuid'] ?? null;
        $e->isCatalog = (bool)($r['is_catalog'] ?? false);
        $e->name = $r['name'];
        $e->code = $r['code'];
        $e->description = $r['description'] ?? null;
        $e->barcode = $r['barcode'] ?? null;
        $e->price = (string)($r['price'] ?? '0');
        $e->wholesalePrice = (string)($r['wholesale_price'] ?? '0');
        $e->superWholesalePrice = (string)($r['super_wholesale_price'] ?? '0');
        $e->unit = $r['unit'] ?? null;
        $e->vatRate = (string)($r['vat_rate'] ?? '0');
        $e->quantity = (int)($r['quantity'] ?? 0);
        $e->sortOrder = (int)($r['sort_order'] ?? 0);
        $e->isActive = (bool)($r['is_active'] ?? true);
        $e->createdAt = new \DateTimeImmutable($r['created_at']);
        $e->updatedAt = new \DateTimeImmutable($r['updated_at']);
        return $e;
    }

    protected function toDbRow(): array
    {
        return [
            'parent_uuid' => $this->parentUuid,
            'is_catalog' => $this->isCatalog,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'barcode' => $this->barcode,
            'price' => $this->price,
            'wholesale_price' => $this->wholesalePrice,
            'super_wholesale_price' => $this->superWholesalePrice,
            'unit' => $this->unit,
            'vat_rate' => $this->vatRate,
            'quantity' => $this->quantity,
            'sort_order' => $this->sortOrder,
            'is_active' => $this->isActive,
        ];
    }

    /**
     * @return static[]
     */
    public static function list(?string $parentUuid = null, ?bool $isCatalog = null, ?bool $isActive = null): array
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
        if ($isActive !== null) {
            $w[] = 'is_active = :ia';
            $p[':ia'] = $isActive ? 1 : 0;
        }
        return static::query(implode(' AND ', $w), $p, 'sort_order ASC, id ASC');
    }

    /**
     * @return array{created: int, uuids: string[], errors: array}
     */
    public static function batchCreate(array $items): array
    {
        $uuids = [];
        $errors = [];
        $created = 0;

        foreach ($items as $index => $i) {
            if (!is_array($i)) {
                $errors[] = ['index' => $index, 'message' => 'Element is not an object'];
                continue;
            }
            try {
                $p = new static();
                $p->fillFromArray($i, $index);
                $p->save();
                $uuids[] = $p->uuid;
                $created++;
            } catch (\Throwable $e) {
                $errors[] = ['index' => $index, 'message' => $e->getMessage()];
            }
        }

        return ['created' => $created, 'uuids' => $uuids, 'errors' => $errors];
    }

    /**
     * Fill properties from a request body array.
     */
    public function fillFromArray(array $i, ?int $indexForSort = null): void
    {
        if (array_key_exists('name', $i)) $this->name = $i['name'] ?? '';
        if (array_key_exists('code', $i)) $this->code = $i['code'] ?? '';
        if (array_key_exists('description', $i)) $this->description = $i['description'];
        if (array_key_exists('barcode', $i)) $this->barcode = ($i['barcode'] !== null && $i['barcode'] !== '') ? (string)$i['barcode'] : null;
        if (array_key_exists('price', $i)) $this->price = (string)$i['price'];
        if (array_key_exists('wholesalePrice', $i)) $this->wholesalePrice = (string)$i['wholesalePrice'];
        if (array_key_exists('superWholesalePrice', $i)) $this->superWholesalePrice = (string)$i['superWholesalePrice'];
        if (array_key_exists('unit', $i)) $this->unit = ($i['unit'] !== null && $i['unit'] !== '') ? (string)$i['unit'] : null;
        if (array_key_exists('vatRate', $i)) $this->vatRate = (string)$i['vatRate'];
        if (array_key_exists('quantity', $i)) $this->quantity = (int)$i['quantity'];
        if (array_key_exists('sortOrder', $i)) {
            $this->sortOrder = (int)$i['sortOrder'];
        } elseif ($indexForSort !== null && $this->id === null) {
            $this->sortOrder = $indexForSort;
        }
        if (array_key_exists('isActive', $i)) $this->isActive = (bool)$i['isActive'];
        if (array_key_exists('isCatalog', $i)) $this->isCatalog = (bool)$i['isCatalog'];
        if (array_key_exists('parentUuid', $i)) $this->parentUuid = $i['parentUuid'] ?: null;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'parentUuid' => $this->parentUuid,
            'isCatalog' => $this->isCatalog,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'barcode' => $this->barcode,
            'price' => $this->price,
            'wholesalePrice' => $this->wholesalePrice,
            'superWholesalePrice' => $this->superWholesalePrice,
            'unit' => $this->unit,
            'vatRate' => $this->vatRate,
            'quantity' => $this->quantity,
            'sortOrder' => $this->sortOrder,
            'isActive' => $this->isActive,
            'createdAt' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
