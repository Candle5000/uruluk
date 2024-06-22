-- Project Name : Uruluk
-- Date/Time    : 2024/06/12 14:23:31
-- Author       : candl
-- RDBMS Type   : MySQL
-- Application  : A5:SQL Mk-2

/*
  << 注意！！ >>
  BackupToTempTable, RestoreFromTempTable疑似命令が付加されています。
  これにより、drop table, create table 後もデータが残ります。
  この機能は一時的に $$TableName のような一時テーブルを作成します。
  この機能は A5:SQL Mk-2でのみ有効であることに注意してください。
*/

-- アクセスカウント
-- * BackupToTempTable
drop table if exists `access_count` cascade;

-- * RestoreFromTempTable
create table `access_count` (
  `page_id` INT not null comment 'ページID'
  , `count_date` DATE not null comment 'カウント日付'
  , `pv_count` INT default 0 not null comment 'PVカウント'
  , constraint `access_count_PKC` primary key (`page_id`,`count_date`)
) comment 'アクセスカウント' ;

-- 性能
-- * BackupToTempTable
drop table if exists `attribute` cascade;

-- * RestoreFromTempTable
create table `attribute` (
  `attribute_id` INT not null AUTO_INCREMENT comment '性能ID'
  , `short_name_key` VARCHAR(128) comment '略称キー'
  , `name_key` VARCHAR(128) comment '名称キー'
  , `short_name` VARCHAR(8) comment '略称'
  , `name_en` VARCHAR(32) comment '名称(英語)'
  , `name_ja` VARCHAR(32) comment '名称(日本語)'
  , `unit_key` VARCHAR(128) comment '単位キー'
  , `unit` VARCHAR(16) comment '単位'
  , `sort_key` INT not null comment 'ソート順'
  , constraint `attribute_PKC` primary key (`attribute_id`)
) comment '性能' ;

alter table `attribute` add unique `attribute_IX1` (`sort_key`) ;

-- ベースアイテム
-- * BackupToTempTable
drop table if exists `base_item` cascade;

-- * RestoreFromTempTable
create table `base_item` (
  `base_item_id` INT not null AUTO_INCREMENT comment 'ベースアイテムID'
  , `item_class_id` INT not null comment 'アイテムクラスID'
  , `name_key` VARCHAR(32) comment '名称キー'
  , `name_en` VARCHAR(32) comment '名称(英語)'
  , `name_ja` VARCHAR(32) comment '名称(日本語)'
  , `image_name` VARCHAR(64) comment '画像名称'
  , `sort_key` VARCHAR(16) not null comment 'ソート順'
  , constraint `base_item_PKC` primary key (`base_item_id`)
) comment 'ベースアイテム' ;

alter table `base_item` add unique `base_item_IX1` (`sort_key`) ;

-- クリーチャー
-- * BackupToTempTable
drop table if exists `creature` cascade;

