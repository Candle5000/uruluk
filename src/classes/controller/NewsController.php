<?php

namespace Controller;

use \Exception;
use Slim\Http\Request;
use Slim\Http\Response;
use Model\NewsModel;

/**
 * 更新情報	コントローラ.
 */
class NewsController extends Controller {

	public function index(Request $request, Response $response) {
		$this->title = '更新情報';
		$getParam = $request->getQueryParams();
		$page = 0;
		if (isset($getParam['page']) && is_numeric($getParam['page'])) {
			$page = $getParam['page'];
		}
		try {
			$this->db->beginTransaction();

			$news = new NewsModel($this->db, $this->logger);

			$args = [
				'header' => $this->getHeaderInfo(),
				'news' => $news->getNews($page),
				'page' => $page,
				'footer' => $this->getFooterInfo()
			];

			$this->db->commit();
		} catch (Exception $e) {
			$this->db->rollBack();
			throw $e;
		}

        return $this->renderer->render($response, 'news/index.phtml', $args);
	}

}
