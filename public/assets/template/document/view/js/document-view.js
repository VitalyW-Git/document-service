'use strict';

$(function () {
    console.log(5555)
    const root = document.getElementById('documentViewRoot');
    if (!root) {
        return;
    }

    const api = window.documentApi;
    if (!api) {
        console.error('documentApi is not available');
        return;
    }

    const fileId = root.dataset.fileId;
    let currentPage = 1;
    let defaultRowData = [];

    loadRows();

    function loadRows(page = 1) {
        currentPage = page;
        api.fetchRows(fileId, page)
            .done((response) => {
                if (response && Array.isArray(response.list) && response.list.length) {
                    if (!defaultRowData.length) {
                        defaultRowData = Object.keys(response.list[0].row_data || {});
                        setupAddRowForm();
                        setupEditRowForm();
                    }
                    drawTable(response.list);
                    drawPagination(response);
                } else {
                    $('#rowsContainer').html('<p class="text-center">Нет данных</p>');
                    $('#paginationContainer').empty();
                }
            })
            .fail(() => {
                $('#rowsContainer').html('<div class="alert alert-danger">Ошибка при загрузке данных</div>');
            });
    }

    function drawTable(list) {
        if (!list.length) {
            $('#rowsContainer').html('<p class="text-center">Нет данных</p>');
            return;
        }

        let html = builderElementTable(list);
        $('#rowsContainer').html(html);

        $('.edit-row').on('click', function () {
            const rowId = $(this).data('id');
            const rowData = $(this).data('row');
            openEditModal(rowId, rowData);
        });

        $('.delete-row').on('click', function () {
            const rowId = $(this).data('id');
            if (confirm('Вы уверены, что хотите удалить эту строку?')) {
                deleteRow(rowId);
            }
        });
    }

    function builderElementTable(list) {
        let html = '<div class="table-responsive"><table class="table table-striped"><thead><tr>';
        defaultRowData.forEach((header) => {
            html += `<th>${header}</th>`;
        });
        html += '<th>Действия</th></tr></thead><tbody>';

        list.forEach((item) => {
            html += '<tr>';
            defaultRowData.forEach((header) => {
                const cellValue = item.row_data ? item.row_data[header] : '';
                html += `<td>${cellValue || ''}</td>`;
            });
            html += '<td>';
            html += `<button class="btn btn-sm btn-warning edit-row" data-id="${item.id}" data-row='${JSON.stringify(item.row_data || {})}'>Редактировать</button> `;
            html += `<button class="btn btn-sm btn-danger delete-row" data-id="${item.id}">Удалить</button>`;
            html += '</td></tr>';
        });

        return html += '</tbody></table></div>';
    }

    function drawPagination(response) {
        if (response.totalPages <= 1) {
            $('#paginationContainer').html('');
            return;
        }

        let html = builderElementPagination(response);
        $('#paginationContainer').html(html);

        $('#paginationContainer .page-link').on('click', function (event) {
            event.preventDefault();
            const page = $(this).data('page');
            loadRows(page);
        });
    }

    function builderElementPagination(response) {
        let html = '<ul class="pagination justify-content-center">';

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

    function setupAddRowForm() {
        let html = '';
        defaultRowData.forEach((header) => {
            html += '<div class="mb-3">';
            html += `<label class="form-label">${header}</label>`;
            html += `<input type="text" class="form-control" name="${header}" required>`;
            html += '</div>';
        });
        $('#addRowFields').html(html);
    }

    function setupEditRowForm() {
        let html = '';
        defaultRowData.forEach((header, index) => {
            const safeId = `edit_field_${index}`;
            html += '<div class="mb-3">';
            html += `<label class="form-label">${header}</label>`;
            html += `<input type="text" class="form-control" name="${header}" id="${safeId}" data-header="${header}" required>`;
            html += '</div>';
        });
        $('#editRowFields').html(html);
    }

    function openEditModal(rowId, rowData) {
        $('#editRowId').val(rowId);
        defaultRowData.forEach((header, index) => {
            const safeId = `edit_field_${index}`;
            $(`#${safeId}`).val((rowData && rowData[header]) || '');
        });
        new bootstrap.Modal(document.getElementById('editRowModal')).show();
    }

    $('#saveAddRow').on('click', function () {
        const formData = {};
        defaultRowData.forEach((header) => {
            formData[header] = $(`#addRowForm input[name="${header}"]`).val();
        });

        api.addRow(fileId, formData)
            .done((response) => {
                if (response?.success) {
                    bootstrap.Modal.getInstance(document.getElementById('addRowModal')).hide();
                    loadRows(currentPage);
                    showToast('Строка успешно добавлена.');
                } else {
                    alert(response?.message || 'Не удалось добавить строку');
                }
            })
            .fail(() => {
                alert('Ошибка при добавлении строки');
            });
    });

    $('#saveEditRow').on('click', function () {
        const rowId = $('#editRowId').val();
        const formData = {};
        defaultRowData.forEach((header) => {
            const input = $(`#editRowFields input[data-header="${header}"]`);
            formData[header] = input.length > 0 ? input.val() : '';
        });

        api.updateRow(fileId, rowId, formData)
            .done((response) => {
                if (response?.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editRowModal')).hide();
                    loadRows(currentPage);
                    showToast('Строка успешно обновлена.');
                } else {
                    alert(response?.message || 'Не удалось обновить строку');
                }
            })
            .fail(() => {
                alert('Ошибка при обновлении строки');
            });
    });

    function deleteRow(rowId) {
        api.deleteRow(fileId, rowId)
            .done((response) => {
                if (response?.success) {
                    loadRows(currentPage);
                    showToast('Строка успешно удалена.');
                } else {
                    alert(response?.message || 'Не удалось удалить строку');
                }
            })
            .fail(() => {
                alert('Ошибка при удалении строки');
            });
    }
});

