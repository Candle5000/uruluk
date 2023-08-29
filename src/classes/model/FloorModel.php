<?php

namespace Model;

use \PDO;

class FloorModel extends Model
{

    private const RARITY_RARE = "rare";

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

    public function getFloorDetail($floorId)
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
                'items' => $this->getFloorRareItemList($floorId),
                'banana' => $this->getFloorBananaItemList($floorId),
                'treasure' => $this->getFloorTreasureItemList($floorId),
                'creatures' => $this->getFloorCreatureList($floorId)
            ];
        } else {
            return null;
        }
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

    private function getFloorRareItemList($floorId)
    {
        $rarity = self::RARITY_RARE;
        $sql = <<<SQL
            SELECT DISTINCT
                I.item_id
                , IC.name_en item_class
                , I.base_item_id
                , I.class_flactuable
                , I.name_en
                , I.name_ja
                , I.rarity
                , I.image_name
            FROM
                floor_drop_group FG
                INNER JOIN drop_item_group DG
                    ON FG.drop_item_group_id = DG.drop_item_group_id
                INNER JOIN item I
                    ON DG.item_id = I.item_id
                INNER JOIN item_class IC
                    ON I.item_class_id = IC.item_class_id
            WHERE
                FG.floor_id = :floor_id
                AND I.rarity = :rarity
            ORDER BY
                I.sort_key
                , I.item_id
            SQL;
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':floor_id', $floorId, PDO::PARAM_INT);
        $stmt->bindParam(':rarity', $rarity, PDO::PARAM_STR);
        $stmt->execute();
        $items = [];
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = [
                'item_id' => $result['item_id'],
                'item_class' => $result['item_class'],
                'base_item_id' => $result['base_item_id'],
                'class_flactuable' => $result['class_flactuable'],
                'name_en' => $result['name_en'],
                'name_ja' => $result['name_ja'],
                'rarity' => $result['rarity'],
                'image_name' => $result['image_name'],
            ];
        }
        return $items;
    }

    private function getFloorBananaItemList($floorId)
    {
        $sql = <<<SQL
            SELECT DISTINCT
                I.item_id
                , IC.name_en item_class
                , I.base_item_id
                , I.class_flactuable
                , I.name_en
                , I.name_ja
                , I.rarity
                , I.image_name
            FROM
                floor_banana_drop_group FG
                INNER JOIN drop_item_group DG
                    ON FG.drop_item_group_id = DG.drop_item_group_id
                INNER JOIN item I
                    ON DG.item_id = I.item_id
                INNER JOIN item_class IC
                    ON I.item_class_id = IC.item_class_id
            WHERE
                FG.floor_id = :floor_id
                AND rarity IN ('rare', 'artifact')
            ORDER BY
                I.sort_key
                , I.item_id
            SQL;
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':floor_id', $floorId, PDO::PARAM_INT);
        $stmt->execute();
        $items = [];
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = [
                'item_id' => $result['item_id'],
                'item_class' => $result['item_class'],
                'base_item_id' => $result['base_item_id'],
                'class_flactuable' => $result['class_flactuable'],
                'name_en' => $result['name_en'],
                'name_ja' => $result['name_ja'],
                'rarity' => $result['rarity'],
                'image_name' => $result['image_name'],
            ];
        }
        return $items;
    }

    private function getFloorTreasureItemList($floorId)
    {
        $sql = <<<SQL
            SELECT
                I.item_id
                , IC.name_en item_class
                , I.base_item_id
                , I.name_en
                , I.name_ja
                , I.rarity
                , I.image_name
                , FT.note
            FROM
                floor_treasure FT
                INNER JOIN item I
                    ON FT.item_id = I.item_id
                INNER JOIN item_class IC
                    ON I.item_class_id = IC.item_class_id
            WHERE
                FT.floor_id = :floor_id
            ORDER BY
                I.sort_key
                , I.item_id
            SQL;
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':floor_id', $floorId, PDO::PARAM_INT);
        $stmt->execute();
        $items = [];
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = [
                'item_id' => $result['item_id'],
                'item_class' => $result['item_class'],
                'base_item_id' => $result['base_item_id'],
                'name_en' => $result['name_en'],
                'name_ja' => $result['name_ja'],
                'rarity' => $result['rarity'],
                'image_name' => $result['image_name'],
                'note' => $result['note'],
            ];
        }
        return $items;
    }

    private function getFloorCreatureList($floorId)
    {
        $sql = <<<SQL
            SELECT
                FC.event_id
                , E.note
                , C.creature_id
                , C.boss
                , C.name_en
                , C.name_ja
                , C.image_name
            FROM
                floor_creature FC
                INNER JOIN creature C
                    ON FC.creature_id = C.creature_id
                LEFT JOIN creature_pop_event E
                    ON FC.event_id = E.event_id
            WHERE
                FC.floor_id = :floor_id
            ORDER BY
                FC.event_id
                , C.boss
                , C.sort_key
            SQL;
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':floor_id', $floorId, PDO::PARAM_INT);
        $stmt->execute();
        $floorCreatures = [
            'default' => [],
            'events' => []
        ];
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $creature = [
                'creature_id' => $result['creature_id'],
                'boss' => $result['boss'],
                'name_en' => $result['name_en'],
                'name_ja' => $result['name_ja'],
                'image_name' => $result['image_name']
            ];
            $eventId = $result['event_id'];
            if ($eventId == 0) {
                $floorCreatures['default'][] = $creature;
            } else {
                if (!array_key_exists($eventId, $floorCreatures['events'])) {
                    $floorCreatures['events'][$eventId] = [
                        'note' => $result['note'],
                        'creatures' => []
                    ];
                }
                $floorCreatures['events'][$eventId]['creatures'][] = $creature;
            }
        }
        return $floorCreatures;
    }
}
