<?php

declare(strict_types=1);

namespace App\Services\Document\Export;

use App\Entities\Document\FileEntityInterface;

interface DocumentExporterInterface
{
    public function export(FileEntityInterface $file): string;
}