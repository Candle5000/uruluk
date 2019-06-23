<?php

namespace Model;

use \PDO;

class ItemModel extends Model {

	private const SQL_COLUMNS_FOR_ITEMS_WITH_ATTRS = "  I.item_id"
		. "  , I.item_class_id"
		. "  , I.image_name"
		. "  , I.name_en"
		. "  , I.name_ja"
		. "  , I.rarity"
		. "  , I.skill_en"
		. "  , I.skill_ja"
		. "  , I.comment_en"
		. "  , I.comment_ja"
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
		$stmt->bindParam(':itemClassName', $itemClassName);
		$stmt->execute();
		if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			return  $result['item_class_id'];
		} else {
			return null;
		}
	}

	public function getRareItemsByClass(int $itemClassId) {
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
			. "  AND I.rarity IN ('rare', 'artifact') "
			. "ORDER BY"
			. "  I.rarity"
			. "  , I.sort_key"
			. "  , A.sort_key"
			. "  , IA.flactuable";
		$params = ["itemClassId" => $itemClassId];
		return getItemsObject($sql, $params);
	}

	public function getItemById(int $itemId) {
		$sql = "SELECT"
			. self::SQL_COLUMNS_FOR_ITEMS_WITH_ATTRS
			. "FROM"
			. "  item I "
			. "  LEFT JOIN item_attribute IA "
			. "    ON I.item_id = IA.item_id "
			. "  LEFT JOIN attribute A "
			. "    ON IA.attribute_id = A.attribute_id "
			. "WHERE"
			. "  I.item_id = :itemId ";
		$params = ["itemId" => $itemId];
		return getItemsObject($sql, $params);
	}

	private function getItemsObject(string $sql, array $params) {
		$this->logger->debug($sql);
		$stmt = $this->db->prepare($sql);
		foreach ($params as $key => $param) {
			$stmt->bindParam($key, $param);
		}
		$stmt->execute();
		$rareItems = array();
		$artifactItems = array();
		$item = array();
		$prevItemId = null;
		$charaClass = ['A' => 'axe', 'S' => 'sword', 'D' => 'dagger'];

		while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($result['item_id'] !== $prevItemId) {
				if (!empty($item) && $item['rarity'] == 'rare') $rareItems[] = $item;
				if (!empty($item) && $item['rarity'] == 'artifact') $artifactItems[] = $item;
				$item['item_id'] = $result['item_id'];
				$item['item_class_id'] = $result['item_class_id'];
				$item['image_name'] = ($result['image_name'] === null)
					? "item_noimg.png" : $result['image_name'];
				$item['name_en'] = $result['name_en'];
				$item['name_ja'] = $result['name_ja'];
				$item['rarity'] = $result['rarity'];
				$item['skill_en'] = $result['skill_en'];
				$item['skill_ja'] = $result['skill_ja'];
				$item['comment_en'] = $result['comment_en'];
				$item['comment_ja'] = $result['comment_ja'];
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

		if (!empty($item) && $item['rarity'] == 'rare') $rareItems[] = $item;
		if (!empty($item) && $item['rarity'] == 'artifact') $artifactItems[] = $item;
		$items = ['rare' => $rareItems, 'artifact' => $artifactItems];
		return $items;
	}

}
