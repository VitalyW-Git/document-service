<?php

namespace App\Services\Document;

use App\Entities\FileEntity;
use App\Entities\FileRowEntity;
use App\Models\ActivityLogModelAbstract;
use App\Models\FileRowModelAbstract;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;
use TCPDF;

final class DocumentExportService
{
    private const TEMP_DIRECTORY = 'temp';

    public function __construct(
        private readonly FileRowModelAbstract     $fileRowModel,
        private readonly ActivityLogModelAbstract $activityLogModel,
        private readonly string                   $writePath
    ) {
        $this->ensureTempDirectory();
    }

    public function exportExcel(FileEntity $file): string
    {
        $rows = $this->fetchRows($file->id);

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

        $filePath = $this->buildTempPath('xlsx', $file->id);
        (new Xlsx($spreadsheet))->save($filePath);

        $this->logActivity($file->id, 'export_excel', "Экспорт в Excel: {$file->original_name}");

        return $filePath;
    }

    public function exportPdf(FileEntity $file): string
    {
        $rows = $this->fetchRows($file->id);

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('document Service');
        $pdf->SetAuthor('document Service');
        $pdf->SetTitle($file->name);
        $pdf->SetSubject('Export');
        $pdf->SetKeywords('Excel, PDF, Export');

        $pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
        $pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(15, 27, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        $pdf->SetAutoPageBreak(true, 25);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->setLanguageArray([]);

        $pdf->AddPage();
        $pdf->SetFont('dejavusans', '', 10);

        $html = '<h1>' . htmlspecialchars($file->name) . '</h1>';
        $html .= '<p><strong>Дата создания:</strong> ' . $file->created_at . '</p>';
        $html .= '<p><strong>Количество строк:</strong> ' . $file->row_count . '</p>';

        if (!empty($rows)) {
            $firstRow = $rows[0]->row_data;
            $headers = array_keys($firstRow);

            $html .= '<table border="1" cellpadding="5" cellspacing="0">';
            $html .= '<thead><tr>';
            foreach ($headers as $header) {
                $html .= '<th><strong>' . htmlspecialchars($header) . '</strong></th>';
            }
            $html .= '</tr></thead><tbody>';

            foreach ($rows as $row) {
                $rowData = $row->row_data;
                $html .= '<tr>';
                foreach ($headers as $header) {
                    $html .= '<td>' . htmlspecialchars($rowData[$header] ?? '') . '</td>';
                }
                $html .= '</tr>';
            }

            $html .= '</tbody></table>';
        }

        $pdf->writeHTML($html, true, false, true, false, '');

        $filePath = $this->buildTempPath('pdf', $file->id);
        $pdf->Output($filePath, 'F');

        $this->logActivity($file->id, 'export_pdf', "Экспорт в PDF: {$file->original_name}");

        return $filePath;
    }

    /**
     * @return list<FileRowEntity>
     */
    private function fetchRows(string $fileId): array
    {
        return $this->fileRowModel
            ->where('file_id', $fileId)
            ->orderBy('row_index', 'ASC')
            ->findAll();
    }

    private function buildTempPath(string $extension, string $fileId): string
    {
        return rtrim($this->writePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . self::TEMP_DIRECTORY . DIRECTORY_SEPARATOR . 'export_' . $fileId . '_' . time() . '.' . $extension;
    }

    private function ensureTempDirectory(): void
    {
        $path = rtrim($this->writePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . self::TEMP_DIRECTORY;

        if (!is_dir($path) && !mkdir($path, 0755, true) && !is_dir($path)) {
            throw new RuntimeException('Не удалось создать временную директорию для экспорта');
        }
    }

    private function logActivity(string $fileId, string $action, string $description): void
    {
        $this->activityLogModel->insert([
            'file_id' => $fileId,
            'action' => $action,
            'description' => $description,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

