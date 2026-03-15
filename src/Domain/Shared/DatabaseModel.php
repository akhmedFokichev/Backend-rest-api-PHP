<?php

namespace App\Domain\Shared;

interface DatabaseModel
{
    public static function findByUuid(string $uuid): ?static;

    public function save(): void;

    public function delete(): void;

    public function toArray(): array;
}
