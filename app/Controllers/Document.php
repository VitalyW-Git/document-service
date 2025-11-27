<?php

namespace App\Controllers;

use App\Models\FileModel;
use App\Models\FileRowModel;
use App\Models\ActivityLogModel;
use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use TCPDF;

class Document extends BaseController
{
    protected FileModel $fileModel;
    protected FileRowModel $fileRowModel;
    protected ActivityLogModel $activityLogModel;
    protected string $uploadPath;
    protected $helpers = ['url', 'text'];

    public function __construct()
    {
        $this->fileModel = new FileModel();
        $this->fileRowModel = new FileRowModel();
        $this->activityLogModel = new ActivityLogModel();
        $this->uploadPath = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR;
    }

    public function index()
    {
        $page = $this->request->getGet('page') ?? 1;
        $perPage = 10;

        $files = $this->fileModel->orderBy('created_at', 'DESC')
            ->paginate($perPage, 'default', $page);

        $data = [
            'files' => $files,
            'pager' => $this->fileModel->pager,
        ];

        return view('Document/index', $data);
    }

    public function view($id)
    {
        $file = $this->fileModel->find($id);
        if (!$file) {
            return redirect()->to('/Document')->with('error', 'Файл не найден');
        }

        $data = [
            'file' => $file,
        ];

        return view('Document/view', $data);
    }

    
    public function upload()
    {
        $file = $this->request->getFile('file');
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Файл не загружен']);
        }

        $allowedExtensions = ['xlsx', 'xls'];
        $extension = $file->getExtension();

        if (!in_array($extension, $allowedExtensions)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Разрешены только файлы Excel (.xlsx, .xls)']);
        }

        $originalName = $file->getName();
        $newName = $file->getRandomName();
        $filePath = $this->uploadPath . $newName;
        if (!$file->move($this->uploadPath, $newName)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Ошибка при сохранении файла']);
        }
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            $rowCount = count($rows) - 1;
            $fileData = [
                'name' => pathinfo($originalName, PATHINFO_FILENAME),
                'original_name' => $originalName,
                'file_path' => $filePath,
                'row_count' => $rowCount,
            ];

            $fileId = $this->fileModel->insert($fileData);
            $headers = array_shift($rows);

            foreach ($rows as $index => $row) {
                $rowData = [];
                foreach ($headers as $colIndex => $header) {
                    $rowData[$header] = $row[$colIndex] ?? '';
                }
                $this->fileRowModel->insert([
                    'file_id' => $fileId,
                    'row_data' => json_encode($rowData, JSON_UNESCAPED_UNICODE),
                    'row_index' => $index + 1,
                ]);
            }

