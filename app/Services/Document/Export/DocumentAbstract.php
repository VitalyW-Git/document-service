<?php

namespace App\Services\Document\Export;

use RuntimeException;

abstract class DocumentAbstract
{
    protected function buildTempPath(string $extension, string $fileId): string
    {
        $tempDir = rtrim($this->writePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'temp';

        return $tempDir . DIRECTORY_SEPARATOR . 'export_' . $fileId . '_' . time() . '.' . $extension;
    }

    protected function ensureTempDirectory(): void
    {
        $path = rtrim($this->writePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'temp';

        if (!is_dir($path) && !mkdir($path, 0755, true)) {
            throw new RuntimeException('Не удалось создать временную директорию для экспорта');
        }
    }

    protected abstract function fetchRows(string $fileId): array;

    protected abstract function logActivity(string $fileId, string $action, string $description): void;
}