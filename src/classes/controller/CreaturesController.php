<?php

namespace Controller;

use \Exception;
use Slim\Exception\NotFoundException;
use Slim\Http\Request;
use Slim\Http\Response;
use Model\CreatureModel;
use Model\FloorModel;
use Model\ItemModel;

/**
 * クリーチャーデータ コントローラ.
 */
class CreaturesController extends Controller
{

    public function index(Request $request, Response $response, array $args)
    {
        $this->title = $this->i18n->s('page_title.creatures');
        $this->scripts[] = '/js/creature.js?id=00090';

        try {
            $this->db->beginTransaction();

            $creature = new CreatureModel($this->db, $this->logger, $this->i18n);
            $floor = new FloorModel($this->db, $this->logger, $this->i18n);

            if (array_key_exists('creatureId', $args)) {
                $detail = $creature->getCreatureDetailById($args['creatureId']);
                if ($detail == null) throw new NotFoundException($request, $response);
            }

            $args = [
                'header' => $this->getHeaderInfo(),
                'creatures' => $creature->getCreatureStutsList(),
                'floorIndex' => $floor->getFloorIndex(),
                'footer' => $this->getFooterInfo(),
                'l' => $this->i18n
            ];

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

        return $this->renderer->render($response, 'creatures/index.phtml', $args);
    }

    public function detail(Request $request, Response $response, array $args)
    {
        $creature = new CreatureModel($this->db, $this->logger, $this->i18n);
        $item = new ItemModel($this->db, $this->logger, $this->i18n);
        $floor = new FloorModel($this->db, $this->logger, $this->i18n);
        $detail = $creature->getCreatureDetailById($args['creatureId']);
        if ($detail == null) throw new NotFoundException($request, $response);
        $data = [
            'creature' => $detail,
            'items' => $item->getItemsByCreatureId($args['creatureId']),
            'floors' => $floor->getFloorsByCreatureId($args['creatureId'])
        ];

        return $response->withJson($data);
    }
}