-- * RestoreFromTempTable
create table `creature` (
  `creature_id` INT not null AUTO_INCREMENT comment 'クリーチャーID'
  , `boss` BIT(1) default FALSE not null comment 'ボス'
  , `name_key` VARCHAR(128) comment '名称キー'
  , `name_en` VARCHAR(32) comment '名称(英語)'
  , `name_ja` VARCHAR(32) comment '名称(日本語)'
  , `min_ad` INT comment '最小攻撃力'
  , `max_ad` INT comment '最大攻撃力'
  , `as` INT comment '攻撃速度'
  , `str` INT comment '筋力'
  , `def` INT comment '防御力'
  , `dex` INT comment '技量'
  , `vit` INT comment '生命力'
  , `ws` INT comment '移動速度'
  , `voh` INT comment '生命力吸収'
  , `dr` INT comment 'ダメージ反射'
  , `xp` INT comment '経験値'
  , `tb` BIT(1) default FALSE not null comment '結界地出現'
  , `tb_ad` DECIMAL(10,1) comment '結界地補正 攻撃力'
  , `tb_as` DECIMAL(10,1) comment '結界地補正 攻撃速度'
  , `tb_str` DECIMAL(10,1) comment '結界地補正 筋力'
  , `tb_def` DECIMAL(10,1) comment '結界地補正 防御力'
  , `tb_dex` DECIMAL(10,1) comment '結界地補正 技量'
  , `tb_vit` DECIMAL(10,1) comment '結界地補正 生命力'
  , `tb_ws` DECIMAL(10,1) comment '結界地補正 移動速度'
  , `tb_voh` DECIMAL(10,1) comment '結界地補正 生命力吸収'
  , `tb_dr` DECIMAL(10,1) comment '結界地補正 ダメージ反射'
  , `tb_xp` DECIMAL(10,1) comment '結界地補正 経験値'
  , `note` TEXT comment '説明'
  , `image_name` VARCHAR(32) comment '画像名称'
  , `sort_key` INT not null comment 'ソート順'
  , constraint `creature_PKC` primary key (`creature_id`)
) comment 'クリーチャー' ;

create unique index `creature_IX1`
  on `creature`(`boss`,`sort_key`);

-- クリーチャードロップアイテム
-- * BackupToTempTable
drop table if exists `creature_drop_item` cascade;

-- * RestoreFromTempTable
create table `creature_drop_item` (
  `creature_id` INT not null comment 'クリーチャーID'
  , `item_id` INT not null comment 'アイテムID'
  , constraint `creature_drop_item_PKC` primary key (`creature_id`,`item_id`)
) comment 'クリーチャードロップアイテム' ;

alter table `creature_drop_item` add unique `creature_drop_item_IX1` (`item_id`,`creature_id`) ;

-- クリーチャー出現条件
-- * BackupToTempTable
drop table if exists `creature_pop_event` cascade;

-- * RestoreFromTempTable
create table `creature_pop_event` (
  `event_id` INT not null AUTO_INCREMENT comment 'イベントID'
  , `note` TEXT comment '説明'
  , `description_key` VARCHAR(128) comment '説明キー'
  , constraint `creature_pop_event_PKC` primary key (`event_id`)
) comment 'クリーチャー出現条件' ;

-- クリーチャースペシャルアタック
-- * BackupToTempTable
drop table if exists `creature_special_attack` cascade;

-- * RestoreFromTempTable
create table `creature_special_attack` (
  `creature_id` INT not null comment 'クリーチャーID'
  , `special_attack_id` INT not null comment 'スペシャルアタックID'
  , constraint `creature_special_attack_PKC` primary key (`creature_id`,`special_attack_id`)
) comment 'クリーチャースペシャルアタック' ;

-- ドロップアイテムグループ
-- * BackupToTempTable
drop table if exists `drop_item_group` cascade;

-- * RestoreFromTempTable
create table `drop_item_group` (
  `drop_item_group_id` INT not null comment 'ドロップアイテムグループID'
  , `item_id` INT not null comment 'アイテムID'
  , constraint `drop_item_group_PKC` primary key (`drop_item_group_id`,`item_id`)
) comment 'ドロップアイテムグループ' ;

-- ドロップアイテムグループ名
-- * BackupToTempTable
drop table if exists `drop_item_group_name` cascade;

-- * RestoreFromTempTable
create table `drop_item_group_name` (
  `drop_item_group_id` INT not null AUTO_INCREMENT comment 'ドロップアイテムグループID'
  , `label` VARCHAR(64) comment 'ラベル'
  , constraint `drop_item_group_name_PKC` primary key (`drop_item_group_id`)
) comment 'ドロップアイテムグループ名' ;

-- フロア
-- * BackupToTempTable
drop table if exists `floor` cascade;

