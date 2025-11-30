<?php

namespace App\Services\Document\Export;

use App\Entities\Document\FileRowEntity;
use App\Models\Document\ActivityLogModel;
use App\Models\Document\FileRowModel;

final class ExcelDocument extends ExcelDocumentExporterAbstract
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

    protected function logActivity(string $fileId, string $action, string $description): void
    {
        $this->activityLogModel->insert([
            'file_id' => $fileId,
            'action' => $action,
            'description' => $description,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}


