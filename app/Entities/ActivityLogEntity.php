<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

/**
 * @property string      $id
 * @property string      $file_id
 * @property string      $action
 * @property string|null $description
 * @property string      $created_at
 */
final class ActivityLogEntity extends Entity
{
    /** @var list<string>  */
    protected $dates = ['created_at'];
}