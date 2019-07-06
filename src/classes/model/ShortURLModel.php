<?php

namespace Model;

class ShortURLModel extends Model {

	const CHARS = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

	public function createShortUrl(string $url) {
		$checkKey = $this->selectKeyByUrl($url);
		if ($checkKey !== null) return $checkKey;
		$key = '';
		do {
			$key = '';
			for ($i = 0; $i < 6; $i++) {
				$key .= substr(CHARS, rand(0, strlen(CHARS) - 1), 1);
			}
		} while ($this->selectUrlByKey($key) !== null);
		$this->insert($key, $url);
		return $key;
	}

	public function getUrlByKey(string $key) {
		return $this->selectUrlByKey($key);
	}

	private function insert(string $urlKey, string $url) {
		$sql = 'INSERT '
			. 'INTO short_url(short_url_key, url, created_at, last_access) '
			. 'VALUES (:urlKey , :url , NOW() , NULL )';

		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':urlKey', $urlKey);
		$stmt->bindParam(':url', $url);
		$stmt->execute();
		return $stmt->rowCount();
	}

	private function selectKeyByUrl(string $url) {
		$sql = 'SELECT'
			. '  short_url_key'
			. 'FROM'
			. '  short_url '
			. 'WHERE'
			. '  url = :url';

		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':url', $url);
		$stmt->execute();
		$result = $stmt->fetch();
		return $stmt->rowCount() === 0 ? null : $result['short_url_key'];
	}

	private function selectUrlByKey(string $urlKey) {
		$sql = 'SELECT'
			. '  url'
			. 'FROM'
			. '  short_url '
			. 'WHERE'
			. '  short_url_key = :urlKey';

		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':urlKey', $urlKey);
		$stmt->execute();
		$result = $stmt->fetch();
		return $stmt->rowCount() === 0 ? null : $result['url'];
	}

	private function update(string $urlKey) {
		$sql = 'UPDATE short_url '
			. 'SET'
			. '  last_access = NOW() '
			. 'WHERE'
			. '  short_url_key = ?';
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':urlKey', $urlKey);
		$stmt->execute();
		return $stmt->rowCount();
	}

}
