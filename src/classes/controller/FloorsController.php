<?php

namespace Controller;

use \Exception;
use Model\CreatureModel;
use Slim\Exception\NotFoundException;
use Slim\Http\Request;
use Slim\Http\Response;
use Model\FloorModel;
use Model\ItemModel;
use Model\QuestModel;
use Model\ShopModel;

/**
 * フロアデータ コントローラ.
 */
class FloorsController extends Controller
{

    public function index(Request $request, Response $response, array $args)
    {
        $this->title = 'フロアデータ';

        try {
            $this->db->beginTransaction();

            $floor = new FloorModel($this->db, $this->logger, $this->i18n);

            $args = [
                'header' => $this->getHeaderInfo(),
                'floorIndex' => $floor->getFloorIndex(),
                'footer' => $this->getFooterInfo(),
                'l' => $this->i18n
            ];

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

        return $this->renderer->render($response, 'floors/index.phtml', $args);
    }

    public function detail(Request $request, Response $response, array $args)
    {
        $this->title = 'フロアデータ';
        $this->scripts[] = '/js/floor.js?id=00081';
        $this->scripts[] = '/lib/photoswipe/photoswipe.min.js?id=00041';
        $this->scripts[] = '/lib/photoswipe/photoswipe-ui-default.min.js?id=00041';

        try {
            $this->db->beginTransaction();

            $floor = new FloorModel($this->db, $this->logger, $this->i18n);
            $quest = new QuestModel($this->db, $this->logger, $this->i18n);
            $shop = new ShopModel($this->db, $this->logger, $this->i18n);
            $item = new ItemModel($this->db, $this->logger, $this->i18n);
            $creature = new CreatureModel($this->db, $this->logger, $this->i18n);
            $detail = $floor->getFloorDetail($args['floorId']);

            if ($detail == null) throw new NotFoundException($request, $response);

            $quests = [];
            foreach ($quest->getQuestDetailListByFloorId($args['floorId']) as $q) {
                $q['required_items'] = $item->getQuestRequiredItemsByQuestId($q['quest_id']);
                $q['reward_items'] = $item->getQuestRewardItemsByQuestId($q['quest_id']);
                $quests[] = $q;
            }

            $shops = [];
            foreach ($shop->getShopListByFloorId($args['floorId']) as $s) {
                $s['items'] = $item->getShopItemsByShopId($s['shop_id']);
                $shops[] = $s;
            }

            $args = [
                'header' => $this->getHeaderInfo(),
                'floorIndex' => $floor->getFloorIndex(),
                'detail' => $detail,
                'items' => $item->getRareItemsByFloorId($args['floorId']),
                'banana' => $item->getBananaItemsByFloorId($args['floorId']),
                'treasure' => $item->getTreasureItemsByFloorId($args['floorId']),
                'creatures' => $creature->getCreaturesByFloorId($args['floorId']),
                'quests' => $quests,
                'shops' => $shops,
                'footer' => $this->getFooterInfo(),
                'l' => $this->i18n
            ];

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

        return $this->renderer->render($response, 'floors/floor.phtml', $args);
    }
}
