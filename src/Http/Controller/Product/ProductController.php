<?php

namespace App\Http\Controller\Product;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Domain\Product\Entity\Product;
use OpenApi\Attributes as OA;

class ProductController
{
    #[OA\Get(
        path: "/product",
        summary: "Список товаров и каталогов",
        description: "Иерархия: parent_uuid отсутствует = все; parent_uuid='' = корневые; parent_uuid=uuid = дочерние каталога. is_catalog: true = только каталоги, false = только товары.",
        tags: ["Product"],
        parameters: [
            new OA\Parameter(
                name: "parent_uuid",
                in: "query",
                description: "'' = только корневые, не передавать = все, uuid = дочерние этого каталога",
                required: false,
                schema: new OA\Schema(type: "string", format: "uuid", nullable: true)
            ),
            new OA\Parameter(
                name: "is_catalog",
                in: "query",
                description: "true — только каталоги, false — только товары",
                required: false,
                schema: new OA\Schema(type: "boolean", nullable: true)
            ),
            new OA\Parameter(
                name: "is_active",
                in: "query",
                description: "true — только активные, false — только неактивные",
                required: false,
                schema: new OA\Schema(type: "boolean", nullable: true)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Список товаров/каталогов",
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
                                new OA\Property(property: "name", type: "string"),
                                new OA\Property(property: "code", type: "string"),
                                new OA\Property(property: "description", type: "string", nullable: true),
                                new OA\Property(property: "barcode", type: "string", nullable: true),
                                new OA\Property(property: "price", type: "string"),
                                new OA\Property(property: "wholesalePrice", type: "string"),
                                new OA\Property(property: "superWholesalePrice", type: "string"),
                                new OA\Property(property: "unit", type: "string", nullable: true),
                                new OA\Property(property: "vatRate", type: "string"),
                                new OA\Property(property: "quantity", type: "integer"),
                                new OA\Property(property: "sortOrder", type: "integer"),
                                new OA\Property(property: "isActive", type: "boolean"),
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
        $isCatalog = isset($q['is_catalog']) ? filter_var($q['is_catalog'], FILTER_VALIDATE_BOOLEAN) : null;
        $isActive = isset($q['is_active']) ? filter_var($q['is_active'], FILTER_VALIDATE_BOOLEAN) : null;

        $items = Product::list($parentUuid, $isCatalog, $isActive);
        $res->getBody()->write(json_encode(array_map(fn($item) => $item->toArray(), $items)));
        return $res->withHeader('Content-Type', 'application/json');
    }

    #[OA\Post(
        path: "/product",
        summary: "Создать товар",
        tags: ["Product"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    type: "object",
                    required: ["name", "code"],
                    properties: [
                        new OA\Property(property: "name", type: "string", example: "Товар 1"),
                        new OA\Property(property: "code", type: "string", example: "PRD-001"),
                        new OA\Property(property: "description", type: "string", nullable: true),
                        new OA\Property(property: "barcode", type: "string", nullable: true, example: "4607011417556"),
                        new OA\Property(property: "price", type: "string", example: "99.99"),
                        new OA\Property(property: "wholesalePrice", type: "string", example: "89.99"),
                        new OA\Property(property: "superWholesalePrice", type: "string", example: "79.99"),
                        new OA\Property(property: "unit", type: "string", nullable: true, example: "шт"),
                        new OA\Property(property: "vatRate", type: "string", example: "20"),
                        new OA\Property(property: "quantity", type: "integer", example: 0),
                        new OA\Property(property: "sortOrder", type: "integer", example: 0),
                        new OA\Property(property: "isActive", type: "boolean", example: true),
                        new OA\Property(property: "isCatalog", type: "boolean", example: false),
                        new OA\Property(property: "parentUuid", type: "string", format: "uuid", nullable: true)
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Товар создан",
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
        $p = new Product();
        $p->fillFromArray($i);
        $p->save();

        $res->getBody()->write(json_encode(['uuid' => $p->uuid]));
        return $res->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    #[OA\Post(
        path: "/product/batch",
        summary: "Добавить список товаров",
        description: "Принимает JSON-массив товаров и создаёт их в базе.",
        tags: ["Product"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    type: "array",
                    items: new OA\Items(
                        type: "object",
                        required: ["name", "code"],
                        properties: [
                            new OA\Property(property: "name", type: "string"),
                            new OA\Property(property: "code", type: "string"),
                            new OA\Property(property: "description", type: "string", nullable: true),
                            new OA\Property(property: "barcode", type: "string", nullable: true),
                            new OA\Property(property: "price", type: "string"),
                            new OA\Property(property: "wholesalePrice", type: "string"),
                            new OA\Property(property: "superWholesalePrice", type: "string"),
                            new OA\Property(property: "unit", type: "string", nullable: true),
                            new OA\Property(property: "vatRate", type: "string"),
                            new OA\Property(property: "quantity", type: "integer"),
                            new OA\Property(property: "sortOrder", type: "integer"),
                            new OA\Property(property: "isActive", type: "boolean"),
                            new OA\Property(property: "isCatalog", type: "boolean"),
                            new OA\Property(property: "parentUuid", type: "string", format: "uuid", nullable: true)
                        ]
                    )
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Результат массового создания",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        type: "object",
                        properties: [
                            new OA\Property(property: "created", type: "integer"),
                            new OA\Property(property: "uuids", type: "array", items: new OA\Items(type: "string", format: "uuid")),
                            new OA\Property(property: "errors", type: "array", items: new OA\Items(
                                type: "object",
                                properties: [
                                    new OA\Property(property: "index", type: "integer"),
                                    new OA\Property(property: "message", type: "string")
                                ]
                            ))
                        ]
                    )
                )
            )
        ]
    )]
    public function batchCreate(ServerRequestInterface $req, ResponseInterface $res): ResponseInterface
    {
        $body = json_decode((string)$req->getBody(), true);
        $items = is_array($body) ? $body : [];
        $result = Product::batchCreate($items);

        $res->getBody()->write(json_encode($result));
        return $res->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    #[OA\Put(
        path: "/product/{uuid}",
        summary: "Обновить товар",
        tags: ["Product"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    type: "object",
                    properties: [
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "code", type: "string"),
                        new OA\Property(property: "description", type: "string", nullable: true),
                        new OA\Property(property: "barcode", type: "string", nullable: true),
                        new OA\Property(property: "price", type: "string"),
                        new OA\Property(property: "wholesalePrice", type: "string"),
                        new OA\Property(property: "superWholesalePrice", type: "string"),
                        new OA\Property(property: "unit", type: "string", nullable: true),
                        new OA\Property(property: "vatRate", type: "string"),
                        new OA\Property(property: "quantity", type: "integer"),
                        new OA\Property(property: "sortOrder", type: "integer"),
                        new OA\Property(property: "isActive", type: "boolean"),
                        new OA\Property(property: "isCatalog", type: "boolean"),
                        new OA\Property(property: "parentUuid", type: "string", format: "uuid", nullable: true)
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Товар обновлён"),
            new OA\Response(response: 404, description: "Товар не найден")
        ]
    )]
    public function update(ServerRequestInterface $req, ResponseInterface $res, array $args): ResponseInterface
    {
        $uuid = (string)$args['uuid'];
        $p = Product::findByUuid($uuid);
        if (!$p) {
            $res->getBody()->write(json_encode(['error' => 'Product not found']));
            return $res->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        $i = json_decode((string)$req->getBody(), true) ?: [];
        $p->fillFromArray($i);
        $p->save();

        return $res->withStatus(204);
    }

    #[OA\Delete(
        path: "/product/{uuid}",
        summary: "Удалить товар",
        tags: ["Product"],
        parameters: [new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))],
        responses: [new OA\Response(response: 204, description: "Товар удалён")]
    )]
    public function delete(ServerRequestInterface $req, ResponseInterface $res, array $args): ResponseInterface
    {
        $p = Product::findByUuid((string)$args['uuid']);
        if ($p) {
            $p->delete();
        }
        return $res->withStatus(204);
    }
}
