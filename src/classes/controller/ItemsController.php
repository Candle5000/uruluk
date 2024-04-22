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
        $this->title = $this->i18n->s('page_title.items');

        try {
            $this->db->beginTransaction();

            $item = new ItemModel($this->db, $this->logger, $this->i18n);
            $args = [
                'header' => $this->getHeaderInfo(),
                'menu' => $item->getBaseItems(),
                'footer' => $this->getFooterInfo(),
                'l' => $this->i18n
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
        $this->title = ucfirst($args['itemClassName']) . ' ' . $this->i18n->s('page_title.items_rare');
        $this->scripts[] = '/js/item.js?id=00088';

        try {
            $this->db->beginTransaction();

            $item = new ItemModel($this->db, $this->logger, $this->i18n);
            $itemClassId = $item->getItemClassId($args['itemClassName']);
            if ($itemClassId === null) throw new NotFoundException($request, $response);
            $args = [
                'header' => $this->getHeaderInfo(),
                'menu' => $item->getBaseItems(),
                'itemClass' => $args['itemClassName'],
                'items' => [
                    'rare' => $item->getItemsByClassAndRarity($itemClassId, 'rare'),
                    'artifact' => $item->getItemsByClassAndRarity($itemClassId, 'artifact')
                ],
                'footer' => $this->getFooterInfo(),
                'l' => $this->i18n
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
        $this->title = ucfirst($args['itemClassName']) . ' ' . $this->i18n->s('page_title.items_common');
        $this->scripts[] = '/js/item.js?id=00088';

        try {
            $this->db->beginTransaction();

            $item = new ItemModel($this->db, $this->logger, $this->i18n);
            if (!is_numeric($args['baseItemId'])) throw new NotFoundException($request, $response);
            $itemClassId = $item->getItemClassId($args['itemClassName']);
            if ($itemClassId === null) throw new NotFoundException($request, $response);
            $baseItem = $item->getBaseItem($itemClassId, $args['baseItemId']);
            if ($baseItem === null) throw new NotFoundException($request, $response);
            $args = [
                'header' => $this->getHeaderInfo(),
                'menu' => $item->getBaseItems(),
                'itemClass' => $args['itemClassName'],
                'baseItem' => $baseItem,
                'items' => $item->getCommonItemsByClassAndBaseItem($itemClassId, $args['baseItemId']),
                'footer' => $this->getFooterInfo(),
                'l' => $this->i18n
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
        $floor = new FloorModel($this->db, $this->logger, $this->i18n);
        $creature = new CreatureModel($this->db, $this->logger, $this->i18n);
        $quest = new QuestModel($this->db, $this->logger, $this->i18n);
        $shop = new ShopModel($this->db, $this->logger, $this->i18n);
        $tag = new TagModel($this->db, $this->logger, $this->i18n);
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
