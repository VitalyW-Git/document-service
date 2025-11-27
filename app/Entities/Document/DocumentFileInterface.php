<?php

namespace App\Entities\Document;

interface DocumentFileInterface
{
    public function getId(): string;

    public function getName(): string;

    public function getOriginalName(): string;

    public function getRowCount(): int;

    public function getCreatedAt(): string;

    public function getUpdatedAt(): string;
}