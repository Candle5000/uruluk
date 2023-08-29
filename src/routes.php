<?php

use Slim\App;
use Controller\TopMenuController;
use Controller\NewsController;
use Controller\PrivacyPolicyController;
use Controller\ItemsController;
use Controller\CreaturesController;
use Controller\FloorsController;
use Controller\SimulatorController;
use Controller\ShortURLController;

return function (App $app) {
    $container = $app->getContainer();

    $app->get('/', TopMenuController::class . ':index');

    $app->get('/privacy', PrivacyPolicyController::class . ':index');

    $app->get('/news', NewsController::class . ':index');

    $app->get('/items', ItemsController::class . ':index');

    $app->get('/items/detail/{itemId}', ItemsController::class . ':detail');

    $app->get('/items/{itemClassName}/rare', ItemsController::class . ':rareItem');

    $app->get('/items/{itemClassName}/rare/{itemId}', ItemsController::class . ':rareItem');

    $app->get('/items/{itemClassName}/{baseItemId}', ItemsController::class . ':commonItem');

    $app->get('/items/{itemClassName}/{baseItemId}/{itemId}', ItemsController::class . ':commonItem');

    $app->get('/creatures', CreaturesController::class . ':index');

    $app->get('/creatures/{creatureId}', CreaturesController::class . ':index');

    $app->get('/creatures/detail/{creatureId}', CreaturesController::class . ':detail');

    $app->get('/floors', FloorsController::class . ':index');

    $app->get('/floors/{floorId}', FloorsController::class . ':detail');

    $app->get('/simulator', SimulatorController::class . ':index');

    $app->get('/simulator/item/{itemClassName}', SimulatorController::class . ':item');

    $app->get('/s', ShortURLController::class . ':index');

    $app->get('/s/{key}', ShortURLController::class . ':index');

    $app->post('/s', ShortURLController::class . ':post');
};
