-- Project Name : Uruluk
-- Date/Time    : 2019/07/06 21:01:59
-- Author       : Candle
-- RDBMS Type   : MySQL
-- Application  : A5:SQL Mk-2

/*
  BackupToTempTable, RestoreFromTempTable疑似命令が付加されています。
  これにより、drop table, create table 後もデータが残ります。
  この機能は一時的に $$TableName のような一時テーブルを作成します。
*/

-- アクセスカウント
--* BackupToTempTable
drop table if exists access_count cascade;

--* RestoreFromTempTable
create table access_count (
  page_id INT not null comment 'ページID'
  , count_date DATE not null comment 'カウント日付'
  , pv_count INT default 0 not null comment 'PVカウント'
  , constraint access_count_PKC primary key (page_id,count_date)
) comment 'アクセスカウント' ;

-- 性能
--* BackupToTempTable
drop table if exists attribute cascade;

--* RestoreFromTempTable
create table attribute (
  attribute_id INT not null AUTO_INCREMENT comment '性能ID'
  , short_name VARCHAR(8) comment '略称'
  , name_en VARCHAR(32) comment '名称(英語)'
  , name_ja VARCHAR(32) comment '名称(日本語)'
  , unit VARCHAR(16) comment '単位'
  , sort_key INT not null comment 'ソート順'
  , constraint attribute_PKC primary key (attribute_id)
) comment '性能' ;

alter table attribute add unique attribute_IX1 (sort_key) ;

-- ベースアイテム
--* BackupToTempTable
drop table if exists base_item cascade;

--* RestoreFromTempTable
create table base_item (
  base_item_id INT not null AUTO_INCREMENT comment 'ベースアイテムID'
  , item_class_id INT not null comment 'アイテムクラスID'
  , name_en VARCHAR(32) comment '名称(英語)'
  , name_ja VARCHAR(32) comment '名称(日本語)'
  , image_name VARCHAR(64) comment '画像名称'
  , sort_key INT not null comment 'ソート順'
  , constraint base_item_PKC primary key (base_item_id)
) comment 'ベースアイテム' ;

alter table base_item add unique base_item_IX1 (sort_key) ;

-- キャラクタークラス性能
--* BackupToTempTable
drop table if exists character_attribute cascade;

--* RestoreFromTempTable
create table character_attribute (
  character_class_id INT not null comment 'キャラクタークラスID'
  , attribute_id INT not null comment '性能ID'
  , attribute_value FLOAT not null comment '性能値'
  , constraint character_attribute_PKC primary key (character_class_id,attribute_id)
) comment 'キャラクタークラス性能' ;

-- キャラクタークラス
--* BackupToTempTable
drop table if exists character_class cascade;

--* RestoreFromTempTable
create table character_class (
  character_class_id INT not null AUTO_INCREMENT comment 'キャラクタークラスID'
  , short_name VARCHAR(8) comment '略称'
  , name_en VARCHAR(32) comment '名称(英語)'
  , name_ja VARCHAR(32) comment '名称(日本語)'
  , sort_key INT not null comment 'ソート順'
  , constraint character_class_PKC primary key (character_class_id)
) comment 'キャラクタークラス' ;

alter table character_class add unique character_class_IX1 (sort_key) ;

-- クリーチャー
--* BackupToTempTable
drop table if exists creature cascade;

--* RestoreFromTempTable
create table creature (
  creature_id INT not null comment 'クリーチャーID'
  , name_en VARCHAR(32) comment '名称(英語)'
  , name_ja VARCHAR(32) comment '名称(日本語)'
  , xp INT comment '経験値'
  , note TEXT comment '説明'
  , constraint creature_PKC primary key (creature_id)
) comment 'クリーチャー' ;

-- クリーチャー性能
--* BackupToTempTable
drop table if exists creature_attribute cascade;

