<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\PersonnelModel;
use App\Models\TaskModel;
use App\Models\DailyProgressLogModel;

class Report extends ResourceController
{
    protected $format = 'json';

    /**
     * Tổng hợp dữ liệu thống kê sản lượng năng suất đồ gỗ và bảng xếp hạng thợ mộc
     */
    public function getPerformanceSummary()
    {
        $userModel = new PersonnelModel();
        $taskModel = new TaskModel();
        $logModel = new DailyProgressLogModel();

        // Chạy auto-approve các log cũ trước khi kết xuất báo cáo năng lực
        $logModel->autoApproveOldLogs();

        // 1. Thống kê tổng quan số lượng
        $db = \Config\Database::connect();
        
        $totalStaff = $userModel->where('role', 'staff')->countAllResults();
        $totalTasks = $taskModel->countAll();
        
        $pendingTasks = $taskModel->where('status', 'pending')->countAllResults();
        $inProgressTasks = $taskModel->where('status', 'in_progress')->countAllResults();
        $completedTasks = $taskModel->where('status', 'completed')->countAllResults();

        // 2. Tính toán năng suất sản lượng chi tiết của từng nhân viên (Employee Productivity)
        // Những nhân viên có logs được phê duyệt (Approved/Auto-approved)
        $productivityQuery = $db->query("
            SELECT 
                u.id as userId, 
                u.name, 
                u.avatar,
                (SELECT COUNT(DISTINCT ta.task_id) FROM task_assignments ta WHERE ta.user_id = u.id) as assignedTasksCount,
                COUNT(dpl.id) as approvedLogsCount,
                IFNULL(SUM(dpl.progress_percent), 0) as totalProgressPoints
            FROM users u
            LEFT JOIN daily_progress_logs dpl ON dpl.user_id = u.id AND dpl.status = 'approved'
            WHERE u.role = 'staff'
            GROUP BY u.id, u.name, u.avatar
            ORDER BY totalProgressPoints DESC
        ");

        $employeeProductivity = $productivityQuery->getResultArray();

        return $this->respond([
            'summary' => [
                'totalStaff' => $totalStaff,
                'totalTasks' => $totalTasks,
                'pendingTasks' => $pendingTasks,
                'inProgressTasks' => $inProgressTasks,
                'completedTasks' => $completedTasks
            ],
            'employeeProductivity' => $employeeProductivity
        ]);
    }
}
