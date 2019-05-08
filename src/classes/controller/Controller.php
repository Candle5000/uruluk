<?php

namespace Controller;

use Psr\Container\ContainerInterface;
use Slim\Views\PhpRenderer;

abstract class Controller {
	/** @var \PDO */
	protected $db;
	/** @var PhpRenderer */
	protected $renderer;
	/** @var \Monolog\Processor\UidProcessor */
	protected $logger;

	public function __construct(ContainerInterface $container) {
		$this->db = $container['db'];
		$this->renderer = $container['renderer'];
		$this->logger = $container['logger'];
	}
}

