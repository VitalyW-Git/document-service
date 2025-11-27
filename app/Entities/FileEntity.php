<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property string      $id
 * @property string      $name
 * @property string      $original_name
 * @property string      $file_path
 * @property int|null    $row_count
 * @property string|null $created_at
 * @property string|null $updated_at
 */
final class FileEntity extends Entity
{
    /** @var list<string>  */
    protected $dates = ['created_at', 'updated_at'];

    /** @var list<string>  */
    protected $casts = [
        'row_count' => 'integer',
    ];
}


