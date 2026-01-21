<?php
namespace App\Application\Storage;

use App\Domain\Storage\Repository\FileRepositoryInterface;

class ListFilesUseCase
{
    private FileRepositoryInterface $fileRepo;

    public function __construct(FileRepositoryInterface $fileRepo)
    {
        $this->fileRepo = $fileRepo;
    }

    /**
     * @return \App\Domain\Storage\Entity\File[]
     */
    public function execute(): array
    {
        return $this->fileRepo->findAll();
    }
}
