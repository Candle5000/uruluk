<?php

namespace Model;

use \PDO;

class CreatureModel extends Model {

	public function getCreatureNameList() {
		$sql = <<<SQL
			SELECT
			  creature_id
			  , boss
			  , name_en
			  , name_ja
			  , image_name
			FROM
			  creature
			ORDER BY
			  boss
			  , sort_key
			SQL;
		$this->logger->debug($sql);
		$stmt = $this->db->prepare($sql);
		$stmt->execute();
		$creatures = [];
		while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$creatures[] = [
				'creature_id' => $result['creature_id'],
				'boss' => $result['boss'] == 1,
				'name_en' => $result['name_en'],
				'name_ja' => $result['name_ja'],
				'image_name' => $result['image_name']
			];
		}
		return $creatures;
	}

	public function getCreatureDetailById($id) {
		$sql = <<<SQL
			SELECT
			  creature_id
			  , boss
			  , name_en
			  , name_ja
			  , min_ad
			  , max_ad
			  , `as`
			  , def
			  , dex
			  , vit
			  , voh
			  , dr
			  , xp
			  , note
			  , image_name
			FROM
			  creature
			WHERE
			  creature_id = :id
			SQL;
		$this->logger->debug($sql);
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			return [
				'creature_id' => $result['creature_id'],
				'boss' => $result['boss'],
				'name_en' => $result['name_en'],
				'name_ja' => $result['name_ja'],
				'image_name' => $result['image_name'],
				'min_ad' => $result['min_ad'],
				'max_ad' => $result['max_ad'],
				'as' => $result['as'],
				'def' => $result['def'],
				'dex' => $result['dex'],
				'vit' => $result['vit'],
				'voh' => $result['voh'],
				'dr' => $result['dr'],
				'xp' => $result['xp'],
				'special_attacks' => $this->getSpecialAttacksByCreatureId($id),
				'items' => $this->getItemsByCreatureId($id),
				'floors' => $this->getFloorsByCreatureId($id),
				'note' => $result['note'],
			];
		} else {
			return null;
		}
	}

	private function getSpecialAttacksByCreatureId($id) {
		$sql = <<<SQL
			SELECT
			  SA.special_attack_id
			  , SA.name
			  , SA.cooldown
			  , SA.note
			FROM
			  creature_special_attack CSA 
			  INNER JOIN special_attack SA 
			    ON CSA.special_attack_id = SA.special_attack_id 
			WHERE
			  CSA.creature_id = :id 
			ORDER BY
			  SA.special_attack_id
			SQL;
		$this->logger->debug($sql);
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$sa = [];
		while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$sa[] = [
				'special_attack_id' => $result['special_attack_id'],
				'name' => $result['name'],
				'cooldown' => $result['cooldown'],
				'note' => $result['note'],
			];
		}
		return $sa;
	}

	private function getItemsByCreatureId($id) {
		$sql = <<<SQL
			SELECT
			  I.item_id
			  , IC.name_en item_class
			  , I.base_item_id
			  , I.name_en
			  , I.rarity
			  , I.image_name 
			FROM
			  creature_drop_item CDI 
			  INNER JOIN item I 
				ON CDI.item_id = I.item_id 
			  INNER JOIN item_class IC 
				ON I.item_class_id = IC.item_class_id 
			WHERE
			  CDI.creature_id = :id 
			ORDER BY
			  I.sort_key
			SQL;
		$this->logger->debug($sql);
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':id', $id, PDO::PARAM_INT);
		$stmt->execute();
		$items = [];
		while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$items[] = [
				'item_id' => $result['item_id'],
				'item_class' => $result['item_class'],
				'base_item_id' => $result['base_item_id'],
				'name_en' => $result['name_en'],
				'rarity' => $result['rarity'],
				'image_name' => $result['image_name'],
			];
		}
		return $items;
	}

	private function getFloorsByCreatureId($id) {
		$sql = <<<SQL
			SELECT
			  F.floor_id
			  , F.short_name
			  , F.name_en
			  , E.note 
			FROM
			  floor_creature FC 
			  INNER JOIN floor F 
				ON FC.floor_id = F.floor_id 
			  LEFT JOIN creature_pop_event E 
				ON FC.event_id = E.event_id 
			WHERE
			  FC.creature_id = :id 
			ORDER BY
			  F.sort_key
			  , E.event_id
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
				'name_en' => $result['name_en'],
				'note' => $result['note'],
			];
		}
		return $floors;
	}

}
