<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ Thống Điều Hành Mộc Việt - Quản Trị Toàn Diện</title>
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
                        <span class="font-extrabold tracking-tight text-sm uppercase brand-font">Mộc Việt</span>
                        <span class="text-[9px] text-slate-400 block font-mono leading-none">QUẢN TRỊ TOÀN DIỆN</span>
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
                <div class="flex items-center gap-2.5 bg-slate-800 px-3 py-1.5 rounded-xl border border-slate-700">
                    <img 
                        src="<?= $currentUser['avatar'] ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&q=80&w=150' ?>" 
                        alt="Avatar" 
                        class="w-6.5 h-6.5 rounded-full object-cover border border-slate-600">
                    <div class="hidden sm:block text-left">
                        <p class="text-xs font-bold leading-tight"><?= esc($currentUser['name']) ?></p>
                        <span class="text-[9px] uppercase tracking-wider text-slate-400 font-semibold font-mono">
                            <?php 
                                if($currentUser['role'] === 'admin') echo 'Quản trị viên';
                                elseif($currentUser['role'] === 'manager') echo 'Quản lý xưởng';
                                else echo 'Thợ thi công';
                            ?>
                        </span>
                    </div>
                </div>

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
                    <span><?= $currentUser['role'] === 'staff' ? 'Nhật Ký Tiến Độ Ngày' : 'Duyệt Nhật Trình Mộc' ?></span>
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
                    Mọi nhật trình thi công ghi nhận ngày cũ thuộc mộc thợ sẽ tự động chuyển trạng thái "Approved" để bảo toàn điểm năng suất sản lượng.
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
                            <span>Báo Cáo Nhật Trình</span>
                        </button>
                        <button onclick="switchTab('reports')" class="w-full text-xs font-bold p-3 rounded-lg flex items-center gap-2.5 hover:bg-slate-50 text-slate-600">
                            <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                            <span>Thống Kê Báo Cáo</span>
                        </button>
                    </nav>
                </div>
                <div class="text-[9px] text-slate-400">
                    © 2026 Mộc Việt Co., Ltd.
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
                    <p class="text-xs text-slate-500">Giám sát tổng thể tiến trình đục đẽo đóng mộc, thi công và xếp hạng năng lực nhân sự hằng ngày.</p>
                </div>

                <!-- Numerical KPI Grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                    <div class="p-5 bg-white border border-slate-200/80 rounded-2xl shadow-sm flex items-center gap-3">
                        <div class="p-3 bg-indigo-50 rounded-xl text-indigo-600"><i data-lucide="users" class="w-5 h-5"></i></div>
                        <div>
                            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Thợ sản xuất</span>
                            <span class="text-2xl font-extrabold text-slate-900 leading-none" id="kpi-staff">...</span>
                        </div>
                    </div>
                    <div class="p-5 bg-white border border-slate-200/80 rounded-2xl shadow-sm flex items-center gap-3">
                        <div class="p-3 bg-amber-50 rounded-xl text-amber-600"><i data-lucide="briefcase" class="w-5 h-5"></i></div>
                        <div>
                            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1">Dự án mộc gỗ</span>
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
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest">Tiến trình cập nhật nhật nhật trình mới nhất</h3>
                            <button onclick="switchTab('logs')" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-0.5 cursor-pointer">
                                <span>Xem toàn bộ nhật trình</span> <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
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
                                <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-300 block">Thợ mộc tiêu biểu trong tháng</span>
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
                                    <span class="text-[9px] text-slate-400">Chọn nhiều thợ</span>
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
                        <p class="text-xs text-slate-500">Tổ chức danh sách thợ mộc, phân công chức trách chuyên môn và cấp quyền tùy biến.</p>
                    </div>
                    <button onclick="openStaffModal()" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 cursor-pointer shadow-sm transition-all">
                        <i data-lucide="user-plus" class="w-4 h-4"></i> Thêm Nhân Sự Mới
                    </button>
                </div>

                <!-- Search box -->
                <div class="flex bg-white items-center gap-2 px-3 py-2 border border-slate-200 rounded-xl max-w-sm shadow-sm">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                    <input type="text" id="staff-search" oninput="filterStaffTable()" placeholder="Nhập tên thợ hoặc điện thoại..." class="text-xs w-full bg-transparent focus:outline-none">
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
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                        <i data-lucide="tags" class="text-indigo-600 w-6 h-6"></i> Tổ Chức Danh Mục Kỹ Thuật
                    </h1>
                    <p class="text-xs text-slate-500">Khai báo danh mục công đoạn đóng đồ gỗ (ví dụ: Chà nhám, Đóng tủ bếp, Sơn PU) hỗ trợ chia ca phân công.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Categories List Panel -->
                    <div class="md:col-span-2 bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest border-b border-slate-100 pb-2">Danh sách công đoạn khả dụng</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" id="categories-grid">
                            <!-- Preloaded categories via standard dynamic PHP block -->
                            <?php foreach($categories as $cat): ?>
                            <div class="p-4 bg-slate-50 border border-slate-150 rounded-xl space-y-2 relative group hover:bg-slate-100/50 transition-colors">
                                <div class="flex justify-between items-start">
                                    <h4 class="font-bold text-slate-800 text-xs"><?= esc($cat['name']) ?></h4>
                                    <span class="text-[9px] font-mono text-slate-400">ID: <?= esc($cat['id']) ?></span>
                                </div>
                                <p class="text-[11px] text-slate-500 leading-relaxed"><?= esc($cat['description'] ?: 'Không có chú thích mô tả.') ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Side guidelines info -->
                    <div class="p-5 bg-white border border-slate-200 rounded-2xl shadow-sm h-fit space-y-3">
                        <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-1">
                            <i data-lucide="info" class="w-4 h-4 text-indigo-600"></i> Ý nghĩa danh mục
                        </h4>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Mỗi công đoạn đóng đồ mộc giúp thống kê chính xác lượng thợ thực thi và tiến độ chung một cách tự động khóa % khi giao mộc tháp ráp.
                        </p>
                    </div>
                </div>
            </section>

            <!-- TAB CONTAINER 4: TASK PLANNING & TIMELINE -->
            <section id="viewport-tasks" class="viewport-tab hidden space-y-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                            <i data-lucide="clipboard-list" class="text-indigo-600 w-6 h-6"></i> Phân Công Dự Án & Timeline Lắp Đặt
                        </h1>
                        <p class="text-xs text-slate-500">Khởi tạo kế hoạch sản xuất mộc, chỉ định cấu trúc hạn ngày và **giao việc đồng thời cho một nhóm nhiều thợ**.</p>
                    </div>
                    <button onclick="openTaskModal()" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800  text-white text-xs font-bold rounded-xl flex items-center gap-1.5 cursor-pointer shadow-sm transition-all">
                        <i data-lucide="plus" class="w-4 h-4"></i> Tạo Việc & Phân Thợ
                    </button>
                </div>

                <!-- Tasks Listing & Gantt overview -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Detailed task list left side -->
                    <div class="lg:col-span-2 space-y-4" id="tasks-timeline-container">
                        <!-- Loaded via JS dynamically -->
                        <div class="text-center py-10 bg-white border border-slate-200 rounded-2xl italic text-slate-400 text-xs">
                            Đang kết nối danh sách việc thi công mộc...
                        </div>
                    </div>

                    <!-- Right sidebar task statistics -->
                    <div class="space-y-6">
                        <div class="p-5 bg-white border border-slate-200 rounded-2xl shadow-sm space-y-4">
                            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Lưu ý khi gán việc</h3>
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Bạn có thể gán nhiều thợ phụ và thợ cả vào cùng một sản phẩm đồ gỗ để họ phối hợp gọt đẽo và nộp báo cáo chung vào một nhật trình hoàn tât.
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
                            <span><?= $currentUser['role'] === 'staff' ? 'Báo Cáo Nhật Trình Ngày' : 'Duyệt Sản Lượng Tiến Độ Thợ' ?></span>
                        </h1>
                        <p class="text-xs text-slate-500">
                            <?= $currentUser['role'] === 'staff' ? 'Nhập ghi chú hàng ngày, báo cáo % mộc gỗ đã thi công kèm ảnh thực tế dâng lên quản đốc.' : 'Khảo sát xem xét chất lượng đẽo phôi mộc gỗ thực tế của từng thợ để duyệt điểm năng lực.' ?>
                        </p>
                    </div>
                    <?php if($currentUser['role'] === 'staff'): ?>
                    <button onclick="openSubmitLogModal()" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl flex items-center gap-1.5 cursor-pointer shadow-sm transition-all">
                        <i data-lucide="plus" class="w-4 h-4"></i> Viết Nhật Trình Hôm Nay
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
                            <i data-lucide="bar-chart-3" class="text-indigo-600 w-6 h-6"></i> Thống Kê Hiệu Suất Sản Lượng Gần Thực Tế
                        </h1>
                        <p class="text-xs text-slate-500">Tính toán điểm tích lũy lũy tiến công việc đã qua chất chuẩn của thợ mộc.</p>
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
                                <i data-lucide="award" class="w-4 h-4 text-amber-500"></i> BẢNG XẾP HẠNG NĂNG SUẤT ĐÓNG GỖ THÔNG QUA TÍCH ĐIỂM
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-400 uppercase tracking-wider text-[9px] font-bold border-b border-slate-100">
                                        <th class="py-2 px-3">Hạng / Thợ mộc</th>
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
                    <i data-lucide="user-plus" class="text-indigo-600 w-5 h-5"></i> <span id="staff-modal-title">Thêm Hồ Sơ Thợ Mộc</span>
                </h3>
                <button onclick="closeStaffModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded cursor-pointer"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <form id="staff-form" class="space-y-4" onsubmit="handleStaffSubmit(event)">
                <input type="hidden" id="staff-edit-id" value="">
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Họ tên thợ <span class="text-rose-500">*</span></label>
                        <input type="text" id="staff-name" required placeholder="Ví dụ: Phạm Văn Mộc" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none">
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
                            <option value="staff">Thợ sản xuất phụ trợ (Staff)</option>
                            <option value="manager">Quản lý sản xuất xưởng (Manager)</option>
                            <option value="admin">Quản trị viên tối cao (Admin)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Chức vụ phụ trách mộc</label>
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
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-slate-700">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="permissions" value="p1" class="rounded text-slate-900 focus:ring-0">
                            <span>p1: Xem tất cả công việc</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="permissions" value="p2" class="rounded text-slate-900 focus:ring-0">
                            <span>p2: Quản lý công việc</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="permissions" value="p3" class="rounded text-slate-900 focus:ring-0">
                            <span>p3: Duyệt tiến hàng ngày</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="permissions" value="p4" class="rounded text-slate-900 focus:ring-0">
                            <span>p4: Quản lý nhân sự</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="permissions" value="p5" class="rounded text-slate-900 focus:ring-0">
                            <span>p5: Quản lý danh mục</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="permissions" value="p6" class="rounded text-slate-900 focus:ring-0">
                            <span>p6: Xem báo cáo thống kê</span>
                        </label>
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
                    <i data-lucide="clipboard-list" class="text-indigo-600 w-5 h-5"></i> <span id="task-modal-title">Tạo Việc Mới & Chỉ Định Thợ</span>
                </h3>
                <button onclick="closeTaskModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded cursor-pointer"><i data-lucide="x" class="w-4 h-4"></i></button>
            </div>

            <form id="task-form" class="space-y-4" onsubmit="handleTaskSubmit(event)">
                <input type="hidden" id="task-edit-id" value="">

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Tên công việc / Dự án gỗ mỹ nghệ <span class="text-rose-500">*</span></label>
                    <input type="text" id="task-title" required placeholder="Góc mộc tủ bếp, làm bóng bình vân gỗ..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none">
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
                            <option value="pending">Chuẩn bị phôi (Pending)</option>
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
                        <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Hạn định mộc gỗ <span class="text-rose-500">*</span></label>
                        <input type="date" id="task-end-date" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Mô tả quy cách, thông số chi tiết của sản phẩm</label>
                    <textarea id="task-description" rows="3" placeholder="Sản phẩm mộc tủ gỗ sồi, mép gỗ mài bóng PU..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none"></textarea>
                </div>

                <div class="bg-slate-50 p-4 border border-slate-150 rounded-xl space-y-2">
                    <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider">Chỉ định nhóm thợ thi công đồng thời (Chọn nhiều thợ)</span>
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
                    <i data-lucide="plus" class="text-indigo-600 w-5 h-5"></i> <span>Ghi Báo Cáo Nhật Trình Mộc</span>
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
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Đăng ảnh hiện trường phôi mộc gỗ thực tế <span class="text-rose-500">*</span></label>
                    <input type="file" id="log-image" accept="image/png, image/jpeg, image/jpg" class="hidden" onchange="previewSelectedImage(event)">
                    <div onclick="document.getElementById('log-image').click()" class="border-2 border-dashed border-slate-200 hover:border-slate-400 bg-slate-50 rounded-2xl p-6 text-center cursor-pointer transition-all space-y-2">
                        <i data-lucide="image" class="w-8 h-8 text-slate-400 mx-auto"></i>
                        <span class="block text-xs font-bold text-slate-500">Ấn để chọn ảnh hoặc kéo thả vào đây</span>
                        <span class="text-[9px] text-slate-400">Yêu cầu ảnh JPG/PNG mặt cắt đẽo phoi dăm mộc mạc</span>
                        <div id="image-upload-preview-container" class="hidden pt-2">
                            <img id="image-upload-preview" src="#" alt="Xem trước" class="max-h-[140px] rounded-xl mx-auto border border-slate-200 shadow-sm">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Chú thích chi tiết công việc mộc hôm nay <span class="text-rose-500">*</span></label>
                    <textarea id="log-notes" rows="3" required placeholder="Ghi chú rõ thớ thớ thạch gỗ đắp mịn sơn thế nào..." class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs focus:ring-1 focus:ring-slate-900 focus:outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-2.5 pt-2 border-t border-slate-100">
                    <button type="button" onclick="closeSubmitLogModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 rounded-xl text-xs font-bold text-slate-700 cursor-pointer">Bỏ qua</button>
                    <button type="submit" id="btn-log-submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-sm cursor-pointer">Nộp nhật trình</button>
                </div>
            </form>
        </div>
    </div>


    <footer class="bg-slate-900 text-slate-500 py-6 border-t border-slate-800 text-center text-xs mt-auto no-print">
        <p class="flex items-center justify-center gap-1">
            Thiết kế bởi hệ thống <span class="font-semibold text-slate-300">Quản Lý Nhân Sự & Công Việc Mộc Việt Co., Ltd</span> 
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
                const [staffRes, tasksRes, logsRes, statsRes] = await Promise.all([
                    fetch('<?= base_url('api/users') ?>'),
                    fetch('<?= base_url('api/tasks') ?>'),
                    fetch('<?= base_url('api/logs') ?>'),
                    fetch('<?= base_url('api/dashboard/stats') ?>')
                ]);

                if (staffRes.ok) cacheStaff = await staffRes.json();
                if (tasksRes.ok) cacheTasks = await tasksRes.json();
                if (logsRes.ok) cacheLogs = await logsRes.json();
                if (statsRes.ok) cacheStats = await statsRes.json();

                // Build frontend layouts based on results
                renderDashboardMetrics();
                renderStaffTable();
                renderTasksTimeline();
                renderProgressLogs();
                renderPerformanceReports();
                
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
            document.getElementById('kpi-staff').innerText = s.totalStaff + ' thợ';
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
                            <span class="block text-[10px] text-amber-300 font-bold uppercase tracking-wider">Tích lũy ${top.totalProgressPoints}% điểm mộc</span>
                        </div>
                    `;
                }
            } else {
                bestPerformerWidget.innerHTML = `<span class="text-xs text-slate-400">Chưa ghi nhận hoạt động</span>`;
            }

            // Recent logs inline feed
            const container = document.getElementById('dashboard-recent-logs-list');
            if (cacheLogs.length === 0) {
                container.innerHTML = `<p class="text-xs text-slate-400 italic text-center py-6">Chưa có nhật trình nào được trình dâng hôm nay.</p>`;
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
                tbody.innerHTML = `<tr><td colSpan="6" class="text-center py-10 text-slate-400 italic">Không tìm thấy thợ mộc nào.</td></tr>`;
                return;
            }

            cacheStaff.forEach(user => {
                // Role translation
                let roleBadge = "bg-blue-50 text-blue-700";
                let roleName = "Thợ đóng mộc";
                if(user.role === 'admin') {
                    roleBadge = "bg-rose-50 text-rose-700";
                    roleName = "Quản trị tối cao";
                } else if(user.role === 'manager') {
                    roleBadge = "bg-amber-50 text-amber-700";
                    roleName = "Quản đốc xưởng";
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
        function openStaffModal() {
            document.getElementById('staff-modal').classList.remove('hidden');
            document.getElementById('staff-modal-title').innerText = "Thêm Hồ Sơ Thợ Mộc";
            document.getElementById('staff-edit-id').value = '';
            document.getElementById('staff-form').reset();
            document.getElementById('pwd-required-star').style.display = 'inline';
            adjustPermissionCheckboxesByRole();
        }

        function closeStaffModal() {
            document.getElementById('staff-modal').classList.add('hidden');
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
                    showToast("success", "Lưu thông số", editId ? "Cập nhật hồ sơ thợ mộc thành công!" : "Ghi nhận tuyển mới thợ mộc thành sự thực.");
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
            document.getElementById('staff-modal-title').innerText = "Hiệu Chỉnh Hồ Sơ Thợ: " + user.name;
            document.getElementById('staff-edit-id').value = user.id;

            document.getElementById('staff-name').value = user.name;
            document.getElementById('staff-phone').value = user.phone;
            document.getElementById('staff-ic').value = user.identity_card;
            document.getElementById('staff-dob').value = user.dob || '';
            document.getElementById('staff-address').value = user.address || '';
            document.getElementById('staff-role').value = user.role;
            document.getElementById('staff-position-id').value = user.position_id || '';
            document.getElementById('pwd-required-star').style.display = 'none';

            // Check permissions
            const permissionsList = user.custom_permissions || [];
            document.querySelectorAll('input[name="permissions"]').forEach(cb => {
                cb.checked = permissionsList.includes(cb.value);
            });
        }

        async function deleteStaff(userId) {
            if (!confirm("Bạn có tin chắc muốn rút hồ sơ thợ mộc này khỏi cơ sở dữ liệu hành chính?")) return;

            try {
                const response = await fetch('<?= base_url('api/users') ?>/' + userId, {
                    method: 'DELETE'
                });

                if (response.ok) {
                    showToast("success", "Đã xóa thợ", "Bảo hộ lao động đã gỡ hồ sơ nhân vật này thành công.");
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
                                    <img src="${u.avatar}" alt="${u.name}" class="inline-block h-6 w-6 rounded-full object-cover ring-2 ring-white" title="${u.name} (${u.position_name || 'Thợ mộc'})">
                                `).join('')}
                            </div>
                        </div>
                    `;
                } else {
                    assignedGridHTML = `<span class="text-[10px] text-amber-600 font-medium italic">Chưa giao cho thợ nào</span>`;
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
                            <span class="bg-indigo-50 text-indigo-700 border border-indigo-100 text-[9px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md">${task.job_category_name || 'Đóng mộc'}</span>
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
                container.innerHTML = `<span class="text-slate-400 text-xs italic">Hãy đăng tuyển hồ sơ thợ phụ trước.</span>`;
                return;
            }

            subStaff.forEach(user => {
                const label = document.createElement('label');
                label.className = "flex items-center gap-2 bg-white hover:bg-slate-50 border border-slate-150 p-2 rounded-xl cursor-pointer select-none transition-all";
                label.innerHTML = `
                    <input type="checkbox" name="assignees" value="${user.id}" class="rounded text-slate-900 focus:ring-0">
                    <img src="${user.avatar}" alt="Avatar" class="w-5 h-5 rounded-full object-cover">
                    <span>${user.name} <small class="text-slate-400">(${user.position_name || 'Thợ mộc'})</small></span>
                `;
                container.appendChild(label);
            });
        }

        function openTaskModal() {
            document.getElementById('task-modal').classList.remove('hidden');
            document.getElementById('task-modal-title').innerText = "Tạo Việc Mới & Chỉ Định Thợ";
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
                    showToast("success", "Kế hoạch cập nhật", editId ? "Đã lưu vết chỉnh sửa dự án mộc!" : "Phân công tổ thêu ráp mộc thành công.");
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
            document.getElementById('task-modal-title').innerText = "Cập nhật dự án mộc: " + task.title;
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
            if(!confirm("Bạn có chắc muốn hủy bỏ đầu việc này? Việc này sẽ gỡ thợ tương ứng.")) return;

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
                        Chưa có lượt nhật báo cáo nhật nhật trình nào lọt vào bộ lọc này.
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

                            <p class="text-[11px] font-extrabold text-slate-500">Dự án mộc: <span class="text-slate-800">${log.task_title}</span></p>
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
                select.innerHTML += `<option value="${task.id}">${task.title} [${task.job_category_name || 'Mộc'}]</option>`;
            });
        }

        function openSubmitLogModal() {
            document.getElementById('submit-log-modal').classList.remove('hidden');
            document.getElementById('submit-log-form').reset();
            document.getElementById('image-upload-preview-container').classList.add('hidden');
            document.getElementById('slider-val-lbl').innerText = '50%';
        }
        function closeSubmitLogModal() {
            document.getElementById('submit-log-modal').classList.add('hidden');
        }

        function previewSelectedImage(event) {
            const file = event.target.files[0];
            const reader = new FileReader();
            reader.onload = function() {
                const preview = document.getElementById('image-upload-preview');
                const container = document.getElementById('image-upload-preview-container');
                preview.src = reader.result;
                container.classList.remove('hidden');
            }
            if (file) {
                reader.readAsDataURL(file);
            }
        }

        async function handleLogSubmit(event) {
            event.preventDefault();
            const btnSubmit = document.getElementById('btn-log-submit');
            btnSubmit.disabled = true;
            btnSubmit.innerText = "ĐANG TẢI BÁO CÁO MỘC...";

            const task_id = document.getElementById('log-task-id').value;
            const progress_percent = document.getElementById('log-progress-slider').value;
            const date = document.getElementById('log-date').value;
            const notes = document.getElementById('log-notes').value.trim();
            const imageInput = document.getElementById('log-image');

            // Send via Multipart Form data for php file upload
            const fd = new FormData();
            fd.append('task_id', task_id);
            fd.append('user_id', PHP_CURRENT_USER.id);
            fd.append('progress_percent', progress_percent);
            fd.append('date', date);
            fd.append('notes', notes);
            if (imageInput.files[0]) {
                fd.append('image', imageInput.files[0]);
            }

            try {
                const response = await fetch('<?= base_url('api/logs') ?>', {
                    method: 'POST',
                    body: fd
                });

                const r = await response.json();

                if (response.ok) {
                    showToast("success", "Nộp thành công", "Sản lượng nhật trình gỗ đã gửi lên máy chủ PHP và đợi duyệt!");
                    closeSubmitLogModal();
                    syncData();
                } else {
                    showToast("alarm", "Lỗi dữ liệu", r.messages?.error || "Gửi nhật trình thất bại");
                }
            } catch (err) {
                console.error(err);
            } finally {
                btnSubmit.disabled = false;
                btnSubmit.innerText = "Nộp nhật trình";
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
                tbody.innerHTML = `<tr><td colSpan="4" class="py-6 text-center text-slate-400 italic">Chưa phát hiện nhật trình hoạt động tích lũy</td></tr>`;
            } else {
                productivity.forEach((emp, index) => {
                    let rating = "Thợ Khá";
                    let ratingStyle = "bg-blue-50 text-blue-700";
                    if (emp.totalProgressPoints >= 105) {
                        rating = "Mộc Tinh Xảo";
                        ratingStyle = "bg-emerald-50 text-emerald-700";
                    } else if (emp.totalProgressPoints < 40) {
                        rating = "Thợ Phụ Cơ Bản";
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
                        <td class="py-3 px-3 text-center text-slate-500">${emp.approvedLogsCount} lượt thợ</td>
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
    </script>
</body>
</html>
