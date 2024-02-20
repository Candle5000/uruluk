<?php

namespace Model;

use \PDO;

class FloorModel extends Model
{

    public function getFloorIndex()
    {
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

    public function getFloorDetail(int $floorId)
    {
        $sql = <<<SQL
            SELECT
                short_name
                , name_en
                , name_ja
                , image_name
                , image_size
            FROM
                `floor`
            WHERE
                floor_id = :floor_id
            SQL;
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':floor_id', $floorId, PDO::PARAM_INT);
        $stmt->execute();
        $floors = [];
        if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return [
                'floor_id' => $floorId,
                'short_name' => $result['short_name'],
                'name_en' => $result['name_en'],
                'name_ja' => $result['name_ja'],
                'image_name' => $result['image_name'],
                'image_size' => $result['image_size'],
            ];
        } else {
            return null;
        }
    }

    public function getFloorsByItemId(int $itemId)
    {
        $sql = <<<SQL
            SELECT DISTINCT
                F.floor_id
                , F.short_name
            FROM
                floor_drop_group FG
                INNER JOIN floor F
                    ON FG.floor_id = F.floor_id
                INNER JOIN drop_item_group IG
                    ON IG.drop_item_group_id = FG.drop_item_group_id
            WHERE
                IG.item_id = :id
            ORDER BY
                F.sort_key
            SQL;
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $itemId, PDO::PARAM_INT);
        $stmt->execute();
        $floors = [];
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $floors[] = [
                'floor_id' => $result['floor_id'],
                'short_name' => $result['short_name'],
            ];
        }
        return $floors;
    }

    public function getBananaFloorsByItemId(int $itemId)
    {
        $sql = <<<SQL
            SELECT DISTINCT
                F.floor_id
                , F.short_name
            FROM
                floor_banana_drop_group FG
                INNER JOIN drop_item_group DG
                    ON FG.drop_item_group_id = DG.drop_item_group_id
                INNER JOIN floor F
                    ON FG.floor_id = F.floor_id
            WHERE
                DG.item_id = :id
            ORDER BY
                F.sort_key
            SQL;
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $itemId, PDO::PARAM_INT);
        $stmt->execute();
        $floors = [];
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $floors[] = [
                'floor_id' => $result['floor_id'],
                'short_name' => $result['short_name'],
            ];
        }
        return $floors;
    }

    public function getTreasureFloorsByItemId(int $itemId)
    {
        $sql = <<<SQL
            SELECT
                F.floor_id
                , F.short_name
                , FT.note
            FROM
                floor_treasure FT
                INNER JOIN floor F
                    ON FT.floor_id = F.floor_id
            WHERE
                FT.item_id = :id
            ORDER BY
                F.sort_key
            SQL;
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $itemId, PDO::PARAM_INT);
        $stmt->execute();
        $floors = [];
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $floors[] = [
                'floor_id' => $result['floor_id'],
                'short_name' => $result['short_name'],
                'note' => $result['note'],
            ];
        }
        return $floors;
    }

    public function getFloorsByCreatureId(int $creatureId)
    {
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
        $stmt->bindParam(':id', $creatureId, PDO::PARAM_INT);
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

    private function getFloorGroupList()
    {
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

    private function getFloorList()
    {
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
