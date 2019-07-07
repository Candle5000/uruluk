<?php

use Slim\App;

return function (App $app) {
    $app->add($app->getContainer()['csrf']);
};
