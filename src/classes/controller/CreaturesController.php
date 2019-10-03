<?php

namespace Controller;

use \Exception;
use Slim\Exception\NotFoundException;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * アイテムデータ コントローラ.
 */
class CreaturesController extends Controller {

	public function index(Request $request, Response $response) {
		$this->title = 'クリーチャーデータ';

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

        return $this->renderer->render($response, 'creatures/index.phtml', $args);
	}

}
