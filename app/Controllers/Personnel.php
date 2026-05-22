<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\PersonnelModel;
use App\Models\PositionModel;
use App\Models\JobCategoryModel;

class Personnel extends ResourceController
{
    protected $format = 'json';

    private const DEFAULT_AVATAR = 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=150';

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
            'avatar' => $this->request->getVar('avatar') ?? self::DEFAULT_AVATAR,
            'role' => $this->request->getVar('role') ?? 'staff',
            'position_id' => $this->request->getVar('position_id'),
            'custom_permissions' => json_encode($this->request->getVar('custom_permissions') ?? [])
        ];

        // Validate duplicates
        $exist = $model->where('phone', $data['phone'])->first();
        if ($exist) {
            return $this->fail('Số điện thoại nhân viên đã tồn tại trong doanh nghiệp', 400);
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

        $input = $this->request->getJSON(true);
        if (!is_array($input) || empty($input)) {
            $input = $this->request->getRawInput();
        }
        if (!is_array($input)) {
            $input = [];
        }

        $data = [
            'name' => $input['name'] ?? $user['name'],
            'phone' => $input['phone'] ?? $user['phone'],
            'username' => $input['username'] ?? ($user['username'] ?? ''),
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

        if (isset($input['avatar']) && is_string($input['avatar']) && $input['avatar'] !== '') {
            $avatarResult = $this->resolveAvatarValue($input['avatar']);
            if (!$avatarResult['ok']) {
                return $this->fail($avatarResult['error'], 400);
            }
            $data['avatar'] = $avatarResult['value'];
        }

        $avatarFile = $this->request->getFile('avatar');
        if ($avatarFile && $avatarFile->isValid() && !$avatarFile->hasMoved()) {
            $avatarResult = $this->storeAvatarFile($avatarFile);
            if (!$avatarResult['ok']) {
                return $this->fail($avatarResult['error'], 400);
            }
            $data['avatar'] = $avatarResult['value'];
        }

        if ($model->update($id, $data)) {
            $updatedUser = $model->find($id);
            if (isset($updatedUser['custom_permissions']) && is_string($updatedUser['custom_permissions'])) {
                $updatedUser['custom_permissions'] = json_decode($updatedUser['custom_permissions'], true) ?? [];
            }

            // Keep session user in sync so dashboard renders latest avatar/profile data after reload.
            $session = session();
            $sessionUser = $session->get('user');
            if (is_array($sessionUser) && isset($sessionUser['id']) && $sessionUser['id'] === $id) {
                unset($updatedUser['password']);
                $session->set('user', $updatedUser);
            }

            return $this->respond([
                'status' => 'success',
                'message' => 'Cập nhật nhân viên thành công',
                'data' => $updatedUser
            ]);
        }

        $errorBag = $model->errors();
        $dbError = $model->db->error();

        if (!empty($errorBag)) {
            return $this->fail(implode(' | ', $errorBag), 400);
        }

        if (!empty($dbError['message'])) {
            return $this->fail('Có lỗi cơ sở dữ liệu: ' . $dbError['message'], 500);
        }

        return $this->fail('Có lỗi bất ngờ khi cập nhật thông tin', 500);
    }

    private function resolveAvatarValue(string $avatarInput): array
    {
        $avatarInput = trim($avatarInput);

        if ($avatarInput === '') {
            return ['ok' => false, 'error' => 'Ảnh đại diện không hợp lệ'];
        }

        // Accept existing URL/path values without transformation.
        if (strpos($avatarInput, 'data:image/') !== 0) {
            return ['ok' => true, 'value' => $avatarInput];
        }

        if (!preg_match('/^data:image\/(png|jpe?g);base64,/', $avatarInput, $matches)) {
            return ['ok' => false, 'error' => 'Chỉ hỗ trợ ảnh PNG/JPG'];
        }

        $encodedPayload = substr($avatarInput, strpos($avatarInput, ',') + 1);
        $binaryData = base64_decode($encodedPayload, true);
        if ($binaryData === false) {
            return ['ok' => false, 'error' => 'Không thể đọc dữ liệu ảnh'];
        }

        // 3MB hard limit after decoding.
        if (strlen($binaryData) > 3 * 1024 * 1024) {
            return ['ok' => false, 'error' => 'Ảnh vượt quá giới hạn 3MB'];
        }

        $extension = $matches[1] === 'png' ? 'png' : 'jpg';
        $fileName = uniqid('avatar_', true) . '.' . $extension;
        $targetDir = FCPATH . 'uploads/avatars';

        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            return ['ok' => false, 'error' => 'Không thể tạo thư mục lưu ảnh đại diện'];
        }

        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
        if (file_put_contents($targetPath, $binaryData) === false) {
            return ['ok' => false, 'error' => 'Không thể lưu ảnh đại diện'];
        }

        return ['ok' => true, 'value' => base_url('uploads/avatars/' . $fileName)];
    }

    private function storeAvatarFile($avatarFile): array
    {
        $validated = $this->validate([
            'avatar' => [
                'uploaded[avatar]',
                'mime_in[avatar,image/jpg,image/jpeg,image/png]',
                'max_size[avatar,3072]',
            ],
        ]);

        if (!$validated) {
            return ['ok' => false, 'error' => 'File ảnh không hợp lệ, chỉ hỗ trợ JPG/PNG dưới 3MB'];
        }

        $targetDir = FCPATH . 'uploads/avatars';
        if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
            return ['ok' => false, 'error' => 'Không thể tạo thư mục upload avatar'];
        }

        $newName = $avatarFile->getRandomName();
        $avatarFile->move($targetDir, $newName);

        return ['ok' => true, 'value' => base_url('uploads/avatars/' . $newName)];
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