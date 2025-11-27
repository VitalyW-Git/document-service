<?php

namespace App\Entities\Document;

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
final class FileEntity extends Entity implements DocumentFileInterface
{
    /** @var list<string> */
    protected $dates = ['created_at', 'updated_at'];

    /** @var list<string> */
    protected $casts = [
        'row_count' => 'integer',
    ];

    public function getId(): string
    {
        return (string) ($this->attributes['id'] ?? '');
    }

    public function getName(): string
    {
        return (string) ($this->attributes['name'] ?? '');
    }

    public function getOriginalName(): string
    {
        return (string) ($this->attributes['original_name'] ?? '');
    }

    public function getRowCount(): ?int
    {
        return $this->attributes['row_count'] ?? null;
    }

    public function getCreatedAt(): ?string
    {
        return $this->attributes['created_at'] ?? null;
    }
}