-- * RestoreFromTempTable
create table `floor` (
  `floor_id` INT not null AUTO_INCREMENT comment 'フロアID'
  , `floor_group_id` INT not null comment 'フロアグループID'
  , `short_name` VARCHAR(16) comment '略称'
  , `name_key` VARCHAR(128) comment '名称キー'
  , `name_en` VARCHAR(64) comment '名称(英語)'
  , `name_ja` VARCHAR(64) comment '名称(日本語)'
  , `image_name` VARCHAR(64) comment '画像名称'
  , `image_size` VARCHAR(16) comment '画像サイズ'
  , `sort_key` INT not null comment 'ソート順'
  , constraint `floor_PKC` primary key (`floor_id`)
) comment 'フロア' ;

alter table `floor` add unique `floor_IX1` (`sort_key`) ;

-- フロアバナナドロップグループ
-- * BackupToTempTable
drop table if exists `floor_banana_drop_group` cascade;

-- * RestoreFromTempTable
create table `floor_banana_drop_group` (
  `floor_id` INT not null comment 'フロアID'
  , `drop_item_group_id` INT not null comment 'ドロップアイテムグループID'
  , constraint `floor_banana_drop_group_PKC` primary key (`floor_id`,`drop_item_group_id`)
) comment 'フロアバナナドロップグループ' ;

-- フロアバナナアイテム
-- * BackupToTempTable
drop table if exists `floor_banana_item` cascade;

-- * RestoreFromTempTable
create table `floor_banana_item` (
  `floor_id` INT not null comment 'フロアID'
  , `item_id` INT not null comment 'アイテムID'
  , constraint `floor_banana_item_PKC` primary key (`floor_id`,`item_id`)
) comment 'フロアバナナアイテム' ;

-- フロアクリーチャー
-- * BackupToTempTable
drop table if exists `floor_creature` cascade;

-- * RestoreFromTempTable
create table `floor_creature` (
  `floor_id` INT not null comment 'フロアID'
  , `event_id` INT default 0 not null comment 'イベントID'
  , `creature_id` INT not null comment 'クリーチャーID'
  , constraint `floor_creature_PKC` primary key (`floor_id`,`event_id`,`creature_id`)
) comment 'フロアクリーチャー' ;

create unique index `floor_creature_IX1`
  on `floor_creature`(`creature_id`,`floor_id`,`event_id`);

-- フロア移動先
-- * BackupToTempTable
drop table if exists `floor_destination` cascade;

-- * RestoreFromTempTable
create table `floor_destination` (
  `floor_id` INT not null comment '移動元フロアID'
  , `destination_floor_id` INT not null comment '移動先フロアID'
  , constraint `floor_destination_PKC` primary key (`floor_id`,`destination_floor_id`)
) comment 'フロア移動先' ;

-- フロアドロップグループ
-- * BackupToTempTable
drop table if exists `floor_drop_group` cascade;

-- * RestoreFromTempTable
create table `floor_drop_group` (
  `floor_id` INT not null comment 'フロアID'
  , `drop_item_group_id` INT not null comment 'ドロップアイテムグループID'
  , constraint `floor_drop_group_PKC` primary key (`floor_id`,`drop_item_group_id`)
) comment 'フロアドロップグループ' ;

-- フロアドロップアイテム
-- * BackupToTempTable
drop table if exists `floor_drop_item` cascade;

-- * RestoreFromTempTable
create table `floor_drop_item` (
  `floor_id` INT not null comment 'フロアID'
  , `item_id` INT not null comment 'アイテムID'
  , constraint `floor_drop_item_PKC` primary key (`floor_id`,`item_id`)
) comment 'フロアドロップアイテム' ;

-- フロアグループ
-- * BackupToTempTable
drop table if exists `floor_group` cascade;

