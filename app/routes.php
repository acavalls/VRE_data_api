<?php

/*use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;*/

// 

// Creating routes.

// Documentation home.
$app->get('/', 'staticPages:home');

// Documentation entry point.
$app->get('/doc', function($request, $response, $args) {

    $swagger = \Swagger\scan([__DIR__]);

    $response = $response->withHeader('Content-Type', 'application/json');
    $response = $response->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization');
    $response = $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
    $response = $response->withHeader('Access-Control-Allow-Origin', 'https://editor.swagger.io');

    echo $swagger;
    return $response;

});

// Versioning group
$app->group('/v1', function() use ($container) {

    // Files: get file.
    $this->group('/files', function() use ($container) {
        $this->get('/[{file_path:.*}]', 'apiController:get_content_by_path');
    });

    // Files metadata: get metadata.
    $this->group('/metadata', function () use ($container) {

        // Getting the metadata for all the files associated to a vre_id.
        $this->get('', 'apiController:get_files');

        // Adding the id attribute in the query string.
        $this->get('/[{file_id}[/]]', 'apiController:get_files_by_id');

    })->add(new App\Middleware\JsonResponse($container));

})->add(new App\Middleware\TokenVerify($container));
//});
