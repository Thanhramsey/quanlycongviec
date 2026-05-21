<?php

namespace App\Models;

use CodeIgniter\Model;

class PersonnelModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'id', 'phone', 'username', 'password', 'name', 
        'dob', 'address', 'identity_card', 'avatar', 
        'role', 'position_id', 'custom_permissions'
    ];

    protected $useTimestamps = false;

    /**
     * Get user details with position info
     */
    public function getWithPosition($userId = null)
    {
        $builder = $this->db->table($this->table)
            ->select('users.*, positions.name as position_name')
            ->join('positions', 'positions.id = users.position_id', 'left');

        if ($userId) {
            return $builder->where('users.id', $userId)->get()->getRowArray();
        }

        return $builder->get()->getResultArray();
    }
}
