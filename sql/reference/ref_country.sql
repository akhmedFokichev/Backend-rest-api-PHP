-- ========================================
-- Создание справочника Country (страны)
-- ========================================
-- Этот скрипт создает таблицу для справочника стран
-- с поддержкой иерархии (каталоги и объекты)
--
-- Использование:
-- 1. Скопируйте весь текст ниже
-- 2. Вставьте в phpMyAdmin -> SQL
-- 3. Нажмите "Вперед" (Go)
-- ========================================

CREATE TABLE IF NOT EXISTS `ref_country` (
  `id` INT UNSIGNED AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL,
  `parent_uuid` CHAR(36) NULL,
  `is_catalog` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = каталог (папка), 0 = объект (страна)',
  `code` VARCHAR(32) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_uuid` (`uuid`),
  INDEX `idx_parent` (`parent_uuid`),
  INDEX `idx_is_catalog` (`is_catalog`),
  INDEX `idx_sort` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

