<?php

use Slim\Factory\AppFactory;
use App\Domain\Shared\BaseModel;
use App\Domain\Storage\Entity\File;
use App\Http\Controller\IdentityController;
use App\Application\Identity\RegisterUserUseCase;
use App\Application\Identity\LoginUserUseCase;
use App\Application\Identity\RefreshTokenUseCase;
use App\Domain\Identity\Service\PasswordService;
use App\Infrastructure\Repository\UserRepositoryMysql;
use App\Infrastructure\Repository\SessionRepositoryMysql;
use App\Http\Controller\Reference\CountryController;
use App\Http\Controller\Storage\FileController;
use App\Http\Controller\Product\ProductController;
use App\Http\Controller\DocsController;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/autoload_clean.php';
require __DIR__ . '/../config/Config.php';

// config
$config = new \Config();

if ($config->db_name === '' || $config->username === '') {
    $envPath = __DIR__ . '/../config/env.local.php';
    if (!file_exists($envPath)) {
        header('Content-Type: text/plain; charset=utf-8');
        http_response_code(503);
        echo "Configuration missing. Create file config/env.local.php with DB_NAME, DB_USER, DB_PASS.\n";
        echo "See config/env.local.example.php for template.";
        exit;
    }
    header('Content-Type: text/plain; charset=utf-8');
    http_response_code(503);
    echo "Invalid config: DB_NAME and DB_USER must be set in config/env.local.php";
    exit;
}

// Database
$dsn = "mysql:host={$config->host};dbname={$config->db_name};charset=utf8mb4";
$pdo = new PDO($dsn, $config->username, $config->password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

// Active Record: set PDO for all models
BaseModel::setPdo($pdo);
File::setStorageDir(__DIR__ . '/../storage/files');

// Identity (kept with UseCase/Repository — complex auth logic)
$userRepo = new UserRepositoryMysql($pdo);
$sessionRepo = new SessionRepositoryMysql($pdo);
$passwords = new PasswordService();
$registerUC = new RegisterUserUseCase($userRepo, $passwords);
$loginUC = new LoginUserUseCase($userRepo, $sessionRepo, $passwords, $config->clientIds[0] ?? 'web_app', $config->secretKey);
$refreshUC = new RefreshTokenUseCase($userRepo, $sessionRepo);
$identity = new IdentityController($registerUC, $loginUC, $refreshUC);

// Controllers (no DI needed — Active Record models handle DB themselves)
$country = new CountryController();
$storage = new FileController();
$product = new ProductController();
$docs = new DocsController();

// Slim
$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

// Routes
$routes = require __DIR__ . '/../src/Http/routes.php';
$routes($app, $identity, $country, $storage, $product, $docs);

$app->run();
