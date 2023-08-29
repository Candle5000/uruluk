<?php

namespace Controller;

use \Exception;
use Slim\Exception\NotFoundException;
use Slim\Http\Request;
use Slim\Http\Response;
use Model\CreatureModel;

/**
 * クリーチャーデータ コントローラ.
 */
class CreaturesController extends Controller
{

    public function index(Request $request, Response $response, array $args)
    {
        $this->title = 'クリーチャーデータ';
        $this->scripts[] = '/js/creature.js?id=00066';

        try {
            $this->db->beginTransaction();

            $creature = new CreatureModel($this->db, $this->logger);

            if (array_key_exists('creatureId', $args)) {
                $detail = $creature->getCreatureDetailById($args['creatureId']);
                if ($detail == null) throw new NotFoundException($request, $response);
            }

            $args = [
                'header' => $this->getHeaderInfo(),
                'creatures' => $creature->getCreatureStutsList(),
                'footer' => $this->getFooterInfo()
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
        $creature = new CreatureModel($this->db, $this->logger);
        $detail = $creature->getCreatureDetailById($args['creatureId']);
        if ($detail == null) throw new NotFoundException($request, $response);
        $data = [
            'creature' => $detail
        ];

        return $response->withJson($data);
    }
}
