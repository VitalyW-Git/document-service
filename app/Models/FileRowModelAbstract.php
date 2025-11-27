<?php

namespace App\Models;

use App\Entities\FileRowEntity;

final class FileRowModelAbstract extends ModelAbstract
{
    protected $table = 'file_rows';
    protected $returnType = FileRowEntity::class;

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