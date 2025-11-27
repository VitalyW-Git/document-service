<?php

namespace App\Entities\Document;

use CodeIgniter\Entity\Entity;

/**
 * @property string $id
 * @property string $file_id
 * @property string $row_data
 * @property int    $row_index
 * @property string $created_at
 * @property string $updated_at
 */
final class FileRowEntity extends Entity implements FileRowEntityInterface
{
    /** @var list<string>  */
    protected $dates = ['created_at', 'updated_at'];

    /** @var list<string>  */
    protected $casts = [
        'row_data' => 'json-array',
        'row_index' => 'integer',
    ];

    public function getId(): string
    {
        return $this->attributes['id'];
    }

    public function getFileId(): string
    {
        return $this->attributes['file_id'];
    }

    public function getRowData(): string
    {
        return $this->attributes['row_data'];
    }

    public function getRowIndex(): int
    {
        return $this->attributes['row_index'];
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