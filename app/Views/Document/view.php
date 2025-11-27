<?= $this->extend('layouts/default') ?>

<?= $this->section('title') ?>Просмотр файла<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div
    class="container mt-4"
    id="documentViewRoot"
    data-file-id="<?= esc($file['id']) ?>"
>
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><?= esc($file['name']) ?></h1>
                <div>
                    <a href="<?= base_url('/') ?>" class="btn btn-secondary">Назад к списку</a>
                    <a href="<?= base_url('document/export-excel/' . $file['id']) ?>" class="btn btn-success">Экспорт в
                        Excel</a>
                    <a href="<?= base_url('document/export-pdf/' . $file['id']) ?>" class="btn btn-danger">Экспорт в
                        PDF</a>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Информация о файле</h5>
                </div>
                <div class="card-body">
                    <p><strong>Оригинальное имя:</strong> <?= esc($file['original_name']) ?></p>
                    <p><strong>Дата создания:</strong> <?= esc($file['created_at']) ?></p>
                    <p><strong>Дата изменения:</strong> <?= esc($file['updated_at']) ?></p>
                    <p><strong>Количество строк:</strong> <?= esc($file['row_count']) ?></p>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Содержимое файла</h5>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addRowModal">
                        Добавить строку
                    </button>
                </div>
                <div class="card-body">
                    <div id="rowsContainer">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Загрузка...</span>
                            </div>
                        </div>
                    </div>

                    <nav aria-label="Page navigation" id="paginationContainer" class="mt-3">
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->include('Document/Modal/addRow') ?>

<?= $this->include('Document/Modal/editRow') ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="/assets/template/document/api-js/document-api.js"></script>
<script src="/assets/shared/js/toast-container.js"></script>
<script src="/assets/template/document/view/js/document-view.js"></script>
<?= $this->endSection() ?>


