<?php

namespace Controller;

use \Exception;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * シミュレータ コントローラ.
 */
class SimulatorController extends Controller {

	public function index(Request $request, Response $response) {
		try {
			$this->db->beginTransaction();

			$args = [
				'header' => ['title' => 'シミュレータ'],
				'footer' => $this->getFooterInfo()
			];

			$this->db->commit();
		} catch (Exception $e) {
			$this->db->rollBack();
			throw $e;
		}

        return $this->renderer->render($response, 'simulator/index.phtml', $args);
	}

}
