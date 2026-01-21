<?php
namespace App\Http\Controller;

use OpenApi\Generator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DocsController
{
    public function swaggerJson(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            // Проверяем, установлена ли библиотека swagger-php
            if (!class_exists(\OpenApi\Generator::class)) {
                throw new \RuntimeException(
                    "OpenAPI library (zircote/swagger-php) is not installed. " .
                    "Please run 'composer install' or 'composer require zircote/swagger-php' on the server."
                );
            }
            
            // Определяем путь к директории src для сканирования
            // DocsController находится в src/Http/Controller/, поэтому:
            // __DIR__ = src/Http/Controller/
            // __DIR__ . '/../' = src/Http/
            // __DIR__ . '/../../' = src/
            $httpPath = __DIR__ . '/../';
            $srcPath = __DIR__ . '/../../';
            
            // Проверяем существование директорий
            if (!is_dir($httpPath)) {
                throw new \RuntimeException("HTTP directory does not exist: {$httpPath}");
            }
            if (!is_dir($srcPath)) {
                throw new \RuntimeException("Source directory does not exist: {$srcPath}");
            }
            
            // Проверяем существование ключевых файлов
            $openApiFile = $httpPath . 'OpenApi.php';
            if (!file_exists($openApiFile)) {
                throw new \RuntimeException("OpenApi.php not found at: {$openApiFile}");
            }
            
            // Явно загружаем класс OpenApi, чтобы он был доступен при сканировании
            if (!class_exists(\App\Http\OpenApi::class)) {
                require_once $openApiFile;
            }
            
            // Сканируем директорию src/Http, где находятся контроллеры и OpenApi.php
            // Также можно сканировать всю директорию src, если нужно включить другие файлы
            $scanPaths = [
                realpath($httpPath) ?: $httpPath,  // src/Http - где находятся контроллеры и OpenApi.php
            ];
            
            $openapi = Generator::scan($scanPaths);
            
            $json = $openapi->toJson();
            
            $response->getBody()->write($json);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Methods', 'GET, OPTIONS')
                ->withHeader('Access-Control-Allow-Headers', 'Content-Type');
        } catch (\Throwable $e) {
            // Логируем полную информацию об ошибке для отладки
            error_log("OpenAPI generation error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // Возвращаем детальную информацию об ошибке (в продакшене можно скрыть детали)
            $errorDetails = [
                'error' => 'Failed to generate OpenAPI documentation',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ];
            
            // В режиме разработки добавляем stack trace
            if (ini_get('display_errors')) {
                $errorDetails['trace'] = $e->getTraceAsString();
            }
            
            $response->getBody()->write(json_encode($errorDetails, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return $response
                ->withStatus(500)
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Access-Control-Allow-Origin', '*');
        }
    }
}
