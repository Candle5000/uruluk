<?php

namespace Model;

use \PDO;

class ItemModel extends Model {

	private const DEFAULT_IMG = [
		'sword' => 'short_sword.png',
		'shield' => 'small_shield.png',
		'axe' => 'wood_axe.png',
		'mantle' => 'mantle.png',
		'dagger' => 'dagger.png',
		'ring' => 'ring0.png',
		'helm' => 'helm_sword.png',
		'armor' => 'leather_vest.png',
		'gloves' => 'gloves.png',
		'boots' => 'boots.png',
		'freshy' => 'toad.png',
		'puppet' => 'puppet0.png'
	];

	private const SQL_COLUMNS_FOR_ITEMS_WITH_ATTRS = "  I.item_id"
		. "  , I.item_class_id"
		. "  , I.image_name"
		. "  , I.name_en"
		. "  , I.name_ja"
		. "  , I.rarity"
		. "  , I.skill_en"
		. "  , I.skill_axe_en"
		. "  , I.skill_sword_en"
		. "  , I.skill_dagger_en"
		. "  , I.comment_en"
		. "  , I.comment_ja"
		. "  , I.sort_key"
		. "  , I.price"
		. "  , A.short_name"
		. "  , IA.color"
		. "  , IA.flactuable"
		. "  , IA.based_source"
		. "  , IA.attribute_value"
		. "  , IA.attribute_value_sword"
		. "  , IA.attribute_value_axe"
		. "  , IA.attribute_value_dagger"
		. "  , A.unit"
		. "  , IA.max_required"
		. "  , IA.max_required_sword"
		. "  , IA.max_required_axe"
		. "  , IA.max_required_dagger ";

