<?php

namespace App\Services\Document;

use App\Entities\Document\FileEntityInterface;
use App\Services\Document\Export\DocumentExporterInterface;

final class DocumentExportService
{
    public function __construct(
        private readonly DocumentExporterInterface $excelExporter,
        private readonly DocumentExporterInterface $pdfExporter,
    ) {
    }

    public function exportExcel(FileEntityInterface $file): string
    {
        return $this->excelExporter->export($file);
    }

    public function exportPdf(FileEntityInterface $file): string
    {
        return $this->pdfExporter->export($file);
    }
}

