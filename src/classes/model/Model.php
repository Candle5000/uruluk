<?php

namespace Model;

use \PDO;
use Monolog\Logger;

abstract class Model {
	/** @var PDO */
	protected $db;
	/** @var Logger */
	protected $logger;

	public function __construct(PDO $db, Logger $logger) {
		$this->db = $db;
		$this->logger = $logger;
	}
}