--* RestoreFromTempTable
create table creature_attribute (
  creature_id INT not null comment 'クリーチャーID'
  , attribute_id INT not null comment '性能ID'
  , attribute_value FLOAT comment '性能値'
  , constraint creature_attribute_PKC primary key (creature_id,attribute_id)
) comment 'クリーチャー性能' ;

-- クリーチャードロップアイテム
--* BackupToTempTable
drop table if exists creature_drop_item cascade;

--* RestoreFromTempTable
create table creature_drop_item (
  creature_id INT not null comment 'クリーチャーID'
  , item_id INT not null comment 'アイテムID'
  , constraint creature_drop_item_PKC primary key (creature_id,item_id)
) comment 'クリーチャードロップアイテム' ;

-- クリーチャースペシャルアタック
--* BackupToTempTable
drop table if exists creature_special_attack cascade;

--* RestoreFromTempTable
create table creature_special_attack (
  creature_id INT not null comment 'クリーチャーID'
  , special_attack_id INT not null comment 'スペシャルアタックID'
  , constraint creature_special_attack_PKC primary key (creature_id,special_attack_id)
) comment 'クリーチャースペシャルアタック' ;

-- フロア
--* BackupToTempTable
drop table if exists floor cascade;

--* RestoreFromTempTable
create table floor (
  floor_id INT not null comment 'フロアID'
  , floor_group_id INT not null comment 'フロアグループID'
  , short_name VARCHAR(16) comment '略称'
  , name_en VARCHAR(64) comment '名称(英語)'
  , name_ja VARCHAR(64) comment '名称(日本語)'
  , sort_key INT not null comment 'ソート順'
  , constraint floor_PKC primary key (floor_id)
) comment 'フロア' ;

alter table floor add unique floor_IX1 (sort_key) ;

-- フロアクリーチャー
--* BackupToTempTable
drop table if exists floor_creature cascade;

--* RestoreFromTempTable
create table floor_creature (
  floor_id INT not null comment 'フロアID'
  , creature_id INT not null comment 'クリーチャーID'
  , constraint floor_creature_PKC primary key (floor_id,creature_id)
) comment 'フロアクリーチャー' ;

-- フロア移動先
--* BackupToTempTable
drop table if exists floor_destination cascade;

--* RestoreFromTempTable
create table floor_destination (
  floor_id INT not null comment '移動元フロアID'
  , destination_floor_id INT not null comment '移動先フロアID'
  , constraint floor_destination_PKC primary key (floor_id,destination_floor_id)
) comment 'フロア移動先' ;

-- フロアドロップアイテム
--* BackupToTempTable
drop table if exists floor_drop_item cascade;

--* RestoreFromTempTable
create table floor_drop_item (
  floor_id INT not null comment 'フロアID'
  , item_id INT not null comment 'アイテムID'
  , constraint floor_drop_item_PKC primary key (floor_id,item_id)
) comment 'フロアドロップアイテム' ;

-- フロアグループ
--* BackupToTempTable
drop table if exists floor_group cascade;

--* RestoreFromTempTable
create table floor_group (
  floor_group_id INT not null AUTO_INCREMENT comment 'フロアグループID'
  , name_en VARCHAR(32) comment '名称(英語)'
  , name_ja VARCHAR(32) comment '名称(日本語)'
  , sort_key INT not null comment 'ソート順'
  , constraint floor_group_PKC primary key (floor_group_id)
) comment 'フロアグループ' ;

alter table floor_group add unique floor_group_IX1 (sort_key) ;

-- アイテム
--* BackupToTempTable
drop table if exists item cascade;

