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
            ->getRowArray();
    }

    private function getSameDayLogForTask(string $taskId, string $userId, string $date): ?array
    {
        $db = \Config\Database::connect();

        return $db->table('daily_progress_logs')
            ->select('*')
            ->where('task_id', $taskId)
            ->where('user_id', $userId)
            ->where('date', $date)
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray();
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

        // Hỗ trợ cả hai dạng JSON và FormData.
        $input = [];
        $contentType = strtolower((string) $this->request->getHeaderLine('Content-Type'));
        if (str_contains($contentType, 'application/json')) {
            try {
                $input = $this->request->getJSON(true) ?? [];
            } catch (\Throwable $e) {
                $input = [];
            }
        }
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

            // Handle attachment upload (image/doc/xls/pdf)
            $imageUrl = 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=600&q=80';
            $imageFile = $this->request->getFile('attachment');
            if (!$imageFile || !$imageFile->isValid()) {
                $imageFile = $this->request->getFile('image');
            }

            if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
                // Validate allowed file types and max 10MB.
                $validated = $this->validate([
                    'attachment' => [
                        'uploaded[attachment]',
                        'mime_in[attachment,image/jpg,image/jpeg,image/png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet]',
                        'max_size[attachment,10240]',
                    ],
                ]);

                if (!$validated) {
                    // Retry validation with legacy field name.
                    $validated = $this->validate([
                        'image' => [
                            'uploaded[image]',
                            'mime_in[image,image/jpg,image/jpeg,image/png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet]',
                            'max_size[image,10240]',
                        ],
                    ]);
                }

                if ($validated) {
                    $newName = $imageFile->getRandomName();
                    $imageFile->move(FCPATH . 'uploads/progress_logs', $newName);
                    $imageUrl = base_url() . '/uploads/progress_logs/' . $newName;
                } else {
                    return $this->fail('File tải lên không hợp lệ. Hỗ trợ JPG/PNG/PDF/DOC/DOCX/XLS/XLSX, tối đa 10MB', 400);
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

        $sameDayLog = $this->getSameDayLogForTask($taskId, $userId, $date);
        if ($sameDayLog) {
            $updateData = $data;
            $updateData['approved_by'] = null;
            $updateData['approved_at'] = null;

            if ($model->update((int) $sameDayLog['id'], $updateData)) {
                $this->updateTaskOverallStatus($taskId);
                $log = $model->find((int) $sameDayLog['id']);

                return $this->respond([
                    'status' => 'success',
                    'message' => 'Đã cập nhật báo cáo trong cùng ngày cho công việc này',
                    'log' => $log
                ]);
            }

            return $this->fail('Không thể cập nhật báo cáo trùng ngày');
        }

        if ($model->insert($data)) {
            $this->updateTaskOverallStatus($taskId);
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

        $input = $this->request->getJSON(true);
        if (!is_array($input) || empty($input)) {
            $input = $this->request->getRawInput();
        }
        if (!is_array($input) || empty($input)) {
            $input = $this->request->getVar() ?? [];
        }

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
                $this->updateTaskOverallStatus($log['task_id']);
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
    private function updateTaskOverallStatus(string $taskId): void
    {
        $db = \Config\Database::connect();

        $assignedRows = $db->table('task_assignments')
            ->select('user_id')
            ->where('task_id', $taskId)
            ->get()
            ->getResultArray();

        if (empty($assignedRows)) {
            $db->table('tasks')->where('id', $taskId)->update(['status' => 'pending']);
            return;
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

        $averageProgress = $sum / max(1, count($assignedUserIds));

        $newStatus = 'pending';
        if ($averageProgress > 0) {
            $newStatus = 'in_progress';
        }
        if ($averageProgress >= 100) {
            $newStatus = 'completed';
        }

        $db->table('tasks')
            ->where('id', $taskId)
            ->update([
                'status' => $newStatus
            ]);
    }
}