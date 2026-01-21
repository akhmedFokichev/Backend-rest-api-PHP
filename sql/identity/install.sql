-- Полная установка модуля Identity
-- Создание всех необходимых таблиц

-- 1. Таблица пользователей
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

-- 2. Таблица сессий (JWT токены)
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

-- Готово! Модуль Identity установлен.
