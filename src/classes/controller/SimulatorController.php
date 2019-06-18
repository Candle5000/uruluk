<?php

namespace Controller;

use \Exception;
use Slim\Http\Request;
use Slim\Http\Response;
use Model\ItemModel;

/**
 * シミュレータ コントローラ.
 */
class SimulatorController extends Controller {

	public function index(Request $request, Response $response) {
		try {
			$this->db->beginTransaction();

			$args = [
				'header' => ['title' => 'シミュレータ', 'script' => ['/js/simulator.js']],
				'slots' => ['sword', 'shield', 'ring', 'ring', 'helm', 'armor', 'gloves', 'boots', 'common', 'puppet', 'puppet', 'puppet'],
				'footer' => $this->getFooterInfo()
			];

			$this->db->commit();
		} catch (Exception $e) {
			$this->db->rollBack();
			throw $e;
		}

		return $this->renderer->render($response, 'simulator/index.phtml', $args);
	}

	public function rareItem(Request $request, Response $response, array $args) {
		$item = new ItemModel($this->db, $this->logger);
		$itemClassId = $item->getItemClassId($args['itemClassName']);
		if ($itemClassId === null) throw new NotFoundException($request, $response);
		$data = [
			'items' => $item->getRareItemsByClass($itemClassId),
		];

		return $response->withJson($data);
	}

}
