<?php

namespace App\Controllers;

use App\Libraries\AutoApproveSetting;
use CodeIgniter\RESTful\ResourceController;

class AutoApproveSettingController extends ResourceController
{
    protected $format = 'json';

    private function getCurrentUser(): ?array
    {
        return session()->get('user');
    }

    private function canManageAutoApprove(array $user): bool
    {
        return in_array(($user['role'] ?? ''), ['admin', 'manager'], true);
    }

    public function index()
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return $this->failUnauthorized('Vui lòng đăng nhập lại');
        }

        return $this->respond((new AutoApproveSetting())->getState());
    }

    public function update($id = null)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser) {
            return $this->failUnauthorized('Vui lòng đăng nhập lại');
        }

        if (!$this->canManageAutoApprove($currentUser)) {
            return $this->failForbidden('Bạn không có quyền thay đổi thiết lập này');
        }

        try {
            $payload = $this->request->getJSON(true);
        } catch (\Throwable $exception) {
            $payload = null;
        }

        if (!is_array($payload)) {
            $payload = $this->request->getRawInput();
        }

        $enabled = filter_var($payload['enabled'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($enabled === null) {
            return $this->failValidationErrors('Thiếu giá trị bật/tắt hợp lệ');
        }

        return $this->respond((new AutoApproveSetting())->setEnabled($enabled));
    }
}