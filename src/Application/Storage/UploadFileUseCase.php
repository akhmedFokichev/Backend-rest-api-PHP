<?php
namespace App\Application\Storage;

use App\Domain\Storage\Entity\File;
use App\Domain\Storage\Repository\FileRepositoryInterface;

class UploadFileUseCase
{
    private FileRepositoryInterface $fileRepo;
    private string $storageDir;

    public function __construct(FileRepositoryInterface $fileRepo, string $storageDir)
    {
        $this->fileRepo = $fileRepo;
        $this->storageDir = $storageDir;
    }

    /**
     * @param array $uploadedFile массив из $_FILES
     * @return File
     * @throws \RuntimeException
     */
    public function execute(array $uploadedFile): File
    {
        // Валидация
        if (!isset($uploadedFile['tmp_name']) || !is_uploaded_file($uploadedFile['tmp_name'])) {
            throw new \RuntimeException('Invalid uploaded file');
        }

        if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('File upload error: ' . $uploadedFile['error']);
        }

        // Генерация UUID и имени файла
        $uuid = $this->generateUuid();
        $originalName = $uploadedFile['name'] ?? 'unnamed';
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $storageName = $uuid . ($extension ? '.' . $extension : '');
        $mimeType = $uploadedFile['type'] ?? 'application/octet-stream';
        $size = $uploadedFile['size'] ?? 0;

        // Путь для сохранения
        $relativePath = $storageName;
        $fullPath = $this->storageDir . '/' . $storageName;

        // Создаем директорию если не существует
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }

        // Перемещаем файл
        if (!move_uploaded_file($uploadedFile['tmp_name'], $fullPath)) {
            throw new \RuntimeException('Failed to move uploaded file');
        }

        // Создаем сущность
        $file = new File($uuid, $originalName, $storageName, $mimeType, $size, $relativePath);

        // Сохраняем в БД
        $this->fileRepo->add($file);

        return $file;
    }

    private function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
