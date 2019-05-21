<?php

namespace Controller;

use \Exception;
use Slim\Http\Request;
use Slim\Http\Response;
use Model\AccessCountModel;

/**
 * トップページ	コントローラ.
 */
class TopMenuController extends Controller {

	const PAGE_ID = 1;

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
		}

        return $this->renderer->render($response, 'index.phtml', $args);
	}

}
