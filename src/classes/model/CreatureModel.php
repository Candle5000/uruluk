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
                , c.name_key
                , c.name_en
                , c.name_ja
                , c.image_name
                , c.min_ad
                , c.max_ad
                , c.`as`
                , c.str
                , c.def
                , c.dex
                , c.vit
                , c.voh
                , c.dr
                , c.tb
                , c.tb_ad
                , c.tb_as
                , c.tb_str
                , c.tb_def
                , c.tb_dex
                , c.tb_vit
                , c.tb_ws
                , c.tb_voh
                , c.tb_dr
                , c.sort_key
                , fc.floor_id
                , fc.event_id
            FROM
                creature c
                LEFT JOIN floor_creature fc
                    ON fc.creature_id = c.creature_id
                LEFT JOIN floor f
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
                    'name' => $this->i18n->s($result['name_key']),
                    'name_en' => $result['name_en'],
                    'name_ja' => $result['name_ja'],
                    'image_name' => $result['image_name'],
                    'min_ad' => $this->getFormattedStats($result['min_ad'], false),
                    'max_ad' => $this->getFormattedStats($result['max_ad'], false),
                    'as' => $this->getFormattedStats($result['as'], true),
                    'str' => $this->getFormattedStats($result['str'], true),
                    'def' => $this->getFormattedStats($result['def'], false),
                    'dex' => $this->getFormattedStats($result['dex'], false),
                    'vit' => $this->getFormattedStats($result['vit'], false),
                    'voh' => $this->getFormattedStats($result['voh'], false),
                    'dr' => $this->getFormattedStats($result['dr'], false),
                    'tb' => $result['tb'],
                    'tb_ad' => $result['tb_ad'],
                    'tb_as' => $result['tb_as'],
                    'tb_str' => $result['tb_str'],
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

    public function getCreatureDetailById($creatureId)
    {
        $sql = <<<SQL
            SELECT
                creature_id
                , boss
                , name_key
                , name_en
                , name_ja
                , min_ad
                , max_ad
                , `as`
                , str
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
                , tb_str
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
        $stmt->bindParam(':id', $creatureId, PDO::PARAM_INT);
        $stmt->execute();
        if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return [
                'creature_id' => $result['creature_id'],
                'boss' => $result['boss'],
                'name' => $this->i18n->s($result['name_key']),
                'name_en' => $result['name_en'],
                'name_ja' => $result['name_ja'],
                'image_name' => $result['image_name'],
                'min_ad' => $result['min_ad'],
                'max_ad' => $result['max_ad'],
                'as' => $result['as'],
                'str' => $result['str'],
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
                'tb_str' => $result['tb_str'],
                'tb_def' => $result['tb_def'],
                'tb_dex' => $result['tb_dex'],
                'tb_vit' => $result['tb_vit'],
                'tb_ws' => $result['tb_ws'],
                'tb_voh' => $result['tb_voh'],
                'tb_dr' => $result['tb_dr'],
                'tb_xp' => $result['tb_xp'],
                'special_attacks' => $this->getSpecialAttacksByCreatureId($creatureId),
                'note' => $result['note'],
            ];
        } else {
            return null;
        }
    }

    public function getCreaturesByItemId(int $itemId)
    {
        $sql = <<<SQL
            SELECT
                C.creature_id
                , C.boss
                , C.name_key
                , C.name_en
                , C.image_name
            FROM
                creature_drop_item CI
                INNER JOIN creature C
                ON CI.creature_id = C.creature_id
            WHERE
                CI.item_id = :id
            ORDER BY
                C.sort_key
            SQL;
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $itemId, PDO::PARAM_INT);
        $stmt->execute();
        $creatures = [];
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $creatures[] = [
                'creature_id' => $result['creature_id'],
                'boss' => $result['boss'],
                'name' => $this->i18n->s($result['name_key']),
                'name_en' => $result['name_en'],
                'image_name' => $result['image_name'],
            ];
        }
        return $creatures;
    }

    public function getCreaturesByFloorId(int $floorId)
    {
        $sql = <<<SQL
            SELECT
                FC.event_id
                , E.description_key
                , E.note
                , C.creature_id
                , C.boss
                , C.name_key
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
                'name' => $this->i18n->s($result['name_key']),
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
                        'description' => $this->i18n->s($result['description_key']),
                        'note' => $result['note'],
                        'creatures' => []
                    ];
                }
                $floorCreatures['events'][$eventId]['creatures'][] = $creature;
            }
        }
        return $floorCreatures;
    }

    private function getSpecialAttacksByCreatureId(int $creatureId)
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
        $stmt->bindParam(':id', $creatureId, PDO::PARAM_INT);
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
