-- Таблица для хранения информации о файлах
CREATE TABLE IF NOT EXISTS `storage_files` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `uuid` VARCHAR(36) NOT NULL UNIQUE COMMENT 'UUID файла',
    `original_name` VARCHAR(255) NOT NULL COMMENT 'Оригинальное имя файла',
    `storage_name` VARCHAR(255) NOT NULL COMMENT 'Имя файла в хранилище',
    `mime_type` VARCHAR(100) NOT NULL COMMENT 'MIME тип файла',
    `size` INT UNSIGNED NOT NULL COMMENT 'Размер файла в байтах',
    `path` VARCHAR(500) NOT NULL COMMENT 'Относительный путь к файлу',
    `created_at` DATETIME NOT NULL COMMENT 'Дата создания',
    `updated_at` DATETIME NOT NULL COMMENT 'Дата обновления',
    INDEX `idx_uuid` (`uuid`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Хранилище файлов';
