<?php
namespace App\Http\Controller\Storage;

use App\Application\Storage\UploadFileUseCase;
use App\Application\Storage\ListFilesUseCase;
use App\Application\Storage\GetFileUseCase;
use App\Application\Storage\DeleteFileUseCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;

class FileController
{
    public function __construct(
        private UploadFileUseCase $uploadUC,
        private ListFilesUseCase $listUC,
        private GetFileUseCase $getUC,
        private DeleteFileUseCase $deleteUC
    ) {}

    #[OA\Post(
        path: "/storage/files",
        summary: "Загрузка файла",
        description: "Загружает файл на сервер и сохраняет информацию в БД",
        tags: ["Storage"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["file"],
                    properties: [
                        new OA\Property(
                            property: "file",
                            type: "string",
                            format: "binary",
                            description: "Файл для загрузки"
                        )
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Файл успешно загружен",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 1),
                            new OA\Property(property: "uuid", type: "string", format: "uuid", example: "550e8400-e29b-41d4-a716-446655440000"),
                            new OA\Property(property: "originalName", type: "string", example: "document.pdf"),
                            new OA\Property(property: "mimeType", type: "string", example: "application/pdf"),
                            new OA\Property(property: "size", type: "integer", example: 1024000),
                            new OA\Property(property: "createdAt", type: "string", format: "date-time")
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 400,
                description: "Ошибка загрузки файла"
            )
        ]
    )]
    public function upload(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $uploadedFiles = $request->getUploadedFiles();
            
            if (!isset($uploadedFiles['file'])) {
                throw new \RuntimeException('No file uploaded');
            }

            $uploadedFile = $uploadedFiles['file'];
            
            // Конвертируем PSR-7 UploadedFile в массив для Use Case
            $fileArray = [
                'name' => $uploadedFile->getClientFilename(),
                'type' => $uploadedFile->getClientMediaType(),
                'size' => $uploadedFile->getSize(),
                'tmp_name' => $uploadedFile->getStream()->getMetadata('uri'),
                'error' => $uploadedFile->getError()
            ];

            $file = $this->uploadUC->execute($fileArray);
            
            $response->getBody()->write(json_encode($file->toArray()));
            return $response
                ->withStatus(201)
                ->withHeader('Content-Type', 'application/json');
                
        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response
                ->withStatus(400)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    #[OA\Get(
        path: "/storage/files",
        summary: "Получить список файлов",
        description: "Возвращает список всех загруженных файлов",
        tags: ["Storage"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Список файлов",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        type: "array",
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: "id", type: "integer"),
                                new OA\Property(property: "uuid", type: "string", format: "uuid"),
                                new OA\Property(property: "originalName", type: "string"),
                                new OA\Property(property: "mimeType", type: "string"),
                                new OA\Property(property: "size", type: "integer"),
                                new OA\Property(property: "createdAt", type: "string", format: "date-time")
                            ]
                        )
                    )
                )
            )
        ]
    )]
    public function list(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $files = $this->listUC->execute();
        $data = array_map(fn($file) => $file->toArray(), $files);
        
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }

    #[OA\Get(
        path: "/storage/files/{uuid}",
        summary: "Скачать файл",
        description: "Скачивает файл по его UUID",
        tags: ["Storage"],
        parameters: [
            new OA\Parameter(
                name: "uuid",
                in: "path",
                required: true,
                description: "UUID файла",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Файл успешно получен",
                content: new OA\MediaType(mediaType: "application/octet-stream")
            ),
            new OA\Response(
                response: 404,
                description: "Файл не найден"
            )
        ]
    )]
    public function download(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $uuid = $args['uuid'] ?? '';
            $result = $this->getUC->execute($uuid);
            
            $file = $result['file'];
            $fullPath = $result['fullPath'];

            // Читаем файл
            $fileContent = file_get_contents($fullPath);
            
            $response->getBody()->write($fileContent);
            return $response
                ->withHeader('Content-Type', $file->mimeType)
                ->withHeader('Content-Disposition', 'attachment; filename="' . $file->originalName . '"')
                ->withHeader('Content-Length', (string)$file->size);
                
        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response
                ->withStatus(404)
                ->withHeader('Content-Type', 'application/json');
        }
    }

    #[OA\Delete(
        path: "/storage/files/{uuid}",
        summary: "Удалить файл",
        description: "Удаляет файл и его запись из БД",
        tags: ["Storage"],
        parameters: [
            new OA\Parameter(
                name: "uuid",
                in: "path",
                required: true,
                description: "UUID файла",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        responses: [
            new OA\Response(
                response: 204,
                description: "Файл успешно удален"
            ),
            new OA\Response(
                response: 404,
                description: "Файл не найден"
            )
        ]
    )]
    public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $uuid = $args['uuid'] ?? '';
            $this->deleteUC->execute($uuid);
            
            return $response->withStatus(204);
            
        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response
                ->withStatus(404)
                ->withHeader('Content-Type', 'application/json');
        }
    }
}
