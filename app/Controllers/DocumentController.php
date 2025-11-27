<?php

namespace App\Controllers;

use App\Entities\Document\FileEntity;
use App\Models\Document\FileModel;
use App\Services\Document\DocumentExportService;
use App\Services\Document\DocumentStorageService;
use Exception;
use RuntimeException;

class DocumentController extends BaseController
{
    protected DocumentStorageService $storageService;
    protected DocumentExportService $exportService;
    protected FileModel $fileModel;

    public function __construct()
    {
        $this->fileModel = new FileModel();
        $this->storageService = service('documentStorage');
        $this->exportService = service('documentExport');
    }

    public function index()
    {
        return view('Document/index');
    }

    public function listFiles()
    {
        $page = (int) ($this->request->getPost('page') ?? 1);
        $page = max(1, $page);
        $filesData = $this->storageService->paginateFiles($page);

        return $this->response->setJSON([
            'list' => $filesData['files'],
            'currentPage' => $filesData['pager']->getCurrentPage(),
            'totalPages' => $filesData['pager']->getPageCount(),
        ]);
    }

    public function view($id)
    {
        /** @var FileEntity $file */
        $file = $this->fileModel->find($id);
        if (!$file) {
            return redirect()->to('/Document')->with('error', 'Файл не найден');
        }

        return view('Document/view', ['file' => $file->toArray()]);
    }

    public function upload()
    {
        $file = $this->request->getFile('file');
        if (!$file) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Файл не загружен'
            ]);
        }

        try {
            $fileId = $this->storageService->upload($file);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Файл успешно загружен',
                'file_id' => $fileId,
            ]);
        } catch (RuntimeException $exception) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function getRows(string $id)
    {
        $page = $this->request->getGet('page') ?? 1;
        $rowsData = $this->storageService->paginateRows($id, $page);

        return $this->response->setJSON([
            'list' => $rowsData['rows'],
            'currentPage' => $rowsData['pager']->getCurrentPage(),
            'totalPages' => $rowsData['pager']->getPageCount(),
        ]);
    }

    public function addRow(string $id)
    {
        /** @var FileEntity|null $file */
        $file = $this->fileModel->find($id);
        if (!$file) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Файл не найден'
            ]);
        }
        $fileRowId = $this->storageService->addRow($file, $this->request->getPost());

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Строка успешно добавлена',
            'rowId' => $fileRowId,
        ]);
    }

    public function updateRow(string $id, string $rowId)
    {
        /** @var FileEntity $file */
        $file = $this->fileModel->find($id);
        if (!$file) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Файл не найден'
            ]);
        }
        try {
            $this->storageService->updateRow($file, $rowId, $this->request->getPost());
        } catch (RuntimeException $exception) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Строка успешно обновлена',
        ]);
    }

    public function deleteRow(string $id, string $rowId)
    {
        /** @var FileEntity $file */
        $file = $this->fileModel->find($id);
        if (!$file) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Файл не найден'
            ]);
        }

        try {
            $this->storageService->deleteRow($file, $rowId);
        } catch (RuntimeException $exception) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Строка успешно удалена',
        ]);
    }

    public function delete(string $id)
    {
        /** @var FileEntity $file */
        $file = $this->fileModel->find($id);
        if (!$file) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Файл не найден'
            ]);
        }

        $this->storageService->deleteFile($file);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Файл успешно удален',
        ]);
    }

    public function exportExcel(string $id)
    {
        /** @var FileEntity $file */
        $file = $this->fileModel->find($id);
        if (!$file) {
            throw new Exception('Файл не найден');
        }

        try {
            $filePath = $this->exportService->exportExcel($file);
        } catch (RuntimeException $exception) {
            throw new Exception('Ошибка экспорта EXCEL: ' . $exception->getMessage());
        }

        return $this->response->download($filePath, null)->setFileName($file->getName() . '_export.xlsx');
    }

    public function exportPdf(string $id)
    {
        /** @var FileEntity $file */
        $file = $this->fileModel->find($id);
        if (!$file) {
            throw new Exception('Файл не найден');
        }

        try {
            $filePath = $this->exportService->exportPdf($file);
        } catch (RuntimeException $exception) {
            throw new Exception('Ошибка экспорта PDF: ' . $exception->getMessage());
        }

        return $this->response->download($filePath, null)->setFileName($file->getName() . '_export.pdf');
    }
}

