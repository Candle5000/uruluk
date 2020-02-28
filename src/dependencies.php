<?php

use Slim\App;

return function (App $app) {
	$container = $app->getContainer();

	// pdo mysql
	$container['db'] = function ($c) {
		$db = $c['settings']['db'];
		$pdo = new PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'], $db['user'], $db['pass']);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		return $pdo;
	};

	// view renderer
	$container['renderer'] = function ($c) {
		$settings = $c->get('settings')['renderer'];
		return new \Slim\Views\PhpRenderer($settings['template_path']);
	};

	// monolog
	$container['logger'] = function ($c) {
		$settings = $c->get('settings')['logger'];
		$logger = new \Monolog\Logger($settings['name']);
		$logger->pushProcessor(new \Monolog\Processor\UidProcessor());
		$logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
		return $logger;
	};

	// csrf guard
	$container['csrf'] = function ($c) {
		$csrf = new \Slim\Csrf\Guard();
		$csrf->setPersistentTokenMode(true);
		return $csrf;
	};

	// Google Service
	$container['google'] = function ($c) {
		$settings = $c->get('settings')['google'];
		$google = [
			'analytics_id' => $settings['analytics_id'],
			'adsense_id' => $settings['adsense_id'],
		];
		return $google;
	};
};
