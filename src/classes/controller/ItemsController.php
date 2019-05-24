<?php

namespace Controller;

use \Exception;
use Slim\Exception\NotFoundException;
use Slim\Http\Request;
use Slim\Http\Response;
use Model\ItemModel;

/**
 * アイテムデータ コントローラ.
 */
class ItemsController extends Controller {

	public function index(Request $request, Response $response) {
		try {
			$this->db->beginTransaction();

			$args = [
				'footer' => $this->getFooterInfo()
			];

			$this->db->commit();
		} catch (Exception $e) {
			$this->db->rollBack();
			throw $e;
		}

        return $this->renderer->render($response, 'items/index.phtml', $args);
	}

	public function rareItem(Request $request, Response $response, array $args) {
		try {
			$this->db->beginTransaction();

			$item = new ItemModel($this->db, $this->logger);
			$itemClassId = $item->getItemClassId($args['itemClassName']);
			if ($itemClassId === null) throw new NotFoundException($request, $response);
			$args = [
				'item_class' => ucfirst($args['itemClassName']),
				'items' => $item->getRareItemsByClass($itemClassId),
				'footer' => $this->getFooterInfo()
			];

			$this->db->commit();
		} catch (Exception $e) {
			$this->db->rollBack();
			throw $e;
		}

        return $this->renderer->render($response, 'items/rare.phtml', $args);
	}

}
