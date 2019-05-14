<?php

use Slim\App;
use Controller\TopMenuController;
use Controller\ItemsController;

return function (App $app) {
    $container = $app->getContainer();

    $app->get('/', TopMenuController::class . ':index');

	$app->get('/items', ItemsController::class . ':index');

	$app->get('/items/rare/{class}', ItemsController::class . ':rareItem');
};
