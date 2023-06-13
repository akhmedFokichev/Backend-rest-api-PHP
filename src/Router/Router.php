<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @var App $app
 */

$app->post('auth', \App\Module\Auth\Controller\AuthController::class . ':login');

$app->get('/', \App\Module\Storage\Controller\StorageController::class . ':home');