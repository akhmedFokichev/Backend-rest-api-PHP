<?php
namespace App\Http\Controller\Reference;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Application\Reference\Country\ListCountryUseCase;
use App\Application\Reference\Country\CreateCountryUseCase;
use App\Application\Reference\Country\UpdateCountryUseCase;
use App\Application\Reference\Country\DeleteCountryUseCase;

class CountryController
{
    public function __construct(
        private ListCountryUseCase $listUC,
        private CreateCountryUseCase $createUC,
        private UpdateCountryUseCase $updateUC,
        private DeleteCountryUseCase $deleteUC,
    ) {}

    public function list(ServerRequestInterface $req, ResponseInterface $res): ResponseInterface
    {
        $q = $req->getQueryParams();
        // Если parent_uuid не передан - null (получить все)
        // Если parent_uuid = '' (пустая строка) - получить корневые элементы
        // Если parent_uuid = 'xxx' - получить дочерние элементы
        $parentUuid = array_key_exists('parent_uuid', $q) ? (string)$q['parent_uuid'] : null;
        $isCatalog = isset($q['is_catalog']) ? (bool)$q['is_catalog'] : null;
        $items = $this->listUC->execute($parentUuid, $isCatalog);
        $res->getBody()->write(json_encode(array_map(fn($item) => $item->toArray(), $items)));
        return $res->withHeader('Content-Type','application/json');
    }

    public function create(ServerRequestInterface $req, ResponseInterface $res): ResponseInterface
    {
        $i = json_decode((string)$req->getBody(), true) ?: [];
        $isCatalog = isset($i['is_catalog']) ? (bool)$i['is_catalog'] : false;
        $uuid = $this->createUC->execute($i['code'] ?? '', $i['name'] ?? '', $isCatalog, $i['parent_uuid'] ?? null, (int)($i['sort_order'] ?? 0));
        $res->getBody()->write(json_encode(['uuid'=>$uuid]));
        return $res->withHeader('Content-Type','application/json')->withStatus(201);
    }

    public function update(ServerRequestInterface $req, ResponseInterface $res, array $args): ResponseInterface
    {
        $i = json_decode((string)$req->getBody(), true) ?: [];
        $isCatalog = array_key_exists('is_catalog', $i) ? (bool)$i['is_catalog'] : null;
        $this->updateUC->execute((string)$args['uuid'], $i['code'] ?? null, $i['name'] ?? null, $i['parent_uuid'] ?? null, $isCatalog, isset($i['sort_order']) ? (int)$i['sort_order'] : null);
        return $res->withStatus(204);
    }

    public function delete(ServerRequestInterface $req, ResponseInterface $res, array $args): ResponseInterface
    {
        $this->deleteUC->execute((string)$args['uuid']);
        return $res->withStatus(204);
    }
}