            $this->logActivity($fileId, 'upload', "Загружен файл: {$originalName}");

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Файл успешно загружен',
                'file_id' => $fileId,
            ]);
        } catch (\Exception $e) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            return $this->response->setJSON(['success' => false, 'message' => 'Ошибка при обработке файла: ' . $e->getMessage()]);
        }
    }

    public function getRows(string $id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Только AJAX запросы']);
        }

        $page = $this->request->getGet('page') ?? 1;
        $perPage = 5;

        $rows = $this->fileRowModel->where('file_id', $id)
            ->orderBy('row_index', 'ASC')
            ->paginate($perPage, 'default', $page);

        $decodedRows = [];
        foreach ($rows as $row) {
            $row['row_data'] = json_decode($row['row_data'], true);
            $decodedRows[] = $row;
        }

        return $this->response->setJSON([
            'list' =>  $decodedRows,
            'currentPage' => $this->fileRowModel->pager->getCurrentPage(),
            'totalPages' => $this->fileRowModel->pager->getPageCount(),
        ]);
    }

    public function addRow(string $id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Только AJAX запросы']);
        }

        $file = $this->fileModel->find($id);
        if (!$file) {
            return $this->response->setJSON(['success' => false, 'message' => 'Файл не найден']);
        }

        $postData = $this->request->getPost();
        unset($postData['csrf_test_name']);

        $maxIndex = $this->fileRowModel->where('file_id', $id)
            ->selectMax('row_index')
            ->first();
        $newIndex = ($maxIndex['row_index'] ?? 0) + 1;

        $rowId = $this->fileRowModel->insert([
            'file_id' => $id,
            'row_data' => json_encode($postData, JSON_UNESCAPED_UNICODE),
            'row_index' => $newIndex,
        ]);

        $file = $this->fileModel->find($id);
        $this->fileModel->update($id, ['row_count' => ($file['row_count'] ?? 0) + 1]);
        $this->logActivity($id, 'add_row', "Добавлена строка #{$newIndex}");

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Строка успешно добавлена',
            'row_id' => $rowId,
        ]);
    }

    public function updateRow(string $id, string $rowId)
    {
        $file = $this->fileModel->find($id);
        if (!$file) {
            return $this->response->setJSON(['success' => false, 'message' => 'Файл не найден']);
        }

        $rowModel = $this->fileRowModel->find($rowId);
        if (!$rowModel || $rowModel['file_id'] != $id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Строка не найдена']);
        }

        $postData = $this->request->getPost();
        unset($postData['csrf_test_name']);

        $this->fileRowModel->update($rowId, [
            'row_data' => json_encode($postData, JSON_UNESCAPED_UNICODE),
        ]);

        $this->logActivity($id, 'update_row', "Обновлена строка #{$rowModel['row_index']}");

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Строка успешно обновлена',
        ]);
    }

    public function deleteRow(string $id, string $rowId)
    {
        $file = $this->fileModel->find($id);
        if (!$file) {
            return $this->response->setJSON(['success' => false, 'message' => 'Файл не найден']);
        }

        $rowModel = $this->fileRowModel->find($rowId);
        if (!$rowModel || $rowModel['file_id'] != $id) {
            return $this->response->setJSON(['success' => false, 'message' => 'Строка не найдена']);
        }

        $this->fileRowModel->delete($rowId);
        $file = $this->fileModel->find($id);
        $this->fileModel->update($id, ['row_count' => max(0, ($file['row_count'] ?? 0) - 1)]);
        $this->logActivity($id, 'delete_row', "Удалена строка #{$rowModel['row_index']}");

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Строка успешно удалена',
        ]);
    }

    public function delete(string $id)
    {
        $file = $this->fileModel->find($id);
        if (!$file) {
            return $this->response->setJSON(['success' => false, 'message' => 'Файл не найден']);
        }

        if (file_exists($file['file_path'])) {
            unlink($file['file_path']);
        }

        $this->fileRowModel->where('file_id', $id)->delete();
        $this->fileModel->delete($id);
        $this->logActivity($id, 'delete_file', "Удален файл: {$file['original_name']}");

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Файл успешно удален',
        ]);
    }

    public function exportExcel(string $id)
    {
        $file = $this->fileModel->find($id);
        if (!$file) {
            return redirect()->to('/Document')->with('error', 'Файл не найден');
        }

        $rows = $this->fileRowModel->where('file_id', $id)
            ->orderBy('row_index', 'ASC')
            ->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Данные');

        if (!empty($rows)) {
            $firstRow = json_decode($rows[0]['row_data'], true);
            $headers = array_keys($firstRow);
            
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $col++;
            }

            $rowNum = 2;
            foreach ($rows as $row) {
                $rowData = json_decode($row['row_data'], true);
                $col = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue($col . $rowNum, $rowData[$header] ?? '');
                    $col++;
                }
                $rowNum++;
            }
        }

        $writer = new Xlsx($spreadsheet);
        $filename = WRITEPATH . 'temp' . DIRECTORY_SEPARATOR . 'export_' . $id . '_' . time() . '.xlsx';
        
        if (!is_dir(WRITEPATH . 'temp')) {
            mkdir(WRITEPATH . 'temp', 0755, true);
        }

        $writer->save($filename);

        $this->logActivity($id, 'export_excel', "Экспорт в Excel: {$file['original_name']}");

        return $this->response->download($filename, null)->setFileName($file['name'] . '_export.xlsx');
    }

    public function exportPdf(string $id): void
    {
        $file = $this->fileModel->find($id);
        if (!$file) {
            throw new Exception('Файл не найден');
        }

        $rows = $this->fileRowModel->where('file_id', $id)
            ->orderBy('row_index', 'ASC')
            ->findAll();

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('document Service');
        $pdf->SetAuthor('document Service');
        $pdf->SetTitle($file['name']);
        $pdf->SetSubject('Export');
        $pdf->SetKeywords('Excel, PDF, Export');

        $pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
        $pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(15, 27, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        $pdf->SetAutoPageBreak(TRUE, 25);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->setLanguageArray([]);

        $pdf->AddPage();
        $pdf->SetFont('dejavusans', '', 10);

        $html = '<h1>' . htmlspecialchars($file['name']) . '</h1>';
        $html .= '<p><strong>Дата создания:</strong> ' . $file['created_at'] . '</p>';
        $html .= '<p><strong>Количество строк:</strong> ' . $file['row_count'] . '</p>';

        if (!empty($rows)) {
            $firstRow = json_decode($rows[0]['row_data'], true);
            $headers = array_keys($firstRow);

            $html .= '<table border="1" cellpadding="5" cellspacing="0">';
            $html .= '<thead><tr>';
            foreach ($headers as $header) {
                $html .= '<th><strong>' . htmlspecialchars($header) . '</strong></th>';
            }
            $html .= '</tr></thead><tbody>';

            foreach ($rows as $row) {
                $rowData = json_decode($row['row_data'], true);
                $html .= '<tr>';
                foreach ($headers as $header) {
                    $html .= '<td>' . htmlspecialchars($rowData[$header] ?? '') . '</td>';
                }
                $html .= '</tr>';
            }

            $html .= '</tbody></table>';
        }

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output($file['name'] . '_export.pdf', 'D');

        $this->logActivity($id, 'export_pdf', "Экспорт в PDF: {$file['original_name']}");
    }

    protected function logActivity(string $fileId, string $action, string $description = '')
    {
        $this->activityLogModel->insert([
            'file_id' => $fileId,
            'action' => $action,
            'description' => $description,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}

