<?php

declare(strict_types=1);

namespace App\DTO\Document;

use App\Entities\Document\FileEntityInterface;

class FileDTO
{
    public string $id;
    public string $name;
    public int $rowCount;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(
        FileEntityInterface $file
    )
    {
        $this->id = $file->getId();
        $this->name = $file->getName();
        $this->rowCount = $file->getRowCount();
        $this->createdAt = $file->getCreatedAt();
        $this->updatedAt = $file->getUpdatedAt();
    }
}