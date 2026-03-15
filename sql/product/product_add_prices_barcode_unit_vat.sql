-- Добавление полей: штрих-код, оптовая цена, супер оптовая цена, единица измерения, НДС
-- Выполнять только если таблица product уже создана без этих полей (старая версия).
-- Если колонка уже есть — соответствующий ALTER выдаст ошибку, переходите к следующему.

ALTER TABLE `product` ADD COLUMN `barcode` VARCHAR(64) NULL COMMENT 'Штрих-код' AFTER `description`;
ALTER TABLE `product` ADD COLUMN `wholesale_price` DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Оптовая цена' AFTER `price`;
ALTER TABLE `product` ADD COLUMN `super_wholesale_price` DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Супер оптовая цена' AFTER `wholesale_price`;
ALTER TABLE `product` ADD COLUMN `unit` VARCHAR(20) NULL COMMENT 'Единица измерения (шт, кг, л)' AFTER `super_wholesale_price`;
ALTER TABLE `product` ADD COLUMN `vat_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'НДС, %' AFTER `unit`;
