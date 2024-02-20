<?php

namespace Model;

use \PDO;

class ItemModel extends Model
{

    private const DEFAULT_IMG = [
        'sword' => 'short_sword.png',
        'shield' => 'small_shield.png',
        'axe' => 'wood_axe.png',
        'mantle' => 'mantle.png',
        'dagger' => 'dagger.png',
        'ring' => 'ring0.png',
        'helm' => 'helm_sword.png',
        'armor' => 'leather_vest.png',
        'gloves' => 'gloves.png',
        'boots' => 'boots.png',
        'freshy' => 'toad.png',
        'puppet' => 'puppet0.png'
    ];

    private const SQL_COLUMNS_FOR_ITEMS_WITH_ATTRS = <<<SQL
        I.item_id
        , I.item_class_id
        , IC.name_en item_class_name
        , I.image_name
        , I.name_en
        , I.name_ja
        , I.rarity
        , I.skill_en
        , I.skill_axe_en
        , I.skill_sword_en
        , I.skill_dagger_en
        , S.skill_id
        , S.name skill_name
        , S.trigger_type skill_trigger_type
        , S.kill_trigger_type skill_kill_trigger_type
        , S.activation_rate skill_activation_rate
        , S.trigger_charge skill_trigger_charge
        , S.effect_type skill_effect_type
        , SAT.short_name skill_effect_target_attribute
        , S.effect_amount skill_effect_amount
        , S.effect_duration skill_effect_duration
        , S.sort_key skill_sort_key
        , SA.skill_id skill_axe_id
        , SA.name skill_axe_name
        , SA.trigger_type skill_axe_trigger_type
        , SA.kill_trigger_type skill_axe_kill_trigger_type
        , SA.activation_rate skill_axe_activation_rate
        , SA.trigger_charge skill_axe_trigger_charge
        , SA.effect_type skill_axe_effect_type
        , SAAT.short_name skill_axe_effect_target_attribute
        , SA.effect_amount skill_axe_effect_amount
        , SA.effect_duration skill_axe_effect_duration
        , SA.sort_key skill_axe_sort_key
        , SS.skill_id skill_sword_id
        , SS.name skill_sword_name
        , SS.trigger_type skill_sword_trigger_type
        , SS.kill_trigger_type skill_sword_kill_trigger_type
        , SS.activation_rate skill_sword_activation_rate
        , SS.trigger_charge skill_sword_trigger_charge
        , SS.effect_type skill_sword_effect_type
        , SSAT.short_name skill_sword_effect_target_attribute
        , SS.effect_amount skill_sword_effect_amount
        , SS.effect_duration skill_sword_effect_duration
        , SS.sort_key skill_sword_sort_key
        , SD.skill_id skill_dagger_id
        , SD.name skill_dagger_name
        , SD.trigger_type skill_dagger_trigger_type
        , SD.kill_trigger_type skill_dagger_kill_trigger_type
        , SD.activation_rate skill_dagger_activation_rate
        , SD.trigger_charge skill_dagger_trigger_charge
        , SD.effect_type skill_dagger_effect_type
        , SDAT.short_name skill_dagger_effect_target_attribute
        , SD.effect_amount skill_dagger_effect_amount
        , SD.effect_duration skill_dagger_effect_duration
        , SD.sort_key skill_dagger_sort_key
        , I.comment_en
        , I.comment_ja
        , I.sort_key
        , I.price
        , A.short_name
        , IA.color
        , IA.flactuable
        , IA.based_source
        , IA.attribute_value
        , IA.attribute_value_sword
        , IA.attribute_value_axe
        , IA.attribute_value_dagger
        , A.unit
        , IA.max_required
        , IA.max_required_sword
        , IA.max_required_axe
        , IA.max_required_dagger
        SQL;

