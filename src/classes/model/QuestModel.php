<?php

namespace Model;

use \PDO;

class QuestModel extends Model {

	private const REQUIRED = 0;
	private const REWARD = 1;

	public function getQuestDetailListByItemId(int $itemId) {
		return [
			'reward' => $this->getQuestListByItemId($itemId, self::REWARD),
			'required' => $this->getQuestListByItemId($itemId, self::REQUIRED)
		];
	}

	public function getQuestDetailListByFloorId(int $floorId) {
		$sql = <<<SQL
			SELECT
				quest_id
				, floor_id
				, repeatable
				, autosave
				, required_items_note
				, reward_items_note
				, note
			FROM
				quest
			WHERE
				floor_id = :floor_id
			ORDER BY
				quest_id
			SQL;
		$this->logger->debug($sql);
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':floor_id', $floorId, PDO::PARAM_INT);
		$stmt->execute();
		$quests = [];
		while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$quests[] = [
				'quest_id' => $result['quest_id'],
				'floor_id' => $floorId,
				'repeatable' => $result['repeatable'],
				'autosave' => $result['autosave'],
				'required_items_note' => $result['required_items_note'],
				'reward_items_note' => $result['reward_items_note'],
				'reward_common_items' => $result['reward_common_items'],
				'note' => $result['note'],
				'icons' => $this->getQuestIcons($result['quest_id']),
				'required_items' => $this->getQuestRequiredItems($result['quest_id']),
				'reward_items' => $this->getQuestRewardItems($result['quest_id']),
			];
		}
		return $quests;
	}

	private function getQuestListByItemId(int $itemId, int $reward) {
		$table = $reward == self::REWARD ? 'quest_reward_item' : 'quest_required_item';
		$sql = <<<SQL
			SELECT
				Q.quest_id
				, Q.floor_id
				, F.short_name
				, Q.repeatable
				, Q.autosave
				, Q.required_items_note
				, Q.reward_items_note
				, Q.reward_common_items
				, Q.note
			FROM
				quest Q
				INNER JOIN $table QRI
					ON Q.quest_id = QRI.quest_id
				INNER JOIN floor F
					ON Q.floor_id = F.floor_id
			WHERE
				QRI.item_id = :item_id
			ORDER BY
				Q.quest_id
			SQL;
		$this->logger->debug($sql);
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':item_id', $floorId, PDO::PARAM_INT);
		$stmt->execute();
		$quests = [];
		while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$quests[] = [
				'quest_id' => $result['quest_id'],
				'floor_id' => $result['floor_id'],
				'short_name' => $result['short_name'],
				'repeatable' => $result['repeatable'],
				'autosave' => $result['autosave'],
				'required_items_note' => $result['required_items_note'],
				'reward_items_note' => $result['reward_items_note'],
				'reward_common_items' => $result['reward_common_items'],
				'note' => $result['note'],
				'icons' => $this->getQuestIcons($result['quest_id'])
			];
		}
		return $quests;
	}

	private function getQuestIcons(int $questId) {
		$sql = <<<SQL
			SELECT
				quest_reward
				, quest_icon_id
				, image_path
			FROM
				quest_icon
			WHERE
				quest_id = :quest_id
			ORDER BY
				quest_reward
				, quest_icon_id
			SQL;
		$this->logger->debug($sql);
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':quest_id', $questId, PDO::PARAM_INT);
		$stmt->execute();
		$icons = [];
		while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$icons[] = [
				'quest_reward' => $result['quest_reward'],
				'quest_icon_id' => $result['quest_icon_id'],
				'image_path' => $result['image_path']
			];
		}
		return $icons;
	}

	private function getQuestRequiredItems($questId) {
		$sql = <<<SQL
			SELECT
				I.item_id
				, I.item_class_id
				, IC.name_en item_class
				, I.base_item_id
				, I.name_en
				, I.name_ja
				, I.rarity
				, I.image_name
			FROM
				quest_required_item QRI
				INNER JOIN item I
					ON QRI.item_id = I.item_id
				INNER JOIN item_class IC
					ON I.item_class_id = IC.item_class_id
			WHERE
				QRI.quest_id = :quest_id
			ORDER BY
				I.sort_key
			SQL;
		$this->logger->debug($sql);
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':quest_id', $questId, PDO::PARAM_INT);
		$stmt->execute();
		$items = [];
		while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$items[] = [
				'item_id' => $result['item_id'],
				'item_class_id' => $result['item_class_id'],
				'item_class' => $result['item_class'],
				'base_item_id' => $result['base_item_id'],
				'name_en' => $result['name_en'],
				'name_ja' => $result['name_ja'],
				'rarity' => $result['rarity'],
				'image_name' => $result['image_name'],
			];
		}
		return $items;
	}

	private function getQuestRewardItems($questId) {
		$sql = <<<SQL
			SELECT
				I.item_id
				, I.item_class_id
				, IC.name_en item_class
				, I.base_item_id
				, I.name_en
				, I.name_ja
				, I.rarity
				, I.image_name
			FROM
				quest_reward_item QRI
				INNER JOIN item I
					ON QRI.item_id = I.item_id
				INNER JOIN item_class IC
					ON I.item_class_id = IC.item_class_id
			WHERE
				QRI.quest_id = :quest_id
			ORDER BY
				I.sort_key
			SQL;
		$this->logger->debug($sql);
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':quest_id', $questId, PDO::PARAM_INT);
		$stmt->execute();
		$items = [];
		while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$items[] = [
				'item_id' => $result['item_id'],
				'item_class_id' => $result['item_class_id'],
				'item_class' => $result['item_class'],
				'base_item_id' => $result['base_item_id'],
				'name_en' => $result['name_en'],
				'name_ja' => $result['name_ja'],
				'rarity' => $result['rarity'],
				'image_name' => $result['image_name'],
			];
		}
		return $items;
	}

}
