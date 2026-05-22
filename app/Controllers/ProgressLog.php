<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\DailyProgressLogModel;

class ProgressLog extends ResourceController
{
    protected $format = 'json';

    private function getCurrentUser(): ?array
    {
        return session()->get('user');
    }

    private function canViewAllLogs(array $user): bool
    {
        return ($user['role'] ?? null) === 'admin';
    }

    private function isTaskAssignedToUser(string $taskId, string $userId): bool
    {
        $db = \Config\Database::connect();

        return (bool) $db->table('task_assignments')
            ->where('task_id', $taskId)
            ->where('user_id', $userId)
            ->countAllResults();
    }

    private function getPreviousProgressForTask(string $taskId, string $userId, string $date): ?array
    {
        $db = \Config\Database::connect();

        return $db->table('daily_progress_logs')
            ->select('date, progress_percent')
            ->where('task_id', $taskId)
            ->where('user_id', $userId)
            ->where('date <', $date)
            ->orderBy('date', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()
            ->getFirstRowArray();
    }

    /**
     * Get/List progress logs
     */
    public function index()
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return $this->failUnauthorized('Vui lòng đăng nhập lại');
        }

        $model = new DailyProgressLogModel();
        
        $taskId = $this->request->getGet('task_id');
        $userId = $this->canViewAllLogs($currentUser) ? $this->request->getGet('user_id') : ($currentUser['id'] ?? null);

        // Lấy danh sách chi tiết (Tự động cập nhật logs cũ thành Approved)
        $logs = $model->getDetailedLogs($taskId, $userId);

        return $this->respond($logs);
    }

    /**
     * Nhân viên đăng tải báo cáo tiến độ và upload ảnh minh chứng thực tế
     */
    public function create()
    {
        $model = new DailyProgressLogModel();
        $currentUser = $this->getCurrentUser();

        if (!$currentUser) {
            return $this->failUnauthorized('Vui lòng đăng nhập lại');
        }

        // Hỗ trợ cả hai dạng JSON và FormData để tương thích hoàn toàn với Node Express mọc lẫn PHP core backend
        $input = $this->request->getJSON(true);
        if (!empty($input)) {
            $taskId = $input['task_id'] ?? '';
            $date = $input['date'] ?? date('Y-m-d');
            $notes = $input['notes'] ?? '';
            $progressPercent = $input['progress_percent'] ?? 0;
            $imageUrl = $input['image'] ?? 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=600&q=80';
        } else {
            $taskId = $this->request->getPost('task_id');
            $date = $this->request->getPost('date') ?? date('Y-m-d');
            $notes = $this->request->getPost('notes') ?? '';
            $progressPercent = $this->request->getPost('progress_percent') ?? 0;

            // Handle Image Upload
            $imageUrl = 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=600&q=80'; // Mẫu ảnh gỗ mặc định
            $imageFile = $this->request->getFile('image');

            if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
                // Xác thực loại file là hình ảnh và thô hóa dung lượng
                $validated = $this->validate([
                    'image' => [
                        'uploaded[image]',
                        'mime_in[image,image/jpg,image/jpeg,image/png]',
                        'max_size[image,3072]', // Tối đa 3MB
                    ],
                ]);

                if ($validated) {
                    // Đặt tên ngẫu nhiên bảo mật tránh trùng lặp
                    $newName = $imageFile->getRandomName();
                    $imageFile->move(FCPATH . 'uploads/progress_logs', $newName);
                    $imageUrl = base_url() . '/uploads/progress_logs/' . $newName;
                } else {
                    return $this->fail('File tải lên không hợp lệ, chỉ hỗ trợ JPG/PNG dưới 3MB', 400);
                }
            }
        }

        $userId = $currentUser['id'] ?? '';

        if (empty($taskId) || empty($userId)) {
            return $this->fail('Vui lòng chọn công việc và xác định nhân sự báo cáo', 400);
        }

        if (!$this->canViewAllLogs($currentUser) && !$this->isTaskAssignedToUser($taskId, $userId)) {
            return $this->fail('Công việc này chưa được giao cho bạn', 403);
        }

        $previousLog = $this->getPreviousProgressForTask($taskId, $userId, $date);
        if ($previousLog && (int) $progressPercent < (int) ($previousLog['progress_percent'] ?? 0)) {
            return $this->fail(
                'Tiến độ của ngày sau phải lớn hơn hoặc bằng ngày trước. Mức trước đó là ' . (int) ($previousLog['progress_percent'] ?? 0) . '%.',
                400
            );
        }

        $data = [
            'task_id' => $taskId,
            'user_id' => $userId,
            'date' => $date,
            'notes' => $notes,
            'progress_percent' => intval($progressPercent),
            'image' => $imageUrl,
            'status' => 'pending', // Phờ duyệt ban đầu
            'auto_approved' => 0
        ];

        if ($model->insert($data)) {
            $insertedId = $model->getInsertID();
            $log = $model->find($insertedId);
            return $this->respondCreated([
                'status' => 'success',
                'message' => 'Nộp báo cáo tiến độ công việc thành công',
                'log' => $log
            ]);
        }

        return $this->fail('Không thể lưu báo cáo tiến độ');
    }

    /**
     * Admin/Quản lý phê duyệt (approve / reject) báo cáo của nhân viên
     */
    public function approve($id = null)
    {
        $model = new DailyProgressLogModel();
        $log = $model->find($id);

        if (!$log) {
            return $this->failNotFound('Không tìm thấy nhật ký tương ứng');
        }

        $input = $this->request->getRawInput();
        $status = $input['status'] ?? 'approved'; // approved hoặc rejected
        $approvedBy = $input['approved_by'] ?? 'u1'; // ID của quản lý/admin

        if (!in_array($status, ['approved', 'rejected'])) {
            return $this->fail('Trạng thái phê duyệt không hợp lệ', 400);
        }

        $updateData = [
            'status' => $status,
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s'),
            'auto_approved' => 0
        ];

        if ($model->update($id, $updateData)) {
            // Cập nhật mức độ hoàn thành cao nhất của công việc nếu log được duyệt
            if ($status === 'approved') {
                $this->updateTaskOverallStatus($log['task_id'], $log['progress_percent']);
            }

            return $this->respond([
                'status' => 'success',
                'message' => $status === 'approved' ? 'Đã duyệt báo cáo công việc' : 'Đã từ chối báo cáo công việc',
                'log_id' => $id
            ]);
        }

        return $this->fail('Không thể phê duyệt báo cáo này');
    }

    /**
     * Cập nhật trạng thái tổng thể của Task dựa trên tiến độ % được duyệt cao nhất
     */
    private function updateTaskOverallStatus($taskId, $progressPercent)
    {
        $db = \Config\Database::connect();
        
        $newStatus = 'in_progress';
        if ($progressPercent >= 100) {
            $newStatus = 'completed';
        }

        $db->table('tasks')
            ->where('id', $taskId)
            ->update([
                'status' => $newStatus
            ]);
    }
}