	public function getItemClassId(string $itemClassName) {
		$sql = 'SELECT'
			. '  item_class_id '
			. 'FROM'
			. '  item_class '
			. 'WHERE'
			. '  name_en = :itemClassName';
		$this->logger->debug($sql);
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':itemClassName', $itemClassName, PDO::PARAM_STR);
		$stmt->execute();
		if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			return $result['item_class_id'];
		} else {
			return null;
		}
	}

	public function getBaseItems() {
		$sql = <<<SQL
			SELECT
			  IC.item_class_id
			  , IC.name_en class_name_en
			  , IC.name_ja class_name_ja
			  , IC.image_name class_image_name
			  , BI.base_item_id
			  , BI.name_en base_name_en
			  , BI.name_ja base_name_ja
			  , BI.image_name base_image_name
			FROM
			  base_item BI
			INNER JOIN item_class IC
			  ON BI.item_class_id = IC.item_class_id
			ORDER BY
			  IC.sort_key
			  , BI.sort_key
			SQL;
		$this->logger->debug($sql);
		$stmt = $this->db->prepare($sql);
		$stmt->execute();
		$itemClassList = [];
		$prevClassId = null;
		while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($result['item_class_id'] !== $prevClassId) {
				$itemClassList[] = [
					'id' => $result['item_class_id'],
					'name_en' => $result['class_name_en'],
					'name_ja' => $result['class_name_ja'],
					'image_name' => $result['class_image_name'],
					'base_items' => []
				];
				$prevClassId = $result['item_class_id'];
			}
			$itemClassList[count($itemClassList) - 1]['base_items'][] = [
				'id' => $result['base_item_id'],
				'name_en' => $result['base_name_en'],
				'name_ja' => $result['base_name_ja'],
				'image_name' => $result['base_image_name']
			];
		}
		return $itemClassList;
	}

	public function getBaseItem(int $itemClassId, int $baseItemId) {
		$sql = <<<SQL
			SELECT
			  name_en
			  , name_ja
			  , image_name
			  , sort_key
			FROM
			  base_item
			WHERE
			  base_item_id = :baseItemId
			  AND item_class_id = :itemClassId
			SQL;
		$this->logger->debug($sql);
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':baseItemId', $baseItemId, PDO::PARAM_INT);
		$stmt->bindParam(':itemClassId', $itemClassId, PDO::PARAM_INT);
		$stmt->execute();
		if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			return [
				'name_en' => $result['name_en'],
				'name_ja' => $result['name_ja'],
				'image_name' => $result['image_name'],
				'sort_key' => $result['sort_key']
			];
		} else {
			return null;
		}
	}

	public function getItemsByClass(int $itemClassId) {
		$sql = "SELECT"
			. self::SQL_COLUMNS_FOR_ITEMS_WITH_ATTRS
			. "FROM"
			. "  item I "
			. "  LEFT JOIN item_attribute IA "
			. "    ON I.item_id = IA.item_id "
			. "  LEFT JOIN attribute A "
			. "    ON IA.attribute_id = A.attribute_id "
			. "WHERE"
			. "  I.item_class_id = :itemClassId "
			. "ORDER BY"
			. "  I.sort_key"
			. "  , A.sort_key"
			. "  , IA.flactuable";
		$params = [['param' => 'itemClassId', 'var' => $itemClassId, 'type' => PDO::PARAM_INT]];
		return $this->getItemsObject($sql, $params);
	}

	public function getItemsByClassAndRarity(int $itemClassId, string $rarity) {
		return $this->getItemsByClassAndRarities($itemClassId, [$rarity]);
	}

	public function getItemsByClassAndRarities(int $itemClassId, array $rarities) {
		$sql_rarity = [];
		foreach ($rarities as $key => $rarity) {
			$sql_rarity[] = ":rarity$key";
		}
		$sql = "SELECT"
			. self::SQL_COLUMNS_FOR_ITEMS_WITH_ATTRS
			. "FROM"
			. "  item I "
			. "  LEFT JOIN item_attribute IA "
			. "    ON I.item_id = IA.item_id "
			. "  LEFT JOIN attribute A "
			. "    ON IA.attribute_id = A.attribute_id "
			. "WHERE"
			. "  I.item_class_id = :itemClassId ";
		if (count($sql_rarity) > 0) {
			$sql .= "  AND I.rarity IN(" . implode($sql_rarity, ", ") . ")";
		}
		$sql .= "ORDER BY"
			. "  I.sort_key"
			. "  , A.sort_key"
			. "  , IA.flactuable";
		$params[] = ['param' => 'itemClassId', 'var' => $itemClassId, 'type' => PDO::PARAM_INT];
		foreach ($rarities as $key => $rarity) {
			$params[] = ['param' => ":rarity$key",
				'var' => $rarities[$key], 'type' => PDO::PARAM_STR];
		}
		return $this->getItemsObject($sql, $params);
	}

	public function getCommonItemsByClassAndBaseItem(int $itemClassId, int $baseItemId) {
		$select = self::SQL_COLUMNS_FOR_ITEMS_WITH_ATTRS;
		$sql = <<<SQL
			SELECT
			  $select
			FROM
			  item I
			  LEFT JOIN item_attribute IA
				ON I.item_id = IA.item_id
			  LEFT JOIN attribute A
				ON IA.attribute_id = A.attribute_id
			WHERE
			  I.rarity = :rarity
			  AND I.base_item_id = :baseItemId
			ORDER BY
			  I.sort_key
			SQL;
		$params[] = ['param' => 'rarity', 'var' => 'common', 'type' => PDO::PARAM_STR];
		$params[] = ['param' => 'baseItemId', 'var' => $baseItemId, 'type' => PDO::PARAM_INT];
		return $this->getItemsObject($sql, $params);
	}

	public function getItemByIdAndClass(int $itemId, string $itemClass) {
		$sql = "SELECT"
			. self::SQL_COLUMNS_FOR_ITEMS_WITH_ATTRS
			. "FROM"
			. "  item I "
			. "  LEFT JOIN item_class IC "
			. "    ON I.item_class_id = IC.item_class_id "
			. "  LEFT JOIN item_attribute IA "
			. "    ON I.item_id = IA.item_id "
			. "  LEFT JOIN attribute A "
			. "    ON IA.attribute_id = A.attribute_id "
			. "WHERE"
			. "  I.item_id = :itemId "
			. "  AND IC.name_en = :itemClassName ";
		$params = [
			['param' => 'itemId', 'var' => $itemId, 'type' => PDO::PARAM_INT],
			['param' => 'itemClassName', 'var' => $itemClass, 'type' => PDO::PARAM_STR]
		];
		$itemObj = $this->getItemsObject($sql, $params);
		if (count($itemObj) == 0) {
			return $this->getNone($itemClass);
		} else {
			return $itemObj[0];
		}
	}

	public function getNone($itemClass) {
		return [
			'name_en' => 'None',
			'rarity' => 'common',
			'image_name' => self::DEFAULT_IMG[$itemClass],
			'attributes' => []
		];
	}

	public function getItemDetailById(int $id) {
		return [
			'floors' => $this->getFloorsByItemId($id),
			'creatures' => $this->getCreaturesByItemId($id)
		];
	}

	private function getItemsObject(string $sql, array $params) {
		$this->logger->debug($sql);
		$stmt = $this->db->prepare($sql);
		foreach ($params as $param) {
			$stmt->bindParam($param['param'], $param['var'], $param['type']);
		}
		$stmt->execute();
		$items = array();
		$item = array();
		$prevItemId = null;
		$charaClass = ['A' => 'axe', 'S' => 'sword', 'D' => 'dagger'];

		while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($result['item_id'] !== $prevItemId) {
				if (!empty($item)) $items[] = $item;
				$item['item_id'] = $result['item_id'];
				$item['item_class_id'] = $result['item_class_id'];
				$item['image_name'] = ($result['image_name'] === null)
					? "item_noimg.png" : $result['image_name'];
				$item['name_en'] = $result['name_en'];
				$item['name_ja'] = $result['name_ja'];
				$item['rarity'] = $result['rarity'];
				$item['skill_en'] = $result['skill_en'];
				$item['skill_axe_en'] = $result['skill_axe_en'];
				$item['skill_sword_en'] = $result['skill_sword_en'];
				$item['skill_dagger_en'] = $result['skill_dagger_en'];
				$item['comment_en'] = $result['comment_en'];
				$item['comment_ja'] = $result['comment_ja'];
				$item['sort_key'] = $result['sort_key'];
				$item['price'] = $result['price'];
				$item['attributes'] = array();
			}

			$prevItemId = $result['item_id'];
			$attribute = array();
			$attribute['color'] = $result['color'];
			$attribute['short_name'] = $result['short_name'];

			if ($result['short_name'] === null) continue;

			$attribute['based_source'] = $result['based_source'];
			$attribute['value'] = $result['attribute_value'];
			$attribute['value_axe'] = $result['attribute_value_axe'];
			$attribute['value_sword'] = $result['attribute_value_sword'];
			$attribute['value_dagger'] = $result['attribute_value_dagger'];
			$attribute['max_required'] = $result['max_required'];
			$attribute['max_required_axe'] = $result['max_required_axe'];
			$attribute['max_required_sword'] = $result['max_required_sword'];
			$attribute['max_required_dagger'] = $result['max_required_dagger'];

			if ($attribute['short_name'] == 'MinAD' || $attribute['short_name'] == 'MaxAD') {
				if ($attribute['short_name'] == 'MaxAD') {
					$minAD = $item['attributes'][count($item['attributes']) - 1];
					$ad = array();
					$ad['color'] = $minAD['color'];
					$ad['short_name'] = 'AD';
					$attr_val = '';
					if ($result['attribute_value'] === null) {
						foreach ($charaClass as $key => $name) {
							if ($key != 'A') $attr_val .= ' / ';
							$attr_val .= $key . ':'
								. $minAD['value_' . $name]
								. '～'
								. $result['attribute_value_' . $name];
						}
					} else {
						$attr_val .= $minAD['value']
							. '～'
							. $result['attribute_value'];
					}
					$ad['attribute_value'] = $attr_val;
					$item['attributes'][] = $ad;
				}
				$item['attributes'][] = $attribute;
				continue;
			}

			$attr_val = '';

			if ($result['flactuable']) {
				$attr_val .= 'Based on '
					. ($result['based_source'] === 'xp' ? 'XP' : 'Kills') . ' [';
			}

			if ($result['attribute_value'] === null) {
				foreach ($charaClass as $key => $name) {
					if ($key != 'A') $attr_val .= ' / ';
					$attr_val .= $key . ':'
						. ($result['attribute_value_' . $name] === '0' ? '?' : $result['attribute_value_' . $name])
						. ($result['unit'] === null ? '' : $result['unit']);
					if ($result['flactuable']) {
						$attr_val .= " ("
							. ($result['max_required_' . $name] === '0' ? '?' : $result['max_required_' . $name]) . ' '
							. ($result['based_source'] === 'xp' ? 'XP' : 'Kills') . ")";
					}
				}
			} else {
				$attr_val .= ($result['attribute_value'] === '0' ? '?' : $result['attribute_value'])
					. ($result['unit'] === null ? '' : $result['unit']);
				if ($result['flactuable']) {
					$attr_val .= " ("
						. ($result['max_required'] === '0' ? '?' : $result['max_required']) . ' '
						. ($result['based_source'] === 'xp' ? 'XP' : 'Kills') . ")";
				}
			}

			if ($result['flactuable']) {
				$attr_val .= ']';
			}

			$attribute['attribute_value'] = $attr_val;
			$item['attributes'][] = $attribute;
		}

		if (!empty($item)) $items[] = $item;
		return $items;
	}

	private function getFloorsByItemId(int $id) {
		$sql = <<<SQL
			SELECT
			  F.floor_id
			  , F.short_name
			FROM
			  floor_drop_item FI
			  INNER JOIN floor F
			    ON FI.floor_id = F.floor_id
			WHERE
			  FI.item_id = :id
			ORDER BY
			  F.sort_key
		SQL;
		$this->logger->debug($sql);
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$floors = [];
		while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$floors[] = [
				'floor_id' => $result['floor_id'],
				'short_name' => $result['short_name'],
			];
		}
		return $floors;
	}

	private function getCreaturesByItemId(int $id) {
		$sql = <<<SQL
			SELECT
			  C.creature_id
			  , C.boss
			  , C.name_en
			  , C.image_name
			FROM
			  creature_drop_item CI
			  INNER JOIN creature C
			    ON CI.creature_id = C.creature_id
			WHERE
			  CI.item_id = :id
			ORDER BY
			  C.sort_key
		SQL;
		$this->logger->debug($sql);
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$creatures = [];
		while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$creatures[] = [
				'creature_id' => $result['creature_id'],
				'boss' => $result['boss'],
				'name_en' => $result['name_en'],
				'image_name' => $result['image_name'],
			];
		}
		return $creatures;
	}

}
