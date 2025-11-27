<?php

namespace App\Services\Document\Export;

use App\Entities\Document\FileEntityInterface;
use TCPDF;

final class PdfDocumentExporter extends AbstractDocumentExporter
{
    public function export(FileEntityInterface $file): string
    {
        $rows = $this->fetchRows($file->getId());

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('document Service');
        $pdf->SetAuthor('document Service');
        $pdf->SetTitle($file->getName());
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

        $html = '<h1>' . htmlspecialchars($file->getName()) . '</h1>';
        $html .= '<p><strong>Дата создания:</strong> ' . $file->getCreatedAt() . '</p>';
        $html .= '<p><strong>Количество строк:</strong> ' . $file->getRowCount() . '</p>';

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

        $filePath = $this->buildTempPath('pdf', $file->getId());
        $pdf->Output($filePath, 'F');

        $this->logActivity($file->getId(), 'export_pdf', "Экспорт в PDF: {$file->getOriginalName()}");

        return $filePath;
    }
}


