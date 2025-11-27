<?php

namespace App\Services\Document;

use App\Entities\Document\FileEntity;
use App\Entities\Document\FileRowEntity;
use App\Models\Document\ActivityLogModel;
use App\Models\Document\FileModel;
use App\Models\Document\FileRowModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

final class DocumentStorageService
{
    public function __construct(
        private readonly FileModel $fileModel,
        private readonly FileRowModel $fileRowModel,
        private readonly ActivityLogModel $activityLogModel,
        private readonly string $uploadPath
    ) {
        $this->ensureUploadDirectory();
    }

    public function getFiles(int $page, int $perPage = 10): array
    {
        $files = $this->fileModel->orderBy('created_at', 'DESC')
            ->paginate($perPage, 'default', $page);
        return array_map(
            static fn (FileEntity $file) => $file->toArray(),
            $files
        );
    }

    public function upload(UploadedFile $file): string
    {
        if (!$file->isValid()) {
            throw new RuntimeException('Файл не загружен');
        }

        $extension = strtolower($file->getExtension());
        if (!in_array($extension, ['xlsx', 'xls'], true)) {
            throw new RuntimeException('Разрешены только файлы Excel (.xlsx, .xls)');
        }

        $originalName = $file->getName();
        $newName = $file->getRandomName();
        $filePath = $this->uploadPath . $newName;

        if (!$file->move($this->uploadPath, $newName)) {
            throw new RuntimeException('Ошибка при сохранении файла');
        }

        try {
            $spreadsheet = IOFactory::load($filePath);
            $rows = $spreadsheet->getActiveSheet()->toArray();
            $rowCount = max(0, count($rows) - 1);

            $fileId = $this->fileModel->insert([
                'name' => pathinfo($originalName, PATHINFO_FILENAME),
                'original_name' => $originalName,
                'file_path' => $filePath,
                'row_count' => $rowCount,
            ]);

            $headers = array_shift($rows) ?? [];
            $this->fileRowInsert((string) $fileId, $headers, $rows);

            $this->activityLogInsert((string) $fileId, 'upload', "Загружен файл: {$originalName}");

            return (string) $fileId;
        } catch (\Throwable $exception) {
            if (is_file($filePath)) {
                unlink($filePath);
            }

            throw new RuntimeException('Ошибка при обработке файла: ' . $exception->getMessage(), 0, $exception);
        }
    }

    public function addRow(FileEntity $file, array $dataPost): string
    {
        $maxRow = $this->fileRowModel
            ->selectMax('row_index')
            ->where('file_id', $file->id)
            ->asArray()
            ->first();

        $newIndex = (int) ($maxRow['row_index'] ?? 0) + 1;

        $fileRowId = $this->fileRowModel->insert([
            'file_id' => $file->id,
            'row_data' => json_encode($dataPost, JSON_UNESCAPED_UNICODE),
            'row_index' => $newIndex,
        ]);

        $this->fileModel->update($file->id, ['row_count' => ($file->row_count ?? 0) + 1]);
        $this->activityLogInsert($file->id, 'add_row', "Добавлена строка #{$newIndex}");

        return (string) $fileRowId;
    }

    public function updateRow(FileEntity $file, string $rowId, array $rowData): void
    {
        $fileRow = $this->findRowForFile($file->id, $rowId);

        $this->fileRowModel->update($rowId, [
            'row_data' => json_encode($rowData, JSON_UNESCAPED_UNICODE),
        ]);

        $this->activityLogInsert($file->id, 'update_row', "Обновлена строка #{$fileRow->row_index}");
    }

    public function deleteRow(FileEntity $file, string $rowId): void
    {
        $fileRow = $this->findRowForFile($file->id, $rowId);

        $this->fileRowModel->delete($rowId);

        $updatedCount = max(0, ($file->row_count ?? 0) - 1);
        $this->fileModel->update($file->id, ['row_count' => $updatedCount]);

        $this->activityLogInsert($file->id, 'delete_row', "Удалена строка #{$fileRow->row_index}");
    }

    public function deleteFile(FileEntity $file): void
    {
        if (is_file($file->file_path)) {
            unlink($file->file_path);
        }

        $this->fileRowModel->where('file_id', $file->id)->delete();
        $this->fileModel->delete($file->id);
        $this->activityLogInsert($file->id, 'delete_file', "Удален файл: {$file->original_name}");
    }

    /**
     * @return array{rows: list<array<string,mixed>>, pager: \CodeIgniter\Pager\Pager}
     */
    public function paginateRows(string $fileId, int $page, int $perPage = 5): array
    {
        $rows = $this->fileRowModel
            ->where('file_id', $fileId)
            ->orderBy('row_index', 'ASC')
            ->paginate($perPage, 'default', $page);

        $decodedRows = array_map(
            static fn (FileRowEntity $row): array => array_merge(
                $row->toArray(),
                ['row_data' => $row->row_data]
            ),
            $rows
        );

        return [
            'rows' => $decodedRows,
            'pager' => $this->fileRowModel->pager,
        ];
    }

    private function fileRowInsert(string $fileId, array $headers, array $rows): void
    {
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
    }

    private function findRowForFile(string $fileId, string $rowId): FileRowEntity
    {
        /** @var FileRowEntity|null $fileRow */
        $fileRow = $this->fileRowModel->find($rowId);

        if (!$fileRow || $fileRow->file_id !== $fileId) {
            throw new RuntimeException('Строка не найдена');
        }

        return $fileRow;
    }

    private function activityLogInsert(string $fileId, string $action, string $description = ''): void
    {
        $this->activityLogModel->insert([
            'file_id' => $fileId,
            'action' => $action,
            'description' => $description,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function ensureUploadDirectory(): void
    {
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }
}

