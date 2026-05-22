<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ Thống Điều Hành Doanh Nghiệp - Quản Trị Toàn Diện</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Inter & Space Grotesk -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        h1, h2, h3, .brand-font {
            font-family: 'Space Grotesk', sans-serif;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .print-full-width {
                width: 100% !important;
                max-width: 100% !important;
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>
    <!-- Lucide Icons via CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body id="moc-viet-body" class="min-h-screen bg-slate-50 flex flex-col text-slate-800">

    <!-- GLOBAL TOP NAVBAR -->
    <header id="app-header" class="bg-slate-900 border-b border-slate-800 text-white z-30 sticky top-0 px-4 py-3 shadow-md no-print">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button onclick="toggleMobileMenu()" class="lg:hidden p-1.5 rounded-lg hover:bg-slate-800 focus:outline-none cursor-pointer">
                    <i data-lucide="menu" class="w-5 h-5" id="mobile-menu-trigger"></i>
                </button>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-500/10 border border-indigo-400/20 flex items-center justify-center">
                        <i data-lucide="hammer" class="w-4.5 h-4.5 text-indigo-400 animate-pulse"></i>
                    </div>
                    <div>
                        <span class="font-extrabold tracking-tight text-sm uppercase brand-font">WorkHub</span>
                        <span class="text-[9px] text-slate-400 block font-mono leading-none">QUẢN LÝ NHÂN SỰ</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <!-- Clock Indicator -->
                <div class="hidden md:flex items-center gap-1.5 text-xs text-slate-300 font-mono">
                    <i data-lucide="clock" class="w-4 h-4 text-slate-400"></i>
                    <span>Hôm nay: <strong>2026-05-21</strong> (Mũi Giờ Việt Nam)</span>
                </div>

                <!-- Account Badge -->
                <button onclick="openProfileModal()" class="flex items-center gap-2.5 bg-slate-800 hover:bg-slate-750 px-3 py-1.5 rounded-xl border border-slate-700 cursor-pointer transition-all text-white text-left focus:outline-none">
                    <img 
                        src="<?= $currentUser['avatar'] ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=150' ?>" 
                        alt="Avatar" 
                        class="w-6.5 h-6.5 rounded-full object-cover border border-slate-600">
                    <div class="hidden sm:block">
                        <p class="text-xs font-bold leading-tight"><?= esc($currentUser['name']) ?></p>
                        <span class="text-[9px] uppercase tracking-wider text-slate-400 font-semibold font-mono">
                            <?php 
                                if($currentUser['role'] === 'admin') echo 'Quản trị viên';
                                elseif($currentUser['role'] === 'manager') echo 'Quản lý';
                                else echo 'Nhân viên';
                            ?>
                        </span>
                    </div>
                </button>

                <!-- Logout Link -->
                <a href="<?= base_url('logout') ?>" 
                   class="px-2.5 py-1.5 hover:bg-rose-500/10 text-slate-300 hover:text-rose-400 rounded-lg text-xs transition-all flex items-center gap-1 font-semibold cursor-pointer">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span class="hidden sm:inline">Đăng xuất</span>
                </a>
            </div>
        </div>
    </header>

    <!-- CORE WORKSPACE SIDEBAR + MAIN AREA -->
    <div class="max-w-7xl w-full mx-auto flex-1 flex flex-col lg:flex-row relative">
        
        <!-- SIDEBAR CONTAINER (Desktop) -->
        <aside id="sidebar-panel" class="w-64 border-r border-slate-200 hidden lg:block p-4 sticky top-16 h-[calc(100vh-4rem)] no-print bg-white shrink-0">
            <nav class="space-y-1.5">
                <!-- Tab 1: Dashboard -->
                <button onclick="switchTab('dashboard')" id="tab-btn-dashboard"
                    class="tab-btn w-full text-xs font-bold px-4 py-3 rounded-xl flex items-center gap-3 transition-all cursor-pointer bg-slate-900 text-white shadow-sm font-extrabold">
                    <i data-lucide="layout-dashboard" class="w-4 h-4 text-indigo-400"></i>
                    <span>Bảng Điều Khiển</span>
                </button>

                <!-- Tab 2: Staff Profiles -->
                <?php if ($currentUser['role'] === 'admin' || in_array('p4', $currentUser['custom_permissions'] ?? [])): ?>
                <button onclick="switchTab('staff')" id="tab-btn-staff"
                    class="tab-btn w-full text-xs font-bold px-4 py-3 rounded-xl flex items-center gap-3 transition-all cursor-pointer text-slate-600 hover:text-slate-900 hover:bg-slate-50">
                    <i data-lucide="users" class="w-4 h-4 text-slate-400"></i>
                    <span>Hồ Sơ Nhân Sự</span>
                </button>
                <?php endif; ?>

                <!-- Tab 3: Categories -->
                <?php if ($currentUser['role'] === 'admin' || in_array('p5', $currentUser['custom_permissions'] ?? [])): ?>
                <button onclick="switchTab('categories')" id="tab-btn-categories"
                    class="tab-btn w-full text-xs font-bold px-4 py-3 rounded-xl flex items-center gap-3 transition-all cursor-pointer text-slate-600 hover:text-slate-900 hover:bg-slate-50">
                    <i data-lucide="tags" class="w-4 h-4 text-slate-400"></i>
                    <span>Tổ Chức Danh Mục</span>
                </button>
                <?php endif; ?>

                <!-- Tab 4: Tasks Planning -->
                <?php if ($currentUser['role'] !== 'staff' || in_array('p2', $currentUser['custom_permissions'] ?? [])): ?>
                <button onclick="switchTab('tasks')" id="tab-btn-tasks"
                    class="tab-btn w-full text-xs font-bold px-4 py-3 rounded-xl flex items-center gap-3 transition-all cursor-pointer text-slate-600 hover:text-slate-900 hover:bg-slate-50">
                    <i data-lucide="clipboard-list" class="w-4 h-4 text-slate-400"></i>
                    <span>Phân Giao & Timeline</span>
                </button>
                <?php endif; ?>

                <!-- Tab 5: Daily Logs -->
                <button onclick="switchTab('logs')" id="tab-btn-logs"
                    class="tab-btn w-full text-xs font-bold px-4 py-3 rounded-xl flex items-center gap-3 transition-all cursor-pointer text-slate-600 hover:text-slate-900 hover:bg-slate-50">
                    <i data-lucide="hammer" class="w-4 h-4 text-slate-400"></i>
                    <span><?= $currentUser['role'] === 'staff' ? 'Báo Cáo Công Việc Ngày' : 'Duyệt Báo Cáo Công Việc' ?></span>
                </button>

                <!-- Tab 6: Performance Reports -->
                <button onclick="switchTab('reports')" id="tab-btn-reports"
                    class="tab-btn w-full text-xs font-bold px-4 py-3 rounded-xl flex items-center gap-3 transition-all cursor-pointer text-slate-600 hover:text-slate-900 hover:bg-slate-50">
                    <i data-lucide="bar-chart-3" class="w-4 h-4 text-slate-400"></i>
                    <span>Thống Kê Báo Cáo</span>
                </button>
            </nav>

            <div class="absolute bottom-6 left-6 right-6 p-4 bg-slate-50 border border-slate-150 rounded-xl space-y-2 text-[10px] text-slate-400">
                <div class="flex gap-1.5 items-center">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-indigo-600"></i>
                    <span class="font-semibold text-slate-700">Tự động duyệt bật</span>
                </div>
                <p class="leading-normal">
                    Mọi báo cáo công việc ghi nhận ngày cũ chưa duyệt sẽ tự động chuyển trạng thái "Approved" để đảm bảo dữ liệu theo dõi.
                </p>
            </div>
        </aside>

        <!-- DRAWER MENU MOBILE -->
        <div id="mobile-menu-drawer" class="hidden fixed inset-0 bg-slate-950/40 backdrop-blur-sm z-40 lg:hidden no-print">
            <div class="fixed top-0 left-0 bottom-0 w-64 bg-white z-50 p-4 space-y-4 flex flex-col justify-between shadow-2xl">
                <div>
                    <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4">
                        <span class="text-xs font-extrabold text-slate-900 tracking-wider">DANH MỤC TRUY CẬP</span>
                        <button onclick="toggleMobileMenu()" class="text-slate-400 hover:text-slate-600 p-1 rounded cursor-pointer">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <nav class="space-y-1">
                        <button onclick="switchTab('dashboard')" class="w-full text-xs font-bold p-3 rounded-lg flex items-center gap-2.5 hover:bg-slate-50 text-slate-600">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                            <span>Bảng Điều Khiển</span>
                        </button>
                        <?php if ($currentUser['role'] === 'admin' || in_array('p4', $currentUser['custom_permissions'] ?? [])): ?>
                        <button onclick="switchTab('staff')" class="w-full text-xs font-bold p-3 rounded-lg flex items-center gap-2.5 hover:bg-slate-50 text-slate-600">
                            <i data-lucide="users" class="w-4 h-4"></i>
                            <span>Hồ Sơ Nhân Sự</span>
                        </button>
                        <?php endif; ?>
                        <?php if ($currentUser['role'] === 'admin' || in_array('p5', $currentUser['custom_permissions'] ?? [])): ?>
                        <button onclick="switchTab('categories')" class="w-full text-xs font-bold p-3 rounded-lg flex items-center gap-2.5 hover:bg-slate-50 text-slate-600">
                            <i data-lucide="tags" class="w-4 h-4"></i>
                            <span>Tổ Chức Danh Mục</span>
                        </button>
                        <?php endif; ?>
                        <?php if ($currentUser['role'] !== 'staff' || in_array('p2', $currentUser['custom_permissions'] ?? [])): ?>
                        <button onclick="switchTab('tasks')" class="w-full text-xs font-bold p-3 rounded-lg flex items-center gap-2.5 hover:bg-slate-50 text-slate-600">
                            <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                            <span>Phân Giao & Timeline</span>
                        </button>
                        <?php endif; ?>
                        <button onclick="switchTab('logs')" class="w-full text-xs font-bold p-3 rounded-lg flex items-center gap-2.5 hover:bg-slate-50 text-slate-600">
                            <i data-lucide="hammer" class="w-4 h-4"></i>
                            <span>Báo Cáo Công Việc</span>
                        </button>
                        <button onclick="switchTab('reports')" class="w-full text-xs font-bold p-3 rounded-lg flex items-center gap-2.5 hover:bg-slate-50 text-slate-600">
                            <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                            <span>Thống Kê Báo Cáo</span>
                        </button>
                    </nav>
                </div>
                <div class="text-[9px] text-slate-400">
                    © 2026 WorkHub Co., Ltd.
                </div>
            </div>
        </div>

        <!-- CORE CONTENT VIEWPORT -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 min-w-0" id="main-content-area">
            
            <!-- Global Feedback Alert -->
            <div id="toast-bin" class="hidden mb-6 p-4 rounded-xl border flex items-start gap-3 text-xs shadow-md">
                <i id="toast-icon" class="w-5 h-5 shrink-0 mt-0.5"></i>
                <div class="flex-1">
                    <b id="toast-title" class="block font-bold">Thao tác thành công</b>
                    <span id="toast-desc" class="text-slate-500">Chi tiết phản hồi vừa được cập nhật.</span>
                </div>
                <button onclick="closeToast()" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <!-- TAB CONTAINER 1: DASHBOARD -->
            <section id="viewport-dashboard" class="viewport-tab space-y-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                        <i data-lucide="layout-dashboard" class="text-indigo-600 w-6 h-6"></i> Bảng Tổng Quan Điều Hành
                    </h1>
                    <p class="text-xs text-slate-500">Giám sát tổng thể tiến độ công việc, hiệu suất và năng lực nhân sự hằng ngày.</p>
                </div>

                <!-- Numerical KPI Grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                    <div class="p-5 bg-white border border-slate-200/80 rounded-2xl shadow-sm flex items-center gap-3">
                        <div class="p-3 bg-indigo-50 rounded-xl text-indigo-600"><i data-lucide="users" class="w-5 h-5"></i></div>
                        <div>
                            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Nhân sự</span>
                            <span class="text-2xl font-extrabold text-slate-900 leading-none" id="kpi-staff">...</span>
                        </div>
                    </div>
                    <div class="p-5 bg-white border border-slate-200/80 rounded-2xl shadow-sm flex items-center gap-3">
                        <div class="p-3 bg-amber-50 rounded-xl text-amber-600"><i data-lucide="briefcase" class="w-5 h-5"></i></div>
                        <div>
                            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Công việc</span>
                            <span class="text-2xl font-extrabold text-slate-900 leading-none" id="kpi-tasks">...</span>
                        </div>
                    </div>
                    <div class="p-5 bg-white border border-slate-200/80 rounded-2xl shadow-sm flex items-center gap-3">
                        <div class="p-3 bg-blue-50 rounded-xl text-blue-600"><i data-lucide="hourglass" class="w-5 h-5"></i></div>
                        <div>
                            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Đang ghé lắp</span>
                            <span class="text-2xl font-extrabold text-slate-900 leading-none" id="kpi-active-tasks">...</span>
                        </div>
                    </div>
                    <div class="p-5 bg-white border border-slate-200/80 rounded-2xl shadow-sm flex items-center gap-3">
                        <div class="p-3 bg-emerald-50 rounded-xl text-emerald-600"><i data-lucide="check-circle" class="w-5 h-5"></i></div>
                        <div>
                            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Hoàn thiện thô</span>
                            <span class="text-2xl font-extrabold text-slate-900 leading-none" id="kpi-done-tasks">...</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Activities & Logs -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm lg:col-span-2 space-y-4">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest">Cập nhật báo cáo mới nhất</h3>
                            <button onclick="switchTab('logs')" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-0.5 cursor-pointer">
                                <span>Xem toàn bộ báo cáo</span> <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                        <div class="divide-y divide-slate-100 max-h-[380px] overflow-y-auto space-y-3 pr-2" id="dashboard-recent-logs-list">
                            <p class="text-xs text-slate-400 italic text-center py-8">Đang đồng bộ cơ sở dữ liệu PHP...</p>
                        </div>
                    </div>

                    <!-- Side helper widget -->
                    <div class="space-y-6">
                        <!-- Auto award worker -->
                        <div class="p-5 bg-gradient-to-br from-indigo-950 to-slate-950 text-white rounded-2xl shadow-lg relative overflow-hidden">
                            <div class="absolute -top-3 -right-3 opacity-10">
                                <i data-lucide="award" class="w-24 h-24"></i>
                            </div>
                            <div class="relative z-10 space-y-3">
                                <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-300 block">Nhân viên tiêu biểu trong tháng</span>
                                <div class="flex items-center gap-3" id="best-performer-widget">
                                    <div class="animate-pulse bg-slate-800 w-10 h-10 rounded-full"></div>
                                    <div class="space-y-1">
                                        <div class="h-3 bg-slate-800 w-24 rounded"></div>
                                        <div class="h-2.5 bg-slate-800 w-16 rounded"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Shortcut guides -->
                        <div class="p-5 bg-white border border-slate-200/80 rounded-2xl shadow-sm space-y-3">
                            <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Hành trình quản trị nhanh</h4>
                            <div class="grid grid-cols-2 gap-2">
                                <button onclick="switchTab('logs')" class="p-2.5 bg-slate-50 hover:bg-slate-100/80 transition-all rounded-xl border border-slate-150 text-left cursor-pointer">
                                    <i data-lucide="file-text" class="w-4 h-4 text-slate-500 mb-1"></i>
                                    <span class="block text-[11px] font-bold text-slate-800">Cập nhật tiến độ</span>
                                    <span class="text-[9px] text-slate-400">Xem và nộp ảnh</span>
                                </button>
                                <?php if($currentUser['role'] !== 'staff'): ?>
                                <button onclick="switchTab('tasks')" class="p-2.5 bg-slate-50 hover:bg-slate-100/80 transition-all rounded-xl border border-slate-150 text-left cursor-pointer">
                                    <i data-lucide="clipboard-list" class="w-4 h-4 text-slate-500 mb-1"></i>
                                    <span class="block text-[11px] font-bold text-slate-800">Giao việc mới</span>
                                    <span class="text-[9px] text-slate-400">Chọn nhiều nhân viên</span>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- TAB CONTAINER 2: STAFF MANAGEMENT -->
            <section id="viewport-staff" class="viewport-tab hidden space-y-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                            <i data-lucide="users" class="text-indigo-600 w-6 h-6"></i> Quản Lý Hồ Sơ Nhân Sự
                        </h1>
                        <p class="text-xs text-slate-500">Tổ chức danh sách nhân sự, phân công chức trách chuyên môn và cấp quyền tùy biến.</p>
                    </div>
                    <button onclick="openStaffModal()" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 cursor-pointer shadow-sm transition-all">
                        <i data-lucide="user-plus" class="w-4 h-4"></i> Thêm Nhân Sự Mới
                    </button>
                </div>

                <!-- Search box -->
                <div class="flex bg-white items-center gap-2 px-3 py-2 border border-slate-200 rounded-xl max-w-sm shadow-sm">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                    <input type="text" id="staff-search" oninput="filterStaffTable()" placeholder="Nhập tên nhân viên hoặc điện thoại..." class="text-xs w-full bg-transparent focus:outline-none">
                </div>

                <!-- Staff Table -->
                <div class="bg-white border border-slate-200/80 rounded-2xl overflow-hidden shadow-sm">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-150/40 border-b border-slate-200 text-slate-500 uppercase tracking-wide font-semibold text-[10px]">
                                    <th class="py-3.5 px-4">Nhân sự</th>
                                    <th class="py-3.5 px-4">Số điện thoại</th>
                                    <th class="py-3.5 px-4">Chức vụ phụ trách</th>
                                    <th class="py-3.5 px-4 text-center">Vai Trò Hệ Thống</th>
                                    <th class="py-3.5 px-4 text-center">Quyền hạn đặc thù</th>
                                    <th class="py-3.5 px-4 text-right">Lựa chọn</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-600" id="staff-table-body">
                                <tr>
                                    <td colSpan="6" class="text-center py-10 text-slate-400 italic">Đang tải hồ sơ...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- TAB CONTAINER 3: CATEGORY MANAGEMENT -->
            <section id="viewport-categories" class="viewport-tab hidden space-y-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                            <i data-lucide="tags" class="text-indigo-600 w-6 h-6"></i> Thiết Lập Danh Mục Cơ Sở
                        </h1>
                        <p class="text-xs text-slate-500">Quản trị danh mục chức danh chuyên môn, danh mục quyền hạn phân hệ và danh sách các lĩnh vực loại công việc linh hoạt.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Column 1: Positions -->
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
                        <div class="flex justify-between items-center border-b border-slate-100 pb-2">
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-1.5">
                                <i data-lucide="briefcase" class="w-4 h-4 text-indigo-500"></i> Chức danh & Vị trí
                            </h3>
                            <?php if ($currentUser['role'] === 'admin' || in_array('p5', $currentUser['custom_permissions'] ?? [])): ?>
                            <button onclick="openCategoryModal('positions')" class="text-[10px] font-extrabold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 cursor-pointer">
                                <i data-lucide="plus" class="w-3 h-3"></i> + THÊM VỊ TRÍ
                            </button>
                            <?php endif; ?>
                        </div>
                        <div class="space-y-3 max-h-[380px] overflow-y-auto pr-1" id="list-positions">
                            <div class="text-slate-400 text-xs italic py-4 text-center">Đang nạp danh mục...</div>
                        </div>
                    </div>

                    <!-- Column 2: Job Categories -->
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
                        <div class="flex justify-between items-center border-b border-slate-100 pb-2">
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-1.5">
                                <i data-lucide="layers" class="w-4 h-4 text-blue-500"></i> Loại công việc
                            </h3>
                            <?php if ($currentUser['role'] === 'admin' || in_array('p5', $currentUser['custom_permissions'] ?? [])): ?>
                            <button onclick="openCategoryModal('jobs')" class="text-[10px] font-extrabold text-blue-600 hover:text-blue-800 flex items-center gap-1 cursor-pointer">
                                <i data-lucide="plus" class="w-3 h-3"></i> + THÊM LOẠI VIỆC
                            </button>
                            <?php endif; ?>
                        </div>
                        <div class="space-y-3 max-h-[380px] overflow-y-auto pr-1" id="list-job-categories">
                            <div class="text-slate-400 text-xs italic py-4 text-center">Đang nạp danh mục...</div>
                        </div>
                    </div>

                    <!-- Column 3: Permissions -->
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
                        <div class="flex justify-between items-center border-b border-slate-100 pb-2">
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-1.5">
                                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-500"></i> Danh mục quyền quyền hạn
                            </h3>
                            <?php if ($currentUser['role'] === 'admin' || in_array('p5', $currentUser['custom_permissions'] ?? [])): ?>
                            <button onclick="openCategoryModal('permissions')" class="text-[10px] font-extrabold text-emerald-600 hover:text-emerald-850 flex items-center gap-1 cursor-pointer">
                                <i data-lucide="plus" class="w-3 h-3"></i> + THÊM QUYỀN HẠN
                            </button>
                            <?php endif; ?>
                        </div>
                        <div class="space-y-3 max-h-[380px] overflow-y-auto pr-1" id="list-permissions">
                            <div class="text-slate-400 text-xs italic py-4 text-center">Đang nạp danh mục...</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- TAB CONTAINER 4: TASK PLANNING & TIMELINE -->
            <section id="viewport-tasks" class="viewport-tab hidden space-y-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                            <i data-lucide="clipboard-list" class="text-indigo-600 w-6 h-6"></i> Phân Công Công Việc & Timeline
                        </h1>
                        <p class="text-xs text-slate-500">Khởi tạo kế hoạch công việc, chỉ định thời hạn và **giao việc đồng thời cho một nhóm nhiều nhân viên**.</p>
                    </div>
                    <button onclick="openTaskModal()" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800  text-white text-xs font-bold rounded-xl flex items-center gap-1.5 cursor-pointer shadow-sm transition-all">
                        <i data-lucide="plus" class="w-4 h-4"></i> Tạo Việc & Phân Công
                    </button>
                </div>

                <!-- Tasks Listing & Gantt overview -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Detailed task list left side -->
                    <div class="lg:col-span-2 space-y-4" id="tasks-timeline-container">
                        <!-- Loaded via JS dynamically -->
                        <div class="text-center py-10 bg-white border border-slate-200 rounded-2xl italic text-slate-400 text-xs">
                            Đang kết nối danh sách công việc...
                        </div>
                    </div>

                    <!-- Right sidebar task statistics -->
                    <div class="space-y-6">
                        <div class="p-5 bg-white border border-slate-200 rounded-2xl shadow-sm space-y-4">
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Lưu ý khi gán việc</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Bạn có thể gán nhiều nhân viên vào cùng một công việc để họ phối hợp và nộp báo cáo chung theo tiến độ.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- TAB CONTAINER 5: DAILY REVIEWS & QUALITY CONTRL -->
            <section id="viewport-logs" class="viewport-tab hidden space-y-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                            <i data-lucide="hammer" class="text-indigo-600 w-6 h-6"></i> 
                            <span><?= $currentUser['role'] === 'staff' ? 'Báo Cáo Công Việc Ngày' : 'Duyệt Tiến Độ Công Việc' ?></span>
                        </h1>
                        <p class="text-xs text-slate-500">
                            <?= $currentUser['role'] === 'staff' ? 'Nhập ghi chú hằng ngày, báo cáo % hoàn thành kèm ảnh minh chứng gửi quản lý.' : 'Xem xét chất lượng và tiến độ thực tế của từng nhân viên để duyệt báo cáo.' ?>
                        </p>
                    </div>
                    <?php if($currentUser['role'] === 'staff'): ?>
                    <button onclick="openSubmitLogModal()" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 cursor-pointer shadow-sm transition-all">
                        <i data-lucide="plus" class="w-4 h-4"></i> Viết Báo Cáo Hôm Nay
                    </button>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Logs list grid -->
                    <div class="lg:col-span-3 space-y-4" id="logs-feed-container">
                        <p class="text-xs text-slate-400 italic text-center py-12">Đang nạp bảng tin cập nhật... </p>
                    </div>

                    <!-- Filter panel side -->
                    <div class="p-5 bg-white border border-slate-200 rounded-2xl shadow-sm h-fit space-y-4 no-print">
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider pb-1 border-b border-slate-100">Tìm lọc nhanh</h3>
                        
                        <div class="space-y-3 text-xs">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Trạng thái duyệt</label>
                                <select id="filter-log-status" onchange="loadProgressLogs()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="pending">Đợi phê duyệt (Pending)</option>
                                    <option value="approved">Đã chuẩn y (Approved)</option>
                                    <option value="rejected">Từ chối (Rejected)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- TAB CONTAINER 6: PERFORMANCE SUMMARY (REPORTS) -->
            <section id="viewport-reports" class="viewport-tab hidden space-y-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                            <i data-lucide="bar-chart-3" class="text-indigo-600 w-6 h-6"></i> Thống Kê Hiệu Suất Công Việc
                        </h1>
                        <p class="text-xs text-slate-500">Tính toán hiệu suất tích lũy dựa trên công việc đã được duyệt.</p>
                    </div>
                    <button onclick="window.print()" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 cursor-pointer shadow-sm transition-all no-print">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i> In Báo Cáo PDF
                    </button>
                </div>

                <!-- Highlight Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Performance Ranking Board -->
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
                        <div class="border-b border-slate-100 pb-2">
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-1">
                                <i data-lucide="award" class="w-4 h-4 text-amber-500"></i> BẢNG XẾP HẠNG HIỆU SUẤT THEO TÍCH ĐIỂM
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-400 uppercase tracking-wider text-[9px] font-bold border-b border-slate-100">
                                        <th class="py-2 px-3">Hạng / Nhân viên</th>
                                        <th class="py-2 px-3 text-center">Đầu việc gán</th>
                                        <th class="py-2 px-3 text-center">Báo cáo ngày chuẩn</th>
                                        <th class="py-2 px-3 text-right">Tổng tiến độ tích</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-600 font-medium" id="performance-table-body">
                                    <!-- Populated dynamically via Report api -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Workload Progress Overview -->
                    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
                        <div class="border-b border-slate-100 pb-2">
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest flex items-center gap-1">
                                <i data-lucide="clipboard-list" class="w-4 h-4 text-indigo-500"></i> TIẾN ĐỘ THÔ CỦA CÁC ĐẦU MỤC CÔNG VIỆC GIAO
                            </h3>
                        </div>
                        <div class="space-y-4" id="report-tasks-list-container">
                            <p class="text-xs text-slate-400 italic text-center py-6">Đang tổng kết dữ liệu...</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- GLOBAL MODAL BACKDROPS (Staff forms, Tasks forms) -->
    
    <!-- 1. Staff Modal (Add / Edit) -->
    <div id="staff-modal" class="hidden fixed inset-0 bg-slate-950/50 backdrop-blur-xs flex items-center justify-center p-4 z-50 no-print">
        <div class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-100 p-6 space-y-5 animate-in fade-in duration-200">
            <div class="flex justify-between items-center pb-3 border-b border-slate-150">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <i data-lucide="user-plus" class="text-indigo-600 w-5 h-5"></i> <span id="staff-modal-title">Thêm Hồ Sơ Nhân Viên</span>
                </h3>
                <button onclick="closeStaffModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded cursor-pointer"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <form id="staff-form" class="space-y-4" onsubmit="handleStaffSubmit(event)">
                <input type="hidden" id="staff-edit-id" value="">

                <div class="space-y-1 text-center">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Ảnh đại diện nhân sự</label>
                    <input type="file" id="staff-avatar-file" accept="image/png, image/jpeg, image/jpg" class="hidden" onchange="previewStaffAvatar(event)">
                    <div onclick="document.getElementById('staff-avatar-file').click()" class="relative group cursor-pointer w-20 h-20 mx-auto rounded-full overflow-hidden border-2 border-slate-200 hover:border-slate-400 transition-all shadow-sm">
                        <img id="staff-avatar-preview" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=150" class="w-full h-full object-cover" alt="Avatar preview">
                        <div class="absolute inset-0 bg-black/45 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <i data-lucide="camera" class="w-5 h-5 text-white"></i>
                        </div>
                    </div>
                    <span class="block text-[9px] text-slate-400 mt-1">Ấn để thay đổi (PNG/JPG)</span>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Họ tên nhân viên <span class="text-rose-500">*</span></label>
                        <input type="text" id="staff-name" required placeholder="Ví dụ: Nguyễn Văn A" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Số điện thoại <span class="text-rose-500">*</span></label>
                        <input type="text" id="staff-phone" required placeholder="Điện thoại làm tài khoản..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Chứng minh thư / CCCD <span class="text-rose-500">*</span></label>
                        <input type="text" id="staff-ic" required placeholder="Nhập số CMND hoặc CCCD..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Ngày sinh</label>
                        <input type="date" id="staff-dob" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Địa chỉ thường trú</label>
                    <textarea id="staff-address" rows="2" placeholder="Chỗ ở hiện tại tuyển dụng..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none"></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Vai Trò Hệ Thống <span class="text-rose-500">*</span></label>
                        <select id="staff-role" onchange="adjustPermissionCheckboxesByRole()" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none">
                            <option value="staff">Nhân viên thực thi (Staff)</option>
                            <option value="manager">Quản lý điều phối (Manager)</option>
                            <option value="admin">Quản trị viên tối cao (Admin)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Chức vụ phụ trách</label>
                        <select id="staff-position-id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none">
                            <option value="">Không phân chức vị</option>
                            <?php foreach($positions as $pos): ?>
                            <option value="<?= esc($pos['id']) ?>"><?= esc($pos['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="bg-slate-50 p-4 border border-slate-150 rounded-xl space-y-2">
                    <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Phân quyền chức trách đặc thù</span>
                    <div id="staff-permissions-grid" class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-slate-700">
                        <span class="text-slate-400 italic text-[11px]">Đang tải danh mục quyền...</span>
                    </div>
                </div>

                <div class="space-y-2" id="staff-password-wrap">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Thiết lập Mật khẩu <span id="pwd-required-star" class="text-rose-500">*</span></label>
                    <input type="password" id="staff-password" placeholder="Mặc định là 123 nếu để trống..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none">
                </div>

                <div class="flex justify-end gap-2.5 pt-2 border-t border-slate-100">
                    <button type="button" onclick="closeStaffModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-700 cursor-pointer">Hủy bỏ</button>
                    <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-sm cursor-pointer">Lưu hồ sơ</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. Task Modal (Add / Edit) -->
    <div id="task-modal" class="hidden fixed inset-0 bg-slate-950/50 backdrop-blur-xs flex items-center justify-center p-4 z-50 no-print">
        <div class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-100 p-6 space-y-5 animate-in fade-in duration-200">
            <div class="flex justify-between items-center pb-3 border-b border-slate-150">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <i data-lucide="clipboard-list" class="text-indigo-600 w-5 h-5"></i> <span id="task-modal-title">Tạo Việc Mới & Chỉ Định Nhân Viên</span>
                </h3>
                <button onclick="closeTaskModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded cursor-pointer"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <form id="task-form" class="space-y-4" onsubmit="handleTaskSubmit(event)">
                <input type="hidden" id="task-edit-id" value="">

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tên công việc / Dự án gỗ mỹ nghệ <span class="text-rose-500">*</span></label>
                    <input type="text" id="task-title" required placeholder="Ví dụ: Chuẩn bị báo cáo tháng, cập nhật hợp đồng..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Công đoạn chế tác <span class="text-rose-500">*</span></label>
                        <select id="task-job-category-id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none">
                            <?php foreach($categories as $cat): ?>
                            <option value="<?= esc($cat['id']) ?>"><?= esc($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Trạng thái hiện thời</label>
                        <select id="task-status" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none">
                            <option value="pending">Chờ bắt đầu (Pending)</option>
                            <option value="in_progress">Ghé ráp thi công (In Progress)</option>
                            <option value="completed">Từ giã bàn giao (Completed)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Ngày bắt đầu <span class="text-rose-500">*</span></label>
                        <input type="date" id="task-start-date" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Hạn hoàn thành <span class="text-rose-500">*</span></label>
                        <input type="date" id="task-end-date" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Mô tả quy cách, thông số chi tiết của sản phẩm</label>
                    <textarea id="task-description" rows="3" placeholder="Mô tả chi tiết nhiệm vụ, đầu ra, phạm vi phụ trách..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none"></textarea>
                </div>

                <div class="bg-slate-50 p-4 border border-slate-150 rounded-xl space-y-2">
                    <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Chỉ định nhóm nhân viên thực hiện (Chọn nhiều người)</span>
                    <div id="workers-selection-grid" class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-[160px] overflow-y-auto text-xs text-slate-700 pr-1">
                        <!-- Dynamically populated via staff users loaded in memory -->
                    </div>
                </div>

                <div class="flex justify-end gap-2.5 pt-2 border-t border-slate-100">
                    <button type="button" onclick="closeTaskModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-700 cursor-pointer">Hủy</button>
                    <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-sm cursor-pointer">Lập kế hoạch</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. Worker Progress Submission Modal (Staff Only) -->
    <div id="submit-log-modal" class="hidden fixed inset-0 bg-slate-950/50 backdrop-blur-xs flex items-center justify-center p-4 z-50 no-print">
        <div class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl border border-slate-100 p-6 space-y-5 animate-in fade-in duration-200">
            <div class="flex justify-between items-center pb-3 border-b border-slate-150">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <i data-lucide="plus" class="text-indigo-600 w-5 h-5"></i> <span>Ghi Báo Cáo Công Việc</span>
                </h3>
                <button onclick="closeSubmitLogModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded cursor-pointer"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <form id="submit-log-form" class="space-y-4" onsubmit="handleLogSubmit(event)">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Chọn việc đang tham gia <span class="text-rose-500">*</span></label>
                    <select id="log-task-id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none">
                        <!-- Loaded through staff's associated tasks only -->
                    </select>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Mức độ hoàn thành (%) <span class="text-rose-500">*</span></label>
                        <div class="flex items-center gap-2 pt-1">
                            <input type="range" id="log-progress-slider" min="0" max="100" step="5" value="50" oninput="document.getElementById('slider-val-lbl').innerText = this.value + '%'" class="w-full accent-slate-900">
                            <span id="slider-val-lbl" class="text-xs font-bold text-slate-900 min-w-[35px]">50%</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Ngày làm việc báo cáo</label>
                        <input type="date" id="log-date" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Đăng ảnh minh chứng thực tế <span class="text-rose-500">*</span></label>
                    <input type="file" id="log-image" accept="image/png, image/jpeg, image/jpg" class="hidden" onchange="previewSelectedImage(event)">
                    <div onclick="document.getElementById('log-image').click()" class="border-2 border-dashed border-slate-200 hover:border-slate-400 bg-slate-50 rounded-2xl p-6 text-center cursor-pointer transition-all space-y-2">
                        <i data-lucide="image" class="w-8 h-8 text-slate-400 mx-auto"></i>
                        <span class="block text-xs font-bold text-slate-500">Ấn để chọn ảnh hoặc kéo thả vào đây</span>
                        <span class="text-[9px] text-slate-400">Yêu cầu ảnh JPG/PNG rõ nội dung công việc</span>
                        <div id="image-upload-preview-container" class="hidden pt-2">
                            <img id="image-upload-preview" src="#" alt="Xem trước" class="max-h-[140px] rounded-xl mx-auto border border-slate-200 shadow-sm">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Chú thích chi tiết công việc hôm nay <span class="text-rose-500">*</span></label>
                    <textarea id="log-notes" rows="3" required placeholder="Ghi chú rõ thớ thớ thạch gỗ đắp mịn sơn thế nào..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-2.5 pt-2 border-t border-slate-100">
                    <button type="button" onclick="closeSubmitLogModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-700 cursor-pointer">Bỏ qua</button>
                    <button type="submit" id="btn-log-submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-sm cursor-pointer">Nộp báo cáo</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 4. Profile Edit Modal -->
    <div id="profile-modal" class="hidden fixed inset-0 bg-slate-950/50 backdrop-blur-xs flex items-center justify-center p-4 z-50 no-print">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-100 p-6 space-y-5 animate-in fade-in duration-250">
            <div class="flex justify-between items-center pb-3 border-b border-slate-150">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <i data-lucide="user" class="text-indigo-600 w-5 h-5"></i> <span>Hồ Sơ Cá Nhân</span>
                </h3>
                <button onclick="closeProfileModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded cursor-pointer"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <form id="profile-form" class="space-y-4" onsubmit="handleProfileSubmit(event)">
                <!-- Avatar Upload Section -->
                <div class="space-y-1 text-center">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Ảnh đại diện</label>
                    <input type="file" id="profile-avatar-file" accept="image/png, image/jpeg, image/jpg" class="hidden" onchange="previewProfileAvatar(event)">
                    <div onclick="document.getElementById('profile-avatar-file').click()" class="relative group cursor-pointer w-20 h-20 mx-auto rounded-full overflow-hidden border-2 border-slate-200 hover:border-slate-400 transition-all shadow-sm">
                        <img id="profile-avatar-preview" src="<?= $currentUser['avatar'] ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=150' ?>" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/45 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                            <i data-lucide="camera" class="w-5 h-5 text-white"></i>
                        </div>
                    </div>
                    <span class="block text-[9px] text-slate-400 mt-1">Ấn để thay đổi (PNG/JPG)</span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Họ và tên</label>
                        <input type="text" id="profile-name" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Số điện thoại</label>
                        <input type="text" id="profile-phone" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Căn cước công dân</label>
                        <input type="text" id="profile-ic" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Ngày sinh</label>
                        <input type="date" id="profile-dob" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Địa chỉ cư trú</label>
                    <input type="text" id="profile-address" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Mật khẩu mới (Bỏ trống nếu không đổi)</label>
                    <input type="password" id="profile-password" placeholder="Nhập để đặt mật khẩu mới..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none">
                </div>

                <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeProfileModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-700 cursor-pointer">Bỏ qua</button>
                    <button type="submit" id="btn-profile-submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-sm cursor-pointer">Lưu hồ sơ</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 5. Master Category Modal (Add / Edit) -->
    <div id="category-modal" class="hidden fixed inset-0 bg-slate-950/50 backdrop-blur-xs flex items-center justify-center p-4 z-50 no-print">
        <div class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-100 p-6 space-y-5 animate-in fade-in duration-200">
            <div class="flex justify-between items-center pb-3 border-b border-slate-150">
                <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <i data-lucide="tags" class="text-indigo-600 w-5 h-5"></i> <span id="category-modal-title">Cập nhật danh mục</span>
                </h3>
                <button onclick="closeCategoryModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded cursor-pointer"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <form id="category-form" class="space-y-4" onsubmit="handleCategorySubmit(event)">
                <input type="hidden" id="category-type" value=""> <!-- positions, jobs, permissions -->
                <input type="hidden" id="category-edit-id" value="">

                <div id="category-id-field-wrapper" class="hidden">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Mã định danh (ID) <span class="text-rose-500">*</span></label>
                    <input type="text" id="category-item-id" placeholder="Ví dụ: p6" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tên mục hiển thị <span class="text-rose-500">*</span></label>
                    <input type="text" id="category-name" required placeholder="Nhập nhãn danh mục..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Mô tả chi tiết</label>
                    <textarea id="category-description" rows="3" placeholder="Nhập diễn giải ngắn gọn..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeCategoryModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-700 cursor-pointer">Bỏ qua</button>
                    <button type="submit" id="btn-category-submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm cursor-pointer">Lưu cập nhật</button>
                </div>
            </form>
        </div>
    </div>


    <footer class="bg-slate-900 text-slate-500 py-6 border-t border-slate-800 text-center text-xs mt-auto no-print">
        <p class="flex items-center justify-center gap-1">
            Thiết kế bởi hệ thống <span class="font-semibold text-slate-300">Quản Lý Nhân Sự & Công Việc Doanh Nghiệp</span> 
        </p>
        <p class="text-[10px] mt-1 text-slate-600 font-mono">
            Vận Hành Hiệu Quả Trên Nền Tảng Pure CodeIgniter 4 MVC Backend • PHP Core Native
        </p>
    </footer>

    <!-- DYNAMIC CORE CLIENT JAVASCRIPT LOGIC WITH INTERACTIVE STATE -->
    <script>
        // Global Local Storage / Memory State synchronized via PHP
        const PHP_CURRENT_USER = <?= json_encode($currentUser) ?>;
        
        // In memory databases updated dynamically
        let cacheStaff = [];
        let cacheTasks = [];
        let cacheLogs = [];
        let cacheStats = null;
        let cachePositions = [];
        let cacheJobCategories = [];
        let cachePermissions = [];

        // Initialize Lucide Icons initially
        lucide.createIcons();

        // On document ready
        window.addEventListener('DOMContentLoaded', () => {
            // Pre-load Date Inputs to today
            const todayStr = '2026-05-21';
            const logDateInput = document.getElementById('log-date');
            if (logDateInput) logDateInput.value = todayStr;

            // Load all database parameters from endpoint
            syncData();
        });

        // ----------------------------------------------------
        // REFRESH & SYNCHRONIZE DATA VIA BACKEND ENDPOINTS
        // ----------------------------------------------------
        async function syncData() {
            try {
                // Parrallel requests to API Controllers
                const [staffRes, tasksRes, logsRes, statsRes, categoriesRes] = await Promise.all([
                    fetch('<?= base_url('api/users') ?>'),
                    fetch('<?= base_url('api/tasks') ?>'),
                    fetch('<?= base_url('api/logs') ?>'),
                    fetch('<?= base_url('api/dashboard/stats') ?>'),
                    fetch('<?= base_url('api/categories') ?>')
                ]);

                if (staffRes.ok) cacheStaff = await staffRes.json();
                if (tasksRes.ok) cacheTasks = await tasksRes.json();
                if (logsRes.ok) cacheLogs = await logsRes.json();
                if (statsRes.ok) cacheStats = await statsRes.json();
                if (categoriesRes && categoriesRes.ok) {
                    const cats = await categoriesRes.json();
                    cachePositions = cats.positions || [];
                    cacheJobCategories = cats.jobCategories || [];
                    cachePermissions = cats.permissions || [];
                }

                // Build frontend layouts based on results
                renderDashboardMetrics();
                renderStaffTable();
                renderTasksTimeline();
                renderProgressLogs();
                renderPerformanceReports();
                renderMasterCategories();
                
                // Form elements update
                populateWorkersSelections();
                populateWorkerTaskSelect();
            } catch (err) {
                console.error("Database connection snapped: ", err);
                showToast("alarm", "Kết nối chậm", "Không thể lấy số liệu gỗ thực tế hoặc CSDL MySQL tạm nghỉ.");
            }
        }

        // ----------------------------------------------------
        // TAB SWAP CONTROLLER
        // ----------------------------------------------------
        function switchTab(tabId) {
            // Hide all tabs
            document.querySelectorAll('.viewport-tab').forEach(section => {
                section.classList.add('hidden');
            });
            // Show requested tab
            const targetEl = document.getElementById('viewport-' + tabId);
            if (targetEl) {
                targetEl.classList.remove('hidden');
            }
            
            // Un-active all desk buttons
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.className = "tab-btn w-full text-xs font-bold px-4 py-3 rounded-xl flex items-center gap-3 transition-all cursor-pointer text-slate-600 hover:text-slate-900 hover:bg-slate-50";
            });

            // Set current active button
            const activeBtn = document.getElementById('tab-btn-' + tabId);
            if (activeBtn) {
                activeBtn.className = "tab-btn w-full text-xs font-bold px-4 py-3 rounded-xl flex items-center gap-3 transition-all cursor-pointer bg-slate-900 text-white shadow-sm font-extrabold";
            }

            // Close mobile menu if responsive
            const drawer = document.getElementById('mobile-menu-drawer');
            drawer.classList.add('hidden');

            // Quick sync when swapping
            syncData();
        }

        function toggleMobileMenu() {
            const drawer = document.getElementById('mobile-menu-drawer');
            if (drawer.classList.contains('hidden')) {
                drawer.classList.remove('hidden');
            } else {
                drawer.classList.add('hidden');
            }
        }

        // ----------------------------------------------------
        // DYNAMIC VIEWPORT 1: DASHBOARD METRICS
        // ----------------------------------------------------
        function renderDashboardMetrics() {
            if (!cacheStats) return;
            const s = cacheStats.summary;
            document.getElementById('kpi-staff').innerText = s.totalStaff + ' người';
            document.getElementById('kpi-tasks').innerText = s.totalTasks + ' việc';
            document.getElementById('kpi-active-tasks').innerText = s.inProgressTasks + ' việc';
            document.getElementById('kpi-done-tasks').innerText = s.completedTasks + ' việc';

            // Top employer avatar block
            const bestPerformerWidget = document.getElementById('best-performer-widget');
            const productivity = cacheStats.employeeProductivity || [];
            
            if (productivity.length > 0) {
                const top = [...productivity].sort((a,b) => b.totalProgressPoints - a.totalProgressPoints)[0];
                if (top) {
                    bestPerformerWidget.innerHTML = `
                        <img src="${top.avatar}" alt="Top worker" class="w-11 h-11 rounded-full object-cover border-2 border-indigo-500/50">
                        <div>
                            <h4 class="font-bold text-sm text-slate-100">${top.name}</h4>
                            <span class="block text-[10px] text-amber-300 font-bold uppercase tracking-wider">Tích lũy ${top.totalProgressPoints}% tiến độ</span>
                        </div>
                    `;
                }
            } else {
                bestPerformerWidget.innerHTML = `<span class="text-xs text-slate-400">Chưa ghi nhận hoạt động</span>`;
            }

            // Recent logs inline feed
            const container = document.getElementById('dashboard-recent-logs-list');
            if (cacheLogs.length === 0) {
                container.innerHTML = `<p class="text-xs text-slate-400 italic text-center py-6">Chưa có báo cáo nào được gửi hôm nay.</p>`;
                return;
            }

            let logsHTML = '';
            cacheLogs.slice(0, 5).forEach(log => {
                let badgeStyle = "bg-amber-50 text-amber-700";
                let badgeText = "Chờ duyệt";
                if (log.status === "approved") {
                    badgeStyle = log.auto_approved == 1 ? "bg-slate-50 text-slate-500" : "bg-emerald-50 text-emerald-700";
                    badgeText = log.auto_approved == 1 ? "Tự động duyệt" : "Đã thông qua";
                } else if (log.status === "rejected") {
                    badgeStyle = "bg-rose-50 text-rose-700";
                    badgeText = "Từ chối";
                }

                logsHTML += `
                    <div class="py-3 flex items-start gap-3 text-xs">
                        <img src="${log.user_avatar}" alt="Avatar" class="w-8 h-8 rounded-full object-cover shrink-0 border border-slate-100">
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-slate-800">${log.user_name}</span>
                                <span class="text-[9px] text-slate-400 font-mono">${log.date}</span>
                            </div>
                            <p class="text-slate-500 text-[11px] truncate mt-0.5">Quy cách: <b>${log.task_title}</b> (${log.progress_percent}%) - ${log.notes}</p>
                        </div>
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold shrink-0 ${badgeStyle}">${badgeText}</span>
                    </div>
                `;
            });
            container.innerHTML = logsHTML;
        }

        // ----------------------------------------------------
        // DYNAMIC VIEWPORT 2: STAFF CRUD
        // ----------------------------------------------------
        function renderStaffTable() {
            const tbody = document.getElementById('staff-table-body');
            tbody.innerHTML = '';

            if (cacheStaff.length === 0) {
                tbody.innerHTML = `<tr><td colSpan="6" class="text-center py-10 text-slate-400 italic">Không tìm thấy nhân sự nào.</td></tr>`;
                return;
            }

            cacheStaff.forEach(user => {
                // Role translation
                let roleBadge = "bg-blue-50 text-blue-700";
                let roleName = "Nhân viên";
                if(user.role === 'admin') {
                    roleBadge = "bg-rose-50 text-rose-700";
                    roleName = "Quản trị tối cao";
                } else if(user.role === 'manager') {
                    roleBadge = "bg-amber-50 text-amber-700";
                    roleName = "Quản lý";
                }

                // Render options buttons based on admin/permission
                let optionsBtn = '-';
                if (PHP_CURRENT_USER.role === 'admin') {
                    optionsBtn = `
                        <button onclick="editStaff('${user.id}')" class="p-1 text-slate-400 hover:text-indigo-600 cursor-pointer" title="Cập nhật hồ sơ"><i data-lucide="edit" class="w-3.5 h-3.5"></i></button>
                        <button onclick="deleteStaff('${user.id}')" class="p-1 text-slate-400 hover:text-rose-600 cursor-pointer" title="Xóa nhân viên"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                    `;
                }

                const positionNameHtml = user.position_name ? `<span class="font-bold text-slate-800 block">${user.position_name}</span>` : `<span class="text-slate-400 italic">Chưa giao vị</span>`;
                const permissionsList = user.custom_permissions || [];
                const permissionsCell = permissionsList.length > 0 ? `<div class="flex flex-wrap gap-1 justify-center">${permissionsList.map(p => `<span class="bg-slate-100 text-slate-600 text-[8px] font-mono px-1 py-0.2 rounded font-semibold">${p}</span>`).join('')}</div>` : `<span class="text-slate-300 italic text-[9px]">Không có</span>`;

                const tr = document.createElement('tr');
                tr.className = "hover:bg-slate-50/50 transition-colors";
                tr.innerHTML = `
                    <td class="py-3 px-4 flex items-center gap-2.5">
                        <img src="${user.avatar}" alt="Avatar" class="w-8 h-8 rounded-full object-cover shrink-0 border border-slate-100">
                        <div>
                            <span class="block font-bold text-slate-800 text-xs">${user.name}</span>
                            <span class="text-[9px] text-slate-400 block font-mono">ID: ${user.id}</span>
                        </div>
                    </td>
                    <td class="py-3 px-4 font-mono font-bold text-slate-700 text-xs">${user.phone}</td>
                    <td class="py-3 px-4">${positionNameHtml}</td>
                    <td class="py-3 px-4 text-center">
                        <span class="text-[9px] font-bold uppercase px-2 py-0.5 rounded-full ${roleBadge}">${roleName}</span>
                    </td>
                    <td class="py-3 px-4 text-center">${permissionsCell}</td>
                    <td class="py-3 px-4 text-right">
                        <div class="flex justify-end gap-1.5">${optionsBtn}</div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
            lucide.createIcons();
        }

        function filterStaffTable() {
            const kw = document.getElementById('staff-search').value.toLowerCase().trim();
            const rows = document.querySelectorAll('#staff-table-body tr');
            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                if (text.includes(kw)) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        }

        // Staff CRUD Submissions
        let staffAvatarBase64 = null;

        function openStaffModal() {
            document.getElementById('staff-modal').classList.remove('hidden');
            document.getElementById('staff-modal-title').innerText = "Thêm Hồ Sơ Nhân Viên";
            document.getElementById('staff-edit-id').value = '';
            document.getElementById('staff-form').reset();
            document.getElementById('pwd-required-star').style.display = 'inline';
            document.getElementById('staff-avatar-preview').src = 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=150';
            staffAvatarBase64 = null;
            renderStaffPermissionOptions();
            adjustPermissionCheckboxesByRole();
        }

        function closeStaffModal() {
            document.getElementById('staff-modal').classList.add('hidden');
        }

        function renderStaffPermissionOptions(selectedPermissions = []) {
            const container = document.getElementById('staff-permissions-grid');
            if (!container) return;

            container.innerHTML = '';

            if (!Array.isArray(cachePermissions) || cachePermissions.length === 0) {
                container.innerHTML = `<span class="text-slate-400 italic text-[11px]">Chưa có quyền nào trong danh mục.</span>`;
                return;
            }

            const selectedSet = new Set(Array.isArray(selectedPermissions) ? selectedPermissions : []);

            cachePermissions.forEach(perm => {
                const label = document.createElement('label');
                label.className = 'flex items-center gap-2 cursor-pointer select-none';
                label.innerHTML = `
                    <input type="checkbox" name="permissions" value="${perm.id}" class="rounded text-slate-900 focus:ring-0" ${selectedSet.has(perm.id) ? 'checked' : ''}>
                    <span>${perm.id}: ${perm.name}</span>
                `;
                container.appendChild(label);
            });
        }

        function adjustPermissionCheckboxesByRole() {
            const role = document.getElementById('staff-role').value;
            const checkboxes = document.querySelectorAll('input[name="permissions"]');
            
            checkboxes.forEach(cb => {
                cb.checked = false;
                if (role === 'admin') {
                    cb.checked = true;
                } else if (role === 'manager') {
                    if (['p1', 'p2', 'p3', 'p6'].includes(cb.value)) {
                        cb.checked = true;
                    }
                } else {
                    if (['p1'].includes(cb.value)) {
                        cb.checked = true;
                    }
                }
            });
        }

        function previewStaffAvatar(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');

                    const maxDim = 160;
                    let width = img.width;
                    let height = img.height;

                    if (width > height) {
                        if (width > maxDim) {
                            height = Math.round((height * maxDim) / width);
                            width = maxDim;
                        }
                    } else {
                        if (height > maxDim) {
                            width = Math.round((width * maxDim) / height);
                            height = maxDim;
                        }
                    }

                    canvas.width = width;
                    canvas.height = height;
                    ctx.drawImage(img, 0, 0, width, height);

                    const compressedBase64 = canvas.toDataURL('image/jpeg', 0.85);
                    document.getElementById('staff-avatar-preview').src = compressedBase64;
                    staffAvatarBase64 = compressedBase64;
                };
                img.src = e.target.result;
            }
            reader.readAsDataURL(file);
        }

        async function handleStaffSubmit(event) {
            event.preventDefault();
            const editId = document.getElementById('staff-edit-id').value;
            const name = document.getElementById('staff-name').value.trim();
            const phone = document.getElementById('staff-phone').value.trim();
            const identity_card = document.getElementById('staff-ic').value.trim();
            const dob = document.getElementById('staff-dob').value;
            const address = document.getElementById('staff-address').value.trim();
            const role = document.getElementById('staff-role').value;
            const position_id = document.getElementById('staff-position-id').value;
            const password = document.getElementById('staff-password').value;

            // Permissions array
            const custom_permissions = [];
            document.querySelectorAll('input[name="permissions"]:checked').forEach(cb => {
                custom_permissions.push(cb.value);
            });

            const bodyData = { name, phone, identity_card, dob, address, role, position_id, custom_permissions };
            if (password) {
                bodyData.password = password;
            }
            if (staffAvatarBase64) {
                bodyData.avatar = staffAvatarBase64;
            }

            try {
                let url = '<?= base_url('api/users') ?>';
                let method = 'POST';

                if (editId) {
                    url = '<?= base_url('api/users') ?>/' + editId;
                    method = 'PUT';
                }

                const response = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(bodyData)
                });

                const resData = await response.json();

                if (response.ok) {
                    showToast("success", "Lưu thông số", editId ? "Cập nhật hồ sơ nhân viên thành công!" : "Thêm mới hồ sơ nhân viên thành công.");
                    closeStaffModal();
                    syncData();
                } else {
                    showToast("alarm", "Ghi hồ sơ lỗi", resData.messages?.error || resData.message || "Vui lòng kiểm tra lại thông số");
                }
            } catch (err) {
                console.error(err);
                showToast("alarm", "Lỗi PHP", "Lỗi máy chủ cơ sở dữ liệu PHP cũ.");
            }
        }

        function editStaff(userId) {
            const user = cacheStaff.find(u => u.id === userId);
            if (!user) return;

            openStaffModal();
            document.getElementById('staff-modal-title').innerText = "Hiệu Chỉnh Hồ Sơ Nhân Viên: " + user.name;
            document.getElementById('staff-edit-id').value = user.id;

            document.getElementById('staff-name').value = user.name;
            document.getElementById('staff-phone').value = user.phone;
            document.getElementById('staff-ic').value = user.identity_card;
            document.getElementById('staff-dob').value = user.dob || '';
            document.getElementById('staff-address').value = user.address || '';
            document.getElementById('staff-role').value = user.role;
            document.getElementById('staff-position-id').value = user.position_id || '';
            document.getElementById('pwd-required-star').style.display = 'none';
            document.getElementById('staff-avatar-preview').src = user.avatar || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=150';
            staffAvatarBase64 = null;

            // Check permissions
            const permissionsList = user.custom_permissions || [];
            renderStaffPermissionOptions(permissionsList);
        }

        async function deleteStaff(userId) {
            if (!confirm("Bạn có chắc muốn xóa hồ sơ nhân viên này khỏi hệ thống?")) return;

            try {
                const response = await fetch('<?= base_url('api/users') ?>/' + userId, {
                    method: 'DELETE'
                });

                if (response.ok) {
                    showToast("success", "Đã xóa nhân sự", "Hồ sơ nhân viên đã được gỡ khỏi hệ thống.");
                    syncData();
                } else {
                    showToast("alarm", "Lỗi thi hành", "Không có quyền gỡ bỏ hoặc ràng buộc dữ liệu.");
                }
            } catch (err) {
                console.error(err);
            }
        }


        // ----------------------------------------------------
        // DYNAMIC VIEWPORT 4: TASKS MANAGEMENT & PLANNING
        // ----------------------------------------------------
        function renderTasksTimeline() {
            const container = document.getElementById('tasks-timeline-container');
            container.innerHTML = '';

            if (cacheTasks.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-12 bg-white border border-slate-200 rounded-2xl italic text-slate-400 text-xs">
                        Chưa xây dựng mục phân giao công việc chế tác nào.
                    </div>
                `;
                return;
            }

            cacheTasks.forEach(task => {
                // Calculate progress bar level
                const approvedLogs = cacheLogs.filter(l => l.task_id === task.id && l.status === "approved");
                const progressVal = approvedLogs.length === 0 ? 0 : Math.max(...approvedLogs.map(l => parseInt(l.progress_percent)));

                // Status badging
                let badgeStyle = "bg-amber-50 text-amber-700";
                let badgeText = "Bắt đầu làm";
                if (task.status === "completed") {
                    badgeStyle = "bg-emerald-50 text-emerald-700";
                    badgeText = "Hoàn thành";
                } else if (task.status === "in_progress") {
                    badgeStyle = "bg-indigo-50 text-indigo-700";
                    badgeText = "Đang thi công";
                }

                // Render workers images
                let assignedGridHTML = '';
                const assignees = task.assigned_users || [];
                if (assignees.length > 0) {
                    assignedGridHTML = `
                        <div class="flex items-center gap-1">
                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider mr-1">Tổ phụ trách:</span>
                            <div class="flex -space-x-2 overflow-hidden">
                                ${assignees.map(u => `
                                    <img src="${u.avatar}" alt="${u.name}" class="inline-block h-6 w-6 rounded-full object-cover ring-2 ring-white" title="${u.name} (${u.position_name || 'Nhân viên'})">
                                `).join('')}
                            </div>
                        </div>
                    `;
                } else {
                    assignedGridHTML = `<span class="text-[10px] text-amber-600 font-medium italic">Chưa giao cho nhân viên nào</span>`;
                }

                // Create Task visual block
                const div = document.createElement('div');
                div.className = "bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm space-y-3 hover:border-slate-300 transition-all";
                
                // Allow dynamic admin/manager controls
                let editTaskControls = '';
                if (PHP_CURRENT_USER.role !== 'staff') {
                    editTaskControls = `
                        <button onclick="editTask('${task.id}')" class="p-1 text-slate-400 hover:text-indigo-600 cursor-pointer" title="Sửa công việc"><i data-lucide="edit" class="w-3.5 h-3.5"></i></button>
                        <button onclick="deleteTask('${task.id}')" class="p-1 text-slate-400 hover:text-rose-600 cursor-pointer" title="Hủy bỏ việc này"><i data-lucide="trash-2" class="w-3.5 h-3.5 text-rose-500"></i></button>
                    `;
                }

                div.innerHTML = `
                    <div class="flex justify-between items-start gap-3">
                        <div>
                            <span class="bg-indigo-50 text-indigo-700 border border-indigo-100 text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md">${task.job_category_name || 'Công việc chung'}</span>
                            <h3 class="text-sm font-bold text-slate-900 mt-1.5">${task.title}</h3>
                            <p class="text-[10px] text-slate-400 font-mono mt-0.5">Ngày chạy: ${task.start_date} ~ Hạn định: ${task.end_date}</p>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase ${badgeStyle}">${badgeText}</span>
                            <div class="flex">${editTaskControls}</div>
                        </div>
                    </div>

                    ${task.description ? `<p class="text-[11px] text-slate-500 leading-relaxed bg-slate-50 p-2.5 rounded-xl border border-slate-150">${task.description}</p>` : ''}

                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 pt-2 border-t border-slate-100">
                        ${assignedGridHTML}
                        <div class="flex items-center gap-3 w-full sm:w-auto shrink-0 justify-end">
                            <span class="text-slate-500 text-[10px] font-bold">Kế hoạch:</span>
                            <div class="flex items-center gap-1.5">
                                <span class="font-bold text-slate-900 text-xs">${progressVal}%</span>
                                <div class="w-24 bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-100">
                                    <div class="bg-indigo-600 h-full rounded-full" style="width: ${progressVal}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Task-specific Vertical Progress Logs (Timeline) -->
                    ${(() => {
                        const taskLogs = cacheLogs.filter(l => l.task_id === task.id || l.taskId === task.id);
                        if (taskLogs.length === 0) {
                            return `
                                <div class="mt-3 pt-3 border-t border-slate-100">
                                    <p class="text-[10px] text-slate-400 italic">Chưa có nhật ký báo cáo tiến độ nào được đăng tải.</p>
                                </div>
                            `;
                        }
                        return `
                            <div class="mt-4 pt-4 border-t border-slate-100">
                                <h4 class="text-[10px] font-extrabold text-slate-500 uppercase tracking-widest mb-3 flex items-center gap-1.5 no-print">
                                    <i data-lucide="history" class="w-3.5 h-3.5 text-indigo-500"></i>
                                    Nhật lý báo cáo Timeline (${taskLogs.length} lần cập nhật)
                                </h4>
                                <div class="relative pl-3 border-l-2 border-slate-150 space-y-3.5 my-2">
                                    ${taskLogs.map(log => {
                                        let logStatusBadge = '';
                                        let dotColor = 'bg-slate-300';
                                        if (log.status === 'approved') {
                                            dotColor = log.auto_approved || log.autoApproved ? 'bg-amber-500' : 'bg-emerald-500';
                                            logStatusBadge = `
                                                <span class="text-[8px] px-1 py-0.5 bg-emerald-50 text-emerald-700 rounded border border-emerald-100 font-bold uppercase tracking-wider">
                                                    ${log.auto_approved || log.autoApproved ? 'Hệ thống duyệt' : 'Đã duyệt'}
                                                </span>`;
                                        } else if (log.status === 'pending') {
                                            dotColor = 'bg-amber-400 animate-pulse';
                                            logStatusBadge = '<span class="text-[8px] px-1 py-0.5 bg-amber-50 text-amber-700 rounded border border-amber-150 font-bold uppercase tracking-wider animate-pulse">Chờ duyệt</span>';
                                        } else {
                                            dotColor = 'bg-rose-500';
                                            logStatusBadge = '<span class="text-[8px] px-1 py-0.5 bg-rose-50 text-rose-700 rounded border border-rose-100 font-bold uppercase tracking-wider">Từ chối</span>';
                                        }

                                        return `
                                             <div class="relative group text-xs">
                                                 <!-- Timeline Dot -->
                                                 <div class="absolute -left-[19px] top-1 w-2.5 h-2.5 rounded-full ${dotColor} border-2 border-white shadow-xs transition-colors group-hover:scale-125"></div>
                                                 
                                                 <div class="bg-slate-50/70 hover:bg-slate-50 p-2.5 rounded-xl border border-slate-150/40 transition-all space-y-1.5">
                                                     <div class="flex flex-wrap items-center justify-between gap-1.5">
                                                         <div class="flex items-center gap-1.5 align-middle">
                                                             <img src="${log.user_avatar || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&q=80&w=250'}" 
                                                                  class="w-4.5 h-4.5 rounded-full object-cover border border-slate-200" title="${log.user_name}">
                                                             <span class="font-bold text-slate-800">${log.user_name}</span>
                                                             <span class="text-[9px] text-slate-400 font-mono">${log.date}</span>
                                                         </div>
                                                         <div class="flex items-center gap-1.5">
                                                             <span class="font-bold text-slate-900">Đạt: ${log.progress_percent || log.progressPercent || 0}%</span>
                                                             ${logStatusBadge}
                                                         </div>
                                                     </div>
                                                     <p class="text-slate-600 font-normal leading-relaxed text-[11px]">${log.notes}</p>
                                                     ${log.image ? `
                                                     <div class="relative max-w-[124px] rounded-lg overflow-hidden border border-slate-150 mt-1 cursor-zoom-in" onclick="zoomImage('${log.image}')">
                                                         <img src="${log.image}" class="w-full h-14 object-cover hover:scale-105 transition-all">
                                                     </div>` : ''}
                                                 </div>
                                             </div>
                                        `;
                                    }).join('')}
                                </div>
                            </div>
                        `;
                    })()}
                `;
                container.appendChild(div);
            });
            lucide.createIcons();
        }

        function populateWorkersSelections() {
            const container = document.getElementById('workers-selection-grid');
            if(!container) return;
            container.innerHTML = '';

            const subStaff = cacheStaff.filter(u => u.role === 'staff');
            if (subStaff.length === 0) {
                container.innerHTML = `<span class="text-slate-400 text-xs italic">Hãy thêm hồ sơ nhân viên trước.</span>`;
                return;
            }

            subStaff.forEach(user => {
                const label = document.createElement('label');
                label.className = "flex items-center gap-2 bg-white hover:bg-slate-50 border border-slate-150 p-2 rounded-xl cursor-pointer select-none transition-all";
                label.innerHTML = `
                    <input type="checkbox" name="assignees" value="${user.id}" class="rounded text-slate-900 focus:ring-0">
                    <img src="${user.avatar}" alt="Avatar" class="w-5 h-5 rounded-full object-cover">
                    <span>${user.name} <small class="text-slate-400">(${user.position_name || 'Nhân viên'})</small></span>
                `;
                container.appendChild(label);
            });
        }

        function openTaskModal() {
            document.getElementById('task-modal').classList.remove('hidden');
            document.getElementById('task-modal-title').innerText = "Tạo Việc Mới & Chỉ Định Nhân Viên";
            document.getElementById('task-edit-id').value = '';
            document.getElementById('task-form').reset();
            
            // Uncheck assignees
            document.querySelectorAll('input[name="assignees"]').forEach(cb => cb.checked = false);
        }

        function closeTaskModal() {
            document.getElementById('task-modal').classList.add('hidden');
        }

        async function handleTaskSubmit(event) {
            event.preventDefault();
            const editId = document.getElementById('task-edit-id').value;
            const title = document.getElementById('task-title').value.trim();
            const job_category_id = document.getElementById('task-job-category-id').value;
            const status = document.getElementById('task-status').value;
            const start_date = document.getElementById('task-start-date').value;
            const end_date = document.getElementById('task-end-date').value;
            const description = document.getElementById('task-description').value.trim();

            const assigned_users = [];
            document.querySelectorAll('input[name="assignees"]:checked').forEach(cb => {
                assigned_users.push(cb.value);
            });

            const bodyData = { title, job_category_id, status, start_date, end_date, description, assigned_users };

            try {
                let url = '<?= base_url('api/tasks') ?>';
                let method = 'POST';

                if (editId) {
                    url = '<?= base_url('api/tasks') ?>/' + editId;
                    method = 'PUT';
                }

                const response = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(bodyData)
                });

                if (response.ok) {
                    showToast("success", "Kế hoạch cập nhật", editId ? "Đã lưu chỉnh sửa công việc!" : "Phân công công việc thành công.");
                    closeTaskModal();
                    syncData();
                } else {
                    showToast("alarm", "Phân việc lỗi", "Vui lòng hoàn tất đúng ngày bắt đầu và kết thúc.");
                }
            } catch (err) {
                console.error(err);
            }
        }

        function editTask(taskId) {
            const task = cacheTasks.find(t => t.id === taskId);
            if (!task) return;

            openTaskModal();
            document.getElementById('task-modal-title').innerText = "Cập nhật công việc: " + task.title;
            document.getElementById('task-edit-id').value = task.id;

            document.getElementById('task-title').value = task.title;
            document.getElementById('task-job-category-id').value = task.job_category_id || '';
            document.getElementById('task-status').value = task.status;
            document.getElementById('task-start-date').value = task.start_date;
            document.getElementById('task-end-date').value = task.end_date;
            document.getElementById('task-description').value = task.description || '';

            // Check assignees checkboxes in modal
            const assignedIds = (task.assigned_users || []).map(u => u.id);
            document.querySelectorAll('input[name="assignees"]').forEach(cb => {
                cb.checked = assignedIds.includes(cb.value);
            });
        }

        async function deleteTask(taskId) {
            if(!confirm("Bạn có chắc muốn hủy bỏ đầu việc này? Việc này sẽ gỡ nhân sự đã phân công.")) return;

            try {
                const response = await fetch('<?= base_url('api/tasks') ?>/' + taskId, {
                    method: 'DELETE'
                });

                if (response.ok) {
                    showToast("success", "Đã gỡ dự án", "Tải trọng công việc được giải tỏa thành công.");
                    syncData();
                }
            } catch (err) {
                console.error(err);
            }
        }


        // ----------------------------------------------------
        // DYNAMIC VIEWPORT 5: REVIEWS PROGRESS FEED & DISPATCH
        // ----------------------------------------------------
        function renderProgressLogs() {
            const container = document.getElementById('logs-feed-container');
            container.innerHTML = '';

            const filterStatus = document.getElementById('filter-log-status').value;
            let displayLogs = [...cacheLogs];

            if (filterStatus) {
                displayLogs = displayLogs.filter(l => l.status === filterStatus);
            }

            if (displayLogs.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-12 bg-white border border-slate-200 rounded-2xl italic text-slate-400 text-xs">
                        Chưa có báo cáo nào phù hợp bộ lọc này.
                    </div>
                `;
                return;
            }

            displayLogs.forEach(log => {
                // Status mapping
                let statusBadge = "bg-amber-100 text-amber-800 border-amber-200";
                let statusName = "Chờ duyệt";
                if (log.status === "approved") {
                    statusBadge = log.auto_approved == 1 ? "bg-slate-100 text-slate-500 border-slate-200" : "bg-emerald-100 text-emerald-800 border-emerald-200";
                    statusName = log.auto_approved == 1 ? "Auto Approved (Hết ngày)" : "Chất lượng đạt chuẩn";
                } else if (log.status === "rejected") {
                    statusBadge = "bg-rose-100 text-rose-800 border-rose-200";
                    statusName = "Đã từ chối (Gọt lỗi)";
                }

                // Render Verification buttons if current user is admin/manager
                let reviewActions = '';
                if (PHP_CURRENT_USER.role !== 'staff' && log.status === 'pending') {
                    reviewActions = `
                        <div class="flex items-center gap-1.5 shrink-0 pt-2 border-t border-slate-100 no-print">
                            <button onclick="approveLog(${log.id}, 'approved')" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-[10px] font-bold cursor-pointer flex items-center gap-1 shadow-sm transition-all">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Chuẩn Y Đạt
                            </button>
                            <button onclick="approveLog(${log.id}, 'rejected')" class="px-3 py-1.5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl text-[10px] font-bold cursor-pointer flex items-center gap-1 shadow-sm transition-all">
                                <i data-lucide="x-circle" class="w-3.5 h-3.5"></i> Từ chối (Làm lại)
                            </button>
                        </div>
                    `;
                }

                const card = document.createElement('div');
                card.className = "bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm flex flex-col md:flex-row";
                card.innerHTML = `
                    <div class="md:w-56 overflow-hidden bg-slate-100 relative max-h-[160px] md:max-h-none flex items-center justify-center shrink-0 border-b md:border-b-0 md:border-r border-slate-150">
                        <img src="${log.image || 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=600&q=80'}" alt="Báo cáo" class="w-full h-full object-cover">
                        <div class="absolute bottom-2 left-2 bg-slate-900/80 text-white px-2 py-0.5 rounded text-[9px] font-bold">${log.progress_percent}% hoàn thiện</div>
                    </div>

                    <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                        <div class="space-y-1.5">
                            <div class="flex justify-between items-start gap-4">
                                <div class="flex items-center gap-2">
                                    <img src="${log.user_avatar}" alt="Avatar" class="w-7 h-7 rounded-full object-cover border border-slate-100 shrink-0">
                                    <div>
                                        <h4 class="font-bold text-slate-800 text-xs">${log.user_name}</h4>
                                        <span class="text-[9px] text-slate-400 block">${log.date}</span>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded-md border text-[9px] font-bold uppercase shrink-0 ${statusBadge}">${statusName}</span>
                            </div>

                            <p class="text-[11px] font-extrabold text-slate-500">Công việc: <span class="text-slate-800">${log.task_title}</span></p>
                            <p class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-2.5 rounded-xl border border-slate-150">${log.notes}</p>
                            
                            ${log.status === 'approved' && log.approver_name ? `
                                <div class="text-[9px] text-slate-400 flex items-center gap-1 pt-1 font-semibold">
                                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-indigo-600"></i>
                                    <span>Đã được kiểm định bởi: <strong class="text-slate-500">${log.approver_name}</strong></span>
                                </div>
                            ` : ''}
                        </div>

                        ${reviewActions}
                    </div>
                `;
                container.appendChild(card);
            });
            lucide.createIcons();
        }

        // Only staff can submit daily logs
        function populateWorkerTaskSelect() {
            const select = document.getElementById('log-task-id');
            if(!select) return;
            select.innerHTML = '';

            // Render option of tasks
            if (cacheTasks.length === 0) {
                select.innerHTML = `<option value="">Không có việc giao nào khả thi...</option>`;
                return;
            }

            cacheTasks.forEach(task => {
                select.innerHTML += `<option value="${task.id}">${task.title} [${task.job_category_name || 'Chung'}]</option>`;
            });
        }

        // --- PROFILE EDIT CONTROLLERS ---
        function openProfileModal() {
            document.getElementById('profile-modal').classList.remove('hidden');
            
            // Populate form fields from current user session data
            document.getElementById('profile-name').value = PHP_CURRENT_USER.name || '';
            document.getElementById('profile-dob').value = PHP_CURRENT_USER.dob || '';
            document.getElementById('profile-address').value = PHP_CURRENT_USER.address || '';
            document.getElementById('profile-ic').value = PHP_CURRENT_USER.identity_card || '';
            document.getElementById('profile-phone').value = PHP_CURRENT_USER.phone || '';
            document.getElementById('profile-password').value = ''; // clear password input field
            document.getElementById('profile-avatar-preview').src = PHP_CURRENT_USER.avatar || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=150';
            
            // Reset base64 variable
            profileAvatarBase64 = null;
        }

        function closeProfileModal() {
            document.getElementById('profile-modal').classList.add('hidden');
        }

        let profileAvatarBase64 = null;

        function previewProfileAvatar(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    // Create canvas for downscaling (avatar is small, max 160x160 is perfect)
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    
                    const maxDim = 160;
                    let width = img.width;
                    let height = img.height;
                    
                    if (width > height) {
                        if (width > maxDim) {
                            height = Math.round((height * maxDim) / width);
                            width = maxDim;
                        }
                    } else {
                        if (height > maxDim) {
                            width = Math.round((width * maxDim) / height);
                            height = maxDim;
                        }
                    }
                    
                    canvas.width = width;
                    canvas.height = height;
                    ctx.drawImage(img, 0, 0, width, height);
                    
                    const compressedBase64 = canvas.toDataURL('image/jpeg', 0.85);
                    document.getElementById('profile-avatar-preview').src = compressedBase64;
                    profileAvatarBase64 = compressedBase64;
                };
                img.src = e.target.result;
            }
            reader.readAsDataURL(file);
        }

        async function handleProfileSubmit(event) {
            event.preventDefault();
            const btnSubmit = document.getElementById('btn-profile-submit');
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span>Đang lưu...</span> <div class="animate-spin rounded-full h-3 w-3 border-2 border-white/30 border-t-white inline-block ml-1"></div>';

            const name = document.getElementById('profile-name').value.trim();
            const dob = document.getElementById('profile-dob').value;
            const address = document.getElementById('profile-address').value.trim();
            const identity_card = document.getElementById('profile-ic').value.trim();
            const phone = document.getElementById('profile-phone').value.trim();
            const password = document.getElementById('profile-password').value;

            const bodyData = {
                name,
                dob,
                address,
                identity_card,
                phone
            };

            if (password) {
                bodyData.password = password;
            }

            if (profileAvatarBase64) {
                bodyData.avatar = profileAvatarBase64;
            }

            try {
                const response = await fetch('<?= base_url('api/users') ?>/' + PHP_CURRENT_USER.id, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(bodyData)
                });

                const result = await response.json();

                if (response.ok && (response.status === 200 || response.status === 201)) {
                    showToast("success", "Cập nhật thành công", "Thông tin hồ sơ cá nhân đã được lưu trữ thành công!");
                    
                    const updatedUser = { 
                        ...PHP_CURRENT_USER, 
                        ...bodyData, 
                        ...(profileAvatarBase64 ? { avatar: profileAvatarBase64 } : {}) 
                    };
                    
                    try {
                        localStorage.setItem('moc_viet_user', JSON.stringify(updatedUser));
                    } catch (e) {
                        console.warn("Storage quota exceeded, skipping local storage cache.", e);
                    }
                    
                    closeProfileModal();
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showToast("alarm", "Thất bại", result.error || "Gặp lỗi trong quá trình lưu hồ sơ.");
                }
            } catch (err) {
                console.error(err);
                showToast("alarm", "Lỗi kết nối", "Không thể liên kết với máy chủ để lưu thông tin.");
            } finally {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = 'Lưu hồ sơ';
            }
        }

        function openSubmitLogModal() {
            document.getElementById('submit-log-modal').classList.remove('hidden');
            document.getElementById('submit-log-form').reset();
            document.getElementById('image-upload-preview-container').classList.add('hidden');
            document.getElementById('slider-val-lbl').innerText = '50%';
            logImageBase64 = null; // Clear old log image
        }
        function closeSubmitLogModal() {
            document.getElementById('submit-log-modal').classList.add('hidden');
        }

        let logImageBase64 = null;

        function previewSelectedImage(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    // Create canvas for downscaling progress log image (max 600px width/height is extremely detailed and small)
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    
                    const maxDim = 600;
                    let width = img.width;
                    let height = img.height;
                    
                    if (width > height) {
                        if (width > maxDim) {
                            height = Math.round((height * maxDim) / width);
                            width = maxDim;
                        }
                    } else {
                        if (height > maxDim) {
                            width = Math.round((width * maxDim) / height);
                            height = maxDim;
                        }
                    }
                    
                    canvas.width = width;
                    canvas.height = height;
                    ctx.drawImage(img, 0, 0, width, height);
                    
                    const compressedBase64 = canvas.toDataURL('image/jpeg', 0.80);
                    
                    const preview = document.getElementById('image-upload-preview');
                    const container = document.getElementById('image-upload-preview-container');
                    preview.src = compressedBase64;
                    container.classList.remove('hidden');
                    
                    logImageBase64 = compressedBase64;
                };
                img.src = e.target.result;
            }
            reader.readAsDataURL(file);
        }

        async function handleLogSubmit(event) {
            event.preventDefault();
            const btnSubmit = document.getElementById('btn-log-submit');
            btnSubmit.disabled = true;
            btnSubmit.innerText = "ĐANG GỬI BÁO CÁO...";

            const task_id = document.getElementById('log-task-id').value;
            const progress_percent = Number(document.getElementById('log-progress-slider').value);
            const date = document.getElementById('log-date').value;
            const notes = document.getElementById('log-notes').value.trim();

            const bodyData = {
                task_id,
                user_id: PHP_CURRENT_USER.id,
                progress_percent,
                date,
                notes
            };

            if (logImageBase64) {
                bodyData.image = logImageBase64;
            }

            try {
                // Send as JSON format so that standard application/json parsed perfectly by Node express server
                const response = await fetch('<?= base_url('api/logs') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(bodyData)
                });

                const r = await response.json();

                if (response.ok) {
                    showToast("success", "Nộp thành công", "Báo cáo công việc đã gửi lên máy chủ thành công.");
                    closeSubmitLogModal();
                    syncData();
                } else {
                    showToast("alarm", "Lỗi dữ liệu", r.error || "Gửi báo cáo thất bại");
                }
            } catch (err) {
                console.error(err);
                showToast("alarm", "Lỗi kết nối", "Không thể gửi dữ liệu báo cáo.");
            } finally {
                btnSubmit.disabled = false;
                btnSubmit.innerText = "Nộp báo cáo";
            }
        }

        async function approveLog(logId, status) {
            try {
                const response = await fetch('<?= base_url('api/logs/approve') ?>/' + logId, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        status: status,
                        approved_by: PHP_CURRENT_USER.id
                    })
                });

                if (response.ok) {
                    showToast("success", "Cập nhật chất lượng", status === 'approved' ? "Chuẩn y điểm năng lượng đạt tiêu chuẩn thẫm mỹ gỗ!" : "Mẫu báo cáo đã bị bác bỏ hoàn thiện");
                    syncData();
                } else {
                    showToast("alarm", "Lỗi duyệt", "Gặp trục trặc khi phê duyệt báo cáo.");
                }
            } catch (err) {
                console.error(err);
            }
        }

        // ----------------------------------------------------
        // DYNAMIC VIEWPORT 6: PERFORMANCE SUMMARY (REPORTS)
        // ----------------------------------------------------
        function renderPerformanceReports() {
            if (!cacheStats) return;

            // Productivity leaderboard
            const tbody = document.getElementById('performance-table-body');
            tbody.innerHTML = '';
            
            const productivity = cacheStats.employeeProductivity || [];

            if (productivity.length === 0) {
                tbody.innerHTML = `<tr><td colSpan="4" class="py-6 text-center text-slate-400 italic">Chưa phát hiện dữ liệu hoạt động tích lũy</td></tr>`;
            } else {
                productivity.forEach((emp, index) => {
                    let rating = "Nhân viên khá";
                    let ratingStyle = "bg-blue-50 text-blue-700";
                    if (emp.totalProgressPoints >= 105) {
                        rating = "Hiệu suất cao";
                        ratingStyle = "bg-emerald-50 text-emerald-700";
                    } else if (emp.totalProgressPoints < 40) {
                        rating = "Mức cơ bản";
                        ratingStyle = "bg-rose-50 text-rose-700";
                    }

                    const tr = document.createElement('tr');
                    tr.className = "hover:bg-slate-50/50 transition-colors";
                    tr.innerHTML = `
                        <td class="py-3 px-3 flex items-center gap-2">
                            <span class="font-extrabold text-[11px] text-slate-800 bg-slate-100 px-1.5 py-0.5 rounded mr-1">${index + 1}</span>
                            <img src="${emp.avatar}" alt="Avatar" class="w-7 h-7 rounded-full object-cover">
                            <div>
                                <span class="block">${emp.name}</span>
                                <span class="text-[8px] font-mono px-1 rounded ${ratingStyle}">${rating}</span>
                            </div>
                        </td>
                        <td class="py-3 px-3 text-center text-slate-700 font-bold">${emp.assignedTasksCount} dự án</td>
                        <td class="py-3 px-3 text-center text-slate-500">${emp.approvedLogsCount} lượt báo cáo</td>
                        <td class="py-3 px-3 text-right text-slate-950 font-mono font-bold">${emp.totalProgressPoints}% IDP</td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            // Tasks progress summary cards
            const tContainer = document.getElementById('report-tasks-list-container');
            tContainer.innerHTML = '';

            if (cacheTasks.length === 0) {
                tContainer.innerHTML = `<p class="text-xs text-slate-400 italic text-center py-6">Không có nhiệm vụ bàn giao gỗ.</p>`;
                return;
            }

            cacheTasks.forEach(task => {
                const approvedLogs = cacheLogs.filter(l => l.task_id === task.id && l.status === "approved");
                const progressLevel = approvedLogs.length === 0 ? 0 : Math.max(...approvedLogs.map(l => parseInt(l.progress_percent)));

                const card = document.createElement('div');
                card.className = "space-y-1.5 p-3.5 bg-slate-50 border border-slate-150 rounded-xl hover:bg-slate-100/50 transition-all";
                card.innerHTML = `
                    <div class="flex justify-between items-start gap-2">
                        <div>
                            <span class="text-slate-800 font-bold text-xs">${task.title}</span>
                            <span class="block text-[9px] text-slate-400 font-mono">${task.start_date} ~ ${task.end_date}</span>
                        </div>
                        <span class="text-[9px] bg-slate-200 text-slate-800 font-bold uppercase rounded px-1">${task.status === 'completed' ? 'Hoàn thiện' : 'Chạy ráp'}</span>
                    </div>
                    <div class="space-y-1 pt-1">
                        <div class="flex justify-between text-[9px]">
                            <span class="text-slate-500 font-semibold">Tỷ lệ tiến trình:</span>
                            <span class="font-mono text-slate-900 font-bold">${progressLevel}%</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-1 overflow-hidden">
                            <div class="h-full bg-indigo-600 rounded-full" style="width: ${progressLevel}%"></div>
                        </div>
                    </div>
                `;
                tContainer.appendChild(card);
            });
        }

        // ----------------------------------------------------
        // DYNAMIC ALARM TOAST MESSAGE BIN
        // ----------------------------------------------------
        function showToast(type, title, message) {
            const btn = document.getElementById('toast-bin');
            const icon = document.getElementById('toast-icon');
            const titEl = document.getElementById('toast-title');
            const descEl = document.getElementById('toast-desc');

            btn.classList.remove('hidden');
            titEl.innerText = title;
            descEl.innerText = message;

            if (type === 'success') {
                btn.className = "mb-6 p-4 rounded-xl border flex items-start gap-3 text-xs shadow-md bg-emerald-50 border-emerald-100 text-emerald-900";
                icon.setAttribute('data-lucide', 'check-circle-2');
            } else {
                btn.className = "mb-6 p-4 rounded-xl border flex items-start gap-3 text-xs shadow-md bg-rose-50 border-rose-100 text-rose-900";
                icon.setAttribute('data-lucide', 'alert-circle');
            }
            lucide.createIcons();
            
            // Auto close after 3 seconds
            setTimeout(() => {
                closeToast();
            }, 3500);
        }

        function closeToast() {
            document.getElementById('toast-bin').classList.add('hidden');
        }

        // ----------------------------------------------------
        // SYSTEM MASTER CATEGORIES CRUD & MANAGEMENT
        // ----------------------------------------------------
        function renderMasterCategories() {
            // Render Positions
            const posContainer = document.getElementById('list-positions');
            if (posContainer) {
                posContainer.innerHTML = '';
                if (cachePositions.length === 0) {
                    posContainer.innerHTML = `<div class="text-slate-400 text-xs italic py-4 text-center">Trống.</div>`;
                } else {
                    cachePositions.forEach(pos => {
                        const isEditable = PHP_CURRENT_USER.role === 'admin' || (PHP_CURRENT_USER.custom_permissions && PHP_CURRENT_USER.custom_permissions.includes('p5'));
                        const actionButtons = isEditable ? `
                            <div class="flex gap-1 items-center shrink-0">
                                <button onclick="openCategoryModal('positions', '${pos.id}')" class="p-1.5 bg-indigo-50 hover:bg-indigo-100 rounded text-indigo-600 hover:text-indigo-800 transition-colors cursor-pointer" title="Sửa"><i data-lucide="edit" class="w-3.5 h-3.5"></i></button>
                                <button onclick="deleteCategory('positions', '${pos.id}')" class="p-1.5 bg-rose-50 hover:bg-rose-100 rounded text-rose-600 hover:text-rose-800 transition-colors cursor-pointer" title="Xóa"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                            </div>
                        ` : '';

                        const item = document.createElement('div');
                        item.className = "p-3 bg-slate-50 hover:bg-slate-100/80 border border-slate-150 rounded-xl space-y-1 relative group transition-colors flex justify-between items-center gap-3";
                        item.innerHTML = `
                            <div class="space-y-0.5 flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-bold text-slate-800 text-xs truncate">${pos.name}</h4>
                                    <span class="text-[9px] font-mono text-slate-400 bg-slate-200/50 px-1 rounded shrink-0">${pos.id}</span>
                                </div>
                                <p class="text-[11px] text-slate-500 leading-relaxed truncate-2-lines">${pos.description || 'Không có mô tả.'}</p>
                            </div>
                            ${actionButtons}
                        `;
                        posContainer.appendChild(item);
                    });
                }
            }

            // Render Job Categories
            const jobsContainer = document.getElementById('list-job-categories');
            if (jobsContainer) {
                jobsContainer.innerHTML = '';
                if (cacheJobCategories.length === 0) {
                    jobsContainer.innerHTML = `<div class="text-slate-400 text-xs italic py-4 text-center">Trống.</div>`;
                } else {
                    cacheJobCategories.forEach(job => {
                        const isEditable = PHP_CURRENT_USER.role === 'admin' || (PHP_CURRENT_USER.custom_permissions && PHP_CURRENT_USER.custom_permissions.includes('p5'));
                        const actionButtons = isEditable ? `
                            <div class="flex gap-1 items-center shrink-0">
                                <button onclick="openCategoryModal('jobs', '${job.id}')" class="p-1.5 bg-blue-50 hover:bg-blue-100 rounded text-blue-600 hover:text-blue-800 transition-colors cursor-pointer" title="Sửa"><i data-lucide="edit" class="w-3.5 h-3.5"></i></button>
                                <button onclick="deleteCategory('jobs', '${job.id}')" class="p-1.5 bg-rose-50 hover:bg-rose-100 rounded text-rose-600 hover:text-rose-800 transition-colors cursor-pointer" title="Xóa"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                            </div>
                        ` : '';

                        const item = document.createElement('div');
                        item.className = "p-3 bg-slate-50 hover:bg-slate-100/80 border border-slate-150 rounded-xl space-y-1 relative group transition-colors flex justify-between items-center gap-3";
                        item.innerHTML = `
                            <div class="space-y-0.5 flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-bold text-slate-800 text-xs truncate">${job.name}</h4>
                                    <span class="text-[9px] font-mono text-slate-400 bg-slate-200/50 px-1 rounded shrink-0">${job.id}</span>
                                </div>
                                <p class="text-[11px] text-slate-500 leading-relaxed truncate-2-lines">${job.description || 'Không có mô tả.'}</p>
                            </div>
                            ${actionButtons}
                        `;
                        jobsContainer.appendChild(item);
                    });
                }
            }

            // Render Permissions
            const permContainer = document.getElementById('list-permissions');
            if (permContainer) {
                permContainer.innerHTML = '';
                if (cachePermissions.length === 0) {
                    permContainer.innerHTML = `<div class="text-slate-400 text-xs italic py-4 text-center">Trống.</div>`;
                } else {
                    cachePermissions.forEach(perm => {
                        const isEditable = PHP_CURRENT_USER.role === 'admin' || (PHP_CURRENT_USER.custom_permissions && PHP_CURRENT_USER.custom_permissions.includes('p5'));
                        const actionButtons = isEditable ? `
                            <div class="flex gap-1 items-center shrink-0">
                                <button onclick="openCategoryModal('permissions', '${perm.id}')" class="p-1.5 bg-emerald-50 hover:bg-emerald-100 rounded text-emerald-600 hover:text-emerald-800 transition-colors cursor-pointer" title="Sửa"><i data-lucide="edit" class="w-3.5 h-3.5"></i></button>
                                <button onclick="deleteCategory('permissions', '${perm.id}')" class="p-1.5 bg-rose-50 hover:bg-rose-100 rounded text-rose-600 hover:text-rose-800 transition-colors cursor-pointer" title="Xóa"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                            </div>
                        ` : '';

                        const item = document.createElement('div');
                        item.className = "p-3 bg-slate-50 hover:bg-slate-100/80 border border-slate-150 rounded-xl space-y-1 relative group transition-colors flex justify-between items-center gap-3";
                        item.innerHTML = `
                            <div class="space-y-0.5 flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-bold text-slate-800 text-xs truncate">${perm.name}</h4>
                                    <span class="text-[9px] font-mono text-slate-400 bg-slate-200/50 px-1 rounded shrink-0">${perm.id}</span>
                                </div>
                                <p class="text-[11px] text-slate-500 leading-relaxed truncate-2-lines">${perm.description || 'Không có mô tả.'}</p>
                            </div>
                            ${actionButtons}
                        `;
                        permContainer.appendChild(item);
                    });
                }
            }
            lucide.createIcons();
        }

        function openCategoryModal(type, itemId = '') {
            document.getElementById('category-form').reset();
            document.getElementById('category-modal').classList.remove('hidden');
            document.getElementById('category-type').value = type;
            document.getElementById('category-edit-id').value = itemId;
            
            const idFieldWrapper = document.getElementById('category-id-field-wrapper');
            idFieldWrapper.classList.add('hidden');
            document.getElementById('category-item-id').required = false;

            let titlePrefix = "Khai báo";
            if (itemId) {
                titlePrefix = "Hiệu chỉnh";
            }

            let typeName = "Danh mục";
            if (type === 'positions') {
                typeName = "Chức Danh Vị Trí";
            } else if (type === 'jobs') {
                typeName = "Loại Hình Công Việc";
            } else if (type === 'permissions') {
                typeName = "Quyền Hệ Thống";
                idFieldWrapper.classList.remove('hidden');
                document.getElementById('category-item-id').required = true;
            }

            document.getElementById('category-modal-title').innerText = `${titlePrefix} ${typeName}`;

            if (itemId) {
                let item = null;
                if (type === 'positions') item = cachePositions.find(x => x.id === itemId);
                else if (type === 'jobs') item = cacheJobCategories.find(x => x.id === itemId);
                else if (type === 'permissions') item = cachePermissions.find(x => x.id === itemId);

                if (item) {
                    document.getElementById('category-name').value = item.name || '';
                    document.getElementById('category-description').value = item.description || '';
                    document.getElementById('category-item-id').value = item.id || '';
                }
            } else {
                document.getElementById('category-item-id').value = '';
            }
        }

        function closeCategoryModal() {
            document.getElementById('category-modal').classList.add('hidden');
        }

        async function handleCategorySubmit(event) {
            event.preventDefault();
            const type = document.getElementById('category-type').value;
            const editId = document.getElementById('category-edit-id').value;
            const name = document.getElementById('category-name').value.trim();
            const description = document.getElementById('category-description').value.trim();
            const itemIdInput = document.getElementById('category-item-id').value.trim();

            const payload = { name, description };
            if (type === 'permissions' && !editId) {
                payload.id = itemIdInput;
            }

            let url = `<?= base_url('api/categories') ?>/` + (type === 'jobs' ? 'jobs' : type);
            let method = "POST";

            if (editId) {
                method = "PUT";
                url += '/' + editId;
            }

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                if (res.ok) {
                    showToast('success', 'Thành công', 'Lưu thông tin danh mục thành công!');
                    closeCategoryModal();
                    syncData();
                } else {
                    const err = await res.json();
                    showToast('danger', 'Lỗi phân phối', err.error || 'Thao tác danh mục thất bại.');
                }
            } catch (ex) {
                console.error(ex);
                showToast('danger', 'Lỗi đồng bộ', 'Không thể gửi dữ liệu danh mục lên hệ thống.');
            }
        }

        async function deleteCategory(type, id) {
            if (!confirm('Bạn có chắc chắn muốn xóa danh mục này? Thao tác có thể ảnh hưởng đến dữ liệu nhân viên/công việc liên quan.')) return;

            let url = `<?= base_url('api/categories') ?>/` + (type === 'jobs' ? 'jobs' : type) + '/' + id;

            try {
                const res = await fetch(url, { method: 'DELETE' });
                if (res.ok) {
                    showToast('success', 'Thành công', 'Đã xóa danh mục ra khỏi danh sách hệ thống!');
                    syncData();
                } else {
                    const err = await res.json();
                    showToast('danger', 'Lỗi thao tác', err.error || 'Không thể xóa mục danh mục này.');
                }
            } catch (ex) {
                console.error(ex);
                showToast('danger', 'Lỗi kết nối', 'Mất đường truyền mạng với server.');
            }
        }
    </script>
</body>
</html>
