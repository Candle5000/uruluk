<?php

namespace Controller;

use Slim\Http\Request;
use Slim\Http\Response;
use Model\AccessCountModel;

/**
 * トップページ	コントローラ.
 */
class TopMenuController extends Controller {

	const PAGE_ID = 1;

	public function index(Request $request, Response $response) {
        // Sample log message
        $this->logger->info("Slim-Skeleton '/' route");

		$accessCount = new AccessCountModel($this->db, $this->logger);
		$args = [
			'pv_today' => $accessCount->getTodayPvWithCountUp(TopMenuController::PAGE_ID),
			'pv_yesterday' => $accessCount->getYesterdayPv(TopMenuController::PAGE_ID),
			'hoge' => 'hoge'
		];

        // Render index view
        return $this->renderer->render($response, 'index.phtml', $args);
	}

}
