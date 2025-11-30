<?php

namespace Config;

use App\Models\Document\ActivityLogModel;
use App\Models\Document\FileModel;
use App\Models\Document\FileRowModel;
use App\Services\Document\DocumentExportService;
use App\Services\Document\DocumentService;
use App\Services\Document\Export\ExcelDocument;
use App\Services\Document\Export\PdfDocument;
use CodeIgniter\Config\BaseService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    public static function documentService(bool $getShared = true): DocumentService
    {
        if ($getShared) {
            return static::getSharedInstance('documentService');
        }

        return new DocumentService(
            new FileModel(),
            new FileRowModel(),
            new ActivityLogModel(),
            WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR,
        );
    }

    public static function documentExportService(bool $getShared = true): DocumentExportService
    {
        if ($getShared) {
            return static::getSharedInstance('documentExportService');
        }

        $fileRowModel = new FileRowModel();
        $activityLogModel = new ActivityLogModel();

        $excelExporter = new ExcelDocument(
            $fileRowModel,
            $activityLogModel,
            WRITEPATH
        );

        $pdfExporter = new PdfDocument(
            $fileRowModel,
            $activityLogModel,
            WRITEPATH
        );

        return new DocumentExportService($excelExporter, $pdfExporter);
    }
}
