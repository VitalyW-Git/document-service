'use strict';

window.documentApi = (function () {
    function buildUrl(path) {
        return `${window.appConfig.baseUrl}${path}`;
    }

    function fetchRows(fileId, page) {
        return $.ajax({
            url: buildUrl(`document/get-rows/${fileId}`),
            type: 'GET',
            dataType: 'json',
            data: { page },
        });
    }

    function addRow(fileId, data) {
        return $.ajax({
            url: buildUrl(`document/add-row/${fileId}`),
            type: 'POST',
            dataType: 'json',
            data,
        });
    }

    function updateRow(fileId, rowId, data) {
        return $.ajax({
            url: buildUrl(`document/update-row/${fileId}/${rowId}`),
            type: 'POST',
            dataType: 'json',
            data,
        });
    }

    function deleteRow(fileId, rowId) {
        return $.ajax({
            url: buildUrl(`document/delete-row/${fileId}/${rowId}`),
            type: 'POST',
            dataType: 'json',
        });
    }

    function uploadDocument(formData) {
        return $.ajax({
            url: buildUrl('document/upload'),
            type: 'POST',
            dataType: 'json',
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
        });
    }

    return {
        fetchRows,
        addRow,
        updateRow,
        deleteRow,
        uploadDocument,
    };
})();

