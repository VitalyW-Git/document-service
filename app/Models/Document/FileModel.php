<?php

namespace App\Models\Document;

use App\Entities\Document\FileEntity;

final class FileModel extends ModelAbstract
{
    protected $table = 'files';
    protected $returnType = FileEntity::class;

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