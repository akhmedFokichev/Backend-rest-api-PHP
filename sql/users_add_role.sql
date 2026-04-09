-- Добавить колонку role в существующую таблицу users (если таблица уже создана без неё)
ALTER TABLE users ADD COLUMN role INT NOT NULL DEFAULT 10 AFTER password_hash;
CREATE INDEX idx_users_role ON users (role);
