<?php

namespace App\Models;

final class ActivityLogModel extends AbstractModel
{
    protected $table = 'activity_log';

    protected $allowedFields = [
        'id',
        'file_id',
        'action', 
        'description', 
        'created_at'
    ];

    protected $useTimestamps = false;
    protected $updatedField = null;

    protected $validationRules = [
        'file_id' => 'required',
        'action' => 'required|max_length[100]',
    ];
}

