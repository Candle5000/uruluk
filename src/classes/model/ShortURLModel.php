<?php

namespace Model;

use \PDO;

class ShortURLModel extends Model {

	const CHARS = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

	public function createShortUrl(string $url) {
		$checkKey = $this->selectKeyByUrl($url);
		if ($checkKey !== null) return $checkKey;
		$key = '';
		do {
			$key = '';
			for ($i = 0; $i < 6; $i++) {
				$key .= substr(ShortURLModel::CHARS, rand(0, strlen(ShortURLModel::CHARS) - 1), 1);
			}
		} while ($this->selectUrlByKey($key) !== null);
		$this->insert($key, $url);
		return $key;
	}

	public function getUrlByKey(string $key) {
		$this->update($key);
		return $this->selectUrlByKey($key);
	}

	private function insert(string $urlKey, string $url) {
		$sql = 'INSERT '
			. 'INTO short_url(short_url_key, url, created_at, last_access) '
			. 'VALUES (:urlKey , :url , NOW() , NULL )';

		$this->logger->debug($sql);
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':urlKey', $urlKey, PDO::PARAM_STR);
		$stmt->bindParam(':url', $url, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->rowCount();
	}

	private function selectKeyByUrl(string $url) {
		$sql = 'SELECT'
			. '  short_url_key '
			. 'FROM'
			. '  short_url '
			. 'WHERE'
			. '  url = :url';

		$this->logger->debug($sql);
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':url', $url, PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetch();
		return $stmt->rowCount() === 0 ? null : $result['short_url_key'];
	}

	private function selectUrlByKey(string $urlKey) {
		$sql = 'SELECT'
			. '  url '
			. 'FROM'
			. '  short_url '
			. 'WHERE'
			. '  short_url_key = :urlKey';

		$this->logger->debug($sql);
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':urlKey', $urlKey, PDO::PARAM_STR);
		$stmt->execute();
		$result = $stmt->fetch();
		return $stmt->rowCount() === 0 ? null : $result['url'];
	}

	private function update(string $urlKey) {
		$sql = 'UPDATE short_url '
			. 'SET'
			. '  last_access = NOW() '
			. 'WHERE'
			. '  short_url_key = :urlKey';

		$this->logger->debug($sql);
		$stmt = $this->db->prepare($sql);
		$stmt->bindParam(':urlKey', $urlKey, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->rowCount();
	}

}
