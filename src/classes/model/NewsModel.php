<?php

namespace Model;

class NewsModel extends Model {

	const LATEST_NEWS_COUNT = 8;
	const PAGE_NEWS_COUNT = 10;

	public function getLatestNews() {
		return $this->select(0, NewsModel::LATEST_NEWS_COUNT);
	}

	public function getNews(int $page) {
		return $this->select($page, NewsModel::PAGE_NEWS_COUNT);
	}

	private function select(int $offset, int $rouCount) {
		$sql = <<<SQL
			SELECT
				post_date
				, subject
				, content
			FROM
				news
			ORDER BY
				post_date DESC
			LIMIT
				:offset, :rowCount
			SQL;
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':offset', $offset);
		$stmt->bindParam(':rowCount', $rowCount);
		$stmt->execute();
		$newsList = [];
		while($result = $stmt->fetch()) {
			$newsList[] = [
				'post_date' => $result['post_date'],
				'subject' => $result['subject'],
				'content' => $result['content']
			];
		}
		return $newsList;
	}

}
