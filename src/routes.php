<?php

use Slim\App;
use Controller\TopMenuController;
use Controller\ItemSearchController;

return function (App $app) {
    $container = $app->getContainer();

    $app->get('/', TopMenuController::class . ':index');

	$app->get('/itemSearch', ItemSearchController::class . ':index');
};
