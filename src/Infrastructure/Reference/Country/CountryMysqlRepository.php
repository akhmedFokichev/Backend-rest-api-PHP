<?php
namespace App\Infrastructure\Reference\Country;

use App\Domain\Reference\Country\CountryRepositoryInterface;
use App\Domain\Reference\Country\Country;
use PDO;

class CountryMysqlRepository implements CountryRepositoryInterface
{
    private PDO $pdo;
    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    private string $table = 'ref_country';

    public function list(?string $parentUuid = null, ?bool $isCatalog = null): array
    {
        $w = []; $p = [];
        // Если parentUuid передан как пустая строка - ищем корневые элементы (parent_uuid IS NULL)
        // Если parentUuid === null - не фильтруем по parent_uuid (получаем все)
        // Если parentUuid имеет значение - ищем дочерние элементы этого родителя
        if ($parentUuid === '') { 
            $w[] = 'parent_uuid IS NULL'; 
        } elseif ($parentUuid !== null) { 
            $w[] = 'parent_uuid = :p'; 
            $p[':p'] = $parentUuid; 
        }
        if ($isCatalog !== null) { $w[] = 'is_catalog = :ic'; $p[':ic'] = $isCatalog ? 1 : 0; }
        $where = $w ? 'WHERE ' . implode(' AND ', $w) : '';
        $sql = "SELECT id, uuid, parent_uuid, is_catalog, code, name, sort_order, created_at, updated_at FROM `{$this->table}` $where ORDER BY sort_order ASC, id ASC";
        $stm = $this->pdo->prepare($sql); $stm->execute($p);
        $rows = $stm->fetchAll(PDO::FETCH_ASSOC);
        $items = [];
        foreach ($rows as $r) {
            $e = new Country();
            $e->id = (int)$r['id'];
            $e->uuid = $r['uuid'];
            $e->parentUuid = $r['parent_uuid'];
            $e->isCatalog = (bool)$r['is_catalog'];
            $e->code = $r['code'];
            $e->name = $r['name'];
            $e->sortOrder = (int)$r['sort_order'];
            $e->createdAt = new \DateTimeImmutable($r['created_at']);
            $e->updatedAt = new \DateTimeImmutable($r['updated_at']);
            $items[] = $e;
        }
        return $items;
    }

    public function create(string $code, string $name, bool $isCatalog = false, ?string $parentUuid = null, int $sortOrder = 0): string
    {
        $uuid = $this->uuidv4();
        $stm = $this->pdo->prepare("INSERT INTO `{$this->table}` (uuid, parent_uuid, is_catalog, code, name, sort_order, created_at, updated_at) VALUES (:u,:p,:ic,:c,:n,:s,NOW(),NOW())");
        $stm->execute([':u'=>$uuid, ':p'=>$parentUuid, ':ic'=>$isCatalog ? 1 : 0, ':c'=>$code, ':n'=>$name, ':s'=>$sortOrder]);
        return $uuid;
    }

    public function update(string $uuid, ?string $code = null, ?string $name = null, ?string $parentUuid = null, ?bool $isCatalog = null, ?int $sortOrder = null): void
    {
        $sets = [];$p=[':u'=>$uuid];
        if ($code !== null) { $sets[]='code=:c'; $p[':c']=$code; }
        if ($name !== null) { $sets[]='name=:n'; $p[':n']=$name; }
        if ($parentUuid !== null) { $sets[]='parent_uuid=:p'; $p[':p']=$parentUuid; }
        if ($isCatalog !== null) { $sets[]='is_catalog=:ic'; $p[':ic']=$isCatalog ? 1 : 0; }
        if ($sortOrder !== null) { $sets[]='sort_order=:s'; $p[':s']=$sortOrder; }
        if (!$sets) return;
        $sql = "UPDATE `{$this->table}` SET ".implode(',', $sets).", updated_at=NOW() WHERE uuid=:u";
        $stm = $this->pdo->prepare($sql); $stm->execute($p);
    }

    public function delete(string $uuid): void
    {
        $stm = $this->pdo->prepare("DELETE FROM `{$this->table}` WHERE uuid=:u");
        $stm->execute([':u'=>$uuid]);
    }

    private function uuidv4(): string
    {
        $d = random_bytes(16); $d[6] = chr((ord($d[6]) & 0x0f) | 0x40); $d[8] = chr((ord($d[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($d), 4));
    }
}

