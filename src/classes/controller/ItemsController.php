<?php

namespace Controller;

use \Exception;
use Slim\Exception\NotFoundException;
use Slim\Http\Request;
use Slim\Http\Response;
use Model\AccessCountModel;
use Model\ItemModel;

/**
 * アイテムデータ コントローラ.
 */
class ItemsController extends Controller {

	public function index(Request $request, Response $response) {
		try {
			$this->db->beginTransaction();

			$accessCount = new AccessCountModel($this->db, $this->logger);
			$args = [
				'pv_today' => $accessCount->getTodayPvWithCountUp(TopMenuController::PAGE_ID),
				'pv_yesterday' => $accessCount->getYesterdayPv(TopMenuController::PAGE_ID)
			];

			$this->db->commit();
		} catch (Exception $e) {
			$this->db->rollBack();
			throw $e;
		}

        return $this->renderer->render($response, 'items/index.phtml');
	}

	public function rareItem(Request $request, Response $response, array $args) {
		try {
			$this->db->beginTransaction();

			$accessCount = new AccessCountModel($this->db, $this->logger);
			$item = new ItemModel($this->db, $this->logger);
			$itemClassId = $item->getItemClassId($args['itemClassName']);
			if ($itemClassId === null) throw new NotFoundException($request, $response);
			$args = [
				'item_class' => ucfirst($args['itemClassName']),
				'pv_today' => $accessCount->getTodayPvWithCountUp(TopMenuController::PAGE_ID),
				'pv_yesterday' => $accessCount->getYesterdayPv(TopMenuController::PAGE_ID),
				'items' => $item->getRareItemsByClass($itemClassId)
			];

			$this->db->commit();
		} catch (Exception $e) {
			$this->db->rollBack();
			throw $e;
		}

        return $this->renderer->render($response, 'items/rare.phtml', $args);
	}

}
