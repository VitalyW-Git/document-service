<?php

namespace App\Entities\Document;

use CodeIgniter\Entity\Entity;

/**
 * @property string      $id
 * @property string      $name
 * @property string      $original_name
 * @property string      $file_path
 * @property int         $row_count
 * @property string      $created_at
 * @property string      $updated_at
 */
final class FileEntity extends Entity implements FileEntityInterface
{
    /** @var list<string> */
    protected $dates = ['created_at', 'updated_at'];

    /** @var list<string> */
    protected $casts = [
        'row_count' => 'integer',
    ];

    public function getId(): string
    {
        return $this->attributes['id'];
    }

    public function getName(): string
    {
        return $this->attributes['name'];
    }

    public function getOriginalName(): string
    {
        return $this->attributes['original_name'];
    }

    public function getRowCount(): int
    {
        return $this->attributes['row_count'];
    }

    public function getCreatedAt(): string
    {
        return $this->attributes['created_at'];
    }

    public function getUpdatedAt(): string
    {
        return $this->attributes['updated_at'];
    }
}


