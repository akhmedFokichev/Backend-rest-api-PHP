<?php
namespace App\Application\Storage;

use App\Domain\Storage\Repository\FileRepositoryInterface;

class DeleteFileUseCase
{
    private FileRepositoryInterface $fileRepo;
    private string $storageDir;

    public function __construct(FileRepositoryInterface $fileRepo, string $storageDir)
    {
        $this->fileRepo = $fileRepo;
        $this->storageDir = $storageDir;
    }

    /**
     * @throws \RuntimeException
     */
    public function execute(string $uuid): void
    {
        $file = $this->fileRepo->findByUuid($uuid);
        
        if (!$file) {
            throw new \RuntimeException('File not found');
        }

        // Удаляем физический файл
        $fullPath = $this->storageDir . '/' . $file->storageName;
        if (file_exists($fullPath)) {
            if (!unlink($fullPath)) {
                throw new \RuntimeException('Failed to delete physical file');
            }
        }

        // Удаляем запись из БД
        $this->fileRepo->delete($uuid);
    }
}
