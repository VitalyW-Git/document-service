<?php

declare(strict_types=1);

namespace App\DTO\Document;

use App\Entities\Document\FileRowEntityInterface;

class FileRowDTO
{
    public string $id;
    public string $fileId;
    public array $rowData;

    public function __construct(
        FileRowEntityInterface $fileRow
    )
    {
        $this->id = $fileRow->getId();
        $this->fileId = $fileRow->getFileId();
        $this->rowData = json_decode($fileRow->getRowData(), true);
    }
}