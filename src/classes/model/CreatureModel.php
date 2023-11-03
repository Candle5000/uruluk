<?php

namespace Model;

use \PDO;

class CreatureModel extends Model
{

    public function getCreatureStutsList()
    {
        $sql = <<<SQL
            SELECT
                c.creature_id
                , c.boss
                , c.name_en
                , c.name_ja
                , c.image_name
                , c.min_ad
                , c.max_ad
                , c.`as`
                , c.def
                , c.dex
                , c.vit
                , c.voh
                , c.dr
                , c.tb
                , c.tb_ad
                , c.tb_as
                , c.tb_def
                , c.tb_dex
                , c.tb_vit
                , c.tb_ws
                , c.tb_voh
                , c.tb_dr
                , fc.floor_id
                , fc.event_id
            FROM
                creature c
                INNER JOIN floor_creature fc
                    ON fc.creature_id = c.creature_id
                INNER JOIN floor f
                    ON f.floor_id = fc.floor_id
            ORDER BY
                c.boss
                , c.sort_key
                , f.sort_key
                , fc.event_id
            SQL;
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $creatures = [];
        $prevCreatureId = 0;
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($result['creature_id'] != $prevCreatureId) {
                $prevCreatureId = $result['creature_id'];
                $creatures[] = [
                    'creature_id' => $result['creature_id'],
                    'boss' => $result['boss'] == 1,
                    'name_en' => $result['name_en'],
                    'name_ja' => $result['name_ja'],
                    'image_name' => $result['image_name'],
                    'min_ad' => $this->getFormattedStats($result['min_ad'], false),
                    'max_ad' => $this->getFormattedStats($result['max_ad'], false),
                    'as' => $this->getFormattedStats($result['as'], true),
                    'def' => $this->getFormattedStats($result['def'], false),
                    'dex' => $this->getFormattedStats($result['dex'], false),
                    'vit' => $this->getFormattedStats($result['vit'], false),
                    'voh' => $this->getFormattedStats($result['voh'], false),
                    'dr' => $this->getFormattedStats($result['dr'], false),
                    'tb' => $result['tb'],
                    'tb_ad' => $result['tb_ad'],
                    'tb_as' => $result['tb_as'],
                    'tb_def' => $result['tb_def'],
                    'tb_dex' => $result['tb_dex'],
                    'tb_vit' => $result['tb_vit'],
                    'tb_voh' => $result['tb_voh'],
                    'tb_dr' => $result['tb_dr'],
                    'floors' => [
                        [
                            'floor_id' => $result['floor_id'],
                            'event_id' => $result['event_id']
                        ]
                    ]
                ];
            } else {
                $creatures[count($creatures) - 1]['floors'][] = [
                    'floor_id' => $result['floor_id'],
                    'event_id' => $result['event_id']
                ];
            }
        }
        return $creatures;
    }

    public function getCreatureDetailById($id)
    {
        $sql = <<<SQL
            SELECT
                creature_id
                , boss
                , name_en
                , name_ja
                , min_ad
                , max_ad
                , `as`
                , def
                , dex
                , vit
                , ws
                , voh
                , dr
                , xp
                , tb
                , tb_ad
                , tb_as
                , tb_def
                , tb_dex
                , tb_vit
                , tb_ws
                , tb_voh
                , tb_dr
                , tb_xp
                , note
                , image_name
            FROM
                creature
            WHERE
                creature_id = :id
            SQL;
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return [
                'creature_id' => $result['creature_id'],
                'boss' => $result['boss'],
                'name_en' => $result['name_en'],
                'name_ja' => $result['name_ja'],
                'image_name' => $result['image_name'],
                'min_ad' => $result['min_ad'],
                'max_ad' => $result['max_ad'],
                'as' => $result['as'],
                'def' => $result['def'],
                'dex' => $result['dex'],
                'vit' => $result['vit'],
                'ws' => $result['ws'],
                'voh' => $result['voh'],
                'dr' => $result['dr'],
                'xp' => $result['xp'],
                'tb' => $result['tb'],
                'tb_ad' => $result['tb_ad'],
                'tb_as' => $result['tb_as'],
                'tb_def' => $result['tb_def'],
                'tb_dex' => $result['tb_dex'],
                'tb_vit' => $result['tb_vit'],
                'tb_ws' => $result['tb_ws'],
                'tb_voh' => $result['tb_voh'],
                'tb_dr' => $result['tb_dr'],
                'tb_xp' => $result['tb_xp'],
                'special_attacks' => $this->getSpecialAttacksByCreatureId($id),
                'items' => $this->getItemsByCreatureId($id),
                'floors' => $this->getFloorsByCreatureId($id),
                'note' => $result['note'],
            ];
        } else {
            return null;
        }
    }

    private function getSpecialAttacksByCreatureId($id)
    {
        $sql = <<<SQL
            SELECT
                SA.special_attack_id
                , SA.name
                , SA.cooldown
                , SA.note
            FROM
                creature_special_attack CSA
                INNER JOIN special_attack SA
                ON CSA.special_attack_id = SA.special_attack_id
            WHERE
                CSA.creature_id = :id
            ORDER BY
                SA.special_attack_id
            SQL;
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $sa = [];
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sa[] = [
                'special_attack_id' => $result['special_attack_id'],
                'name' => $result['name'],
                'cooldown' => $result['cooldown'],
                'note' => $result['note'],
            ];
        }
        return $sa;
    }

    private function getItemsByCreatureId($id)
    {
        $sql = <<<SQL
            SELECT
                I.item_id
                , IC.name_en item_class
                , I.base_item_id
                , I.class_flactuable
                , I.name_en
                , I.rarity
                , I.image_name
            FROM
                creature_drop_item CDI
                INNER JOIN item I
                    ON CDI.item_id = I.item_id
                INNER JOIN item_class IC
                    ON I.item_class_id = IC.item_class_id
            WHERE
                CDI.creature_id = :id
            ORDER BY
                I.sort_key
                , I.item_id
            SQL;
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $items = [];
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = [
                'item_id' => $result['item_id'],
                'item_class' => $result['item_class'],
                'base_item_id' => $result['base_item_id'],
                'class_flactuable' => $result['class_flactuable'],
                'name_en' => $result['name_en'],
                'rarity' => $result['rarity'],
                'image_name' => $result['image_name'],
            ];
        }
        return $items;
    }

    private function getFloorsByCreatureId($id)
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
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
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

    private function getFormattedStats($value, bool $enableUndef)
    {
        switch ($value) {
            case null:
                return '?';
            case 0:
                if ($enableUndef) return '-';
                return $value;
            default:
                return $value;
        }
    }
}
