<?php

namespace Controller;

use \Exception;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * トップページ	コントローラ.
 */
class TopMenuController extends Controller {

	const PAGE_ID = 1;

	public function index(Request $request, Response $response) {
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

        return $this->renderer->render($response, 'index.phtml', $args);
	}

}
