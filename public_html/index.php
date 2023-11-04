<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(-1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/../config/autoload.php';


$di = new DI();

$di->initialization();

$app = AppFactory::create();

$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

include_once __DIR__ . '/../src/Router/Router.php';


// $app->add(new RequestValidMiddleware());

$app->add(new AuthMiddleware());

$app->run();