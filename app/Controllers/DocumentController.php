<?php

namespace App\Controllers;

use App\Entities\Document\FileEntity;
use App\Models\Document\FileModel;
use App\Services\Document\DocumentExportService;
use App\Services\Document\DocumentStorageService;
use CodeIgniter\HTTP\ResponseInterface;
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

    public function index(): string
    {
        return view('Document/index');
    }

    public function listFiles(): ResponseInterface
    {
        $page = $this->request->getPost('page') ?? 1;
        $paginateFiles = $this->storageService->getPaginateFiles($page);

        return $this->response->setJSON([
            'list' => $paginateFiles['files'],
            'currentPage' => $paginateFiles['pager']->getCurrentPage(),
            'totalPages' => $paginateFiles['pager']->getPageCount(),
        ]);
    }

    public function getRows(string $id): ResponseInterface
    {
        $page = $this->request->getGet('page') ?? 1;
        $paginateFileRows = $this->storageService->getPaginateFileRows($id, $page);

        return $this->response->setJSON([
            'list' => $paginateFileRows['rows'],
            'currentPage' => $paginateFileRows['pager']->getCurrentPage(),
            'totalPages' => $paginateFileRows['pager']->getPageCount(),
        ]);
    }

    public function view($id): string|ResponseInterface
    {
        /** @var FileEntity $file */
        $file = $this->fileModel->find($id);
        if (!$file) {
            return redirect()->to('/Document')->with('error', 'Файл не найден');
        }

        return view('Document/view', ['file' => $file->toArray()]);
    }

    public function upload(): ResponseInterface
    {
        $file = $this->request->getFile('file');
        try {
            if (!$file) {
                throw new Exception('Файл не найден');
            }
            $fileId = $this->storageService->upload($file);
        } catch (RuntimeException $exception) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $exception->getMessage(),
            ]);
        }
        return $this->response->setJSON([
            'success' => true,
            'message' => 'Файл успешно загружен',
            'file_id' => $fileId,
        ]);
    }

    public function addRow(string $id): ResponseInterface
    {
        /** @var FileEntity|null $file */
        $file = $this->fileModel->find($id);
        try {
            if (!$file) {
                throw new Exception('Файл не найден');
            }
            $fileRowId = $this->storageService->addRow($file, $this->request->getPost());
        } catch (RuntimeException $exception) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Строка успешно добавлена',
            'rowId' => $fileRowId,
        ]);
    }

    public function updateRow(string $id, string $rowId): ResponseInterface
    {
        /** @var FileEntity $file */
        $file = $this->fileModel->find($id);
        try {
            if (!$file) {
                throw new Exception('Файл не найден');
            }
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

    public function deleteRow(string $id, string $rowId): ResponseInterface
    {
        /** @var FileEntity $file */
        $file = $this->fileModel->find($id);
        try {
            if (!$file) {
                throw new Exception('Файл не найден');
            }
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

    public function delete(string $id): ResponseInterface
    {
        /** @var FileEntity $file */
        $file = $this->fileModel->find($id);
        try {
            if (!$file) {
                throw new Exception('Файл не найден');
            }
            $this->storageService->deleteFile($file);
        } catch (RuntimeException $exception) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $exception->getMessage()
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Файл успешно удален',
        ]);
    }

    public function exportExcel(string $id): ResponseInterface
    {
        /** @var FileEntity $file */
        $file = $this->fileModel->find($id);
        try {
            if (!$file) {
                throw new Exception('Файл не найден');
            }
            $filePath = $this->exportService->exportExcel($file);
        } catch (RuntimeException $exception) {
            throw new Exception('Ошибка экспорта EXCEL: ' . $exception->getMessage());
        }

        return $this->response->download($filePath, null)->setFileName($file->getName() . '_export.xlsx');
    }

    public function exportPdf(string $id): ResponseInterface
    {
        /** @var FileEntity $file */
        $file = $this->fileModel->find($id);
        try {
            if (!$file) {
                throw new Exception('Файл не найден');
            }
            $filePath = $this->exportService->exportPdf($file);
        } catch (RuntimeException $exception) {
            throw new Exception('Ошибка экспорта PDF: ' . $exception->getMessage());
        }

        return $this->response->download($filePath, null)->setFileName($file->getName() . '_export.pdf');
    }
}

