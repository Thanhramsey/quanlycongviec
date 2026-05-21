<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\PersonnelModel;
use App\Models\PositionModel;
use App\Models\JobCategoryModel;

class Personnel extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $model = new PersonnelModel();
        $users = $model->getWithPosition();
        
        // Thập phân hóa custom_permissions từ json thành array
        foreach ($users as &$user) {
            if (is_string($user['custom_permissions'])) {
                $user['custom_permissions'] = json_decode($user['custom_permissions'], true) ?? [];
            }
        }
        
        return $this->respond($users);
    }

    public function show($id = null)
    {
        $model = new PersonnelModel();
        $user = $model->getWithPosition($id);
        
        if (!$user) {
            return $this->failNotFound('Không tìm thấy ID nhân viên');
        }

        if (is_string($user['custom_permissions'])) {
            $user['custom_permissions'] = json_decode($user['custom_permissions'], true) ?? [];
        }

        return $this->respond($user);
    }

    public function create()
    {
        $model = new PersonnelModel();

        $data = [
            'id' => 'u_' . uniqid(),
            'phone' => $this->request->getVar('phone'),
            'username' => $this->request->getVar('username'),
            'password' => password_hash($this->request->getVar('password') ?? '123', PASSWORD_BCRYPT),
            'name' => $this->request->getVar('name'),
            'dob' => $this->request->getVar('dob'),
            'address' => $this->request->getVar('address'),
            'identity_card' => $this->request->getVar('identity_card'),
            'avatar' => $this->request->getVar('avatar') ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=150',
            'role' => $this->request->getVar('role') ?? 'staff',
            'position_id' => $this->request->getVar('position_id'),
            'custom_permissions' => json_encode($this->request->getVar('custom_permissions') ?? [])
        ];

        // Validate duplicates
        $exist = $model->where('phone', $data['phone'])->first();
        if ($exist) {
            return $this->fail('Số điện thoại thợ đã tồn tại trong danh nghiệp', 400);
        }

        if ($model->insert($data)) {
            $data['custom_permissions'] = json_decode($data['custom_permissions'], true);
            return $this->respondCreated([
                'status' => 'success',
                'message' => 'Thêm nhân sư mới thành công',
                'user' => $data
            ]);
        }

        return $this->fail('Ghi nhận thông tin thất bại');
    }

    public function update($id = null)
    {
        $model = new PersonnelModel();
        $user = $model->find($id);

        if (!$user) {
            return $this->failNotFound('Không tìm thấy ID nhân sự');
        }

        $input = $this->request->getRawInput();

        $data = [
            'name' => $input['name'] ?? $user['name'],
            'phone' => $input['phone'] ?? $user['phone'],
            'username' => $input['username'] ?? $user['username'],
            'dob' => $input['dob'] ?? $user['dob'],
            'address' => $input['address'] ?? $user['address'],
            'identity_card' => $input['identity_card'] ?? $user['identity_card'],
            'role' => $input['role'] ?? $user['role'],
            'position_id' => $input['position_id'] ?? $user['position_id'],
        ];

        if (!empty($input['password'])) {
            $data['password'] = password_hash($input['password'], PASSWORD_BCRYPT);
        }

        if (isset($input['custom_permissions'])) {
            $data['custom_permissions'] = json_encode($input['custom_permissions']);
        }

        if (isset($input['avatar'])) {
            $data['avatar'] = $input['avatar'];
        }

        if ($model->update($id, $data)) {
            if (isset($data['custom_permissions'])) {
                $data['custom_permissions'] = json_decode($data['custom_permissions'], true);
            }
            return $this->respond([
                'status' => 'success',
                'message' => 'Cập nhật nhân viên thành công',
                'data' => $data
            ]);
        }

        return $this->fail('Có lỗi bất ngờ khi cập nhật thông tin');
    }

    public function delete($id = null)
    {
        $model = new PersonnelModel();
        $user = $model->find($id);

        if (!$user) {
            return $this->failNotFound('Không tìm thấy nhân sự');
        }

        if ($model->delete($id)) {
            return $this->respondDeleted([
                'status' => 'success',
                'message' => 'Nhân viên đã được gỡ khỏi doanh nghiệp'
            ]);
        }

        return $this->fail('Có lỗi trong quá trình thực thi xóa');
    }
}
