<?php

namespace Controller;

use \Exception;
use Slim\Exception\NotFoundException;
use Slim\Http\Request;
use Slim\Http\Response;
use Model\FloorModel;

/**
 * フロアデータ コントローラ.
 */
class FloorsController extends Controller {

	public function index(Request $request, Response $response, array $args) {
		$this->title = 'フロアデータ';

		try {
			$this->db->beginTransaction();

			$floor = new FloorModel($this->db, $this->logger);

			$args = [
				'header' => $this->getHeaderInfo(),
				'floorIndex' => $floor->getFloorIndex(),
				'footer' => $this->getFooterInfo()
			];

			$this->db->commit();
		} catch (Exception $e) {
			$this->db->rollBack();
			throw $e;
		}
		return $this->renderer->render($response, 'floors/index.phtml', $args);
	}

}
