<?php

namespace App\Models;

final class FileRowModel extends AbstractModel
{
    protected $table = 'file_rows';

    protected $allowedFields = [
        'id',
        'file_id', 
        'row_data', 
        'row_index', 
        'created_at', 
        'updated_at'
    ];

    protected $validationRules = [
        'file_id' => 'required',
        'row_data' => 'required',
        'row_index' => 'required|integer',
    ];
}