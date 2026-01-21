-- Таблица сессий для модуля Identity (JWT токены)
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
