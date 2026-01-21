<?php
namespace App\Application\Storage;

use App\Domain\Storage\Entity\File;
use App\Domain\Storage\Repository\FileRepositoryInterface;

class GetFileUseCase
{
    private FileRepositoryInterface $fileRepo;
    private string $storageDir;

    public function __construct(FileRepositoryInterface $fileRepo, string $storageDir)
    {
        $this->fileRepo = $fileRepo;
        $this->storageDir = $storageDir;
    }

    /**
     * @return array [file: File, fullPath: string]
     * @throws \RuntimeException
     */
    public function execute(string $uuid): array
    {
        $file = $this->fileRepo->findByUuid($uuid);
        
        if (!$file) {
            throw new \RuntimeException('File not found');
        }

        $fullPath = $this->storageDir . '/' . $file->storageName;

        if (!file_exists($fullPath)) {
            throw new \RuntimeException('Physical file not found');
        }

        return [
            'file' => $file,
            'fullPath' => $fullPath
        ];
    }
}
