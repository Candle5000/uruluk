<?php

use Slim\App;
use Controller\TopMenuController;
use Controller\PrivacyPolicyController;
use Controller\ItemsController;
use Controller\CreaturesController;
use Controller\SimulatorController;
use Controller\ShortURLController;

return function (App $app) {
	$container = $app->getContainer();

	$app->get('/', TopMenuController::class . ':index');

	$app->get('/privacy', PrivacyPolicyController::class . ':index');

	$app->get('/items', ItemsController::class . ':index');

	$app->get('/items/rare/{itemClassName}', ItemsController::class . ':rareItem');

	$app->get('/creatures', CreaturesController::class . ':index');

	$app->get('/simulator', SimulatorController::class . ':index');

	$app->get('/simulator/item/{itemClassName}', SimulatorController::class . ':item');

	$app->get('/s', ShortURLController::class . ':index');

	$app->get('/s/{key}', ShortURLController::class . ':index');

	$app->post('/s', ShortURLController::class . ':post');
};
