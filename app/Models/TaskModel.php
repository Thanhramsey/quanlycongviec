<?php

namespace App\Models;

use CodeIgniter\Model;

class TaskModel extends Model
{
    protected $table            = 'tasks';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'id', 'title', 'description', 'job_category_id', 
        'status', 'start_date', 'end_date', 'created_by'
    ];

    /**
     * Get task with extra details (Join category)
     */
    public function getDetailedTask($taskId = null)
    {
        $builder = $this->db->table('tasks t')
            ->select('t.*, jc.name as job_category_name, u.name as creator_name')
            ->join('job_categories jc', 'jc.id = t.job_category_id', 'left')
            ->join('users u', 'u.id = t.created_by', 'left');

        if ($taskId) {
            $task = $builder->where('t.id', $taskId)->get()->getRowArray();
            if ($task) {
                // Get assignments
                $task['assigned_users'] = $this->getAssignments($taskId);
            }
            return $task;
        }

        $tasks = $builder->get()->getResultArray();
        foreach ($tasks as &$task) {
            $task['assigned_users'] = $this->getAssignments($task['id']);
        }
        return $tasks;
    }

    /**
     * Get assignment personnel lists for a task
     */
    public function getAssignments($taskId)
    {
        return $this->db->table('task_assignments ta')
            ->select('u.id, u.name, u.phone, u.avatar, p.name as position_name')
            ->join('users u', 'u.id = ta.user_id')
            ->join('positions p', 'p.id = u.position_id', 'left')
            ->where('ta.task_id', $taskId)
            ->get()
            ->getResultArray();
    }

    /**
     * Assign workers to a task
     */
    public function assignStaff($taskId, array $userIds)
    {
        // First delete safe clean
        $this->db->table('task_assignments')->where('task_id', $taskId)->delete();

        // Write batch
        $data = [];
        foreach ($userIds as $userId) {
            $data[] = [
                'task_id' => $taskId,
                'user_id' => $userId
            ];
        }

        if (!empty($data)) {
            return $this->db->table('task_assignments')->insertBatch($data);
        }
        return true;
    }
}
