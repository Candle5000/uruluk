<?php

namespace Controller;

use \Exception;
use Slim\Http\Request;
use Slim\Http\Response;
use Model\ItemModel;

/**
 * シミュレータ コントローラ.
 */
class SimulatorController extends Controller {

	private const XP_DEFAULT = 10000000;
	private const KILLS_DEFAULT = 1000000;
	private const SLOT_ITEM_CLASSES = ['sword' => ['sword', 'shield'], 'axe' => ['axe', 'mantle'], 'dagger' => ['dagger', 'dagger']];
	private const SLOT_ITEM_CLASSES_COMMON = ['ring', 'ring', 'helm', 'armor', 'gloves', 'boots', 'common', 'puppet', 'puppet', 'puppet'];

	public function index(Request $request, Response $response) {
		$item = new ItemModel($this->db, $this->logger);
		$getParam = $request->getQueryParams();
		$charClass = array_key_exists('c', $getParam) && array_key_exists($getParam['c'], self::SLOT_ITEM_CLASSES) ? $getParam['c'] : 'sword';
		$xp = array_key_exists('xp', $getParam) ? $getParam['xp'] : self::XP_DEFAULT;
		$xp = filter_var($xp, FILTER_VALIDATE_INT, ['options' => ['default' => self::XP_DEFAULT, 'min_range' => 0]]);
		$kills = array_key_exists('kills', $getParam) ? $getParam['kills'] : self::KILLS_DEFAULT;
		$kills = filter_var($kills, FILTER_VALIDATE_INT, ['options' => ['default' => self::KILLS_DEFAULT, 'min_range' => 0]]);
		$itemIds = array_key_exists('s', $getParam) && is_array($getParam['s']) ? $getParam['s'] : [];
		$items = [];
		$slotItemClasses = array_merge(self::SLOT_ITEM_CLASSES[$charClass], self::SLOT_ITEM_CLASSES_COMMON);
		foreach ($slotItemClasses as $index => $itemClass) {
			$itemId = array_key_exists($index, $getParam['s']) ? $getParam['s'][$index] : null;
			if ($itemId == null) {
				$items[] = $item->getNone($itemClass);
				continue;
			}
			$items[] = $item->getItemByIdAndClass($itemId, $itemClass);
		}
		try {
			$this->db->beginTransaction();

			$args = [
				'header' => ['title' => 'シミュレータ', 'script' => ['/js/simulator.js']],
				'character' => $charClass,
				'xp' => $xp,
				'kills' => $kills,
				'items' => $items,
				'slots' => array_merge(self::SLOT_ITEM_CLASSES[$charClass], self::SLOT_ITEM_CLASSES_COMMON),
				'footer' => $this->getFooterInfo()
			];

			$this->db->commit();
		} catch (Exception $e) {
			$this->db->rollBack();
			throw $e;
		}

		return $this->renderer->render($response, 'simulator/index.phtml', $args);
	}

	public function rareItem(Request $request, Response $response, array $args) {
		$item = new ItemModel($this->db, $this->logger);
		$itemClassId = $item->getItemClassId($args['itemClassName']);
		if ($itemClassId === null) throw new NotFoundException($request, $response);
		$data = [
			'items' => $item->getRareItemsByClass($itemClassId),
		];

		return $response->withJson($data);
	}

}