    private const SQL_COLUMNS_FOR_ITEM_LINK_LIST = <<<SQL
        I.item_id
        , I.item_class_id
        , IC.name_en item_class
        , I.base_item_id
        , I.class_flactuable
        , I.name_en
        , I.name_ja
        , I.rarity
        , I.image_name
        SQL;

    private const RARITY_RARE = "rare";

    public function getItemClassId(string $itemClassName)
    {
        $sql = <<<SQL
            SELECT
                item_class_id
            FROM
                item_class
            WHERE
                name_en = :itemClassName
            SQL;
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':itemClassName', $itemClassName, PDO::PARAM_STR);
        $stmt->execute();
        if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $result['item_class_id'];
        } else {
            return null;
        }
    }

    public function getItemClassIds(array $itemClassNames)
    {
        $sql_itemClassNames = [];
        foreach ($itemClassNames as $key => $itemClassName) {
            $sql_itemClassNames[] = ":itemClassName$key";
        }
        $str_itemClassNames = implode(', ', $sql_itemClassNames);
        $sql = <<<SQL
            SELECT
                item_class_id
            FROM
                item_class
            WHERE
                name_en IN($str_itemClassNames)
            SQL;
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        foreach ($itemClassNames as $key => $itemClassName) {
            $params[] = [
                'param' => ":itemClassName$key",
                'var' => $itemClassNames[$key], 'type' => PDO::PARAM_STR
            ];
        }
        foreach ($params as $param) {
            $stmt->bindParam($param['param'], $param['var'], $param['type']);
        }
        $stmt->execute();
        $itemClassIds = [];
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $itemClassIds[] = $result['item_class_id'];
        }
        return $itemClassIds;
    }

    public function getBaseItems()
    {
        $sql = <<<SQL
            SELECT
                IC.item_class_id
                , IC.name_en class_name_en
                , IC.name_ja class_name_ja
                , IC.image_name class_image_name
                , BI.base_item_id
                , BI.name_en base_name_en
                , BI.name_ja base_name_ja
                , BI.image_name base_image_name
            FROM
                base_item BI
                INNER JOIN item_class IC
                    ON BI.item_class_id = IC.item_class_id
            ORDER BY
                IC.sort_key
                , BI.sort_key
            SQL;
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $itemClassList = [];
        $prevClassId = null;
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($result['item_class_id'] !== $prevClassId) {
                $itemClassList[] = [
                    'id' => $result['item_class_id'],
                    'name_en' => $result['class_name_en'],
                    'name_ja' => $result['class_name_ja'],
                    'image_name' => $result['class_image_name'],
                    'base_items' => []
                ];
                $prevClassId = $result['item_class_id'];
            }
            $itemClassList[count($itemClassList) - 1]['base_items'][] = [
                'id' => $result['base_item_id'],
                'name_en' => $result['base_name_en'],
                'name_ja' => $result['base_name_ja'],
                'image_name' => $result['base_image_name']
            ];
        }
        return $itemClassList;
    }

    public function getBaseItem(int $itemClassId, int $baseItemId)
    {
        $sql = <<<SQL
            SELECT
                name_en
                , name_ja
                , image_name
                , sort_key
            FROM
                base_item
            WHERE
                base_item_id = :baseItemId
                AND item_class_id = :itemClassId
            SQL;
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':baseItemId', $baseItemId, PDO::PARAM_INT);
        $stmt->bindParam(':itemClassId', $itemClassId, PDO::PARAM_INT);
        $stmt->execute();
        if ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return [
                'name_en' => $result['name_en'],
                'name_ja' => $result['name_ja'],
                'image_name' => $result['image_name'],
                'sort_key' => $result['sort_key']
            ];
        } else {
            return null;
        }
    }

    public function getItemsByClass(int $itemClassId)
    {
        $columns = self::SQL_COLUMNS_FOR_ITEMS_WITH_ATTRS;
        $sql = <<<SQL
            SELECT
                $columns
            FROM
                item I
                LEFT JOIN item_class IC
                    ON I.item_class_id = IC.item_class_id
                LEFT JOIN item_skill S
                    ON I.skill_id = S.skill_id
                LEFT JOIN attribute SAT
                    ON S.effect_target_attribute_id = SAT.attribute_id
                LEFT JOIN item_skill SA
                    ON I.skill_axe_id = SA.skill_id
                LEFT JOIN attribute SAAT
                    ON SA.effect_target_attribute_id = SAAT.attribute_id
                LEFT JOIN item_skill SS
                    ON I.skill_sword_id = SS.skill_id
                LEFT JOIN attribute SSAT
                    ON SS.effect_target_attribute_id = SSAT.attribute_id
                LEFT JOIN item_skill SD
                    ON I.skill_dagger_id = SD.skill_id
                LEFT JOIN attribute SDAT
                    ON SD.effect_target_attribute_id = SDAT.attribute_id
                LEFT JOIN item_attribute IA
                    ON I.item_id = IA.item_id
                LEFT JOIN attribute A
                    ON IA.attribute_id = A.attribute_id
            WHERE
                I.item_class_id = :itemClassId
            ORDER BY
                I.sort_key
                , A.sort_key
                , IA.flactuable
            SQL;
        $params = [['param' => 'itemClassId', 'var' => $itemClassId, 'type' => PDO::PARAM_INT]];
        return $this->getItemsObject($sql, $params);
    }

    public function getItemsByClassAndRarity(int $itemClassId, string $rarity)
    {
        return $this->getItemsByClassAndRarities([$itemClassId], [$rarity]);
    }

    public function getItemsByClassAndRarities(array $itemClassIds, array $rarities)
    {
        $sql_itemClassId = [];
        foreach ($itemClassIds as $key => $itemClassId) {
            $sql_itemClassId[] = ":itemClassId$key";
        }
        $sql_rarity = [];
        foreach ($rarities as $key => $rarity) {
            $sql_rarity[] = ":rarity$key";
        }
        $columns = self::SQL_COLUMNS_FOR_ITEMS_WITH_ATTRS;
        $str_itemClassId = implode(', ', $sql_itemClassId);
        $sql = <<<SQL
            SELECT
                $columns
            FROM
                item I
                LEFT JOIN item_class IC
                    ON I.item_class_id = IC.item_class_id
                LEFT JOIN item_skill S
                    ON I.skill_id = S.skill_id
                LEFT JOIN attribute SAT
                    ON S.effect_target_attribute_id = SAT.attribute_id
                LEFT JOIN item_skill SA
                    ON I.skill_axe_id = SA.skill_id
                LEFT JOIN attribute SAAT
                    ON SA.effect_target_attribute_id = SAAT.attribute_id
                LEFT JOIN item_skill SS
                    ON I.skill_sword_id = SS.skill_id
                LEFT JOIN attribute SSAT
                    ON SS.effect_target_attribute_id = SSAT.attribute_id
                LEFT JOIN item_skill SD
                    ON I.skill_dagger_id = SD.skill_id
                LEFT JOIN attribute SDAT
                    ON SD.effect_target_attribute_id = SDAT.attribute_id
                LEFT JOIN item_attribute IA
                    ON I.item_id = IA.item_id
                LEFT JOIN attribute A
                    ON IA.attribute_id = A.attribute_id
            WHERE
                I.item_class_id IN($str_itemClassId)
            SQL;
        if (count($sql_rarity) > 0) {
            $sql .= "  AND I.rarity IN(" . implode(', ', $sql_rarity) . ")";
        }
        $sql .= <<<SQL
            ORDER BY
                I.sort_key
                , A.sort_key
                , IA.flactuable
            SQL;
        foreach ($itemClassIds as $key => $itemClassId) {
            $params[] = [
                'param' => ":itemClassId$key",
                'var' => $itemClassIds[$key], 'type' => PDO::PARAM_INT
            ];
        }
        foreach ($rarities as $key => $rarity) {
            $params[] = [
                'param' => ":rarity$key",
                'var' => $rarities[$key], 'type' => PDO::PARAM_STR
            ];
        }
        return $this->getItemsObject($sql, $params);
    }

    public function getCommonItemsByClassAndBaseItem(int $itemClassId, int $baseItemId)
    {
        $select = self::SQL_COLUMNS_FOR_ITEMS_WITH_ATTRS;
        $sql = <<<SQL
            SELECT
                $select
            FROM
                item I
                LEFT JOIN item_class IC
                    ON I.item_class_id = IC.item_class_id
                LEFT JOIN item_skill S
                    ON I.skill_id = S.skill_id
                LEFT JOIN attribute SAT
                    ON S.effect_target_attribute_id = SAT.attribute_id
                LEFT JOIN item_skill SA
                    ON I.skill_axe_id = SA.skill_id
                LEFT JOIN attribute SAAT
                    ON SA.effect_target_attribute_id = SAAT.attribute_id
                LEFT JOIN item_skill SS
                    ON I.skill_sword_id = SS.skill_id
                LEFT JOIN attribute SSAT
                    ON SS.effect_target_attribute_id = SSAT.attribute_id
                LEFT JOIN item_skill SD
                    ON I.skill_dagger_id = SD.skill_id
                LEFT JOIN attribute SDAT
                    ON SD.effect_target_attribute_id = SDAT.attribute_id
                LEFT JOIN item_attribute IA
                    ON I.item_id = IA.item_id
                LEFT JOIN attribute A
                    ON IA.attribute_id = A.attribute_id
            WHERE
                I.rarity = :rarity
                AND I.base_item_id = :baseItemId
            ORDER BY
                I.sort_key
                , A.sort_key
            SQL;
        $params[] = ['param' => 'rarity', 'var' => 'common', 'type' => PDO::PARAM_STR];
        $params[] = ['param' => 'baseItemId', 'var' => $baseItemId, 'type' => PDO::PARAM_INT];
        return $this->getItemsObject($sql, $params);
    }

    public function getItemByIdAndClass(int $itemId, string $itemClass)
    {
        $columns = self::SQL_COLUMNS_FOR_ITEMS_WITH_ATTRS;
        $sql = <<<SQL
            SELECT
                $columns
            FROM
                item I
                LEFT JOIN item_class IC
                    ON I.item_class_id = IC.item_class_id
                LEFT JOIN item_skill S
                    ON I.skill_id = S.skill_id
                LEFT JOIN attribute SAT
                    ON S.effect_target_attribute_id = SAT.attribute_id
                LEFT JOIN item_skill SA
                    ON I.skill_axe_id = SA.skill_id
                LEFT JOIN attribute SAAT
                    ON SA.effect_target_attribute_id = SAAT.attribute_id
                LEFT JOIN item_skill SS
                    ON I.skill_sword_id = SS.skill_id
                LEFT JOIN attribute SSAT
                    ON SS.effect_target_attribute_id = SSAT.attribute_id
                LEFT JOIN item_skill SD
                    ON I.skill_dagger_id = SD.skill_id
                LEFT JOIN attribute SDAT
                    ON SD.effect_target_attribute_id = SDAT.attribute_id
                LEFT JOIN item_attribute IA
                    ON I.item_id = IA.item_id
                LEFT JOIN attribute A
                    ON IA.attribute_id = A.attribute_id
            WHERE
                I.item_id = :itemId
                AND IC.name_en = :itemClassName
            SQL;
        $params = [
            ['param' => 'itemId', 'var' => $itemId, 'type' => PDO::PARAM_INT],
            ['param' => 'itemClassName', 'var' => $itemClass, 'type' => PDO::PARAM_STR]
        ];
        $itemObj = $this->getItemsObject($sql, $params);
        if (count($itemObj) == 0) {
            return $this->getNone($itemClass);
        } else {
            return $itemObj[0];
        }
    }

    public function getItemsByTag(string $tagUrl)
    {
        $columns = self::SQL_COLUMNS_FOR_ITEM_LINK_LIST;
        $sql = <<<SQL
            SELECT
                $columns
            FROM
                item I
                LEFT JOIN item_class IC
                    ON I.item_class_id = IC.item_class_id
                LEFT JOIN item_tag IT
                    ON I.item_id = IT.item_id
                LEFT JOIN tag T
                    ON IT.tag_id = T.tag_id
            WHERE
                T.tag_url = :tagUrl
            ORDER BY
                I.sort_key
            SQL;
        $params = [['param' => 'tagUrl', 'var' => $tagUrl, 'type' => PDO::PARAM_STR]];
        return $this->getItemLinkList($sql, $params);
    }

    public function getItemsByCreatureId(int $creatureId)
    {
        $columns = self::SQL_COLUMNS_FOR_ITEM_LINK_LIST;
        $sql = <<<SQL
            SELECT
                $columns
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
        $params = [['param' => 'id', 'var' => $creatureId, 'type' => PDO::PARAM_INT]];
        return $this->getItemLinkList($sql, $params);
    }

    public function getRareItemsByFloorId(int $floorId)
    {
        $columns = self::SQL_COLUMNS_FOR_ITEM_LINK_LIST;
        $rarity = self::RARITY_RARE;
        $sql = <<<SQL
            SELECT DISTINCT
                $columns
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
        $params = [
            ['param' => 'floor_id', 'var' => $floorId, 'type' => PDO::PARAM_INT],
            ['param' => 'rarity', 'var' => $rarity, 'type' => PDO::PARAM_STR]
        ];
        return $this->getItemLinkList($sql, $params);
    }

    public function getBananaItemsByFloorId(int $floorId)
    {
        $columns = self::SQL_COLUMNS_FOR_ITEM_LINK_LIST;
        $sql = <<<SQL
            SELECT DISTINCT
                $columns
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
        $params = [['param' => 'floor_id', 'var' => $floorId, 'type' => PDO::PARAM_INT]];
        return $this->getItemLinkList($sql, $params);
    }

    public function getTreasureItemsByFloorId(int $floorId)
    {
        $columns = self::SQL_COLUMNS_FOR_ITEM_LINK_LIST;
        $sql = <<<SQL
            SELECT
                $columns
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
                'item_class_id' => $result['item_class_id'],
                'item_class' => $result['item_class'],
                'base_item_id' => $result['base_item_id'],
                'class_flactuable' => $result['class_flactuable'],
                'name_en' => $result['name_en'],
                'name_ja' => $result['name_ja'],
                'rarity' => $result['rarity'],
                'image_name' => $result['image_name'],
                'note' => $result['note'],
            ];
        }
        return $items;
    }

    public function getQuestRequiredItemsByQuestId(int $questId)
    {
        $columns = self::SQL_COLUMNS_FOR_ITEM_LINK_LIST;
        $sql = <<<SQL
            SELECT
                $columns
            FROM
                quest_required_item QRI
                INNER JOIN item I
                    ON QRI.item_id = I.item_id
                INNER JOIN item_class IC
                    ON I.item_class_id = IC.item_class_id
            WHERE
                QRI.quest_id = :quest_id
            ORDER BY
                I.sort_key
                , I.item_id
            SQL;
        $params = [['param' => 'quest_id', 'var' => $questId, 'type' => PDO::PARAM_INT]];
        return $this->getItemLinkList($sql, $params);
    }

    public function getQuestRewardItemsByQuestId(int $questId)
    {
        $columns = self::SQL_COLUMNS_FOR_ITEM_LINK_LIST;
        $sql = <<<SQL
            SELECT
                $columns
            FROM
                quest_reward_item QRI
                INNER JOIN item I
                    ON QRI.item_id = I.item_id
                INNER JOIN item_class IC
                    ON I.item_class_id = IC.item_class_id
            WHERE
                QRI.quest_id = :quest_id
            ORDER BY
                I.sort_key
                , I.item_id
            SQL;
        $params = [['param' => 'quest_id', 'var' => $questId, 'type' => PDO::PARAM_INT]];
        return $this->getItemLinkList($sql, $params);
    }

    public function getShopItemsByShopId(int $shopId)
    {
        $columns = self::SQL_COLUMNS_FOR_ITEM_LINK_LIST;
        $sql = <<<SQL
            SELECT
                $columns
                , SI.price
            FROM
                shop_item SI
                INNER JOIN item I
                    ON SI.item_id = I.item_id
                INNER JOIN item_class IC
                    ON I.item_class_id = IC.item_class_id
            WHERE
                SI.shop_id = :shop_id
            ORDER BY
                IC.shop_sort_key
                , I.sort_key
            SQL;
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':shop_id', $shopId, PDO::PARAM_INT);
        $stmt->execute();
        $items = [];
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = [
                'item_id' => $result['item_id'],
                'item_class_id' => $result['item_class_id'],
                'item_class' => $result['item_class'],
                'base_item_id' => $result['base_item_id'],
                'class_flactuable' => $result['class_flactuable'],
                'name_en' => $result['name_en'],
                'name_ja' => $result['name_ja'],
                'rarity' => $result['rarity'],
                'image_name' => $result['image_name'],
                'price' => $result['price'],
            ];
        }
        return $items;
    }

    public function getNone($itemClass)
    {
        return [
            'name_en' => 'None',
            'rarity' => 'common',
            'image_name' => self::DEFAULT_IMG[$itemClass],
            'attributes' => []
        ];
    }

    private function getItemsObject(string $sql, array $params)
    {
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        foreach ($params as $param) {
            $stmt->bindParam($param['param'], $param['var'], $param['type']);
        }
        $stmt->execute();
        $items = array();
        $item = array();
        $prevItemId = null;
        $charaClass = ['A' => 'axe', 'S' => 'sword', 'D' => 'dagger'];

        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($result['item_id'] !== $prevItemId) {
                if (!empty($item)) $items[] = $item;
                $item['item_id'] = $result['item_id'];
                $item['item_class_id'] = $result['item_class_id'];
                $item['item_class_name'] = strtolower($result['item_class_name']);
                $item['image_name'] = ($result['image_name'] === null)
                    ? "item_noimg.png" : $result['image_name'];
                $item['name_en'] = $result['name_en'];
                $item['name_ja'] = $result['name_ja'];
                $item['rarity'] = $result['rarity'];
                $item['skill_en'] = $result['skill_en'];
                $item['skill_axe_en'] = $result['skill_axe_en'];
                $item['skill_sword_en'] = $result['skill_sword_en'];
                $item['skill_dagger_en'] = $result['skill_dagger_en'];
                if ($result['skill_id'] === null) {
                    $item['skill'] = null;
                } else {
                    $item['skill'] = array();
                    $item['skill']['id'] = $result['skill_id'];
                    $item['skill']['name'] = $result['skill_name'];
                    $item['skill']['trigger_type'] = $result['skill_trigger_type'];
                    $item['skill']['kill_trigger_type'] = $result['skill_kill_trigger_type'];
                    $item['skill']['activation_rate'] = $result['skill_activation_rate'];
                    $item['skill']['trigger_charge'] = $result['skill_trigger_charge'];
                    $item['skill']['effect_type'] = $result['skill_effect_type'];
                    $item['skill']['effect_target_attribute'] = strtolower($result['skill_effect_target_attribute']);
                    $item['skill']['effect_amount'] = $result['skill_effect_amount'];
                    $item['skill']['effect_duration'] = $result['skill_effect_duration'];
                    $item['skill']['sort_key'] = $result['skill_sort_key'];
                    $item['skill']['enabled'] = false;
                }
                if ($result['skill_axe_id'] === null) {
                    $item['skill_axe'] = null;
                } else {
                    $item['skill_axe'] = array();
                    $item['skill_axe']['id'] = $result['skill_axe_id'];
                    $item['skill_axe']['name'] = $result['skill_axe_name'];
                    $item['skill_axe']['trigger_type'] = $result['skill_axe_trigger_type'];
                    $item['skill_axe']['kill_trigger_type'] = $result['skill_axe_kill_trigger_type'];
                    $item['skill_axe']['activation_rate'] = $result['skill_axe_activation_rate'];
                    $item['skill_axe']['trigger_charge'] = $result['skill_axe_trigger_charge'];
                    $item['skill_axe']['effect_type'] = $result['skill_axe_effect_type'];
                    $item['skill_axe']['effect_target_attribute'] = strtolower($result['skill_axe_effect_target_attribute']);
                    $item['skill_axe']['effect_amount'] = $result['skill_axe_effect_amount'];
                    $item['skill_axe']['effect_duration'] = $result['skill_axe_effect_duration'];
                    $item['skill_axe']['sort_key'] = $result['skill_axe_sort_key'];
                    $item['skill_axe']['enabled'] = false;
                }
                if ($result['skill_sword_id'] === null) {
                    $item['skill_sword'] = null;
                } else {
                    $item['skill_sword'] = array();
                    $item['skill_sword']['id'] = $result['skill_sword_id'];
                    $item['skill_sword']['name'] = $result['skill_sword_name'];
                    $item['skill_sword']['trigger_type'] = $result['skill_sword_trigger_type'];
                    $item['skill_sword']['kill_trigger_type'] = $result['skill_sword_kill_trigger_type'];
                    $item['skill_sword']['activation_rate'] = $result['skill_sword_activation_rate'];
                    $item['skill_sword']['trigger_charge'] = $result['skill_sword_trigger_charge'];
                    $item['skill_sword']['effect_type'] = $result['skill_sword_effect_type'];
                    $item['skill_sword']['effect_target_attribute'] = strtolower($result['skill_sword_effect_target_attribute']);
                    $item['skill_sword']['effect_amount'] = $result['skill_sword_effect_amount'];
                    $item['skill_sword']['effect_duration'] = $result['skill_sword_effect_duration'];
                    $item['skill_sword']['sort_key'] = $result['skill_sword_sort_key'];
                    $item['skill_sword']['enabled'] = false;
                }
                if ($result['skill_dagger_id'] === null) {
                    $item['skill_dagger'] = null;
                } else {
                    $item['skill_dagger'] = array();
                    $item['skill_dagger']['id'] = $result['skill_dagger_id'];
                    $item['skill_dagger']['name'] = $result['skill_dagger_name'];
                    $item['skill_dagger']['trigger_type'] = $result['skill_dagger_trigger_type'];
                    $item['skill_dagger']['kill_trigger_type'] = $result['skill_dagger_kill_trigger_type'];
                    $item['skill_dagger']['activation_rate'] = $result['skill_dagger_activation_rate'];
                    $item['skill_dagger']['trigger_charge'] = $result['skill_dagger_trigger_charge'];
                    $item['skill_dagger']['effect_type'] = $result['skill_dagger_effect_type'];
                    $item['skill_dagger']['effect_target_attribute'] = strtolower($result['skill_dagger_effect_target_attribute']);
                    $item['skill_dagger']['effect_amount'] = $result['skill_dagger_effect_amount'];
                    $item['skill_dagger']['effect_duration'] = $result['skill_dagger_effect_duration'];
                    $item['skill_dagger']['sort_key'] = $result['skill_dagger_sort_key'];
                    $item['skill_dagger']['enabled'] = false;
                }
                $item['comment_en'] = $result['comment_en'];
                $item['comment_ja'] = $result['comment_ja'];
                $item['sort_key'] = $result['sort_key'];
                $item['price'] = $result['price'];
                $item['attributes'] = array();
            }

            $prevItemId = $result['item_id'];
            $attribute = array();
            $attribute['color'] = $result['color'];
            $attribute['short_name'] = $result['short_name'];

            if ($result['short_name'] === null) continue;

            $attribute['based_source'] = $result['based_source'];
            $attribute['value'] = $result['attribute_value'];
            $attribute['value_axe'] = $result['attribute_value_axe'];
            $attribute['value_sword'] = $result['attribute_value_sword'];
            $attribute['value_dagger'] = $result['attribute_value_dagger'];
            $attribute['max_required'] = $result['max_required'];
            $attribute['max_required_axe'] = $result['max_required_axe'];
            $attribute['max_required_sword'] = $result['max_required_sword'];
            $attribute['max_required_dagger'] = $result['max_required_dagger'];

            if ($attribute['short_name'] == 'MinAD' || $attribute['short_name'] == 'MaxAD') {
                if ($attribute['short_name'] == 'MaxAD') {
                    $minAD = $item['attributes'][count($item['attributes']) - 1];
                    $ad = array();
                    $ad['color'] = $minAD['color'];
                    $ad['short_name'] = 'AD';
                    $attr_val = '';
                    if ($result['attribute_value'] === null) {
                        foreach ($charaClass as $key => $name) {
                            if ($key != 'A') $attr_val .= ' / ';
                            $attr_val .= $key . ':'
                                . $minAD['value_' . $name]
                                . '～'
                                . $result['attribute_value_' . $name];
                        }
                    } else {
                        $attr_val .= $minAD['value']
                            . '～'
                            . $result['attribute_value'];
                    }
                    $ad['attribute_value'] = $attr_val;
                    $item['attributes'][] = $ad;
                }
                $item['attributes'][] = $attribute;
                continue;
            }

            $attr_val = '';

            if ($result['flactuable']) {
                $attr_val .= 'Based on '
                    . ($result['based_source'] === 'xp' ? 'XP' : 'Kills') . ' [';
            }

            if ($result['attribute_value'] === null) {
                foreach ($charaClass as $key => $name) {
                    if ($key != 'A') $attr_val .= ' / ';
                    $attr_val .= $key . ':'
                        . ($result['attribute_value_' . $name] === '0' ? '?' : $result['attribute_value_' . $name])
                        . ($result['unit'] === null ? '' : $result['unit']);
                    if ($result['flactuable']) {
                        $attr_val .= " ("
                            . ($result['max_required_' . $name] === '0' ? '?' : $result['max_required_' . $name]) . ' '
                            . ($result['based_source'] === 'xp' ? 'XP' : 'Kills') . ")";
                    }
                }
            } else {
                $attr_val .= ($result['attribute_value'] === '0' ? '?' : $result['attribute_value'])
                    . ($result['unit'] === null ? '' : $result['unit']);
                if ($result['flactuable']) {
                    $attr_val .= " ("
                        . ($result['max_required'] === '0' ? '?' : $result['max_required']) . ' '
                        . ($result['based_source'] === 'xp' ? 'XP' : 'Kills') . ")";
                }
            }

            if ($result['flactuable']) {
                $attr_val .= ']';
            }

            $attribute['attribute_value'] = $attr_val;
            $item['attributes'][] = $attribute;
        }

        if (!empty($item)) $items[] = $item;
        return $items;
    }

    private function getItemLinkList(string $sql, array $params)
    {
        $this->logger->debug($sql);
        $stmt = $this->db->prepare($sql);
        foreach ($params as $param) {
            $stmt->bindParam($param['param'], $param['var'], $param['type']);
        }
        $stmt->execute();
        $items = [];
        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $items[] = [
                'item_id' => $result['item_id'],
                'item_class_id' => $result['item_class_id'],
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
}
