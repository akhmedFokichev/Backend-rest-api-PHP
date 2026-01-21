<?php
namespace App\Domain\Storage\Repository;

use App\Domain\Storage\Entity\File;

interface FileRepositoryInterface
{
    /**
     * Сохранить информацию о файле в БД
     */
    public function add(File $file): void;

    /**
     * Найти файл по UUID
     */
    public function findByUuid(string $uuid): ?File;

    /**
     * Получить список всех файлов
     * @return File[]
     */
    public function findAll(): array;

    /**
     * Удалить запись о файле из БД
     */
    public function delete(string $uuid): void;
}
