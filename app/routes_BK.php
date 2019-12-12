<?php

# 1. GET -> HOME PAGE:

$app->get('/', 'staticPages:home');

# 2. GET -> SWAGGER DOCS:

/*
var_dump($app->get('/swagger.json', function($request, $response, $args) {
    $swagger = \Swagger\scan(["/var/www/html/VRE_data_api/app"]);
    header('Content-Type: application/json');
    echo $swagger;
}));
*/

# 3. GET FILE:

$app->group('/content', function() use ($container) {

	$this->get('/[{file_path:.*}]', 'apiController:get_content_by_path');
});

# 0. TESTS:

$app->get('/dummy.json', function($request, $response, $args) use ($container){
	header('Content-Type: application/json');	
});
