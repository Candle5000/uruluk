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
		$this->title = 'アイテムデータ';

		try {
			$this->db->beginTransaction();

			$args = [
				'header' => $this->getHeaderInfo(),
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
		$this->title = ucfirst($args['itemClassName']) . ' レアアイテム';
		$this->scripts[] = '/js/item.js?id=00030';

		try {
			$this->db->beginTransaction();

			$item = new ItemModel($this->db, $this->logger);
			$itemClassId = $item->getItemClassId($args['itemClassName']);
			if ($itemClassId === null) throw new NotFoundException($request, $response);
			$args = [
				'header' => $this->getHeaderInfo(),
				'item_class' => ucfirst($args['itemClassName']),
				'items' => [
					'rare' => $item->getItemsByClassAndRarity($itemClassId, 'rare'),
					'artifact' => $item->getItemsByClassAndRarity($itemClassId, 'artifact')
				],
				'footer' => $this->getFooterInfo()
			];

			$this->db->commit();
		} catch (Exception $e) {
			$this->db->rollBack();
			throw $e;
		}

        return $this->renderer->render($response, 'items/rare.phtml', $args);
	}

	public function commonItem(Request $request, Response $response, array $args) {
		$this->title = ucfirst($args['itemClassName']) . ' ノーマルアイテム';
		$this->scripts[] = '/js/item.js?id=00030';

		try {
			$this->db->beginTransaction();

			$item = new ItemModel($this->db, $this->logger);
			if (!is_numeric($args['baseItemId'])) throw new NotFoundException($request, $response);
			$itemClassId = $item->getItemClassId($args['itemClassName']);
			if ($itemClassId === null) throw new NotFoundException($request, $response);
			$baseItem = $item->getBaseItem($itemClassId, $args['baseItemId']);
			if ($baseItem === null) throw new NotFoundException($request, $response);
			$args = [
				'header' => $this->getHeaderInfo(),
				'item_class' => ucfirst($args['itemClassName']),
				'base_item' => $baseItem,
				'items' => $item->getCommonItemsByClassAndBaseItem($itemClassId, $args['baseItemId']),
				'footer' => $this->getFooterInfo()
			];

			$this->db->commit();
		} catch (Exception $e) {
			$this->db->rollBack();
			throw $e;
		}

        return $this->renderer->render($response, 'items/common.phtml', $args);
	}

	public function detail(Request $request, Response $response, array $args) {
		$item = new ItemModel($this->db, $this->logger);
		$detail = $item->getItemDetailById($args['itemId']);
		$data = ['item' => $detail];
		return $response->withJson($data);
	}

}
