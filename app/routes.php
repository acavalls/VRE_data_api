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
    $response = $response->withHeader('Access-Control-Allow-Origin', '*');

    echo $swagger;
    return $response;

});

// Versioning group
$app->group('/v1', function() use ($container) {

    // Files: get file.
    $this->group('/files', function() use ($container) {
        $this->get('/[{file_path:.*}]', 'apiController:get_file_content_by_path');
    });

    // Files metadata: get metadata.
    $this->group('/metadata', function () use ($container) {

        // Getting the metadata for all the files associated to a vre_id.
        $this->get('', 'apiController:get_files_metadata');

        // Adding the id attribute in the query string.
        $this->get('/[{file_id}[/]]', 'apiController:get_files_metadata_by_id');

    })->add(new App\Middleware\JsonResponse($container));

    $this->group('/objects/{object_id}', function () use ($container) {

        // DRS SERVICE
        $this->get('', 'apiController:get_object_metadata_by_id');

        $this->get('/access/{access_id}', 'apiController:get_file_from_access_id');

    })->add(new App\Middleware\JsonResponse($container));

})->add(new App\Middleware\TokenVerify($container));
//});
