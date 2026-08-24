<?php

use Slim\App;

return function (App $app) {
    $app->add($app->getContainer()->get('csrf'));
    $app->addErrorMiddleware(true, true, true);
};
