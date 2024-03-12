<?php

namespace Model;

use I18n\I18n;
use \PDO;
use Monolog\Logger;

abstract class Model
{
    /** @var PDO */
    protected $db;
    /** @var Logger */
    protected $logger;
    /** @var I18n */
    protected $i18n;

    public function __construct(PDO $db, Logger $logger, I18n $i18n)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->i18n = $i18n;
    }
}
