<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\PositionModel;
use App\Models\JobCategoryModel;
use App\Models\PermissionModel;

class Category extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $posModel = new PositionModel();
        $jobModel = new JobCategoryModel();
        $permModel = new PermissionModel();

        return $this->respond([
            'positions' => $posModel->findAll(),
            'jobCategories' => $jobModel->findAll(),
            'permissions' => $permModel->findAll()
        ]);
    }

    // Positions CRUD
    public function createPosition()
    {
        $model = new PositionModel();
        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        
        $name = $input['name'] ?? '';
        if (empty($name)) {
            return $this->fail('Tên chức vụ không được để trống!', 400);
        }

        $data = [
            'id' => 'pos_' . rand(1000, 99999),
            'name' => $name,
            'description' => $input['description'] ?? ''
        ];

        if ($model->insert($data)) {
            return $this->respondCreated($data);
        }
        return $this->fail('Không thể lưu chức vụ.');
    }

    public function updatePosition($id = null)
    {
        $model = new PositionModel();
        if (!$model->find($id)) {
            return $this->failNotFound('Không tìm thấy chức vụ!');
        }

        $input = $this->request->getJSON(true) ?? $this->request->getRawInput();
        $data = [];
        if (isset($input['name'])) $data['name'] = $input['name'];
        if (isset($input['description'])) $data['description'] = $input['description'];

        if ($model->update($id, $data)) {
            return $this->respond($model->find($id));
        }
        return $this->fail('Không thể cập nhật chức vụ.');
    }

    public function deletePosition($id = null)
    {
        $model = new PositionModel();
        if (!$model->find($id)) {
            return $this->failNotFound('Không tìm thấy chức vụ!');
        }

        if ($model->delete($id)) {
            return $this->respond(['success' => true]);
        }
        return $this->fail('Không thể xóa chức vụ.');
    }

    // Job Categories (Jobs) CRUD
    public function createJobCategory()
    {
        $model = new JobCategoryModel();
        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        
        $name = $input['name'] ?? '';
        if (empty($name)) {
            return $this->fail('Tên loại hình công việc không được để trống!', 400);
        }

        $data = [
            'id' => 'cat_' . rand(1000, 99999),
            'name' => $name,
            'description' => $input['description'] ?? ''
        ];

        if ($model->insert($data)) {
            return $this->respondCreated($data);
        }
        return $this->fail('Không thể lưu loại hình công việc.');
    }

    public function updateJobCategory($id = null)
    {
        $model = new JobCategoryModel();
        if (!$model->find($id)) {
            return $this->failNotFound('Không tìm thấy danh mục công việc!');
        }

        $input = $this->request->getJSON(true) ?? $this->request->getRawInput();
        $data = [];
        if (isset($input['name'])) $data['name'] = $input['name'];
        if (isset($input['description'])) $data['description'] = $input['description'];

        if ($model->update($id, $data)) {
            return $this->respond($model->find($id));
        }
        return $this->fail('Không thể cập nhật danh mục công việc.');
    }

    public function deleteJobCategory($id = null)
    {
        $model = new JobCategoryModel();
        if (!$model->find($id)) {
            return $this->failNotFound('Không tìm thấy danh mục công việc!');
        }

        if ($model->delete($id)) {
            return $this->respond(['success' => true]);
        }
        return $this->fail('Không thể xóa danh mục công việc.');
    }

    // Permissions CRUD
    public function createPermission()
    {
        $model = new PermissionModel();
        $input = $this->request->getJSON(true) ?? $this->request->getPost();
        
        $name = $input['name'] ?? '';
        if (empty($name)) {
            return $this->fail('Tên quyền hạn không được để trống!', 400);
        }

        $data = [
            'id' => $input['id'] ?? ('p_' . rand(1000, 99999)),
            'name' => $name,
            'description' => $input['description'] ?? ''
        ];

        if ($model->insert($data)) {
            return $this->respondCreated($data);
        }
        return $this->fail('Không thể lưu quyền hạn.');
    }

    public function updatePermission($id = null)
    {
        $model = new PermissionModel();
        if (!$model->find($id)) {
            return $this->failNotFound('Không tìm thấy quyền!');
        }

        $input = $this->request->getJSON(true) ?? $this->request->getRawInput();
        $data = [];
        if (isset($input['name'])) $data['name'] = $input['name'];
        if (isset($input['description'])) $data['description'] = $input['description'];

        if ($model->update($id, $data)) {
            return $this->respond($model->find($id));
        }
        return $this->fail('Không thể cập nhật quyền.');
    }

    public function deletePermission($id = null)
    {
        $model = new PermissionModel();
        if (!$model->find($id)) {
            return $this->failNotFound('Không tìm thấy quyền!');
        }

        if ($model->delete($id)) {
            return $this->respond(['success' => true]);
        }
        return $this->fail('Không thể xóa quyền.');
    }
}
