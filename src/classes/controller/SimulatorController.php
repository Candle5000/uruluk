<?php

namespace Controller;

use \Exception;
use Slim\Exception\NotFoundException;
use Slim\Http\Request;
use Slim\Http\Response;
use Model\ItemModel;

/**
 * シミュレータ コントローラ.
 */
class SimulatorController extends Controller {

	private const XP_DEFAULT = 10000000;
	private const KILLS_DEFAULT = 1000000;
	private const BOOSTUPS_NAME = ['nostrum', 'elixir', 'giogan', 'necter', 'hydrabrew'];
	private const SLOT_ITEM_CLASSES = ['sword' => ['sword', 'shield'], 'axe' => ['axe', 'mantle'], 'dagger' => ['dagger', 'dagger']];
	private const SLOT_ITEM_CLASSES_COMMON = ['ring', 'ring', 'helm', 'armor', 'gloves', 'boots', 'freshy', 'puppet', 'puppet', 'puppet'];

	public function index(Request $request, Response $response) {
		$this->title = 'シミュレータ';
		$this->scripts[] = '/js/simulator.js';
		$item = new ItemModel($this->db, $this->logger);
		$getParam = $request->getQueryParams();
		$charClass = array_key_exists('c', $getParam) && array_key_exists($getParam['c'], self::SLOT_ITEM_CLASSES) ? $getParam['c'] : 'sword';
		$xp = array_key_exists('xp', $getParam) ? $getParam['xp'] : self::XP_DEFAULT;
		$xp = filter_var($xp, FILTER_VALIDATE_INT, ['options' => ['default' => self::XP_DEFAULT, 'min_range' => 0]]);
		$kills = array_key_exists('kills', $getParam) ? $getParam['kills'] : self::KILLS_DEFAULT;
		$kills = filter_var($kills, FILTER_VALIDATE_INT, ['options' => ['default' => self::KILLS_DEFAULT, 'min_range' => 0]]);
		$boostups = array_key_exists('b', $getParam) && is_array($getParam['b']) && count($getParam['b']) == 5 ? $getParam['b'] : [2, 4, 1, 1, 1];
		foreach ($boostups as $index => $boostup) {
			$boostups[self::BOOSTUPS_NAME[$index]] = filter_var($boostup, FILTER_VALIDATE_INT, ['options' => ['default' => 0, 'min_range' => 0]]);
		}
		$itemIds = array_key_exists('s', $getParam) && is_array($getParam['s']) ? $getParam['s'] : [];
		$items = [];
		$slotItemClasses = array_merge(self::SLOT_ITEM_CLASSES[$charClass], self::SLOT_ITEM_CLASSES_COMMON);
		foreach ($slotItemClasses as $index => $itemClass) {
			$itemId = array_key_exists($index, $itemIds) ? $itemIds[$index] : null;
			if ($itemId == null) {
				$items[] = $item->getNone($itemClass);
				continue;
			}
			$items[] = $item->getItemByIdAndClass($itemId, $itemClass);
		}
		try {
			$this->db->beginTransaction();

			$args = [
				'header' => $this->getHeaderInfo(),
				'csrf_name' => $request->getAttribute('csrf_name'),
				'csrf_value' => $request->getAttribute('csrf_value'),
				'character' => $charClass,
				'xp' => $xp,
				'kills' => $kills,
				'boostups' => $boostups,
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

	public function item(Request $request, Response $response, array $args) {
		$item = new ItemModel($this->db, $this->logger);
		$getParam = $request->getQueryParams();
		$itemClassId = $item->getItemClassId($args['itemClassName']);
		if ($itemClassId === null) throw new NotFoundException($request, $response);
		$rarities = [];
		if (array_key_exists('rarity', $getParam) && is_array($getParam['rarity'])) {
			foreach ($getParam['rarity'] as $val) {
				if (is_string($val)) $rarities[] = $val;
			}
		}
		$data = [
			'items' => $item->getItemsByClassAndRarities($itemClassId, $rarities),
		];

		return $response->withJson($data);
	}

}