-- * RestoreFromTempTable
create table `floor_group` (
  `floor_group_id` INT not null AUTO_INCREMENT comment 'フロアグループID'
  , `name_key` VARCHAR(128) comment '名称キー'
  , `name_en` VARCHAR(32) comment '名称(英語)'
  , `name_ja` VARCHAR(32) comment '名称(日本語)'
  , `sort_key` INT not null comment 'ソート順'
  , constraint `floor_group_PKC` primary key (`floor_group_id`)
) comment 'フロアグループ' ;

alter table `floor_group` add unique `floor_group_IX1` (`sort_key`) ;

-- フロア宝箱
-- * BackupToTempTable
drop table if exists `floor_treasure` cascade;

-- * RestoreFromTempTable
create table `floor_treasure` (
  `floor_id` INT not null comment 'フロアID'
  , `item_id` INT not null comment 'アイテムID'
  , `note` VARCHAR(256) comment '備考'
  , `description_key` VARCHAR(128) comment '説明キー'
  , constraint `floor_treasure_PKC` primary key (`floor_id`,`item_id`)
) comment 'フロア宝箱' ;

-- 多言語メッセージ
-- * BackupToTempTable
drop table if exists `i18n_message` cascade;

-- * RestoreFromTempTable
create table `i18n_message` (
  `language_code` VARCHAR(8) not null comment '言語コード'
  , `category` VARCHAR(64) not null comment 'カテゴリ'
  , `message_key` VARCHAR(64) not null comment 'キー'
  , `message` VARCHAR(256) not null comment 'メッセージ'
  , constraint `i18n_message_PKC` primary key (`language_code`,`category`,`message_key`)
) comment '多言語メッセージ' ;

-- アイテム
-- * BackupToTempTable
drop table if exists `item` cascade;

-- * RestoreFromTempTable
create table `item` (
  `item_id` INT not null AUTO_INCREMENT comment 'アイテムID'
  , `item_class_id` INT not null comment 'アイテムクラスID'
  , `base_item_id` INT comment 'ベースアイテムID'
  , `brand_id` INT comment 'ブランドID'
  , `class_flactuable` BIT(1) default 0 not null comment 'クラス変動'
  , `name_key` VARCHAR(128) comment '名称キー'
  , `name_en` VARCHAR(64) comment '名称(英語)'
  , `name_ja` VARCHAR(64) comment '名称(日本語)'
  , `rarity` ENUM('common', 'rare', 'artifact') not null comment 'レアリティ'
  , `skill_id` INT comment 'スキルID'
  , `skill_en` VARCHAR(128) comment 'スキル(英語)'
  , `skill_ja` VARCHAR(64) comment 'スキル(日本語)'
  , `skill_axe_id` INT comment 'スキルアックスID'
  , `skill_sword_id` INT comment 'スキルソードID'
  , `skill_dagger_id` INT comment 'スキルダガーID'
  , `skill_axe_en` VARCHAR(128) comment 'スキルアックス(英語)'
  , `skill_sword_en` VARCHAR(128) comment 'スキルソード(英語)'
  , `skill_dagger_en` VARCHAR(128) comment 'スキルダガー(英語)'
  , `description_key` VARCHAR(128) comment '説明文キー'
  , `comment_en` VARCHAR(64) comment 'コメント(英語)'
  , `comment_ja` VARCHAR(64) comment 'コメント(日本語)'
  , `note` TEXT comment '説明'
  , `price` INT comment '売却価格'
  , `image_name` VARCHAR(64) comment '画像名称'
  , `sort_key` VARCHAR(16) not null comment 'ソート順'
  , constraint `item_PKC` primary key (`item_id`)
) comment 'アイテム' ;

alter table `item` add unique `item_IX1` (`item_class_id`,`sort_key`) ;

create unique index `item_IX2`
  on `item`(`item_class_id`,`rarity`,`sort_key`);

-- アイテム性能
-- * BackupToTempTable
drop table if exists `item_attribute` cascade;

