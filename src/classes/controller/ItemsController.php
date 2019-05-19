<?php

namespace Controller;

use Slim\Http\Request;
use Slim\Http\Response;
use Model\ItemModel;

/**
 * アイテムデータ コントローラ.
 */
class ItemsController extends Controller {

	public function index(Request $request, Response $response) {
        return $this->renderer->render($response, 'items/index.phtml');
	}

	public function rareItem(Request $request, Response $response, array $args) {
		$item = new ItemModel($this->db, $this->logger);
		$args['items'] = $item->getRareItemsByClass($args['itemClassId']);
        return $this->renderer->render($response, 'items/rare.phtml', $args);
	}

}
