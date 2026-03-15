-- Таблица товаров
CREATE TABLE IF NOT EXISTS `product` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uuid` VARCHAR(36) NOT NULL UNIQUE COMMENT 'UUID товара/каталога',
    `parent_uuid` VARCHAR(36) NULL COMMENT 'UUID родительского каталога',
    `is_catalog` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=каталог (папка), 0=товар',
    `name` VARCHAR(255) NOT NULL COMMENT 'Название',
    `code` VARCHAR(64) NOT NULL COMMENT 'Артикул/код',
    `description` TEXT NULL COMMENT 'Описание',
    `barcode` VARCHAR(64) NULL COMMENT 'Штрих-код',
    `price` DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Розничная цена',
    `wholesale_price` DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Оптовая цена',
    `super_wholesale_price` DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Супер оптовая цена',
    `unit` VARCHAR(20) NULL COMMENT 'Единица измерения (шт, кг, л)',
    `vat_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'НДС, %',
    `quantity` INT NOT NULL DEFAULT 0 COMMENT 'Остаток',
    `sort_order` INT NOT NULL DEFAULT 0 COMMENT 'Порядок сортировки',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=активен, 0=скрыт',
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    INDEX `idx_uuid` (`uuid`),
    INDEX `idx_parent_uuid` (`parent_uuid`),
    INDEX `idx_is_catalog` (`is_catalog`),
    INDEX `idx_code` (`code`),
    INDEX `idx_barcode` (`barcode`),
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Товары';
