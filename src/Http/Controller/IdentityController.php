<?php
namespace App\Http\Controller;

use App\Application\Identity\RegisterUserUseCase;
use App\Application\Identity\LoginUserUseCase;
use App\Application\Identity\RefreshTokenUseCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenApi\Attributes as OA;

class IdentityController
{
    private RegisterUserUseCase $registerUC;
    private ?LoginUserUseCase $loginUC = null;
    private ?RefreshTokenUseCase $refreshUC = null;

    public function __construct(RegisterUserUseCase $registerUC, ?LoginUserUseCase $loginUC = null, ?RefreshTokenUseCase $refreshUC = null) {
        $this->registerUC = $registerUC;
        $this->loginUC = $loginUC;
        $this->refreshUC = $refreshUC;
    }

    #[OA\Post(
        path: "/identity/registration",
        summary: "Регистрация нового пользователя",
        description: "Создает нового пользователя в системе",
        tags: ["Identity"],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Данные для регистрации",
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    type: "object",
                    required: ["login", "password"],
                    properties: [
                        new OA\Property(property: "login", type: "string", example: "user@example.com", description: "Логин пользователя (email)"),
                        new OA\Property(property: "password", type: "string", format: "password", example: "password123", description: "Пароль пользователя")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Пользователь успешно зарегистрирован",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        type: "object",
                        properties: [
                            new OA\Property(property: "id", type: "integer", example: 1),
                            new OA\Property(property: "login", type: "string", example: "user@example.com")
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 409,
                description: "Пользователь уже существует",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        type: "object",
                        properties: [
                            new OA\Property(property: "error", type: "string", example: "User already exists")
                        ]
                    )
                )
            )
        ]
    )]
    public function registration(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $input = json_decode((string)$request->getBody(), true);
        try {
            $user = $this->registerUC->execute($input['login'], $input['password']);
            $data = ['id'=>$user->id, 'login'=>$user->login];
            $response->getBody()->write(json_encode($data));
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        } catch (\DomainException $e) {
            $response->getBody()->write(json_encode(['error'=>$e->getMessage()]));
            return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
        }
    }

    #[OA\Post(
        path: "/identity/login",
        summary: "Вход в систему",
        description: "Аутентификация пользователя и получение JWT токенов",
        tags: ["Identity"],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Учетные данные пользователя",
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    type: "object",
                    required: ["login", "password"],
                    properties: [
                        new OA\Property(property: "login", type: "string", example: "user@example.com", description: "Логин пользователя"),
                        new OA\Property(property: "password", type: "string", format: "password", example: "password123", description: "Пароль пользователя")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Успешный вход",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        type: "object",
                        properties: [
                            new OA\Property(property: "accessToken", type: "string", example: "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...", description: "JWT токен доступа"),
                            new OA\Property(property: "refreshToken", type: "string", example: "abc123def456...", description: "Токен для обновления"),
                            new OA\Property(property: "expiresIn", type: "integer", example: 3600, description: "Время жизни токена в секундах")
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 401,
                description: "Неверные учетные данные",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        type: "object",
                        properties: [
                            new OA\Property(property: "error", type: "string", example: "Invalid credentials")
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 500,
                description: "Ошибка конфигурации",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        type: "object",
                        properties: [
                            new OA\Property(property: "error", type: "string", example: "Login not configured")
                        ]
                    )
                )
            )
        ]
    )]
    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $input = json_decode((string)$request->getBody(), true);
        if (!$this->loginUC) {
            $response->getBody()->write(json_encode(['error' => 'Login not configured']));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
        $token = $this->loginUC->execute($input['login'] ?? '', $input['password'] ?? '');
        if (!$token) {
            $response->getBody()->write(json_encode(['error' => 'Invalid credentials']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
        $response->getBody()->write(json_encode($token->toArray()));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }

    #[OA\Post(
        path: "/identity/refresh",
        summary: "Обновление токена",
        description: "Обновление access token с помощью refresh token",
        tags: ["Identity"],
        requestBody: new OA\RequestBody(
            required: true,
            description: "Данные для обновления токена",
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
                    type: "object",
                    required: ["login", "refreshToken"],
                    properties: [
                        new OA\Property(property: "login", type: "string", example: "user@example.com", description: "Логин пользователя"),
                        new OA\Property(property: "refreshToken", type: "string", example: "abc123def456...", description: "Refresh token")
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Токен успешно обновлен",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        type: "object",
                        properties: [
                            new OA\Property(property: "accessToken", type: "string", example: "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...", description: "Новый JWT токен доступа"),
                            new OA\Property(property: "refreshToken", type: "string", example: "xyz789abc123...", description: "Новый refresh token"),
                            new OA\Property(property: "expiresIn", type: "integer", example: 3600, description: "Время жизни токена в секундах")
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 401,
                description: "Неверный refresh token",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        type: "object",
                        properties: [
                            new OA\Property(property: "error", type: "string", example: "Invalid refresh request")
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 500,
                description: "Ошибка конфигурации",
                content: new OA\MediaType(
                    mediaType: "application/json",
                    schema: new OA\Schema(
                        type: "object",
                        properties: [
                            new OA\Property(property: "error", type: "string", example: "Refresh not configured")
                        ]
                    )
                )
            )
        ]
    )]
    public function refresh(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $input = json_decode((string)$request->getBody(), true);
        if (!$this->refreshUC) {
            $response->getBody()->write(json_encode(['error' => 'Refresh not configured']));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
        $token = $this->refreshUC->execute($input['login'] ?? '', $input['refreshToken'] ?? '');
        if (!$token) {
            $response->getBody()->write(json_encode(['error' => 'Invalid refresh request']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
        $response->getBody()->write(json_encode($token->toArray()));
        return $response->withStatus(200)->withHeader('Content-Type', 'application/json');
    }
}
