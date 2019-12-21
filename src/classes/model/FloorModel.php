<?php

namespace Model;

use \PDO;

class FloorModel extends Model {

	public function getFloorIndex() {
		$floorIndex = [];
		$floors = $this->getFloorList();
		$floor = array_shift($floors);
		foreach ($this->getFloorGroupList() as $floorGroup) {
			$floorGroup['floors'] = [];
			while ($floor !== null && $floor['floor_group_id'] === $floorGroup['floor_group_id']) {
				$floorGroup['floors'][] = $floor;
				$floor = array_shift($floors);
			}
			$floorIndex[] = $floorGroup;
		}
		return $floorIndex;
	}

	public function getFloorGroupList() {
		$sql = <<<SQL
			SELECT
			  floor_group_id
			  , name_en
			  , name_ja
			FROM
			  floor_group
			ORDER BY
			  sort_key
			SQL;
		$this->logger->debug($sql);
		$stmt = $this->db->prepare($sql);
		$stmt->execute();
		$floorGroups = [];
		while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$floorGroups[] = [
				'floor_group_id' => $result['floor_group_id'],
				'name_en' => $result['name_en'],
				'name_ja' => $result['name_ja']
			];
		}
		return $floorGroups;
	}

	public function getFloorList() {
		$sql = <<<SQL
			SELECT
			  floor_id
			  , floor_group_id
			  , short_name
			  , name_en
			  , name_ja
			FROM
			  `floor`
			ORDER BY
			  floor_group_id
			  , sort_key
			SQL;
		$this->logger->debug($sql);
		$stmt = $this->db->prepare($sql);
		$stmt->execute();
		$floors = [];
		while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$floors[] = [
				'floor_id' => $result['floor_id'],
				'floor_group_id' => $result['floor_group_id'],
				'short_name' => $result['short_name'],
				'name_en' => $result['name_en'],
				'name_ja' => $result['name_ja']
			];
		}
		return $floors;
	}

}
