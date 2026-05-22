<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\TaskModel;

class Task extends ResourceController
{
    protected $format = 'json';

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

        $taskId = 't_' . uniqid();
        $data = [
            'id' => $taskId,
            'title' => $this->request->getVar('title'),
            'description' => $this->request->getVar('description'),
            'job_category_id' => $this->request->getVar('job_category_id'),
            'status' => $this->request->getVar('status') ?? 'pending',
            'start_date' => $this->request->getVar('start_date'),
            'end_date' => $this->request->getVar('end_date'),
            'created_by' => $currentUser['id'] ?? 'u1'
        ];

        if (empty($data['title']) || empty($data['start_date']) || empty($data['end_date'])) {
            return $this->fail('Tiêu đề công trình, ngày bắt đầu và ngày hạn định là bắt buộc', 400);
        }

        if ($model->insert($data)) {
            // Giao việc cho nhiều nhân viên (mảng user_ids gửi lên từ client)
            $assignedUsers = $this->request->getVar('assigned_users') ?? [];
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

        $input = $this->request->getRawInput();

        $data = [
            'title' => $input['title'] ?? $task['title'],
            'description' => $input['description'] ?? $task['description'],
            'job_category_id' => $input['job_category_id'] ?? $task['job_category_id'],
            'status' => $input['status'] ?? $task['status'],
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