-- * RestoreFromTempTable
create table `item_attribute` (
  `item_id` INT not null comment 'アイテムID'
  , `attribute_id` INT not null comment '性能ID'
  , `flactuable` BIT(1) not null comment '変動'
  , `based_source` ENUM('xp', 'kills') comment '変動元'
  , `color` ENUM('white', 'yellow', 'red') not null comment '色'
  , `attribute_value` INT comment '性能値'
  , `attribute_value_axe` INT comment '性能値アックス'
  , `attribute_value_sword` INT comment '性能値ソード'
  , `attribute_value_dagger` INT comment '性能値ダガー'
  , `max_required` INT comment '変動最大要求値'
  , `max_required_axe` INT comment '変動最大要求値アックス'
  , `max_required_sword` INT comment '変動最大要求値ソード'
  , `max_required_dagger` INT comment '変動最大要求値ダガー'
  , constraint `item_attribute_PKC` primary key (`item_id`,`attribute_id`,`flactuable`)
) comment 'アイテム性能' ;

-- アイテムブランド
-- * BackupToTempTable
drop table if exists `item_brand` cascade;

-- * RestoreFromTempTable
create table `item_brand` (
  `brand_id` INT not null AUTO_INCREMENT comment 'ブランドID'
  , `name_en` VARCHAR(32) comment '名称(英語)'
  , `name_ja` VARCHAR(32) comment '名称(日本語)'
  , `sort_key` INT not null comment 'ソート順'
  , constraint `item_brand_PKC` primary key (`brand_id`)
) comment 'アイテムブランド' ;

alter table `item_brand` add unique `item_brand_IX1` (`sort_key`) ;

-- アイテムクラス
-- * BackupToTempTable
drop table if exists `item_class` cascade;

-- * RestoreFromTempTable
create table `item_class` (
  `item_class_id` INT not null AUTO_INCREMENT comment 'アイテムクラスID'
  , `name_key` VARCHAR(128) comment '名称キー'
  , `name_en` VARCHAR(32) comment '名称(英語)'
  , `name_ja` VARCHAR(32) comment '名称(日本語)'
  , `image_name` VARCHAR(64) comment '画像名称'
  , `sort_key` INT not null comment 'ソート順'
  , `shop_sort_key` INT not null comment 'ショップソート順'
  , constraint `item_class_PKC` primary key (`item_class_id`)
) comment 'アイテムクラス' ;

alter table `item_class` add unique `item_class_IX1` (`sort_key`) ;

alter table `item_class` add unique `item_class_IX2` (`shop_sort_key`) ;

-- アイテムスキル
-- * BackupToTempTable
drop table if exists `item_skill` cascade;

-- * RestoreFromTempTable
create table `item_skill` (
  `skill_id` INT AUTO_INCREMENT comment 'アイテムスキルID'
  , `name` VARCHAR(64) not null comment 'スキル名'
  , `trigger_type` ENUM('attack', 'kill', 'damage') not null comment 'トリガー種別'
  , `kill_trigger_type` ENUM('any', 'dr', 'sa') comment '討伐トリガー種別'
  , `activation_rate` INT not null comment '発動率(%)'
  , `trigger_charge` INT not null comment 'トリガーチャージ'
  , `effect_type` ENUM('heal', 'attribute', 'reduce', 'double', 'splash', 'missile', 'lightning') not null comment '効果種別'
  , `effect_target_attribute_id` INT comment '対象スタッツ'
  , `effect_amount` INT comment '効果量(%)'
  , `effect_duration` INT comment '効果時間(sec)'
  , `description_key` VARCHAR(128) comment '説明文キー'
  , `description_arg_1` INT comment '説明文引数1'
  , `description_arg_2` INT comment '説明文引数2'
  , `description_arg_3` INT comment '説明文引数3'
  , `sort_key` INT not null comment 'ソート順'
  , constraint `item_skill_PKC` primary key (`skill_id`)
) comment 'アイテムスキル' ;

alter table `item_skill` add unique `item_skill_IX1` (`name`) ;

