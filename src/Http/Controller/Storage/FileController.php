<?php

namespace App\Http\Controller\Storage;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Domain\Storage\Entity\File;
use OpenApi\Attributes as OA;

class FileController
{
    #[OA\Post(
        path: "/storage/files",
        summary: "Загрузка файла",
        tags: ["Storage"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["file"],
                    properties: [
                        new OA\Property(property: "file", type: "string", format: "binary")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Файл загружен",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(properties: [
                        new OA\Property(property: "id", type: "integer"),
                        new OA\Property(property: "uuid", type: "string", format: "uuid"),
                        new OA\Property(property: "originalName", type: "string"),
                        new OA\Property(property: "mimeType", type: "string"),
                        new OA\Property(property: "size", type: "integer"),
                        new OA\Property(property: "createdAt", type: "string", format: "date-time")
                    ])
                )
            ),
            new OA\Response(response: 400, description: "Ошибка загрузки")
        ]
    )]
    public function upload(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $uploadedFiles = $request->getUploadedFiles();
            if (!isset($uploadedFiles['file'])) {
                throw new \RuntimeException('No file uploaded');
            }
            $uploaded = $uploadedFiles['file'];

            $fileArray = [
                'name' => $uploaded->getClientFilename(),
                'type' => $uploaded->getClientMediaType(),
                'size' => $uploaded->getSize(),
                'tmp_name' => $uploaded->getStream()->getMetadata('uri'),
                'error' => $uploaded->getError()
            ];

            $file = new File();
            $file->uploadFrom($fileArray);

            $response->getBody()->write(json_encode($file->toArray()));
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }

    #[OA\Get(
        path: "/storage/files",
        summary: "Список файлов",
        tags: ["Storage"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Список файлов",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        type: "array",
                        items: new OA\Items(properties: [
                            new OA\Property(property: "id", type: "integer"),
                            new OA\Property(property: "uuid", type: "string", format: "uuid"),
                            new OA\Property(property: "originalName", type: "string"),
                            new OA\Property(property: "mimeType", type: "string"),
                            new OA\Property(property: "size", type: "integer"),
                            new OA\Property(property: "createdAt", type: "string", format: "date-time")
                        ])
                    )
                )
            )
        ]
    )]
    public function list(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $files = File::findAll('created_at DESC');
        $data = array_map(fn($f) => $f->toArray(), $files);

        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }

    #[OA\Get(
        path: "/storage/files/{uuid}",
        summary: "Скачать файл",
        tags: ["Storage"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 200, description: "Файл", content: new OA\MediaType(mediaType: "application/octet-stream")),
            new OA\Response(response: 404, description: "Файл не найден")
        ]
    )]
    public function download(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $file = File::findByUuid($args['uuid'] ?? '');
            if (!$file) {
                throw new \RuntimeException('File not found');
            }

            $fullPath = $file->getFullPath();
            if (!file_exists($fullPath)) {
                throw new \RuntimeException('Physical file not found');
            }

            $response->getBody()->write(file_get_contents($fullPath));
            return $response
                ->withHeader('Content-Type', $file->mimeType)
                ->withHeader('Content-Disposition', 'attachment; filename="' . $file->originalName . '"')
                ->withHeader('Content-Length', (string)$file->size);
        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
    }

    #[OA\Delete(
        path: "/storage/files/{uuid}",
        summary: "Удалить файл",
        tags: ["Storage"],
        parameters: [
            new OA\Parameter(name: "uuid", in: "path", required: true, schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(response: 204, description: "Файл удалён"),
            new OA\Response(response: 404, description: "Файл не найден")
        ]
    )]
    public function delete(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $file = File::findByUuid($args['uuid'] ?? '');
            if (!$file) {
                throw new \RuntimeException('File not found');
            }
            $file->deleteWithFile();
            return $response->withStatus(204);
        } catch (\Throwable $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }
    }
}
