<?php

namespace Controller;

use Psr\Container\ContainerInterface;
use Slim\Views\PhpRenderer;
use Monolog\Logger;
use Model\AccessCountModel;

abstract class Controller {
	/** @var \PDO */
	protected $db;
	/** @var PhpRenderer */
	protected $renderer;
	/** @var Logger */
	protected $logger;

	public function __construct(ContainerInterface $container) {
		$this->db = $container['db'];
		$this->renderer = $container['renderer'];
		$this->logger = $container['logger'];
	}

	protected function getFooterInfo() {
		$accessCount = new AccessCountModel($this->db, $this->logger);
		return [
			'pv_today' => $accessCount->getTodayPvWithCountUp(TopMenuController::PAGE_ID),
			'pv_yesterday' => $accessCount->getYesterdayPv(TopMenuController::PAGE_ID)
		];
	}
}

