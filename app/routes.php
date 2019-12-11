<?php

/*use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;*/

// 

// Creating routes.

// Documentation home.
$app->get('/', 'staticPages:home');

// Documentation entry point.
$app->get('/swagger.json', function($request, $response, $args) {
    $swagger = \Swagger\scan(["/home/user/VRE_data_api/app"]);
    header('Content-Type: application/json');
    echo $swagger;
});

// Versioning group

$app->group('/v1', function() use ($container) {

    $this->get('/doc', function($request, $response, $args) {
            $swagger = \Swagger\scan(["/home/user/VRE_data_api/app"]);
            header('Content-Type: application/json');
            echo $swagger;
    });

    // Files: get & post file.
    $this->group('/files', function() use ($container) {
        $this->get('/[{file_path:.*}]', 'apiController:get_content_by_path');
    });

    // Files metadata: get & post metadata.
    $this->group('/metadata', function () use ($container) {

        // Getting all the associated files to an specific username/email.
        $this->get('', 'apiController:get_files');

        // We add to the query the id attribute.
        $this->get('/[{file_id}[/]]', 'apiController:get_files_by_id');

    })->add(new App\Middleware\JsonResponse($container));

})->add(new App\Middleware\TokenVerify($container));
//});