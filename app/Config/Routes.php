<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous, so let's keep it false.
$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// Root and Web routes
$routes->get('/', 'Home::index');
$routes->get('login', 'Home::login');
$routes->get('logout', 'Home::logout');

// We can map all the APIs requested for the Woodworking Enterprise application.
$routes->group('api', function($routes) {
    // 1. Authentications
    $routes->post('auth/login', 'Auth::login');
    $routes->get('auth/logout', 'Auth::logout');

    // 2. Personnel CRUD (Hồ Sơ Nhân Sự & Gán Quyền)
    $routes->get('users', 'Personnel::index');
    $routes->get('users/(:segment)', 'Personnel::show/$1');
    $routes->post('users', 'Personnel::create');
    $routes->put('users/(:segment)', 'Personnel::update/$1');
    $routes->delete('users/(:segment)', 'Personnel::delete/$1');

    // 3. Task Planning & Assignments (Phân Giao Công Việc & Timeline)
    $routes->get('tasks', 'Task::index');
    $routes->get('tasks/(:segment)', 'Task::show/$1');
    $routes->post('tasks', 'Task::create');
    $routes->put('tasks/(:segment)', 'Task::update/$1');
    $routes->delete('tasks/(:segment)', 'Task::delete/$1');

    // 4. Daily Progress Logs (Nhật Ký Tiến Độ Nhật Trình Lắp Đặt)
    $routes->get('logs', 'ProgressLog::index');
    $routes->post('logs', 'ProgressLog::create'); // Supports image file upload
    $routes->put('logs/approve/(:num)', 'ProgressLog::approve/$1'); // Admin/Manager approve or reject log

    // 5. Reporting and Charts (Tổng hợp biểu đồ, Bảng xếp hạng nảng suất)
    $routes->get('dashboard/stats', 'Report::getPerformanceSummary');

    // 6. Category Management (Danh mục Chức vụ, Loại việc, Quyền hạn)
    $routes->get('categories', 'Category::index');
    $routes->post('categories/positions', 'Category::createPosition');
    $routes->put('categories/positions/(:segment)', 'Category::updatePosition/$1');
    $routes->delete('categories/positions/(:segment)', 'Category::deletePosition/$1');

    $routes->post('categories/jobs', 'Category::createJobCategory');
    $routes->put('categories/jobs/(:segment)', 'Category::updateJobCategory/$1');
    $routes->delete('categories/jobs/(:segment)', 'Category::deleteJobCategory/$1');

    $routes->post('categories/permissions', 'Category::createPermission');
    $routes->put('categories/permissions/(:segment)', 'Category::updatePermission/$1');
    $routes->delete('categories/permissions/(:segment)', 'Category::deletePermission/$1');
});
