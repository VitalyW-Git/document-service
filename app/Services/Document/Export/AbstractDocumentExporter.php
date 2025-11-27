<?php

namespace App\Services\Document\Export;

use App\Entities\Document\FileRowEntity;
use App\Models\Document\ActivityLogModel;
use App\Models\Document\FileRowModel;
use RuntimeException;

abstract class AbstractDocumentExporter implements DocumentExporterInterface
{
    public function __construct(
        protected readonly FileRowModel $fileRowModel,
        protected readonly ActivityLogModel $activityLogModel,
        protected readonly string $writePath
    ) {
        $this->ensureTempDirectory();
    }

    /**
     * @return list<FileRowEntity>
     */
    protected function fetchRows(string $fileId): array
    {
        return $this->fileRowModel
            ->where('file_id', $fileId)
            ->orderBy('row_index', 'ASC')
            ->findAll();
    }

    protected function buildTempPath(string $extension, string $fileId): string
    {
        $tempDir = rtrim($this->writePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'temp';

        return $tempDir . DIRECTORY_SEPARATOR . 'export_' . $fileId . '_' . time() . '.' . $extension;
    }

    protected function logActivity(string $fileId, string $action, string $description): void
    {
        $this->activityLogModel->insert([
            'file_id' => $fileId,
            'action' => $action,
            'description' => $description,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function ensureTempDirectory(): void
    {
        $path = rtrim($this->writePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'temp';

        if (!is_dir($path) && !mkdir($path, 0755, true)) {
            throw new RuntimeException('Не удалось создать временную директорию для экспорта');
        }
    }
}


