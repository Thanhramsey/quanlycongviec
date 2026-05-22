<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\PersonnelModel;
use App\Models\TaskModel;
use App\Models\DailyProgressLogModel;

class Report extends ResourceController
{
    protected $format = 'json';

    private function calculateDeltaPoints(array $approvedLogs): int
    {
        if (empty($approvedLogs)) {
            return 0;
        }

        usort($approvedLogs, static function ($a, $b) {
            $taskCompare = strcmp((string) ($a['task_id'] ?? ''), (string) ($b['task_id'] ?? ''));
            if ($taskCompare !== 0) {
                return $taskCompare;
            }

            $dateCompare = strcmp((string) ($a['date'] ?? ''), (string) ($b['date'] ?? ''));
            if ($dateCompare !== 0) {
                return $dateCompare;
            }

            return ((int) ($a['id'] ?? 0)) <=> ((int) ($b['id'] ?? 0));
        });

        $previousByTask = [];
        $total = 0;

        foreach ($approvedLogs as $log) {
            $taskId = (string) ($log['task_id'] ?? '');
            $current = (int) ($log['progress_percent'] ?? 0);

            if ($taskId === '') {
                continue;
            }

            if (!array_key_exists($taskId, $previousByTask)) {
                $delta = max(0, $current);
            } else {
                $delta = max(0, $current - (int) $previousByTask[$taskId]);
            }

            $previousByTask[$taskId] = $current;
            $total += $delta;
        }

        return $total;
    }

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
                'totalProgressPoints' => $this->calculateDeltaPoints($approvedOwnLogs),
            ]];

            return $this->respond([
                'summary' => $summary,
                'employeeProductivity' => $employeeProductivity,
            ]);
        }

        $db = \Config\Database::connect();
        $allTasks = $taskModel->getDetailedTask();
        $allLogs = $logModel->getDetailedLogs();

        $staffUsers = $userModel->where('role', 'staff')->findAll();
        $totalStaff = count($staffUsers);
        $totalTasks = count($allTasks);
        $pendingTasks = count(array_filter($allTasks, static fn($task) => ($task['status'] ?? '') === 'pending'));
        $inProgressTasks = count(array_filter($allTasks, static fn($task) => ($task['status'] ?? '') === 'in_progress'));
        $completedTasks = count(array_filter($allTasks, static fn($task) => ($task['status'] ?? '') === 'completed'));

        $assignedRows = $db->table('task_assignments')
            ->select('user_id, COUNT(DISTINCT task_id) as assigned_tasks_count')
            ->groupBy('user_id')
            ->get()
            ->getResultArray();

        $assignedCountByUser = [];
        foreach ($assignedRows as $row) {
            $assignedCountByUser[(string) ($row['user_id'] ?? '')] = (int) ($row['assigned_tasks_count'] ?? 0);
        }

        $approvedLogsByUser = [];
        foreach ($allLogs as $log) {
            if (($log['status'] ?? '') !== 'approved') {
                continue;
            }

            $userId = (string) ($log['user_id'] ?? '');
            if ($userId === '') {
                continue;
            }

            if (!isset($approvedLogsByUser[$userId])) {
                $approvedLogsByUser[$userId] = [];
            }
            $approvedLogsByUser[$userId][] = $log;
        }

        $employeeProductivity = [];
        foreach ($staffUsers as $staff) {
            $userId = (string) ($staff['id'] ?? '');
            $approvedLogs = $approvedLogsByUser[$userId] ?? [];

            $employeeProductivity[] = [
                'userId' => $userId,
                'name' => $staff['name'] ?? '',
                'avatar' => $staff['avatar'] ?? '',
                'assignedTasksCount' => $assignedCountByUser[$userId] ?? 0,
                'approvedLogsCount' => count($approvedLogs),
                'totalProgressPoints' => $this->calculateDeltaPoints($approvedLogs),
            ];
        }

        usort($employeeProductivity, static function ($a, $b) {
            return ((int) ($b['totalProgressPoints'] ?? 0)) <=> ((int) ($a['totalProgressPoints'] ?? 0));
        });

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
