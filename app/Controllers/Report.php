<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\PersonnelModel;
use App\Models\TaskModel;
use App\Models\DailyProgressLogModel;

class Report extends ResourceController
{
    protected $format = 'json';

    private function getCurrentUser(): ?array
    {
        return session()->get('user');
    }

    private function canViewAllData(array $user): bool
    {
        return ($user['role'] ?? null) === 'admin';
    }

    private function isTaskAssignedToUser(array $task, string $userId): bool
    {
        foreach ($task['assigned_users'] ?? [] as $assignee) {
            if (($assignee['id'] ?? null) === $userId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tổng hợp dữ liệu thống kê sản lượng năng suất đồ gỗ và bảng xếp hạng thợ mộc
     */
    public function getPerformanceSummary()
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return $this->failUnauthorized('Vui lòng đăng nhập lại');
        }

        $userModel = new PersonnelModel();
        $taskModel = new TaskModel();
        $logModel = new DailyProgressLogModel();

        // Chạy auto-approve các log cũ trước khi kết xuất báo cáo năng lực
        $logModel->autoApproveOldLogs();

        if (!$this->canViewAllData($currentUser)) {
            $visibleTasks = $taskModel->getDetailedTask();
            $visibleTasks = array_values(array_filter($visibleTasks, function ($task) use ($currentUser) {
                return $this->isTaskAssignedToUser($task, (string) ($currentUser['id'] ?? ''));
            }));

            $visibleAssigneeIds = [];
            foreach ($visibleTasks as $task) {
                foreach ($task['assigned_users'] ?? [] as $assignee) {
                    if (!empty($assignee['id'])) {
                        $visibleAssigneeIds[$assignee['id']] = true;
                    }
                }
            }

            $ownLogs = $logModel->getDetailedLogs(null, $currentUser['id'] ?? null);
            $approvedOwnLogs = array_values(array_filter($ownLogs, function ($log) {
                return ($log['status'] ?? '') === 'approved';
            }));

            $summary = [
                'totalStaff' => count($visibleAssigneeIds),
                'totalTasks' => count($visibleTasks),
                'pendingTasks' => count(array_filter($visibleTasks, fn($task) => ($task['status'] ?? '') === 'pending')),
                'inProgressTasks' => count(array_filter($visibleTasks, fn($task) => ($task['status'] ?? '') === 'in_progress')),
                'completedTasks' => count(array_filter($visibleTasks, fn($task) => ($task['status'] ?? '') === 'completed')),
            ];

            $employeeProductivity = [[
                'userId' => $currentUser['id'] ?? '',
                'name' => $currentUser['name'] ?? '',
                'avatar' => $currentUser['avatar'] ?? '',
                'assignedTasksCount' => count($visibleTasks),
                'approvedLogsCount' => count($approvedOwnLogs),
                'totalProgressPoints' => array_sum(array_map(fn($log) => (int) ($log['progress_percent'] ?? 0), $approvedOwnLogs)),
            ]];

            return $this->respond([
                'summary' => $summary,
                'employeeProductivity' => $employeeProductivity,
            ]);
        }

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