alter table `item_skill` add unique `item_skill_IX2` (`sort_key`) ;

-- アイテムタグ
-- * BackupToTempTable
drop table if exists `item_tag` cascade;

-- * RestoreFromTempTable
create table `item_tag` (
  `item_id` INT not null comment 'アイテムID'
  , `tag_id` INT not null comment 'タグID'
  , constraint `item_tag_PKC` primary key (`item_id`,`tag_id`)
) comment 'アイテムタグ' ;

-- お知らせ
-- * BackupToTempTable
drop table if exists `news` cascade;

-- * RestoreFromTempTable
create table `news` (
  `post_date` DATETIME not null comment '投稿日時'
  , `subject` VARCHAR(256) comment '件名'
  , `subject_en` VARCHAR(256) comment '件名(英語)'
  , `subject_zh_cn` VARCHAR(256) comment '件名(簡体中文)'
  , `subject_zh_tw` VARCHAR(256) comment '件名(繫體中文)'
  , `content` TEXT comment '本文'
  , `content_en` TEXT comment '本文(英語)'
  , `content_zh_cn` TEXT comment '本文(簡体中文)'
  , `content_zh_tw` TEXT comment '本文(繫體中文)'
  , constraint `news_PKC` primary key (`post_date`)
) comment 'お知らせ' ;

-- クエスト
-- * BackupToTempTable
drop table if exists `quest` cascade;

-- * RestoreFromTempTable
create table `quest` (
  `quest_id` INT not null AUTO_INCREMENT comment 'クエストID'
  , `floor_id` INT not null comment 'フロアID'
  , `repeatable` BIT(1) not null comment '繰り返し可能'
  , `autosave` BIT(1) not null comment '自動セーブ'
  , `required_items_description_key` VARCHAR(128) comment '要求アイテム説明キー'
  , `required_items_note` VARCHAR(64) comment '要求アイテム備考'
  , `reward_items_description_key` VARCHAR(128) comment '報酬アイテム説明キー'
  , `reward_items_note` VARCHAR(64) comment '報酬アイテム備考'
  , `reward_common_items` BIT(1) not null comment '報酬コモンアイテム'
  , `description_key` VARCHAR(128) comment '説明キー'
  , `note` VARCHAR(256) comment '備考'
  , constraint `quest_PKC` primary key (`quest_id`)
) comment 'クエスト' ;

-- クエストアイコン
-- * BackupToTempTable
drop table if exists `quest_icon` cascade;

-- * RestoreFromTempTable
create table `quest_icon` (
  `quest_id` INT not null comment 'クエストID'
  , `quest_reward` BIT(1) not null comment 'クエスト報酬'
  , `quest_icon_id` INT not null comment 'クエストアイコンID'
  , `image_path` VARCHAR(64) not null comment '画像パス'
  , constraint `quest_icon_PKC` primary key (`quest_id`,`quest_reward`,`quest_icon_id`)
) comment 'クエストアイコン' ;

-- クエスト要求アイテム
-- * BackupToTempTable
drop table if exists `quest_required_item` cascade;

-- * RestoreFromTempTable
create table `quest_required_item` (
  `quest_id` INT not null comment 'クエストID'
  , `item_id` INT not null comment 'アイテムID'
  , constraint `quest_required_item_PKC` primary key (`quest_id`,`item_id`)
) comment 'クエスト要求アイテム' ;

-- クエスト報酬アイテム
-- * BackupToTempTable
drop table if exists `quest_reward_item` cascade;

-- * RestoreFromTempTable
create table `quest_reward_item` (
  `quest_id` INT not null comment 'クエストID'
  , `item_id` INT not null comment 'アイテムID'
  , constraint `quest_reward_item_PKC` primary key (`quest_id`,`item_id`)
) comment 'クエスト報酬アイテム' ;

-- ショップ
-- * BackupToTempTable
drop table if exists `shop` cascade;

