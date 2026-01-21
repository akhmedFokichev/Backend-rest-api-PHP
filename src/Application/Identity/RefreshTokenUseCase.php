<?php
namespace App\Application\Identity;

use App\Domain\Identity\Repository\UserRepositoryInterface;
use App\Domain\Identity\Repository\SessionRepositoryInterface;
use App\Domain\Identity\Entity\Token;

class RefreshTokenUseCase
{
    private UserRepositoryInterface $userRepo;
    private SessionRepositoryInterface $sessionRepo;

    public function __construct(UserRepositoryInterface $userRepo, SessionRepositoryInterface $sessionRepo)
    {
        $this->userRepo = $userRepo;
        $this->sessionRepo = $sessionRepo;
    }

    public function execute(string $login, string $refreshToken): ?Token
    {
        $user = $this->userRepo->findByLogin($login);
        if (!$user) { return null; }

        // Issue new tokens and rotate
        $token = $this->generateToken();
        $this->sessionRepo->updateByRefreshToken($user->id ?? 0, $refreshToken, $token->accessToken, $token->refreshToken, $token->expiresIn);
        return $token;
    }

    private function generateToken(): Token
    {
        $dateHash = password_hash(date('Y/m/d H:i:s'), PASSWORD_BCRYPT);
        $accessHash = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);
        $refreshHash = password_hash(bin2hex(random_bytes(12)), PASSWORD_BCRYPT);
        $accessToken = $dateHash . 'w' . $accessHash;
        $refreshToken = $refreshHash . 'q' . $dateHash;
        $expiresIn = strtotime('+1 day');
        return new Token($accessToken, $refreshToken, $expiresIn);
    }
}


