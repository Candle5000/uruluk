<?php

namespace Controller;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * トップページ	コントローラ.
 */
class TopMenuController extends Controller {

	public function index(Request $request, Response $response, array $args) {
        // Sample log message
        $this->logger->info("Slim-Skeleton '/' route");

        // Render index view
        return $this->renderer->render($response, 'index.phtml', $args);
	}

}
