<?php

use Slim\App;
use App\Http\Controller\IdentityController;
use App\Http\Controller\Reference\CountryController;
use App\Http\Controller\Storage\FileController;
use App\Http\Controller\DocsController;
use OpenApi\Attributes as OA;

return static function (App $app, IdentityController $identity, ?CountryController $country = null, ?FileController $storage = null, ?DocsController $docs = null): void {
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

    // Storage
    if ($storage) {
        $app->post('/storage/files', [$storage, 'upload']);
        $app->get('/storage/files', [$storage, 'list']);
        $app->get('/storage/files/{uuid}', [$storage, 'download']);
        $app->delete('/storage/files/{uuid}', [$storage, 'delete']);
    }

    // API Documentation
    if ($docs) {
        $app->get('/api-docs.json', [$docs, 'swaggerJson']);
        $app->options('/api-docs.json', function ($req, $res) {
            return $res
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Methods', 'GET, OPTIONS')
                ->withHeader('Access-Control-Allow-Headers', 'Content-Type')
                ->withStatus(204);
        });
        
        // Swagger UI HTML page
        $app->get('/swagger-ui.html', function ($req, $res) {
            $htmlPath = __DIR__ . '/../../public_html/swagger-ui.html';
            if (file_exists($htmlPath)) {
                $html = file_get_contents($htmlPath);
                $res->getBody()->write($html);
                return $res->withHeader('Content-Type', 'text/html');
            }
            $res->getBody()->write('Swagger UI file not found');
            return $res->withStatus(404);
        });
    }
};


