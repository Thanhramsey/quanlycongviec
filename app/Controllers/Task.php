<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\TaskModel;

class Task extends ResourceController
{
    protected $format = 'json';

    private function getTaskAverageAssignedProgress(string $taskId): float
    {
        $db = \Config\Database::connect();

        $assignedRows = $db->table('task_assignments')
            ->select('user_id')
            ->where('task_id', $taskId)
            ->get()
            ->getResultArray();

        if (empty($assignedRows)) {
            return 0.0;
        }

        $assignedUserIds = array_map(static fn($row) => (string) ($row['user_id'] ?? ''), $assignedRows);

        $logs = $db->table('daily_progress_logs')
            ->select('user_id, progress_percent, date, id')
            ->where('task_id', $taskId)
            ->where('status', 'approved')
            ->whereIn('user_id', $assignedUserIds)
            ->orderBy('user_id', 'ASC')
            ->orderBy('date', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        $latestProgressByUser = [];
        foreach ($logs as $log) {
            $userId = (string) ($log['user_id'] ?? '');
            if ($userId === '' || array_key_exists($userId, $latestProgressByUser)) {
                continue;
            }

            $latestProgressByUser[$userId] = (int) ($log['progress_percent'] ?? 0);
        }

        $sum = 0;
        foreach ($assignedUserIds as $userId) {
            $sum += (int) ($latestProgressByUser[$userId] ?? 0);
        }

        return $sum / max(1, count($assignedUserIds));
    }

    private function resolveTaskStatusFromLogs(string $taskId): string
    {
        $averageProgress = $this->getTaskAverageAssignedProgress($taskId);

        if ($averageProgress >= 100) {
            return 'completed';
        }

        if ($averageProgress > 0) {
            return 'in_progress';
        }

        return 'pending';
    }

    private function getCurrentUser(): ?array
    {
        return session()->get('user');
    }

    private function canViewAllTasks(array $user): bool
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

    public function index()
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return $this->failUnauthorized('Vui lòng đăng nhập lại');
        }

        $model = new TaskModel();
        // Lấy chi tiết công việc kèm theo danh sách nhân viên được giao
        $tasks = $model->getDetailedTask();

        if (!$this->canViewAllTasks($currentUser)) {
            $tasks = array_values(array_filter($tasks, function ($task) use ($currentUser) {
                return $this->isTaskAssignedToUser($task, (string) ($currentUser['id'] ?? ''));
            }));
        }

        return $this->respond($tasks);
    }

    public function show($id = null)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return $this->failUnauthorized('Vui lòng đăng nhập lại');
        }

        $model = new TaskModel();
        $task = $model->getDetailedTask($id);

        if (!$task) {
            return $this->failNotFound('Không tìm thấy đầu mục công việc');
        }

        if (!$this->canViewAllTasks($currentUser) && !$this->isTaskAssignedToUser($task, (string) ($currentUser['id'] ?? ''))) {
            return $this->failNotFound('Không tìm thấy đầu mục công việc');
        }

        return $this->respond($task);
    }

    public function create()
    {
        $model = new TaskModel();
        $currentUser = $this->getCurrentUser();

        if (!$currentUser) {
            return $this->failUnauthorized('Vui lòng đăng nhập lại');
        }

        $input = $this->request->getJSON(true);
        if (!is_array($input) || empty($input)) {
            $input = $this->request->getVar() ?? [];
        }

        $taskId = 't_' . uniqid();
        $data = [
            'id' => $taskId,
            'title' => $input['title'] ?? '',
            'description' => $input['description'] ?? '',
            'job_category_id' => $input['job_category_id'] ?? null,
            // New task always starts in pending state.
            'status' => 'pending',
            'start_date' => $input['start_date'] ?? '',
            'end_date' => $input['end_date'] ?? '',
            'created_by' => $currentUser['id'] ?? 'u1'
        ];

        if (empty($data['title']) || empty($data['start_date']) || empty($data['end_date'])) {
            return $this->fail('Tiêu đề công trình, ngày bắt đầu và ngày hạn định là bắt buộc', 400);
        }

        if ($model->insert($data)) {
            // Giao việc cho nhiều nhân viên (mảng user_ids gửi lên từ client)
            $assignedUsers = $input['assigned_users'] ?? [];
            if (!empty($assignedUsers)) {
                $model->assignStaff($taskId, $assignedUsers);
            }

            $responseTask = $model->getDetailedTask($taskId);
            return $this->respondCreated([
                'status' => 'success',
                'message' => 'Lập kế hoạch công việc thành công',
                'task' => $responseTask
            ]);
        }

        return $this->fail('Không thể thêm mới công việc');
    }

    public function update($id = null)
    {
        $model = new TaskModel();
        $currentUser = $this->getCurrentUser();

        if (!$currentUser) {
            return $this->failUnauthorized('Vui lòng đăng nhập lại');
        }

        $task = $model->find($id);

        if (!$task) {
            return $this->failNotFound('Không tìm thấy công việc tương thích');
        }

        // PUT requests from dashboard send JSON body, so read JSON first.
        $input = $this->request->getJSON(true);
        if (!is_array($input) || empty($input)) {
            $input = $this->request->getRawInput();
        }
        if (!is_array($input) || empty($input)) {
            $input = $this->request->getVar() ?? [];
        }

        $data = [
            'title' => $input['title'] ?? $task['title'],
            'description' => $input['description'] ?? $task['description'],
            'job_category_id' => $input['job_category_id'] ?? $task['job_category_id'],
            // Status is derived from submitted progress, not manual form selection.
            'status' => $this->resolveTaskStatusFromLogs((string) $id),
            'start_date' => $input['start_date'] ?? $task['start_date'],
            'end_date' => $input['end_date'] ?? $task['end_date'],
        ];

        if ($model->update($id, $data)) {
            // Cập nhật lại danh sách nhân sự tham gia nếu có truyền lên
            if (isset($input['assigned_users'])) {
                $model->assignStaff($id, $input['assigned_users']);
            }

            $updatedTask = $model->getDetailedTask($id);
            return $this->respond([
                'status' => 'success',
                'message' => 'Cập nhật tiến độ công việc thành công',
                'task' => $updatedTask
            ]);
        }

        return $this->fail('Có lỗi khi cập nhật công việc');
    }

    public function delete($id = null)
    {
        $model = new TaskModel();
        $currentUser = $this->getCurrentUser();

        if (!$currentUser) {
            return $this->failUnauthorized('Vui lòng đăng nhập lại');
        }

        $task = $model->find($id);

        if (!$task) {
            return $this->failNotFound('Không tìm thấy đầu việc tương thích');
        }

        // Xóa các liên kết phân công trước (task_assignments đã config ON DELETE CASCADE nhưng đảm bảo an toàn nếu chưa)
        $this->db = \Config\Database::connect();
        $this->db->table('task_assignments')->where('task_id', $id)->delete();

        if ($model->delete($id)) {
            return $this->respondDeleted([
                'status' => 'success',
                'message' => 'Đã hủy bỏ đầu mục công việc và dữ liệu phân công'
            ]);
        }

        return $this->fail('Lỗi xóa công việc khỏi cơ sở dữ liệu');
    }
}
