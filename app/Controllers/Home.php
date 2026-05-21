<?php

namespace App\Controllers;

use App\Models\PositionModel;
use App\Models\JobCategoryModel;

class Home extends BaseController
{
    public function index()
    {
        $session = session();
        $user = $session->get('user');

        if (!$user) {
            return view('login');
        }

        // Prepare some starting data for PHP view rendering
        $positionModel = new PositionModel();
        $jobCategoryModel = new JobCategoryModel();

        $data = [
            'currentUser' => $user,
            'positions' => $positionModel->findAll(),
            'categories' => $jobCategoryModel->findAll()
        ];

        return view('dashboard', $data);
    }

    public function login()
    {
        $session = session();
        if ($session->get('user')) {
            return redirect()->to('/');
        }
        return view('login');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/');
    }
}
