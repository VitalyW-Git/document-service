<?php

namespace App\Entities\Document;

interface FileRowEntityInterface
{
    public function getId(): string;

    public function getFileId(): string;

    public function getRowData(): string;

    public function getRowIndex(): int;

    public function getCreatedAt(): string;

    public function getUpdatedAt(): string;
}