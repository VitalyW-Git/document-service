<?php

namespace App\DTO\Document;

use App\Entities\Document\FileRowEntity;

class FileRowDTO
{
    public string $id;
    public string $fileId;
    public array $rowData;

    public function __construct(
        FileRowEntity $fileRow
    )
    {
        $this->id = $fileRow->getId();
        $this->fileId = $fileRow->getFileId();
        $this->rowData = json_decode($fileRow->getRowData(), true);
    }
}