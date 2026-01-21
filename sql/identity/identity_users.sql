-- Таблица пользователей для модуля Identity
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
