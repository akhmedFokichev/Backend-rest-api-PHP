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

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/autoload_clean.php';
require __DIR__ . '/../config/Config.php';

// deps
$pdo = new PDO('mysql:host=localhost;dbname=cv82602_slimdev','cv82602_slimdev','4YxsN8Pp');
$userRepo = new UserRepositoryMysql($pdo);
$sessionRepo = new SessionRepositoryMysql($pdo);
$passwords = new PasswordService();
$registerUC = new RegisterUserUseCase($userRepo, $passwords);
$config = new \Config();
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

// slim
$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

// routes (подключены из отдельного файла)
$routes = require __DIR__ . '/../src/Http/routes.php';
$routes($app, $identity, $country);

$app->run();