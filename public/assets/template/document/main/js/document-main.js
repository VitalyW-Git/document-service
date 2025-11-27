'use strict';

$(function () {
    const api = window.documentApi;

    $('#uploadForm').on('submit', function(e) {
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
            .done(function(response) {
                if (response.success) {
                    $message.html('<div class="alert alert-success">' + response.message + '</div>');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    $message.html('<div class="alert alert-danger">' + (response.message || 'Ошибка загрузки') + '</div>');
                }
            })
            .fail(function(xhr) {
                console.error(xhr.responseText);
                $message.html('<div class="alert alert-danger">Ошибка сервера.</div>');
            })
            .always(function() {
                $('#uploadButton').prop('disabled', false).text('Загрузить');
            });
    });
});

