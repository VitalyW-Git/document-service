<?php

namespace App\Services\Document\Export;

use App\Entities\Document\FileEntityInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

abstract class ExcelDocumentExporterAbstract extends DocumentAbstract implements DocumentExporterInterface
{
    public function export(FileEntityInterface $file): string
    {
        $rows = $this->fetchRows($file->getId());

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Данные');

        if (!empty($rows)) {
            $firstRow = $rows[0]->row_data;
            $headers = array_keys($firstRow);

            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $col++;
            }

            $rowNum = 2;
            foreach ($rows as $row) {
                $rowData = $row->row_data;
                $col = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue($col . $rowNum, $rowData[$header] ?? '');
                    $col++;
                }
                $rowNum++;
            }
        }

        $filePath = $this->buildTempPath('xlsx', $file->getId());
        (new Xlsx($spreadsheet))->save($filePath);

        $this->logActivity($file->getId(), 'export_excel', "Экспорт в Excel: {$file->getOriginalName()}");

        return $filePath;
    }
}


