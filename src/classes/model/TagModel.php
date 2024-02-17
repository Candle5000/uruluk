<?php

namespace Model;

use \PDO;

class TagModel extends Model
{

    public function getTags()
    {
        $sql = <<<SQL
            SELECT
                tag_id
                , tag_url
                , tag_name
            FROM
                tag T
            ORDER BY
                T.sort_key
            SQL;
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $tags = [];
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tags[] = [
                'tag_id' => $result['tag_id'],
                'tag_url' => $result['tag_url'],
                'tag_name' => $result['tag_name'],
            ];
        }
        return $tags;
    }

    public function getTagByTagUrl(string $tagUrl)
    {
        $sql = <<<SQL
            SELECT
                tag_id
                , tag_url
                , tag_name
            FROM
                tag
            WHERE
                tag_url = :tagUrl
            SQL;
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tagUrl', $tagUrl, PDO::PARAM_STR);
        $stmt->execute();
        if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return [
                'tag_id' => $result['tag_id'],
                'tag_url' => $result['tag_url'],
                'tag_name' => $result['tag_name'],
            ];
        } else {
            return null;
        }
    }

    public function getTagsByItemId(int $itemId)
    {
        $sql = <<<SQL
            SELECT
                T.tag_id
                , T.tag_url
                , T.tag_name
            FROM
                tag T
                JOIN item_tag IT
                    ON T.tag_id = IT.tag_id
                JOIN item I
                    ON IT.item_id = I.item_id
            WHERE
                I.item_id = :itemId
            ORDER BY
                T.sort_key
            SQL;
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);
        $stmt->execute();
        $tags = [];
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tags[] = [
                'tag_id' => $result['tag_id'],
                'tag_url' => $result['tag_url'],
                'tag_name' => $result['tag_name'],
            ];
        }
        return $tags;
    }
}
