-- Таблица справочника стран для модуля Reference
CREATE TABLE IF NOT EXISTS `ref_country` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uuid` VARCHAR(36) NOT NULL UNIQUE COMMENT 'UUID страны',
    `parent_uuid` VARCHAR(36) NULL DEFAULT NULL COMMENT 'UUID родительского элемента (для иерархии)',
    `is_catalog` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Является ли элемент каталогом (папкой)',
    `code` VARCHAR(10) NOT NULL COMMENT 'Код страны (ISO 3166-1 alpha-2: RU, US, etc)',
    `name` VARCHAR(255) NOT NULL COMMENT 'Название страны',
    `sort_order` INT NOT NULL DEFAULT 0 COMMENT 'Порядок сортировки',
    `created_at` DATETIME NOT NULL COMMENT 'Дата создания',
    `updated_at` DATETIME NOT NULL COMMENT 'Дата обновления',
    INDEX `idx_uuid` (`uuid`),
    INDEX `idx_parent_uuid` (`parent_uuid`),
    INDEX `idx_code` (`code`),
    INDEX `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Справочник стран';

-- Добавляем несколько стран для примера
INSERT INTO `ref_country` (`uuid`, `parent_uuid`, `is_catalog`, `code`, `name`, `sort_order`, `created_at`, `updated_at`) VALUES
('550e8400-e29b-41d4-a716-446655440001', NULL, 0, 'RU', 'Россия', 1, NOW(), NOW()),
('550e8400-e29b-41d4-a716-446655440002', NULL, 0, 'US', 'США', 2, NOW(), NOW()),
('550e8400-e29b-41d4-a716-446655440003', NULL, 0, 'GB', 'Великобритания', 3, NOW(), NOW()),
('550e8400-e29b-41d4-a716-446655440004', NULL, 0, 'DE', 'Германия', 4, NOW(), NOW()),
('550e8400-e29b-41d4-a716-446655440005', NULL, 0, 'FR', 'Франция', 5, NOW(), NOW()),
('550e8400-e29b-41d4-a716-446655440006', NULL, 0, 'CN', 'Китай', 6, NOW(), NOW()),
('550e8400-e29b-41d4-a716-446655440007', NULL, 0, 'JP', 'Япония', 7, NOW(), NOW()),
('550e8400-e29b-41d4-a716-446655440008', NULL, 0, 'KR', 'Южная Корея', 8, NOW(), NOW());
