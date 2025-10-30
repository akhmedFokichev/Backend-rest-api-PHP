<?php
namespace App\Http\Controller;

use App\Application\Identity\RegisterUserUseCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class IdentityController
{
    private RegisterUserUseCase $registerUC;

    public function __construct(RegisterUserUseCase $registerUC) {
        $this->registerUC = $registerUC;
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
}
