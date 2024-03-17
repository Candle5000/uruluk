<?php

namespace Controller;

use \Exception;
use Model\ItemModel;
use Model\TagModel;
use Slim\Exception\NotFoundException;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * タグ コントローラ.
 */
class TagController extends Controller
{

    public function index(Request $request, Response $response, array $args)
    {
        $this->title = 'タグ';

        try {
            $this->db->beginTransaction();

            $tag = new TagModel($this->db, $this->logger, $this->i18n);

            $args = [
                'header' => $this->getHeaderInfo(),
                'tags' => $tag->getTags(),
                'footer' => $this->getFooterInfo(),
                'l' => $this->i18n
            ];

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

        return $this->renderer->render($response, 'tags/index.phtml', $args);
    }

    public function detail(Request $request, Response $response, array $args)
    {
        try {
            $this->db->beginTransaction();

            $tag = new TagModel($this->db, $this->logger, $this->i18n);
            $item = new ItemModel($this->db, $this->logger, $this->i18n);
            $detail = $tag->getTagByTagurl($args['tagUrl']);

            if ($detail == null) throw new NotFoundException($request, $response);

            $this->title = 'タグ:' . $detail['tag_name'];

            $args = [
                'header' => $this->getHeaderInfo(),
                'detail' => $detail,
                'items' => $item->getItemsByTag($args['tagUrl']),
                'footer' => $this->getFooterInfo(),
                'l' => $this->i18n
            ];

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

        return $this->renderer->render($response, 'tags/tag.phtml', $args);
    }
}
