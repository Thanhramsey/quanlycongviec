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

    private function parseDateParam(string $key): ?string
    {
        $value = trim((string) ($this->request->getGet($key) ?? ''));
        if ($value === '') {
            return null;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        return $value;
    }

    private function normalizeDateRange(?string $fromDate, ?string $toDate): array
    {
        if ($fromDate && $toDate && $fromDate > $toDate) {
            return [$toDate, $fromDate];
        }

        return [$fromDate, $toDate];
    }

    private function isDateInRange(?string $date, ?string $fromDate, ?string $toDate): bool
    {
        if (!$date) {
            return false;
        }

        if ($fromDate && $date < $fromDate) {
            return false;
        }
        if ($toDate && $date > $toDate) {
            return false;
        }

        return true;
    }

    private function isTaskOverlappingRange(array $task, ?string $fromDate, ?string $toDate): bool
    {
        if (!$fromDate && !$toDate) {
            return true;
        }

        $start = (string) ($task['start_date'] ?? '');
        $end = (string) ($task['end_date'] ?? '');
        if ($start === '' || $end === '') {
            return false;
        }

        if ($fromDate && $toDate) {
            return !($start > $toDate || $end < $fromDate);
        }
        if ($fromDate) {
            return $end >= $fromDate;
        }

        return $start <= (string) $toDate;
    }

    private function buildPerformanceData(array $currentUser, ?string $fromDate, ?string $toDate): array
    {
        [$fromDate, $toDate] = $this->normalizeDateRange($fromDate, $toDate);

        $userModel = new PersonnelModel();
        $taskModel = new TaskModel();
        $logModel = new DailyProgressLogModel();
        $db = \Config\Database::connect();

        $logModel->autoApproveOldLogs();

        if (!$this->canViewAllData($currentUser)) {
            $visibleTasks = $taskModel->getDetailedTask();
            $visibleTasks = array_values(array_filter($visibleTasks, function ($task) use ($currentUser, $fromDate, $toDate) {
                return $this->isTaskAssignedToUser($task, (string) ($currentUser['id'] ?? ''))
                    && $this->isTaskOverlappingRange($task, $fromDate, $toDate);
            }));

            $ownLogs = $logModel->getDetailedLogs(null, $currentUser['id'] ?? null);
            $approvedOwnLogs = array_values(array_filter($ownLogs, function ($log) use ($fromDate, $toDate) {
                return ($log['status'] ?? '') === 'approved'
                    && $this->isDateInRange((string) ($log['date'] ?? ''), $fromDate, $toDate);
            }));

            $personalSummary = [
                'assignedTasksCount' => count($visibleTasks),
                'approvedLogsCount' => count($approvedOwnLogs),
                'totalProgressPoints' => $this->calculateDeltaPoints($approvedOwnLogs),
            ];

            $employeeProductivity = [[
                'userId' => $currentUser['id'] ?? '',
                'name' => $currentUser['name'] ?? '',
                'avatar' => $currentUser['avatar'] ?? '',
                'assignedTasksCount' => $personalSummary['assignedTasksCount'],
                'approvedLogsCount' => $personalSummary['approvedLogsCount'],
                'totalProgressPoints' => $personalSummary['totalProgressPoints'],
            ]];

            $taskProgressList = [];
            foreach ($visibleTasks as $task) {
                $taskId = (string) ($task['id'] ?? '');
                if ($taskId === '') {
                    continue;
                }

                $taskApprovedLogs = array_values(array_filter($approvedOwnLogs, static function ($log) use ($taskId) {
                    return (string) ($log['task_id'] ?? '') === $taskId;
                }));

                $progress = 0;
                if (!empty($taskApprovedLogs)) {
                    $progress = max(array_map(static fn($log) => (int) ($log['progress_percent'] ?? 0), $taskApprovedLogs));
                }

                $taskProgressList[] = [
                    'id' => $taskId,
                    'title' => $task['title'] ?? 'Không tên',
                    'startDate' => $task['start_date'] ?? '',
                    'endDate' => $task['end_date'] ?? '',
                    'status' => $task['status'] ?? 'pending',
                    'progress' => $progress,
                ];
            }

            return [
                'summary' => [
                    'totalStaff' => 1,
                    'totalTasks' => count($visibleTasks),
                    'pendingTasks' => count(array_filter($visibleTasks, static fn($task) => ($task['status'] ?? '') === 'pending')),
                    'inProgressTasks' => count(array_filter($visibleTasks, static fn($task) => ($task['status'] ?? '') === 'in_progress')),
                    'completedTasks' => count(array_filter($visibleTasks, static fn($task) => ($task['status'] ?? '') === 'completed')),
                ],
                'employeeProductivity' => $employeeProductivity,
                'personalSummary' => $personalSummary,
                'taskProgressList' => $taskProgressList,
                'reportRange' => [
                    'fromDate' => $fromDate,
                    'toDate' => $toDate,
                ],
            ];
        }

        $allTasks = $taskModel->getDetailedTask();
        $filteredTasks = array_values(array_filter($allTasks, function ($task) use ($fromDate, $toDate) {
            return $this->isTaskOverlappingRange($task, $fromDate, $toDate);
        }));

        $allLogs = $logModel->getDetailedLogs();
        $approvedLogs = array_values(array_filter($allLogs, function ($log) use ($fromDate, $toDate) {
            return ($log['status'] ?? '') === 'approved'
                && $this->isDateInRange((string) ($log['date'] ?? ''), $fromDate, $toDate);
        }));

        $staffUsers = $userModel->where('role', 'staff')->findAll();

        $assignedTaskSetByUser = [];
        foreach ($filteredTasks as $task) {
            $taskId = (string) ($task['id'] ?? '');
            foreach ($task['assigned_users'] ?? [] as $assignee) {
                $userId = (string) ($assignee['id'] ?? '');
                if ($userId === '' || $taskId === '') {
                    continue;
                }

                if (!isset($assignedTaskSetByUser[$userId])) {
                    $assignedTaskSetByUser[$userId] = [];
                }
                $assignedTaskSetByUser[$userId][$taskId] = true;
            }
        }

        $approvedLogsByUser = [];
        foreach ($approvedLogs as $log) {
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
            $userApprovedLogs = $approvedLogsByUser[$userId] ?? [];
            $assignedTasksCount = isset($assignedTaskSetByUser[$userId]) ? count($assignedTaskSetByUser[$userId]) : 0;

            $employeeProductivity[] = [
                'userId' => $userId,
                'name' => $staff['name'] ?? '',
                'avatar' => $staff['avatar'] ?? '',
                'assignedTasksCount' => $assignedTasksCount,
                'approvedLogsCount' => count($userApprovedLogs),
                'totalProgressPoints' => $this->calculateDeltaPoints($userApprovedLogs),
            ];
        }

        usort($employeeProductivity, static function ($a, $b) {
            return ((int) ($b['totalProgressPoints'] ?? 0)) <=> ((int) ($a['totalProgressPoints'] ?? 0));
        });

        $totalSystemPoints = array_sum(array_map(static fn($row) => (int) ($row['totalProgressPoints'] ?? 0), $employeeProductivity));
        $personalSummary = [
            'assignedTasksCount' => count($filteredTasks),
            'approvedLogsCount' => count($approvedLogs),
            'totalProgressPoints' => (int) $totalSystemPoints,
        ];

        $taskProgressList = [];
        foreach ($filteredTasks as $task) {
            $taskId = (string) ($task['id'] ?? '');
            $taskApprovedLogs = array_values(array_filter($approvedLogs, static function ($log) use ($taskId) {
                return (string) ($log['task_id'] ?? '') === $taskId;
            }));

            $progress = 0;
            if (!empty($taskApprovedLogs)) {
                $progress = max(array_map(static fn($log) => (int) ($log['progress_percent'] ?? 0), $taskApprovedLogs));
            }

            $taskProgressList[] = [
                'id' => $taskId,
                'title' => $task['title'] ?? 'Không tên',
                'startDate' => $task['start_date'] ?? '',
                'endDate' => $task['end_date'] ?? '',
                'status' => $task['status'] ?? 'pending',
                'progress' => $progress,
            ];
        }

        return [
            'summary' => [
                'totalStaff' => count($staffUsers),
                'totalTasks' => count($filteredTasks),
                'pendingTasks' => count(array_filter($filteredTasks, static fn($task) => ($task['status'] ?? '') === 'pending')),
                'inProgressTasks' => count(array_filter($filteredTasks, static fn($task) => ($task['status'] ?? '') === 'in_progress')),
                'completedTasks' => count(array_filter($filteredTasks, static fn($task) => ($task['status'] ?? '') === 'completed')),
            ],
            'employeeProductivity' => $employeeProductivity,
            'personalSummary' => $personalSummary,
            'taskProgressList' => $taskProgressList,
            'reportRange' => [
                'fromDate' => $fromDate,
                'toDate' => $toDate,
            ],
        ];
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

        $fromDate = $this->parseDateParam('from_date');
        $toDate = $this->parseDateParam('to_date');

        return $this->respond($this->buildPerformanceData($currentUser, $fromDate, $toDate));
    }

    public function exportExcel()
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return $this->failUnauthorized('Vui lòng đăng nhập lại');
        }

        if (!$this->canViewAllData($currentUser)) {
            return $this->failForbidden('Chỉ quản trị được xuất Excel dữ liệu tổng hợp.');
        }

        $fromDate = $this->parseDateParam('from_date');
        $toDate = $this->parseDateParam('to_date');
        $data = $this->buildPerformanceData($currentUser, $fromDate, $toDate);

        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            return $this->failServerError('Không thể tạo dữ liệu xuất.');
        }

        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, ['BAO CAO HIEU SUAT NHAN SU']);
        fputcsv($handle, ['Tu ngay', (string) ($data['reportRange']['fromDate'] ?? '')]);
        fputcsv($handle, ['Den ngay', (string) ($data['reportRange']['toDate'] ?? '')]);
        fputcsv($handle, []);

        fputcsv($handle, ['Tong nhan vien', (int) ($data['summary']['totalStaff'] ?? 0)]);
        fputcsv($handle, ['Tong cong viec', (int) ($data['summary']['totalTasks'] ?? 0)]);
        fputcsv($handle, ['Cong viec cho bat dau', (int) ($data['summary']['pendingTasks'] ?? 0)]);
        fputcsv($handle, ['Cong viec dang lam', (int) ($data['summary']['inProgressTasks'] ?? 0)]);
        fputcsv($handle, ['Cong viec hoan thanh', (int) ($data['summary']['completedTasks'] ?? 0)]);
        fputcsv($handle, []);

        fputcsv($handle, ['Bang xep hang']);
        fputcsv($handle, ['STT', 'Ma nhan vien', 'Ten nhan vien', 'So cong viec duoc giao', 'So bao cao duyet', 'Diem nang suat']);

        foreach ($data['employeeProductivity'] ?? [] as $index => $row) {
            fputcsv($handle, [
                $index + 1,
                (string) ($row['userId'] ?? ''),
                (string) ($row['name'] ?? ''),
                (int) ($row['assignedTasksCount'] ?? 0),
                (int) ($row['approvedLogsCount'] ?? 0),
                (int) ($row['totalProgressPoints'] ?? 0),
            ]);
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle) ?: '';
        fclose($handle);

        $filename = 'bao-cao-hieu-suat-' . date('Ymd_His') . '.csv';

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csvContent);
    }
}
