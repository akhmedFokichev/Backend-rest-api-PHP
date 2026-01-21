-- ========================================
-- Полная установка всех модулей API
-- ========================================
-- 
-- Этот файл создает все необходимые таблицы для всех модулей:
-- 1. Identity - аутентификация и авторизация
-- 2. Reference - справочники (Country)
-- 3. Storage - управление файлами
--
-- ========================================

-- ========================================
-- МОДУЛЬ 1: Identity
-- ========================================

-- Таблица пользователей
CREATE TABLE IF NOT EXISTS `identity_users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `login` VARCHAR(100) NOT NULL UNIQUE COMMENT 'Логин пользователя (email или username)',
    `pass_hash` VARCHAR(255) NOT NULL COMMENT 'Хеш пароля (bcrypt)',
    `access_level` TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Уровень доступа: 0=user, 1=admin, etc',
    `created_at` DATETIME NOT NULL COMMENT 'Дата создания',
    `updated_at` DATETIME NOT NULL COMMENT 'Дата обновления',
    INDEX `idx_login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Пользователи системы';

-- Таблица сессий (JWT токены)
CREATE TABLE IF NOT EXISTS `identity_session` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL COMMENT 'ID пользователя',
    `access_token` VARCHAR(500) NOT NULL COMMENT 'JWT access token',
    `refresh_token` VARCHAR(500) NOT NULL COMMENT 'JWT refresh token',
    `expiresIn` INT UNSIGNED NOT NULL COMMENT 'Время жизни токена в секундах',
    `client_id` VARCHAR(100) NOT NULL COMMENT 'Идентификатор клиента (web_app, mobile_app, etc)',
    `secret_key` VARCHAR(255) NOT NULL COMMENT 'Секретный ключ для подписи JWT',
    `created_at` DATETIME NOT NULL COMMENT 'Дата создания сессии',
    `updated_at` DATETIME NOT NULL COMMENT 'Дата последнего обновления',
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_access_token` (`access_token`(255)),
    INDEX `idx_refresh_token` (`refresh_token`(255)),
    CONSTRAINT `fk_session_user` 
        FOREIGN KEY (`user_id`) 
        REFERENCES `identity_users` (`id`) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Сессии пользователей (JWT токены)';

-- ========================================
-- МОДУЛЬ 2: Reference
-- ========================================

-- Справочник стран
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

-- Тестовые данные для справочника стран
INSERT INTO `ref_country` (`uuid`, `parent_uuid`, `is_catalog`, `code`, `name`, `sort_order`, `created_at`, `updated_at`) VALUES
('550e8400-e29b-41d4-a716-446655440001', NULL, 0, 'RU', 'Россия', 1, NOW(), NOW()),
('550e8400-e29b-41d4-a716-446655440002', NULL, 0, 'US', 'США', 2, NOW(), NOW()),
('550e8400-e29b-41d4-a716-446655440003', NULL, 0, 'GB', 'Великобритания', 3, NOW(), NOW()),
('550e8400-e29b-41d4-a716-446655440004', NULL, 0, 'DE', 'Германия', 4, NOW(), NOW()),
('550e8400-e29b-41d4-a716-446655440005', NULL, 0, 'FR', 'Франция', 5, NOW(), NOW()),
('550e8400-e29b-41d4-a716-446655440006', NULL, 0, 'CN', 'Китай', 6, NOW(), NOW()),
('550e8400-e29b-41d4-a716-446655440007', NULL, 0, 'JP', 'Япония', 7, NOW(), NOW()),
('550e8400-e29b-41d4-a716-446655440008', NULL, 0, 'KR', 'Южная Корея', 8, NOW(), NOW());

-- ========================================
-- МОДУЛЬ 3: Storage
-- ========================================

-- Таблица файлов
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

-- ========================================
-- Установка завершена!
-- ========================================
-- 
-- Созданы таблицы:
-- ✓ identity_users       - пользователи
-- ✓ identity_session     - сессии (JWT)
-- ✓ ref_country          - справочник стран (с тестовыми данными)
-- ✓ storage_files        - файловое хранилище
--
-- Теперь можно использовать API:
-- - /identity/*          - регистрация, логин, refresh
-- - /reference/country   - CRUD для стран
-- - /storage/files       - загрузка, получение, удаление файлов
--
-- ========================================
