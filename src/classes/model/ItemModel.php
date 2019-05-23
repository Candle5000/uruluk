<?php

namespace Model;

use \PDO;

class ItemModel extends Model {

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
		$sql = 'SELECT'
			. '  I.item_id'
			. '  , I.item_class_id'
			. '  , I.image_name'
			. '  , I.name_en'
			. '  , I.name_ja'
			. '  , I.rarity'
			. '  , A.short_name'
			. '  , IA.color'
			. '  , IA.flactuable'
			. '  , IA.based_source'
			. '  , IA.attribute_value'
			. '  , IA.attribute_value_sword'
			. '  , IA.attribute_value_axe'
			. '  , IA.attribute_value_dagger'
			. '  , A.unit'
			. '  , IA.max_required'
			. '  , IA.max_required_sword'
			. '  , IA.max_required_axe'
			. '  , IA.max_required_dagger '
			. 'FROM'
			. '  item I '
			. '  INNER JOIN item_attribute IA '
			. '    ON I.item_id = IA.item_id '
			. '  INNER JOIN attribute A '
			. '    ON IA.attribute_id = A.attribute_id '
			. 'WHERE'
			. '  I.item_class_id = :itemClassId '
			. 'ORDER BY'
			. '  I.sort_key'
			. '  , A.sort_key'
			. '  , IA.flactuable';
		$this->logger->debug($sql);
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':itemClassId', $itemClassId);
		$stmt->execute();
		$items = array();
		$item = array();
		$prevItemId = null;

		while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			if ($result['item_id'] !== $prevItemId) {
				if (!empty($item)) $items[] = $item;
				$item['item_id'] = $result['item_id'];
				$item['item_class_id'] = $result['item_class_id'];
				$item['image_name'] = $result['image_name'];
				$item['name_en'] = $result['name_en'];
				$item['name_ja'] = $result['name_ja'];
				$item['rarity'] = $result['rarity'];
				$item['attributes'] = array();
			}

			$prevItemId = $result['item_id'];
			$attribute = array();
			$attribute['color'] = $result['color'];
			$attribute['short_name'] = $result['short_name'];
			$attr_val = '';

			if ($result['flactuable']) {
				$attr_val .= 'Based on '
					. ($result['based_source'] === 'xp' ? 'XP' : 'Kills') . ' [';
			}

			if ($result['attribute_value'] === 0) {
				$attr_val .= '?'
					. ($result['unit'] === null ? '' : $result['unit']);
			} else if ($result['attribute_value'] === null) {
				$attr_val .= 'A:'
					. ($result['attribute_value_axe'] === 0 ? '?' : $result['attribute_value_axe'])
					. ($result['unit'] === null ? '' : $result['unit']);
				if ($result['flactuable']) {
					$attr_val .= " ("
						. ($result['max_required_axe'] === 0 ? '?' : $result['max_required_axe']) . ' '
						. ($result['based_source'] === 'xp' ? 'XP' : 'Kills') . ")";
				}
				$attr_val .= ' / S:'
					. ($result['attribute_value_sword'] === 0 ? '?' : $result['attribute_value_sword'])
					. ($result['unit'] === null ? '' : $result['unit']);
				if ($result['flactuable']) {
					$attr_val .= " ("
						. ($result['max_required_sword'] === 0 ? '?' : $result['max_required_sword']) . ' '
						. ($result['based_source'] === 'xp' ? 'XP' : 'Kills') . ")";
				}
				$attr_val .= ' / D:'
					. ($result['attribute_value_dagger'] === 0 ? '?' : $result['attribute_value_dagger'])
					. ($result['unit'] === null ? '' : $result['unit']);
				if ($result['flactuable']) {
					$attr_val .= " ("
						. ($result['max_required_dagger'] === 0 ? '?' : $result['max_required_dagger']) . ' '
						. ($result['based_source'] === 'xp' ? 'XP' : 'Kills') . ")";
				}
			} else {
				$attr_val .= ($result['attribute_value'] === 0 ? '?' : $result['attribute_value'])
					. ($result['unit'] === null ? '' : $result['unit']);
				if ($result['flactuable']) {
					$attr_val .= " ("
						. ($result['max_required'] === 0 ? '?' : $result['max_required']) . ' '
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

}
