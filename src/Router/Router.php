<?php

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * @var App $app
 */

$app->get('/', \App\Module\Identity\IdentityController::class . ':info');

// Public routes (no Auth)
$app->group('', function (RouteCollectorProxy $group) {
    // Identity (use POST for registration)
    $group->post('/identity/registration', \App\Module\Identity\IdentityController::class . ':registration');
    $group->post('/identity/registration', \App\Module\Identity\IdentityController::class . ':registration');
    $group->post('/identity/login', \App\Module\Identity\IdentityController::class . ':login');
    $group->post('/identity/refresh', \App\Module\Identity\IdentityController::class . ':refresh');
});

// Protected routes (with Auth)
$app->group('', function (RouteCollectorProxy $group) {
    // Profile
    $group->get('/profile', \App\Module\Profile\ProfileController::class . ':get');

    // Storage
    $group->post('/storage/add/', \App\Module\Storage\Controller\StorageController::class . ':add');
    $group->post('/storage/image/', \App\Module\Storage\Controller\StorageController::class . ':getImage');
})->add(new \AuthMiddleware());