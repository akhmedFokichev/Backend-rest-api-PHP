<?php
use Slim\Factory\AppFactory;
use App\Http\Controller\IdentityController;
use App\Application\Identity\RegisterUserUseCase;
use App\Application\Identity\LoginUserUseCase;
use App\Application\Identity\RefreshTokenUseCase;
use App\Domain\Identity\Service\PasswordService;
use App\Infrastructure\Repository\UserRepositoryMysql;
use App\Infrastructure\Repository\SessionRepositoryMysql;
use App\Infrastructure\Reference\Country\CountryMysqlRepository;
use App\Application\Reference\Country\ListCountryUseCase;
use App\Application\Reference\Country\CreateCountryUseCase;
use App\Application\Reference\Country\UpdateCountryUseCase;
use App\Application\Reference\Country\DeleteCountryUseCase;
use App\Http\Controller\Reference\CountryController;
use App\Http\Controller\Storage\FileController;
use App\Http\Controller\DocsController;
use App\Infrastructure\Storage\FileRepositoryMysql;
use App\Application\Storage\UploadFileUseCase;
use App\Application\Storage\ListFilesUseCase;
use App\Application\Storage\GetFileUseCase;
use App\Application\Storage\DeleteFileUseCase;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/autoload_clean.php';
require __DIR__ . '/../config/Config.php';

// config
$config = new \Config();

// deps
$dsn = "mysql:host={$config->host};dbname={$config->db_name};charset=utf8mb4";
$pdo = new PDO($dsn, $config->username, $config->password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$userRepo = new UserRepositoryMysql($pdo);
$sessionRepo = new SessionRepositoryMysql($pdo);
$passwords = new PasswordService();
$registerUC = new RegisterUserUseCase($userRepo, $passwords);
$loginUC = new LoginUserUseCase($userRepo, $sessionRepo, $passwords, $config->clientIds[0] ?? 'web_app', $config->secretKey);
$refreshUC = new RefreshTokenUseCase($userRepo, $sessionRepo);
$identity = new IdentityController($registerUC, $loginUC, $refreshUC);

// Country reference
$countryRepo = new CountryMysqlRepository($pdo);
$listCountryUC = new ListCountryUseCase($countryRepo);
$createCountryUC = new CreateCountryUseCase($countryRepo);
$updateCountryUC = new UpdateCountryUseCase($countryRepo);
$deleteCountryUC = new DeleteCountryUseCase($countryRepo);
$country = new CountryController($listCountryUC, $createCountryUC, $updateCountryUC, $deleteCountryUC);

// Storage
$storageDir = __DIR__ . '/../storage/files';
$fileRepo = new FileRepositoryMysql($pdo);
$uploadFileUC = new UploadFileUseCase($fileRepo, $storageDir);
$listFilesUC = new ListFilesUseCase($fileRepo);
$getFileUC = new GetFileUseCase($fileRepo, $storageDir);
$deleteFileUC = new DeleteFileUseCase($fileRepo, $storageDir);
$storage = new FileController($uploadFileUC, $listFilesUC, $getFileUC, $deleteFileUC);

// Documentation
$docs = new DocsController();

// slim
$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

// routes (подключены из отдельного файла)
$routes = require __DIR__ . '/../src/Http/routes.php';
$routes($app, $identity, $country, $storage, $docs);

$app->run();