<?php

use Slim\App;
use Controller\TopMenuController;

return function (App $app) {
    $container = $app->getContainer();

    $app->get('/[{name}]', TopMenuController::class . ':index');
};
