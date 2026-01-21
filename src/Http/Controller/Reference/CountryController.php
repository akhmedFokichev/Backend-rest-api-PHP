<?php
namespace App\Http\Controller\Reference;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Application\Reference\Country\ListCountryUseCase;
use App\Application\Reference\Country\CreateCountryUseCase;
use App\Application\Reference\Country\UpdateCountryUseCase;
use App\Application\Reference\Country\DeleteCountryUseCase;
use OpenApi\Attributes as OA;

class CountryController
{
    public function __construct(
        private ListCountryUseCase $listUC,
        private CreateCountryUseCase $createUC,
        private UpdateCountryUseCase $updateUC,
        private DeleteCountryUseCase $deleteUC,
    ) {}

    #[OA\Get(
        path: "/reference/country",
        summary: "Получить список стран",
        description: "Возвращает список стран с возможностью фильтрации по родителю и типу (каталог/объект)",
        tags: ["Reference"],
        parameters: [
            new OA\Parameter(
                name: "parent_uuid",
                in: "query",
                description: "UUID родительского элемента. Если не указан - возвращаются все элементы. Пустая строка '' - только корневые элементы.",
                required: false,
                schema: new OA\Schema(type: "string", format: "uuid", nullable: true)
            ),
            new OA\Parameter(
                name: "is_catalog",
                in: "query",
                description: "Фильтр по типу: true - только каталоги, false - только объекты",
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
                                new OA\Property(property: "id", type: "integer", example: 1),
                                new OA\Property(property: "uuid", type: "string", format: "uuid", example: "550e8400-e29b-41d4-a716-446655440000"),
                                new OA\Property(property: "parentUuid", type: "string", format: "uuid", nullable: true, example: null),
                                new OA\Property(property: "isCatalog", type: "boolean", example: false, description: "true = каталог (папка), false = объект (страна)"),
                                new OA\Property(property: "code", type: "string", example: "RU", description: "Код страны"),
                                new OA\Property(property: "name", type: "string", example: "Россия", description: "Название страны"),
                                new OA\Property(property: "sortOrder", type: "integer", example: 0, description: "Порядок сортировки"),
                                new OA\Property(property: "createdAt", type: "string", format: "date-time", example: "2024-01-01 12:00:00"),
                                new OA\Property(property: "updatedAt", type: "string", format: "date-time", example: "2024-01-01 12:00:00")
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
        // Если parent_uuid не передан - null (получить все)
        // Если parent_uuid = '' (пустая строка) - получить корневые элементы
        // Если parent_uuid = 'xxx' - получить дочерние элементы
        $parentUuid = array_key_exists('parent_uuid', $q) ? (string)$q['parent_uuid'] : null;
        $isCatalog = isset($q['is_catalog']) ? (bool)$q['is_catalog'] : null;
        $items = $this->listUC->execute($parentUuid, $isCatalog);
        $res->getBody()->write(json_encode(array_map(fn($item) => $item->toArray(), $items)));
        return $res->withHeader('Content-Type','application/json');
    }

    #[OA\Post(
        path: "/reference/country",
        summary: "Создать страну",
        description: "Создает новую запись в справочнике стран",
        tags: ["Reference"],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Данные для создания страны",
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    type: "object",
                    required: ["code", "name"],
                    properties: [
                        new OA\Property(property: "code", type: "string", example: "RU", description: "Код страны (обязательно)"),
                        new OA\Property(property: "name", type: "string", example: "Россия", description: "Название страны (обязательно)"),
                        new OA\Property(property: "is_catalog", type: "boolean", example: false, description: "true = каталог, false = объект (по умолчанию false)"),
                        new OA\Property(property: "parent_uuid", type: "string", format: "uuid", nullable: true, example: null, description: "UUID родительского элемента"),
                        new OA\Property(property: "sort_order", type: "integer", example: 0, description: "Порядок сортировки (по умолчанию 0)")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Страна успешно создана",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        type: "object",
                        properties: [
                            new OA\Property(property: "uuid", type: "string", format: "uuid", example: "550e8400-e29b-41d4-a716-446655440000", description: "UUID созданной записи")
                        ]
                    )
                )
            )
        ]
    )]
    public function create(ServerRequestInterface $req, ResponseInterface $res): ResponseInterface
    {
        $i = json_decode((string)$req->getBody(), true) ?: [];
        $isCatalog = isset($i['is_catalog']) ? (bool)$i['is_catalog'] : false;
        $uuid = $this->createUC->execute($i['code'] ?? '', $i['name'] ?? '', $isCatalog, $i['parent_uuid'] ?? null, (int)($i['sort_order'] ?? 0));
        $res->getBody()->write(json_encode(['uuid'=>$uuid]));
        return $res->withHeader('Content-Type','application/json')->withStatus(201);
    }

    #[OA\Put(
        path: "/reference/country/{uuid}",
        summary: "Обновить страну",
        description: "Обновляет существующую запись в справочнике стран",
        tags: ["Reference"],
        parameters: [
            new OA\Parameter(
                name: "uuid",
                in: "path",
                required: true,
                description: "UUID страны для обновления",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Данные для обновления (все поля опциональны)",
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    type: "object",
                    properties: [
                        new OA\Property(property: "code", type: "string", example: "RU", description: "Код страны"),
                        new OA\Property(property: "name", type: "string", example: "Российская Федерация", description: "Название страны"),
                        new OA\Property(property: "is_catalog", type: "boolean", example: false, description: "true = каталог, false = объект"),
                        new OA\Property(property: "parent_uuid", type: "string", format: "uuid", nullable: true, example: null, description: "UUID родительского элемента (для перемещения в иерархии)"),
                        new OA\Property(property: "sort_order", type: "integer", example: 1, description: "Порядок сортировки")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 204,
                description: "Страна успешно обновлена"
            )
        ]
    )]
    public function update(ServerRequestInterface $req, ResponseInterface $res, array $args): ResponseInterface
    {
        $i = json_decode((string)$req->getBody(), true) ?: [];
        $isCatalog = array_key_exists('is_catalog', $i) ? (bool)$i['is_catalog'] : null;
        $this->updateUC->execute((string)$args['uuid'], $i['code'] ?? null, $i['name'] ?? null, $i['parent_uuid'] ?? null, $isCatalog, isset($i['sort_order']) ? (int)$i['sort_order'] : null);
        return $res->withStatus(204);
    }

    #[OA\Delete(
        path: "/reference/country/{uuid}",
        summary: "Удалить страну",
        description: "Удаляет запись из справочника стран",
        tags: ["Reference"],
        parameters: [
            new OA\Parameter(
                name: "uuid",
                in: "path",
                required: true,
                description: "UUID страны для удаления",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Страна успешно удалена"
            )
        ]
    )]
    public function delete(ServerRequestInterface $req, ResponseInterface $res, array $args): ResponseInterface
    {
        $this->deleteUC->execute((string)$args['uuid']);
        return $res->withStatus(204);
    }
}

