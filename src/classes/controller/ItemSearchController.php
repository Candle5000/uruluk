<?php

namespace Controller;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * アイテム検索	コントローラ.
 */
class ItemSearchController extends Controller {

	public function index(Request $request, Response $response, array $args) {
        return $this->renderer->render($response, 'index.phtml', $args);
	}

}
