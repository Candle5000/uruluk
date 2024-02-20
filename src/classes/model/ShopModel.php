<?php

namespace Model;

use \PDO;

class ShopModel extends Model
{

    public function getShopListByItemId(int $itemId)
    {
        $sql = <<<SQL
            SELECT
                S.shop_id
                , S.floor_id
                , F.short_name
                , S.name
                , S.image_name
                , S.random
                , S.note
                , SI.price
            FROM
                shop_item SI
                INNER JOIN shop S
                    ON SI.shop_id = S.shop_id
                INNER JOIN floor F
                    ON S.floor_id = F.floor_id
            WHERE
                SI.item_id = :item_id
            ORDER BY
                F.sort_key
            SQL;
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':item_id', $itemId, PDO::PARAM_INT);
        $stmt->execute();
        $shops = [];
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $shops[] = [
                'shop_id' => $result['shop_id'],
                'floor_id' => $result['floor_id'],
                'short_name' => $result['short_name'],
                'name' => $result['name'],
                'image_name' => $result['image_name'],
                'random' => $result['random'],
                'note' => $result['note'],
                'price' => $result['price']
            ];
        }
        return $shops;
    }

    public function getShopListByFloorId(int $floorId)
    {
        $sql = <<<SQL
            SELECT
                shop_id
                , floor_id
                , name
                , image_name
                , random
                , note
            FROM
                shop
            WHERE
                floor_id = :floor_id
            SQL;
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':floor_id', $floorId, PDO::PARAM_INT);
        $stmt->execute();
        $shops = [];
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $shops[] = [
                'shop_id' => $result['shop_id'],
                'floor_id' => $result['floor_id'],
                'name' => $result['name'],
                'image_name' => $result['image_name'],
                'random' => $result['random'],
                'note' => $result['note'],
            ];
        }
        return $shops;
    }
}
