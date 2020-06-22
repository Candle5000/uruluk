<?php

namespace Controller;

use \Exception;
use Slim\Http\Request;
use Slim\Http\Response;
use Model\NewsModel;

/**
 * トップページ	コントローラ.
 */
class TopMenuController extends Controller {

	const PAGE_ID = 1;

	public function index(Request $request, Response $response) {
		try {
			$this->db->beginTransaction();

			$news = new NewsModel($this->db, $this->logger);

			$args = [
				'header' => $this->getHeaderInfo(),
				'newsList' => $news->getLatestNews(),
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
