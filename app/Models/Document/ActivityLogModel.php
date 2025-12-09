<?php

declare(strict_types=1);

namespace App\Models\Document;

use App\Entities\Document\ActivityLogEntity;

final class ActivityLogModel extends ModelAbstract
{
    protected $table = 'activity_log';
    protected $returnType = ActivityLogEntity::class;

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

