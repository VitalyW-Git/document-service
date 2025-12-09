<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Entities\Document\FileEntity;
use App\Models\Document\FileModel;
use App\Services\Document\DocumentExportService;
use App\Services\Document\DocumentService;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;
use Throwable;

class DocumentController extends BaseController
{
    protected DocumentService $documentService;
    protected DocumentExportService $documentExportService;
    protected FileModel $fileModel;

    public function __construct()
    {
        $this->fileModel = new FileModel();
        $this->documentService = service('documentService');
        $this->documentExportService = service('documentExportService');
    }

    public function index(): string
    {
        return view('Document/index');
    }

    public function listFiles(): ResponseInterface
    {
        $page = (int) $this->request->getPost('page') ?? 1;
        $paginateFiles = $this->documentService->getPaginateFiles($page);

        return $this->response->setJSON([
            'list' => $paginateFiles['files'],
            'currentPage' => $paginateFiles['pager']->getCurrentPage(),
            'totalPages' => $paginateFiles['pager']->getPageCount(),
        ]);
    }

    public function getRows(string $id): ResponseInterface
    {
        $page = (int) $this->request->getGet('page') ?? 1;
        $paginateFileRows = $this->documentService->getPaginateFileRows($id, $page);

        return $this->response->setJSON([
            'list' => $paginateFileRows['rows'],
            'currentPage' => $paginateFileRows['pager']->getCurrentPage(),
            'totalPages' => $paginateFileRows['pager']->getPageCount(),
        ]);
    }

    public function view(string $id): string|ResponseInterface
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
            $fileId = $this->documentService->upload($file);
        } catch (Throwable $exception) {
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
            $fileRowId = $this->documentService->addRow($file, $this->request->getPost());
        } catch (Throwable $exception) {
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
            $this->documentService->updateRow($file, $rowId, $this->request->getPost());
        } catch (Throwable $exception) {
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
            $this->documentService->deleteRow($file, $rowId);
        } catch (Throwable $exception) {
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
            $this->documentService->deleteFile($file);
        } catch (Throwable $exception) {
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
            $filePath = $this->documentExportService->exportExcel($file);
        } catch (Throwable $exception) {
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
            $filePath = $this->documentExportService->exportPdf($file);
        } catch (Throwable $exception) {
            throw new Exception('Ошибка экспорта PDF: ' . $exception->getMessage());
        }

        return $this->response->download($filePath, null)->setFileName($file->getName() . '_export.pdf');
    }
}

