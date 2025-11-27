<?php

namespace Config;

use App\Models\Document\ActivityLogModel;
use App\Models\Document\FileModel;
use App\Models\Document\FileRowModel;
use App\Services\Document\DocumentExportService;
use App\Services\Document\DocumentStorageService;
use App\Services\Document\Export\ExcelDocumentExporter;
use App\Services\Document\Export\PdfDocumentExporter;
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
    public static function documentStorage(bool $getShared = true): DocumentStorageService
    {
        if ($getShared) {
            return static::getSharedInstance('documentStorage');
        }

        return new DocumentStorageService(
            new FileModel(),
            new FileRowModel(),
            new ActivityLogModel(),
            WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR,
        );
    }

    public static function documentExport(bool $getShared = true): DocumentExportService
    {
        if ($getShared) {
            return static::getSharedInstance('documentExport');
        }

        $fileRowModel = new FileRowModel();
        $activityLogModel = new ActivityLogModel();

        $excelExporter = new ExcelDocumentExporter(
            $fileRowModel,
            $activityLogModel,
            WRITEPATH
        );

        $pdfExporter = new PdfDocumentExporter(
            $fileRowModel,
            $activityLogModel,
            WRITEPATH
        );

        return new DocumentExportService($excelExporter, $pdfExporter);
    }
}
