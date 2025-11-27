<?php

namespace App\Services\Document\Export;

use App\Entities\Document\DocumentFileInterface;

interface DocumentExporterInterface
{
    public function export(DocumentFileInterface $file): string;
}