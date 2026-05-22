<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\DailyProgressLogModel;

class ProgressLog extends ResourceController
{
    protected $format = 'json';

    /**
     * Get/List progress logs
     */
    public function index()
    {
        $model = new DailyProgressLogModel();
        
        $taskId = $this->request->getGet('task_id');
        $userId = $this->request->getGet('user_id');

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

        // Hỗ trợ cả hai dạng JSON và FormData để tương thích hoàn toàn với Node Express mọc lẫn PHP core backend
        $input = $this->request->getJSON(true);
        if (!empty($input)) {
            $taskId = $input['task_id'] ?? '';
            $userId = $input['user_id'] ?? '';
            $date = $input['date'] ?? date('Y-m-d');
            $notes = $input['notes'] ?? '';
            $progressPercent = $input['progress_percent'] ?? 0;
            $imageUrl = $input['image'] ?? 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=600&q=80';
        } else {
            $taskId = $this->request->getPost('task_id');
            $userId = $this->request->getPost('user_id');
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

        if (empty($taskId) || empty($userId)) {
            return $this->fail('Vui lòng chọn công việc và xác định nhân sự báo cáo', 400);
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