--* RestoreFromTempTable
create table item (
  item_id INT not null AUTO_INCREMENT comment 'アイテムID'
  , item_class_id INT not null comment 'アイテムクラスID'
  , base_item_id INT comment 'ベースアイテムID'
  , brand_id INT comment 'ブランドID'
  , name_en VARCHAR(64) comment '名称(英語)'
  , name_ja VARCHAR(64) comment '名称(日本語)'
  , rarity ENUM('common', 'rare', 'artifact') not null comment 'レアリティ'
  , skill_en VARCHAR(128) comment 'スキル(英語)'
  , skill_ja VARCHAR(64) comment 'スキル(日本語)'
  , skill_axe_en VARCHAR(128) comment 'スキルアックス(英語)'
  , skill_sword_en VARCHAR(128) comment 'スキルソード(英語)'
  , skill_dagger_en VARCHAR(128) comment 'スキルダガー(英語)'
  , comment_en VARCHAR(64) comment 'コメント(英語)'
  , comment_ja VARCHAR(64) comment 'コメント(日本語)'
  , note TEXT comment '説明'
  , price INT comment '売却価格'
  , image_name VARCHAR(64) comment '画像名称'
  , sort_key INT not null comment 'ソート順'
  , constraint item_PKC primary key (item_id)
) comment 'アイテム' ;

alter table item add unique item_IX1 (sort_key) ;

create unique index item_IX2
  on item(item_class_id,rarity,sort_key);

-- アイテム性能
--* BackupToTempTable
drop table if exists item_attribute cascade;

--* RestoreFromTempTable
create table item_attribute (
  item_id INT not null comment 'アイテムID'
  , attribute_id INT not null comment '性能ID'
  , flactuable BIT(1) not null comment '変動'
  , based_source ENUM('xp', 'kills') comment '変動元'
  , color ENUM('white', 'yellow', 'red') not null comment '色'
  , attribute_value INT comment '性能値'
  , attribute_value_axe INT comment '性能値アックス'
  , attribute_value_sword INT comment '性能値ソード'
  , attribute_value_dagger INT comment '性能値ダガー'
  , max_required INT comment '変動最大要求値'
  , max_required_axe INT comment '変動最大要求値アックス'
  , max_required_sword INT comment '変動最大要求値ソード'
  , max_required_dagger INT comment '変動最大要求値ダガー'
  , constraint item_attribute_PKC primary key (item_id,attribute_id,flactuable)
) comment 'アイテム性能' ;

-- アイテム性能ログ
--* BackupToTempTable
drop table if exists item_attribute_log cascade;

--* RestoreFromTempTable
create table item_attribute_log (
  item_log_id INT not null comment 'ログID'
  , attribute_id INT not null comment '性能ID'
  , flactuable BIT(1) not null comment '変動'
  , based_source ENUM('xp', 'kills') comment '変動元'
  , color ENUM('white', 'yellow', 'red') comment '色'
  , attribute_value INT default 0 comment '性能値'
  , attribute_value_axe INT default 0 comment '性能値アックス'
  , attribute_value_sword INT default 0 comment '性能値ソード'
  , attribute_value_dagger INT default 0 comment '性能値ダガー'
  , max_required INT default 0 comment '変動最大要求値'
  , max_required_axe INT default 0 comment '変動最大要求値アックス'
  , max_required_sword INT default 0 comment '変動最大要求値ソード'
  , max_required_dagger INT default 0 comment '変動最大要求値ダガー'
  , is_deleted BIT(1) default FALSE not null comment '削除'
  , constraint item_attribute_log_PKC primary key (item_log_id,attribute_id,flactuable)
) comment 'アイテム性能ログ' ;

-- アイテムブランド
--* BackupToTempTable
drop table if exists item_brand cascade;

--* RestoreFromTempTable
create table item_brand (
  brand_id INT not null AUTO_INCREMENT comment 'ブランドID'
  , name_en VARCHAR(32) comment '名称(英語)'
  , name_ja VARCHAR(32) comment '名称(日本語)'
  , sort_key INT not null comment 'ソート順'
  , constraint item_brand_PKC primary key (brand_id)
) comment 'アイテムブランド' ;

alter table item_brand add unique item_brand_IX1 (sort_key) ;

-- アイテムクラス
--* BackupToTempTable
drop table if exists item_class cascade;

