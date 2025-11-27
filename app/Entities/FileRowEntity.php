<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property string      $id
 * @property string      $file_id
 * @property array       $row_data
 * @property int         $row_index
 * @property string|null $created_at
 * @property string|null $updated_at
 */
final class FileRowEntity extends Entity
{
    /** @var list<string>  */
    protected $dates = ['created_at', 'updated_at'];

    /** @var list<string>  */
    protected $casts = [
        'row_data' => 'json-array',
        'row_index' => 'integer',
    ];
}