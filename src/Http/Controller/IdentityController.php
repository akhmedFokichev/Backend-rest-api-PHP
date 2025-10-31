<?php
namespace App\Http\Controller;

use App\Application\Identity\RegisterUserUseCase;
use App\Application\Identity\LoginUserUseCase;
use App\Application\Identity\RefreshTokenUseCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