--* RestoreFromTempTable
create table item_class (
  item_class_id INT not null AUTO_INCREMENT comment 'アイテムクラスID'
  , name_en VARCHAR(32) comment '名称(英語)'
  , name_ja VARCHAR(32) comment '名称(日本語)'
  , image_name VARCHAR(64) comment '画像名称'
  , sort_key INT not null comment 'ソート順'
  , constraint item_class_PKC primary key (item_class_id)
) comment 'アイテムクラス' ;

alter table item_class add unique item_class_IX1 (sort_key) ;

-- アイテムログ
--* BackupToTempTable
drop table if exists item_log cascade;

--* RestoreFromTempTable
create table item_log (
  item_log_id INT not null comment 'ログID'
  , item_id INT not null comment 'アイテムID'
  , item_class_id INT comment 'アイテムクラスID'
  , base_item_id INT comment 'ベースアイテムID'
  , brand_id INT comment 'ブランドID'
  , name_en VARCHAR(64) comment '名称(英語)'
  , name_ja VARCHAR(64) comment '名称(日本語)'
  , rarity ENUM('common', 'rare', 'artifact') comment 'レアリティ'
  , skill_en VARCHAR(128) comment 'スキル(英語)'
  , skill_ja VARCHAR(64) comment 'スキル(日本語)'
  , comment_en VARCHAR(64) comment 'コメント(英語)'
  , comment_ja VARCHAR(64) comment 'コメント(日本語)'
  , note TEXT comment '説明'
  , price INT comment '売却価格'
  , image_name VARCHAR(64) comment '画像名称'
  , attach_image_name VARCHAR(64) comment '添付画像'
  , is_deleted BIT(1) default FALSE not null comment '削除'
  , sending_datetime DATETIME default CURRENT_TIMESTAMP not null comment '送信日時'
  , accepting_datetime DATETIME comment '承認日時'
  , user_id INT not null comment '送信ユーザID'
  , constraint item_log_PKC primary key (item_log_id)
) comment 'アイテムログ' ;

-- 短縮URL
--* BackupToTempTable
drop table if exists short_url cascade;

--* RestoreFromTempTable
create table short_url (
  short_url_key VARCHAR(6) not null comment '短縮URLキー'
  , url VARCHAR(255) not null comment 'URL'
  , created_at DATETIME comment '作成日時'
  , last_access DATETIME comment '最終アクセス'
  , constraint short_url_PKC primary key (short_url_key)
) comment '短縮URL' DEFAULT CHARSET=utf8;

alter table short_url add unique short_url_IX1 (url) ;

-- スペシャルアタック
--* BackupToTempTable
drop table if exists special_attack cascade;

--* RestoreFromTempTable
create table special_attack (
  special_attack_id INT not null comment 'スペシャルアタックID'
  , name VARCHAR(32) comment '名称'
  , cooldown INT comment '再使用'
  , note TEXT comment '説明'
  , constraint special_attack_PKC primary key (special_attack_id)
) comment 'スペシャルアタック' ;

-- Urulukユーザ
--* BackupToTempTable
drop table if exists uruluk_user cascade;

--* RestoreFromTempTable
create table uruluk_user (
  user_id INT not null AUTO_INCREMENT comment 'ユーザID'
  , login_id VARCHAR(64) not null comment 'ログインID'
  , user_name VARCHAR(64) not null comment 'ユーザ名'
  , mail_address VARCHAR(256) comment 'メールアドレス'
  , login_password CHAR(64) comment 'パスワード'
  , google_id VARCHAR(256) comment 'GoogleID'
  , facebook_id VARCHAR(256) comment 'FacebookID'
  , twitter_id VARCHAR(256) comment 'TwitterID'
  , permission_level INT default 1 not null comment '権限レベル'
  , last_login_datetime DATETIME comment '最終ログイン日時'
  , created_datetime DATETIME comment '作成日時'
  , constraint uruluk_user_PKC primary key (user_id)
) comment 'Urulukユーザ' ;

