<?php

/*use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;*/

// 

// Creating routes

// documentation home
$app->get('/', 'staticPages:home');

// documentation entry point
$app->get('/swagger.json', function($request, $response, $args) {
    $swagger = \Swagger\scan(["/var/www/html/VREapi/app"]);
    header('Content-Type: application/json');
    echo $swagger;
});

// Versioning group

$app->group('/v1', function() use ($container) {

    $this->get('/doc', function($request, $response, $args) {
            $swagger = \Swagger\scan(["/var/www/html/VREapi/app"]);
            header('Content-Type: application/json');
            echo $swagger;
    });

    // Files content
    $this->group('/content', function() use ($container) {
    	//$this->get('/{file_path}[/]', 'apiController:get_content_by_path');
        $this->get('/[{file_path:.*}]', 'apiController:get_content_by_path');
    });


    // Files meta
    $this->group('/files', function() use ($container) {
	    $this->get(''               , 'apiController:get_files');
        $this->get('/[{file_id}[/]]', 'apiController:get_files_by_id');

    })->add(new App\Middleware\JsonResponse($container));


})->add(new App\Middleware\TokenVerify($container));
//});

