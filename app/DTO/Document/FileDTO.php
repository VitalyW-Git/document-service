<?php

namespace App\DTO\Document;

use App\Entities\Document\FileEntity;

class FileDTO
{
    public string $id;
    public string $name;
    public string $rowCount;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(
        FileEntity $file
    )
    {
        $this->id = $file->getId();
        $this->name = $file->getName();
        $this->rowCount = $file->getRowCount();
        $this->createdAt = $file->getCreatedAt();
        $this->updatedAt = $file->getUpdatedAt();
    }
}