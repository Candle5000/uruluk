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
                , c.str
                , c.min_ad
                , c.max_ad
                , c.def
                , c.dex
                , c.vit
                , c.voh
                , c.dr
                , c.ws
                , c.`as`
                , c.sad
                , c.vot
                , c.tb_str
                , c.tb_ad
                , c.tb_def
                , c.tb_dex
                , c.tb_vit
                , c.tb_voh
                , c.tb_dr
                , c.tb_ws
                , c.tb_as
                , c.ad_enabled
                , c.as_enabled
                , c.sad_enabled
                , c.tb
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
                    'str' => $result['str'],
                    'min_ad' => $this->getFormattedStats($result['min_ad'], false),
                    'max_ad' => $this->getFormattedStats($result['max_ad'], false),
                    'def' => $this->getFormattedStats($result['def'], false),
                    'dex' => $this->getFormattedStats($result['dex'], false),
                    'vit' => $this->getFormattedStats($result['vit'], false),
                    'voh' => $this->getFormattedStats($result['voh'], false),
                    'dr' => $this->getFormattedStats($result['dr'], false),
                    'ws' => $this->getFormattedStats($result['ws'], false),
                    'as' => $this->getFormattedStats($result['as'], true),
                    'sad' => $this->getFormattedStats($result['sad'], true),
                    'vot' => $result['vot'],
                    'tb_str' => $result['tb_str'],
                    'tb_ad' => $result['tb_ad'],
                    'tb_def' => $result['tb_def'],
                    'tb_dex' => $result['tb_dex'],
                    'tb_vit' => $result['tb_vit'],
                    'tb_voh' => $result['tb_voh'],
                    'tb_dr' => $result['tb_dr'],
                    'tb_ws' => $result['tb_ws'],
                    'tb_as' => $result['tb_as'],
                    'ad_enabled' => $result['ad_enabled'],
                    'as_enabled' => $result['as_enabled'],
                    'sad_enabled' => $result['sad_enabled'],
                    'tb' => $result['tb'],
                    'sort_key' => $result['sort_key'],
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
                , str
                , min_ad
                , max_ad
                , def
                , dex
                , vit
                , voh
                , dr
                , ws
                , `as`
                , sad
                , vot
                , xp
                , tb_str
                , tb_ad
                , tb_def
                , tb_dex
                , tb_vit
                , tb_voh
                , tb_dr
                , tb_ws
                , tb_as
                , tb_xp
                , ad_enabled
                , as_enabled
                , sad_enabled
                , tb
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
                'str' => $result['str'],
                'min_ad' => $result['min_ad'],
                'max_ad' => $result['max_ad'],
                'def' => $result['def'],
                'dex' => $result['dex'],
                'vit' => $result['vit'],
                'voh' => $result['voh'],
                'dr' => $result['dr'],
                'sad' => $result['sad'],
                'vot' => $result['vot'],
                'ws' => $result['ws'],
                'as' => $result['as'],
                'xp' => $result['xp'],
                'tb_str' => $result['tb_str'],
                'tb_ad' => $result['tb_ad'],
                'tb_def' => $result['tb_def'],
                'tb_dex' => $result['tb_dex'],
                'tb_vit' => $result['tb_vit'],
                'tb_voh' => $result['tb_voh'],
                'tb_dr' => $result['tb_dr'],
                'tb_ws' => $result['tb_ws'],
                'tb_as' => $result['tb_as'],
                'tb_xp' => $result['tb_xp'],
                'ad_enabled' => $result['ad_enabled'] ? true : false,
                'as_enabled' => $result['as_enabled'] ? true : false,
                'sad_enabled' => $result['sad_enabled'] ? true : false,
                'tb' => $result['tb'] ? true : false,
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
                , SA.name_key
                , CSA.cooldown
                , SA.replace_melee
                , SA.effect_delay
                , SA.trigger_on_vit
                , SA.trigger_on_vit_revert
                , SA.is_once
                , SA.is_long_range
                , SA.damage_type
                , SA.is_movement
                , SA.is_random_summon
                , SA.summon_limit
                , SA.sad_enabled
                , SA.ad_relative
                , SA.ad_actual
                , SA.attack_count
                , SA.double_attack
                , SA.is_spread
                , SA.dps_enabled
                , SA.voh_dr_enabled
                , SA.image_name
                , SA.sort_key
                , false as stats_enabled
                , null as str
                , null as min_ad
                , null as max_ad
                , null as dex
                , null as `as`
                , null as tb_str
                , null as tb_ad
                , null as tb_dex
                , null as tb_as
            FROM
                creature_special_attack CSA
                JOIN special_attack SA
                    ON CSA.special_attack_id = SA.special_attack_id
            WHERE
                CSA.creature_id = :id
                AND SA.visible_in_list = 1
            UNION ALL
            SELECT
                SA2.special_attack_id
                , SA2.name
                , SA2.name_key
                , OSA.cooldown
                , SA2.replace_melee
                , SA2.effect_delay
                , SA2.trigger_on_vit
                , SA2.trigger_on_vit_revert
                , SA2.is_once
                , SA2.is_long_range
                , SA2.damage_type
                , SA2.is_movement
                , SA2.is_random_summon
                , SA2.summon_limit
                , SA2.sad_enabled
                , SA2.ad_relative
                , SA2.ad_actual
                , SA2.attack_count
                , SA2.double_attack
                , SA2.is_spread
                , SA2.dps_enabled
                , SA2.voh_dr_enabled
                , SA2.image_name
                , SA2.sort_key
                , SO.stats_enabled
                , SO.str
                , SO.min_ad
                , SO.max_ad
                , SO.dex
                , SO.`as`
                , SO.tb_str
                , SO.tb_ad
                , SO.tb_dex
                , SO.tb_as
            FROM
                creature_special_attack CSA
                JOIN special_attack SA1
                    ON CSA.special_attack_id = SA1.special_attack_id
                JOIN place_sa_object PO
                    ON SA1.special_attack_id = PO.special_attack_id
                JOIN sa_object SO
                    ON PO.sa_object_id = SO.sa_object_id
                JOIN sa_object_special_attack OSA
                    ON SO.sa_object_id = OSA.sa_object_id
                JOIN special_attack SA2
                    ON OSA.special_attack_id = SA2.special_attack_id
            WHERE
                CSA.creature_id = :id
                AND SA2.visible_in_list = 1
            ORDER BY
                sort_key
                , special_attack_id
            SQL;
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $creatureId, PDO::PARAM_INT);
        $stmt->execute();
        $sa = [];
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sa[] = [
                'special_attack_id' => $result['special_attack_id'],
                'name_i' => $result['name'],
                'name' => $this->i18n->s($result['name_key']),
                'cooldown' => $result['cooldown'],
                'replace_melee' => $result['replace_melee'] ? true : false,
                'effect_delay' => $result['effect_delay'],
                'trigger_on_vit' => $result['trigger_on_vit'],
                'trigger_on_vit_revert' => $result['trigger_on_vit_revert'] ? true : false,
                'is_once' => $result['is_once'] ? true : false,
                'is_long_range' => $result['is_long_range'] ? true : false,
                'damage_type' => $result['damage_type'],
                'is_movement' => $result['is_movement'] ? true : false,
                'is_random_summon' => $result['is_random_summon'] ? true : false,
                'summon_limit' => $result['summon_limit'],
                'sad_enabled' => $result['sad_enabled'] ? true : false,
                'ad_relative' => $result['ad_relative'],
                'ad_actual' => $result['ad_actual'],
                'attack_count' => $result['attack_count'],
                'double_attack' => $result['double_attack'] ? true : false,
                'is_spread' => $result['is_spread'] ? true : false,
                'dps_enabled' => $result['dps_enabled'] ? true : false,
                'voh_dr_enabled' => $result['voh_dr_enabled'] ? true : false,
                'image_name' => $result['image_name'],
                'stats_enabled' => $result['stats_enabled'] ? true : false,
                'str' => $result['str'],
                'min_ad' => $result['min_ad'],
                'max_ad' => $result['max_ad'],
                'dex' => $result['dex'],
                'as' => $result['as'],
                'tb_str' => $result['tb_str'],
                'tb_ad' => $result['tb_ad'],
                'tb_dex' => $result['tb_dex'],
                'tb_as' => $result['tb_as'],
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
