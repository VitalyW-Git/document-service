'use strict';

$(function () {
    const api = window.documentApi;
    const tableRoot = document.getElementById('documentFileRoot');
    if (!tableRoot || !api) {
        return;
    }

    const baseUrl = window.appConfig.baseUrl;
    let currentPage = 1;

    $('#uploadForm').on('submit', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $message = $('#uploadMessage');
        const fileInput = document.getElementById('file');
        const formData = new FormData(this);
        $message.empty();
        if (!fileInput.files || fileInput.files.length === 0) {
            $message.html('<div class="alert alert-danger">Выберите файл!</div>');
            return;
        }

        $('#uploadButton').prop('disabled', true).text('Загрузка...');

        api.uploadDocument(formData)
            .done(function (response) {
                if (response.success) {
                    $message.html('<div class="alert alert-success">' + response.message + '</div>');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    $message.html('<div class="alert alert-danger">' + (response.message || 'Ошибка загрузки') + '</div>');
                }
            })
            .fail(function (xhr) {
                console.error(xhr.responseText);
                $message.html('<div class="alert alert-danger">Ошибка сервера.</div>');
            })
            .always(function () {
                $('#uploadButton').prop('disabled', false).text('Загрузить');
            });
    });
    $(document).on('click', '.delete-file', function () {
        const fileId = this.dataset.fileId;
        if (!confirm('Вы уверены, что хотите удалить эту запись?')) {
            return;
        }
        deleteFile(fileId);
    });

    loadFiles();

    function loadFiles(page = 1) {
        currentPage = page;

        api.fetchFiles(page)
            .done((response) => {
                if (response && !!response?.list) {
                    drawTable(response.list);
                    drawPagination(response);
                } else {
                    $('#filesContainer').html('<p class="text-center">Нет данных</p>');
                    $('#paginationFilesContainer').empty();
                }
            })
            .fail(() => {
                $('#filesContainer').html('<div class="alert alert-danger">Ошибка при загрузке данных</div>');
            });
    }

    function drawTable(list) {
        const html = builderElementTable(list);
        $('#filesContainer').html(html);
    }

    function builderElementTable(list) {
        let html = `<table class="table table-striped mb-0"><thead><tr>
        <th>Имя</th><th>Дата создания</th><th>Дата изменения</th><th>Количество строк</th><th>Действия</th>
        </tr></thead><tbody>`;

        list.forEach((file) => {
            html += `<tr>
                        <td>${file.name}</td>
                        <td>${file.createdAt}</td>
                        <td>${file.updatedAt}</td>
                        <td>${file.rowCount}</td>
                        <td>
                            <a href="${baseUrl}view/${file.id}" class="btn btn-sm btn-primary me-1">Просмотр</a>
                            <a href="${baseUrl}document/export-excel/${file.id}" class="btn btn-sm btn-success me-1">Скачать</a>
                            <button class="btn btn-sm btn-danger delete-file" data-file-id="${file.id}">Удалить</button>
                        </td>
                    </tr>`;
        });

        return html += '</tbody></table>';
    }

    function drawPagination(response) {
        if (response.totalPages <= 1) {
            $('#paginationFilesContainer').html('');
            return;
        }

        let html = builderElementPagination(response);
        $('#paginationFilesContainer').html(html);

        $('#paginationFilesContainer .page-link').on('click', function (event) {
            event.preventDefault();
            const page = Number(this.dataset.page);
            if (Number.isNaN(page) || page === currentPage) {
                return;
            }
            loadFiles(page);
        });
    }

    function builderElementPagination(response) {
        let html = '<ul class="pagination justify-content-center mb-0">';

        if (response.currentPage > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" data-page="${response.currentPage - 1}">Предыдущая</a></li>`;
        }

        for (let i = 1; i <= response.totalPages; i += 1) {
            html += `<li class="page-item ${i === response.currentPage ? 'active' : ''}">`;
            html += `<a class="page-link" href="#" data-page="${i}">${i}</a>`;
            html += '</li>';
        }

        if (response.totalPages !== response.currentPage) {
            html += `<li class="page-item"><a class="page-link" href="#" data-page="${response.currentPage + 1}">Следующая</a></li>`;
        }

        return html += '</ul>';
    }

    function deleteFile(fileId) {
        api.deleteFile(fileId)
            .done((response) => {
                if (response?.success) {
                    showToast('Запись успешно удалена.');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    alert(response?.message || 'Не удалось удалить запись');
                }
            })
            .fail(() => {
                alert('Ошибка при удалении записи');
            });
    }
});
