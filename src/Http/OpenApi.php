<?php
namespace App\Http;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Backend REST API",
    description: "REST API для управления справочниками и аутентификацией пользователей"
)]
#[OA\Server(
    url: "https://tradeapp.xsdk.ru",
    description: "Production server"
)]
#[OA\Server(
    url: "http://localhost",
    description: "Development server"
)]
#[OA\SecurityScheme(
    securityScheme: "bearerAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "JWT токен авторизации"
)]
#[OA\Tag(name: "Identity", description: "Операции аутентификации и авторизации")]
#[OA\Tag(name: "Reference", description: "Управление справочниками")]
#[OA\Tag(name: "Storage", description: "Управление файлами и хранилищем")]
class OpenApi {}
