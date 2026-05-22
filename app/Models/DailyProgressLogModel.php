<?php

namespace App\Models;

use App\Libraries\AutoApproveSetting;
use CodeIgniter\Model;

class DailyProgressLogModel extends Model
{
    protected $table            = 'daily_progress_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'id', 'task_id', 'user_id', 'date', 'notes', 
        'progress_percent', 'image', 'status', 
        'approved_by', 'approved_at', 'auto_approved'
    ];

    /**
     * Tự động duyệt các báo cáo ngày cũ của thợ mộc nếu chưa được duyệt
     * Quy luật: Chỉ tự duyệt khi đã quá 48 giờ kể từ cuối ngày báo cáo và vẫn đang chờ duyệt
     */
    public function autoApproveOldLogs(): int
    {
        if (!(new AutoApproveSetting())->isEnabled()) {
            return 0;
        }

        $thresholdDateTime = date('Y-m-d H:i:s', strtotime('-48 hours'));
        $escapedThreshold = $this->db->escape($thresholdDateTime);

        // Ví dụ: log ngày 2026-05-20 chỉ auto-approve sau 2026-05-22 23:59:59
        $this->db->table($this->table)
            ->where('status', 'pending')
            ->where("STR_TO_DATE(CONCAT(`date`, ' 23:59:59'), '%Y-%m-%d %H:%i:%s') <= {$escapedThreshold}", null, false)
            ->update([
                'status' => 'approved',
                'auto_approved' => 1,
                'approved_at' => date('Y-m-d H:i:s'),
                'approved_by' => null // Hệ thống tự động phê duyệt
            ]);

        return (int) $this->db->affectedRows();
    }

    /**
     * Lấy danh sách thợ kèm theo thông số chi tiết của logs
     */
    public function getDetailedLogs($taskId = null, $userId = null)
    {
        // Chạy auto-approve các báo cáo cũ trước khi truy vấn để đảm bảo số liệu chính xác
        $this->autoApproveOldLogs();

        $builder = $this->db->table('daily_progress_logs dl')
            ->select('dl.*, u.name as user_name, u.avatar as user_avatar, t.title as task_title, manager.name as approver_name')
            ->join('users u', 'u.id = dl.user_id')
            ->join('tasks t', 't.id = dl.task_id')
            ->join('users manager', 'manager.id = dl.approved_by', 'left');

        if ($taskId) {
            $builder->where('dl.task_id', $taskId);
        }
        if ($userId) {
            $builder->where('dl.user_id', $userId);
        }

        return $builder->orderBy('dl.date', 'DESC')->get()->getResultArray();
    }
}
