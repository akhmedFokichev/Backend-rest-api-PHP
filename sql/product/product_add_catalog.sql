-- Добавление поддержки каталогов (иерархия)
-- Выполнять только если в таблице product ещё нет колонок parent_uuid, is_catalog

ALTER TABLE `product` ADD COLUMN `parent_uuid` VARCHAR(36) NULL COMMENT 'UUID родительского каталога' AFTER `uuid`;
ALTER TABLE `product` ADD COLUMN `is_catalog` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=каталог, 0=товар' AFTER `parent_uuid`;
ALTER TABLE `product` ADD INDEX `idx_parent_uuid` (`parent_uuid`);
ALTER TABLE `product` ADD INDEX `idx_is_catalog` (`is_catalog`);
