<?php

namespace App\Enum;

/**
 * Роли пользователя (числовые — удобно сравнивать и добавлять новые уровни).
 * Не связаны с БД — перечисление констант.
 */
enum Role: int
{
    case Guest     = 0;
    case User      = 10;
    case Moderator = 50;
    case Admin     = 100;

    public function label(): string
    {
        return match ($this) {
            self::Guest     => 'Гость',
            self::User     => 'Пользователь',
            self::Moderator=> 'Модератор',
            self::Admin    => 'Администратор',
        };
    }

    /** @return list<int> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** Проверка: эта роль не ниже переданной (например: $role->atLeast(Role::Moderator)) */
    public function atLeast(Role $min): bool
    {
        return $this->value >= $min->value;
    }
}
