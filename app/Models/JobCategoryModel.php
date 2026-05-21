<?php

namespace App\Models;

use CodeIgniter\Model;

class JobCategoryModel extends Model
{
    protected $table            = 'job_categories';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $allowedFields    = ['id', 'name', 'description'];
}
