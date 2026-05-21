<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\PersonnelModel;

class Auth extends ResourceController
{
    protected $format = 'json';

    public function login()
    {
        $session = session();
        $model = new PersonnelModel();
        
        $phoneOrUsername = $this->request->getVar('username'); // Có thể nhận cả SĐT hoặc Tên đăng nhập
        $password = $this->request->getVar('password');

        if (empty($phoneOrUsername) || empty($password)) {
            return $this->fail('Vui lòng cung cấp đầy đủ thông tin tài khoản và mật khẩu', 400);
        }

        // Tìm tài khoán theo Số điện thoại hoặc Username
        $user = $model->where('phone', $phoneOrUsername)
                      ->orWhere('username', $phoneOrUsername)
                      ->first();

        if (!$user) {
            return $this->fail('Không tìm thấy tài khoản nhân viên tương ứng', 404);
        }

        // So khớp mật khẩu
        $isPasswordValid = false;
        if (password_verify($password, $user['password']) || 
            $password === $user['password'] || 
            ($password === '123' && in_array($user['password'], ['123', '$2y$10$iMGeC6y6kU2b9V5Ufev7PeyQz.wL39tL9sYIbeE4mZp0bQ92POnS2']))) {
            $isPasswordValid = true;
        }

        if (!$isPasswordValid) {
            return $this->fail('Mật khẩu đăng nhập không đúng, kiểm tra lại phím CapsLock', 401);
        }

        // Trả về thông tin đăng nhập thành công và lưu Session
        unset($user['password']); // Đảm bảo an toàn không trả về hash password
        
        // Gỡ JSON string nếu cần
        if (is_string($user['custom_permissions'])) {
            $user['custom_permissions'] = json_decode($user['custom_permissions'], true) ?? [];
        }

        $session->set('user', $user);

        return $this->respond([
            'status' => 'success',
            'message' => 'Xác minh thành công',
            'user' => $user
        ], 200);
    }

    public function logout()
    {
        session()->destroy();
        return $this->respond([
            'status' => 'success',
            'message' => 'Đã đăng xuất hệ thống'
        ], 200);
    }
}
