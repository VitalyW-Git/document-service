<?php

namespace App\Models;

final class FileModel extends AbstractModel
{
    protected $table = 'files';

    protected $allowedFields = [
        'id',
        'name', 
        'original_name', 
        'file_path', 
        'row_count', 
        'created_at', 
        'updated_at'
    ];

    protected $validationRules = [
        'name' => 'required|max_length[150]',
        'original_name' => 'required|max_length[150]',
        'file_path' => 'required|max_length[500]',
    ];
}