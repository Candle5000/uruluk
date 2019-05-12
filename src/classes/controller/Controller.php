<?php

namespace Controller;

use Psr\Container\ContainerInterface;
use Slim\Views\PhpRenderer;
use Monolog\Logger;

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
}

