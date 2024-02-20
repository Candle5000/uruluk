<?php

namespace Controller;

use \Exception;
use Model\CreatureModel;
use Model\FloorModel;
use Slim\Exception\NotFoundException;
use Slim\Http\Request;
use Slim\Http\Response;
use Model\ItemModel;
use Model\QuestModel;
use Model\ShopModel;
use Model\TagModel;

/**
 * アイテムデータ コントローラ.
 */
class ItemsController extends Controller
{

    public function index(Request $request, Response $response)
    {
        $this->title = 'アイテムデータ';

        try {
            $this->db->beginTransaction();

            $item = new ItemModel($this->db, $this->logger);
            $args = [
                'header' => $this->getHeaderInfo(),
                'menu' => $item->getBaseItems(),
                'footer' => $this->getFooterInfo()
            ];

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

        return $this->renderer->render($response, 'items/index.phtml', $args);
    }

    public function rareItem(Request $request, Response $response, array $args)
    {
        $this->title = ucfirst($args['itemClassName']) . ' レアアイテム';
        $this->scripts[] = '/js/item.js?id=00075';

        try {
            $this->db->beginTransaction();

            $item = new ItemModel($this->db, $this->logger);
            $itemClassId = $item->getItemClassId($args['itemClassName']);
            if ($itemClassId === null) throw new NotFoundException($request, $response);
            $args = [
                'header' => $this->getHeaderInfo(),
                'menu' => $item->getBaseItems(),
                'item_class' => ucfirst($args['itemClassName']),
                'items' => [
                    'rare' => $item->getItemsByClassAndRarity($itemClassId, 'rare'),
                    'artifact' => $item->getItemsByClassAndRarity($itemClassId, 'artifact')
                ],
                'footer' => $this->getFooterInfo()
            ];

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

        return $this->renderer->render($response, 'items/rare.phtml', $args);
    }

    public function commonItem(Request $request, Response $response, array $args)
    {
        $this->title = ucfirst($args['itemClassName']) . ' ノーマルアイテム';
        $this->scripts[] = '/js/item.js?id=00075';

        try {
            $this->db->beginTransaction();

            $item = new ItemModel($this->db, $this->logger);
            if (!is_numeric($args['baseItemId'])) throw new NotFoundException($request, $response);
            $itemClassId = $item->getItemClassId($args['itemClassName']);
            if ($itemClassId === null) throw new NotFoundException($request, $response);
            $baseItem = $item->getBaseItem($itemClassId, $args['baseItemId']);
            if ($baseItem === null) throw new NotFoundException($request, $response);
            $args = [
                'header' => $this->getHeaderInfo(),
                'menu' => $item->getBaseItems(),
                'item_class' => ucfirst($args['itemClassName']),
                'base_item' => $baseItem,
                'items' => $item->getCommonItemsByClassAndBaseItem($itemClassId, $args['baseItemId']),
                'footer' => $this->getFooterInfo()
            ];

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

        return $this->renderer->render($response, 'items/common.phtml', $args);
    }

    public function detail(Request $request, Response $response, array $args)
    {
        $floor = new FloorModel($this->db, $this->logger);
        $creature = new CreatureModel($this->db, $this->logger);
        $quest = new QuestModel($this->db, $this->logger);
        $shop = new ShopModel($this->db, $this->logger);
        $tag = new TagModel($this->db, $this->logger);
        $data = [
            'floors' => $floor->getFloorsByItemId($args['itemId']),
            'banana' => $floor->getBananaFloorsByItemId($args['itemId']),
            'treasure' => $floor->getTreasureFloorsByItemId($args['itemId']),
            'creatures' => $creature->getCreaturesByItemId($args['itemId']),
            'quests' => $quest->getQuestDetailListByItemId($args['itemId']),
            'shops' => $shop->getShopListByItemId($args['itemId']),
            'tags' => $tag->getTagsByItemId($args['itemId'])
        ];
        return $response->withJson($data);
    }
}
