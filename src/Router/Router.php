<?php

use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @var App $app
 */

$app->get('/', \App\Module\Identity\IdentityController::class . ':login');

// Identity

$app->post('/identity/login', \App\Module\Identity\IdentityController::class . ':login');

$app->post('/identity/add', \App\Module\Identity\IdentityController::class . ':add');

$app->post('identity/delete', \App\Module\Identity\IdentityController::class . ':delete');

$app->post('identity/refresh/', \App\Module\Identity\IdentityController::class . ':refresh');




// Storage

$app->post('/storage/add/', \App\Module\Storage\Controller\StorageController::class . ':add');

$app->post('/storage/image/', \App\Module\Storage\Controller\StorageController::class . ':getImage');