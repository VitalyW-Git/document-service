<?= $this->extend('layouts/default') ?>

<?= $this->section('title') ?>Список файлов<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Управление документами</h1>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Загрузка файла</h5>
                </div>
                <div class="card-body">
                    <form id="uploadForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="file" class="form-label">Выберите Excel файл (.xlsx, .xls)</label>
                            <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls" required>
                        </div>
                        <button type="submit" id="uploadButton" class="btn btn-primary">Загрузить</button>
                    </form>
                    <div id="uploadMessage" class="mt-3"></div>
                </div>
            </div>

            <div class="card" id="documentFileRoot">
                <div class="card-header">
                    <h5 class="mb-0">Список файлов</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive" id="filesContainer">
                        <div class="text-center py-4">Список загружается...</div>
                    </div>
                    <div id="paginationFilesContainer" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="/assets/template/document/api-js/document-api.js"></script>
<script src="/assets/shared/js/toast-container.js"></script>
<script src="/assets/template/document/main/js/document-main.js"></script>
<?= $this->endSection() ?>
