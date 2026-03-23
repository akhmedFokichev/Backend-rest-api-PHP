-- Таблица bearer-токенов пользователя (храним hash токена, не сам токен)
CREATE TABLE IF NOT EXISTS user_token (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NULL DEFAULT NULL,
    revoked_at DATETIME NULL DEFAULT NULL,
    CONSTRAINT fk_user_token_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_user_token_user_id ON user_token (user_id);
CREATE INDEX idx_user_token_active ON user_token (revoked_at, expires_at);