-- * RestoreFromTempTable
create table `shop` (
  `shop_id` INT not null AUTO_INCREMENT comment 'ショップID'
  , `floor_id` INT not null comment 'フロアID'
  , `name` VARCHAR(64) not null comment '名称'
  , `image_name` VARCHAR(64) comment '画像名称'
  , `random` BIT(1) not null comment 'ランダム'
  , `description_key` VARCHAR(128) comment '説明キー'
  , `note` VARCHAR(256) comment '備考'
  , constraint `shop_PKC` primary key (`shop_id`)
) comment 'ショップ' ;

-- ショップアイテム
-- * BackupToTempTable
drop table if exists `shop_item` cascade;

-- * RestoreFromTempTable
create table `shop_item` (
  `shop_id` INT not null comment 'ショップID'
  , `item_id` INT not null comment 'アイテムID'
  , `price` INT not null comment '販売価格'
  , constraint `shop_item_PKC` primary key (`shop_id`,`item_id`)
) comment 'ショップアイテム' ;

-- 短縮URL
-- * BackupToTempTable
drop table if exists `short_url` cascade;

-- * RestoreFromTempTable
create table `short_url` (
  `short_url_key` VARCHAR(6) not null comment '短縮URLキー'
  , `url` VARCHAR(511) not null comment 'URL'
  , `created_at` DATETIME comment '作成日時'
  , `last_access` DATETIME comment '最終アクセス'
  , constraint `short_url_PKC` primary key (`short_url_key`)
) comment '短縮URL' DEFAULT CHARSET=latin1;

alter table `short_url` add unique `short_url_IX1` (`url`) ;

-- スペシャルアタック
-- * BackupToTempTable
drop table if exists `special_attack` cascade;

-- * RestoreFromTempTable
create table `special_attack` (
  `special_attack_id` INT not null AUTO_INCREMENT comment 'スペシャルアタックID'
  , `name` VARCHAR(32) comment '名称'
  , `cooldown` INT comment '再使用'
  , `note` TEXT comment '説明'
  , constraint `special_attack_PKC` primary key (`special_attack_id`)
) comment 'スペシャルアタック' ;

-- タグ
-- * BackupToTempTable
drop table if exists `tag` cascade;

-- * RestoreFromTempTable
create table `tag` (
  `tag_id` INT not null AUTO_INCREMENT comment 'タグID'
  , `tag_url` VARCHAR(32) not null comment 'タグURL'
  , `tag_name` VARCHAR(100) comment 'タグ名'
  , `sort_key` VARCHAR(16) not null comment 'ソート順'
  , constraint `tag_PKC` primary key (`tag_id`)
) comment 'タグ' ;

alter table `tag` add unique `tag_IX1` (`tag_url`) ;

alter table `tag` add unique `tag_IX2` (`sort_key`) ;

-- Urulukユーザ
-- * BackupToTempTable
drop table if exists `uruluk_user` cascade;

-- * RestoreFromTempTable
create table `uruluk_user` (
  `user_id` INT not null AUTO_INCREMENT comment 'ユーザID'
  , `login_id` VARCHAR(64) not null comment 'ログインID'
  , `user_name` VARCHAR(64) not null comment 'ユーザ名'
  , `mail_address` VARCHAR(256) comment 'メールアドレス'
  , `login_password` CHAR(64) comment 'パスワード'
  , `google_id` VARCHAR(256) comment 'GoogleID'
  , `facebook_id` VARCHAR(256) comment 'FacebookID'
  , `twitter_id` VARCHAR(256) comment 'TwitterID'
  , `permission_level` INT default 1 not null comment '権限レベル'
  , `last_login_datetime` DATETIME comment '最終ログイン日時'
  , `created_datetime` DATETIME comment '作成日時'
  , constraint `uruluk_user_PKC` primary key (`user_id`)
) comment 'Urulukユーザ' ;

