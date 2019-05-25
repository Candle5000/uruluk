<?php

use Slim\App;
use Controller\TopMenuController;
use Controller\ItemsController;
use Controller\SimulatorController;

return function (App $app) {
    $container = $app->getContainer();

    $app->get('/', TopMenuController::class . ':index');

	$app->get('/items', ItemsController::class . ':index');

	$app->get('/items/rare/{itemClassName}', ItemsController::class . ':rareItem');

	$app->get('/simulator', SimulatorController::class . ':index');
};
