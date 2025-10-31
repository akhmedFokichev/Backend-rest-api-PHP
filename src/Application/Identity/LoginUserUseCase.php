<?php
namespace App\Application\Identity;

use App\Domain\Identity\Repository\UserRepositoryInterface;
use App\Domain\Identity\Repository\SessionRepositoryInterface;
use App\Domain\Identity\Service\PasswordService;
use App\Domain\Identity\Entity\Token;

class LoginUserUseCase
{
    private UserRepositoryInterface $userRepo;
    private SessionRepositoryInterface $sessionRepo;
    private PasswordService $passwords;
    private string $clientId;
    private string $secretKey;

    public function __construct(UserRepositoryInterface $userRepo, SessionRepositoryInterface $sessionRepo, PasswordService $passwords, string $clientId, string $secretKey)
    {
        $this->userRepo = $userRepo;
        $this->sessionRepo = $sessionRepo;
        $this->passwords = $passwords;
        $this->clientId = $clientId;
        $this->secretKey = $secretKey;
    }

    public function execute(string $login, string $password): ?Token
    {
        $user = $this->userRepo->findByLogin($login);
        if (!$user) { return null; }
        if (!$this->passwords->verify($password, $user->passwordHash)) { return null; }

        $token = $this->generateToken();
        $this->sessionRepo->create($user->id ?? 0, $token->accessToken, $token->refreshToken, $token->expiresIn, $this->clientId, $this->secretKey);
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


