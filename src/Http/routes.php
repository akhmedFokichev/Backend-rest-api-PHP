<?php

use Slim\App;
use App\Http\Controller\IdentityController;
use App\Http\Controller\Reference\CountryController;

return static function (App $app, IdentityController $identity, ?CountryController $country = null): void {
    // Health
    $app->get('/', function ($req, $res) {
        $res->getBody()->write('OK');
        return $res->withHeader('Content-Type', 'text/plain');
    });

    // Identity
    $app->post('/identity/registration', [$identity, 'registration']);
    $app->post('/identity/login', [$identity, 'login']);
    $app->post('/identity/refresh', [$identity, 'refresh']);

    // Country reference
    if ($country) {
        $app->get('/reference/country', [$country, 'list']);
        $app->post('/reference/country', [$country, 'create']);
        $app->put('/reference/country/{uuid}', [$country, 'update']);
        $app->delete('/reference/country/{uuid}', [$country, 'delete']);
    }
};


