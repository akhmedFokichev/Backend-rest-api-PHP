<?php

namespace App\Http\Controller\Reference;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Domain\Reference\Country\Country;
use OpenApi\Attributes as OA;

class CountryController
{
    #[OA\Get(
        path: "/reference/country",
        summary: "Получить список стран",
        description: "Возвращает список стран с возможностью фильтрации по родителю и типу (каталог/объект)",
        tags: ["Reference"],
        parameters: [
            new OA\Parameter(
                name: "parent_uuid",
                in: "query",
                description: "UUID родительского элемента. Не указан — все. Пустая строка — корневые.",
                required: false,
                schema: new OA\Schema(type: "string", format: "uuid", nullable: true)
            ),
            new OA\Parameter(
                name: "is_catalog",
                in: "query",
                description: "true — только каталоги, false — только объекты",
                required: false,
                schema: new OA\Schema(type: "boolean", nullable: true)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Список стран",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        type: "array",
                        items: new OA\Items(
                            type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "uuid", type: "string", format: "uuid"),
                                new OA\Property(property: "parentUuid", type: "string", format: "uuid", nullable: true),
                                new OA\Property(property: "isCatalog", type: "boolean"),
                                new OA\Property(property: "code", type: "string"),
                                new OA\Property(property: "name", type: "string"),
                                new OA\Property(property: "sortOrder", type: "integer"),
                                new OA\Property(property: "createdAt", type: "string", format: "date-time"),
                                new OA\Property(property: "updatedAt", type: "string", format: "date-time")
                            ]
                        )
                    )
                )
            )
        ]
    )]
    public function list(ServerRequestInterface $req, ResponseInterface $res): ResponseInterface
    {
        $q = $req->getQueryParams();
        $parentUuid = array_key_exists('parent_uuid', $q) ? (string)$q['parent_uuid'] : null;
        $isCatalog = isset($q['is_catalog']) ? (bool)$q['is_catalog'] : null;

        $items = Country::list($parentUuid, $isCatalog);
        $res->getBody()->write(json_encode(array_map(fn($item) => $item->toArray(), $items)));
        return $res->withHeader('Content-Type', 'application/json');
    }

    #[OA\Post(
        path: "/reference/country",
        summary: "Создать страну",
        tags: ["Reference"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    type: "object",
                    required: ["code", "name"],
                    properties: [
                        new OA\Property(property: "code", type: "string", example: "RU"),
                        new OA\Property(property: "name", type: "string", example: "Россия"),
                        new OA\Property(property: "is_catalog", type: "boolean", example: false),
                        new OA\Property(property: "parent_uuid", type: "string", format: "uuid", nullable: true),
                        new OA\Property(property: "sort_order", type: "integer", example: 0)
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Страна создана",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(type: "object", properties: [
                        new OA\Property(property: "uuid", type: "string", format: "uuid")
                    ])
                )
            )
        ]
    )]
    public function create(ServerRequestInterface $req, ResponseInterface $res): ResponseInterface
    {
        $i = json_decode((string)$req->getBody(), true) ?: [];

        $c = new Country();
        $c->code = $i['code'] ?? '';
        $c->name = $i['name'] ?? '';
        $c->isCatalog = isset($i['is_catalog']) ? (bool)$i['is_catalog'] : false;
        $c->parentUuid = $i['parent_uuid'] ?? null;
        $c->sortOrder = (int)($i['sort_order'] ?? 0);
        $c->save();

        $res->getBody()->write(json_encode(['uuid' => $c->uuid]));
        return $res->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    #[OA\Put(
        path: "/reference/country/{uuid}",
        summary: "Обновить страну",
        tags: ["Reference"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    type: "object",
                    properties: [
                        new OA\Property(property: "code", type: "string"),
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "is_catalog", type: "boolean"),
                        new OA\Property(property: "parent_uuid", type: "string", format: "uuid", nullable: true),
                        new OA\Property(property: "sort_order", type: "integer")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 204, description: "Страна обновлена"),
            new OA\Response(response: 404, description: "Страна не найдена")
        ]
    )]
    public function update(ServerRequestInterface $req, ResponseInterface $res, array $args): ResponseInterface
    {
        $c = Country::findByUuid((string)$args['uuid']);
        if (!$c) {
            $res->getBody()->write(json_encode(['error' => 'Country not found']));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $i = json_decode((string)$req->getBody(), true) ?: [];
        if (array_key_exists('code', $i)) $c->code = $i['code'];
        if (array_key_exists('name', $i)) $c->name = $i['name'];
        if (array_key_exists('is_catalog', $i)) $c->isCatalog = (bool)$i['is_catalog'];
        if (array_key_exists('parent_uuid', $i)) $c->parentUuid = $i['parent_uuid'] ?: null;
        if (array_key_exists('sort_order', $i)) $c->sortOrder = (int)$i['sort_order'];
        $c->save();

        return $res->withStatus(204);
    }

    #[OA\Delete(
        path: "/reference/country/{uuid}",
        summary: "Удалить страну",
        tags: ["Reference"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [new OA\Response(response: 204, description: "Страна удалена")]
    )]
    public function delete(ServerRequestInterface $req, ResponseInterface $res, array $args): ResponseInterface
    {
        $c = Country::findByUuid((string)$args['uuid']);
        if ($c) {
            $c->delete();
        }
        return $res->withStatus(204);
    }
}
