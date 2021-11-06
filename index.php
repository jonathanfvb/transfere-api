<?php

require_once "vendor/autoload.php";

use Phalcon\Mvc\Micro;

$app = new Micro();

$app->get('/', function () use ($app) {
    $app->response->setStatusCode(200);
    $content = [
        'success' => true,
        'message' => 'Ok'
    ];
    $app->response->setJsonContent($content);
    return $app->response;
});

$app->handle(
    $_SERVER["REQUEST_URI"]
);

