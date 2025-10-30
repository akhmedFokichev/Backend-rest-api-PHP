<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(-1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Middleware\BodyParsingMiddleware;

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../src/autoload.php';

$di = new DI();

$di->initialization();

$app = AppFactory::create();

$app->addRoutingMiddleware();

// Parse JSON bodies
$app->add(new BodyParsingMiddleware());

// Error middleware should be last in the stack
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Global middlewares
$app->add(new RequestValidMiddleware());
$app->add(new \App\Middleware\CorsMiddleware());

include_once __DIR__ . '/../src/Router/Router.php';

$app->run();
