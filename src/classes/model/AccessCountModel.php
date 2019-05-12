<?php

namespace Model;

class AccessCountModel extends Model {

	const SELECT_TARGET_TODAY = 0;
	const SELECT_TARGET_YESTERDAY = 1;

	public function getTodayPvWithCountUp(int $pageId) {
		if($this->update($pageId) === 0) {
			$this->insert($pageId);
		}
		return $this->select($pageId, AccessCountModel::SELECT_TARGET_TODAY);
	}

	public function getYesterdayPv(int $pageId) {
		return $this->select($pageId, AccessCountModel::SELECT_TARGET_YESTERDAY);
	}

	private function insert(int $pageId) {
		$sql = 'INSERT '
			. 'INTO access_count(page_id, count_date, pv_count) '
			. 'VALUES (:pageId, CURRENT_DATE, 1)';

		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':pageId', $pageId);
		$stmt->execute();
		return $stmt->rowCount();
	}

	private function select(int $pageId, int $targetDate) {
		$sqlDate = $targetDate == AccessCountModel::SELECT_TARGET_TODAY
			? 'CURRENT_DATE' : 'DATE_ADD(CURRENT_DATE, INTERVAL - 1 DAY)';
		$sql = 'SELECT'
			. '  pv_count '
			. 'FROM'
			. '  access_count '
			. 'WHERE'
			. '  page_id = :pageId '
			. '  AND count_date = ' . $sqlDate;

		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':pageId', $pageId);
		$stmt->execute();
		$result = $stmt->fetch();
		return $stmt->rowCount() === 0 ? 0 : $result['pv_count'];
	}

	private function update(int $pageId) {
		$sql = 'UPDATE access_count '
			. 'SET'
			. '  pv_count = pv_count + 1 '
			. 'WHERE'
			. '  page_id = :pageId '
			. '  AND count_date = CURRENT_DATE';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':pageId', $pageId);
		$stmt->execute();
		return $stmt->rowCount();
	}

}
