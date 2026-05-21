<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - Hệ Thống Quản Lý Thợ Mộc Việt</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        h1, h2 {
            font-family: 'Space Grotesk', sans-serif;
        }
    </style>
    <!-- Lucide Icons via CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <!-- Logo Icon -->
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-slate-900 text-white mb-4 shadow-md">
            <i data-lucide="hammer" class="w-6 h-6 text-indigo-400"></i>
        </div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">MỘC VIỆT</h2>
        <p class="mt-1 text-sm text-slate-500 font-mono uppercase tracking-widest text-[11px]">
            Hệ Thống Quản Trị Nhân Sự & Công Việc
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div id="login-card" class="bg-white py-8 px-4 border border-slate-200/80 shadow-xl rounded-2xl sm:px-10">
            <!-- Alert Display -->
            <div id="alert-block" class="hidden mb-5 p-3 rounded-xl border flex items-start gap-2.5 text-xs">
                <i id="alert-icon" class="w-4 h-4 mt-0.5 shrink-0"></i>
                <div id="alert-message" class="font-medium leading-relaxed"></div>
            </div>

            <form id="login-form" class="space-y-5" onsubmit="handleLoginSubmit(event)">
                <div>
                    <label for="username" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Số điện thoại / Tên đăng nhập
                    </label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="user" class="w-4 h-4"></i>
                        </div>
                        <input id="username" name="username" type="text" required 
                            placeholder="Ví dụ: admin, manager, hoặc 090..." 
                            class="block w-full pl-9 pr-3 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-900 focus:bg-white transition-all text-slate-800 font-medium">
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center">
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Mật khẩu chứng thực
                        </label>
                    </div>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i data-lucide="lock" class="w-4 h-4"></i>
                        </div>
                        <input id="password" name="password" type="password" required 
                            placeholder="Nhập mật khẩu..." 
                            class="block w-full pl-9 pr-10 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-900 focus:bg-white transition-all text-slate-800 font-medium">
                        
                        <!-- Toggle Password Visiblity Button -->
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3 flex items-center hover:text-slate-800 text-slate-400 cursor-pointer">
                            <i id="password-toggle-icon" data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" id="btn-submit"
                        class="w-full flex justify-center items-center gap-2 py-3 px-4 border border-transparent rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-all cursor-pointer shadow-sm">
                        <span>ĐĂNG NHẬP HỆ THỐNG</span>
                        <i data-lucide="arrow-right-circle" class="w-4 h-4"></i>
                    </button>
                </div>
            </form>

            <!-- Guide and credentials -->
            <div class="mt-6 pt-5 border-t border-slate-100">
                <p class="text-[10px] text-slate-400 text-center leading-relaxed">
                    Tài khoản thử nghiệm nhanh:<br>
                    Quản trị viên: <strong class="text-slate-600">admin / 123</strong><br>
                    Quản lý xưởng: <strong class="text-slate-600">manager / 123</strong><br>
                    Thợ mộc phụ: <strong class="text-slate-600">0911111111 / 123</strong> (hoặc <strong class="text-slate-600">staff_phuc / 123</strong>)
                </p>
            </div>
        </div>
    </div>

    <script>
        // Initialize lucide icons
        lucide.createIcons();

        function togglePasswordVisibility() {
            const pwdInput = document.getElementById('password');
            const toggleIcon = document.getElementById('password-toggle-icon');
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                toggleIcon.setAttribute('data-lucide', 'eye-off');
            } else {
                pwdInput.type = 'password';
                toggleIcon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }

        async function handleLoginSubmit(event) {
            event.preventDefault();
            const btnSubmit = document.getElementById('btn-submit');
            const alertBlock = document.getElementById('alert-block');
            const alertIcon = document.getElementById('alert-icon');
            const alertMessage = document.getElementById('alert-message');

            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;

            // Basic Validation
            if (!username || !password) {
                showAlert('danger', 'Vui lòng cung cấp mật danh và mật mã hợp thức.');
                return;
            }

            // Show loading state
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span>ĐANG CHỨNG THỰC...</span> <div class="animate-spin rounded-full h-4 w-4 border-2 border-white/30 border-t-white"></div>';
            hideAlert();

            try {
                const response = await fetch('<?= base_url('api/auth/login') ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username, password })
                });

                const data = await response.json();

                if (response.ok && data.status === 'success') {
                    showAlert('success', 'Đăng nhập thành công! Đang thiết lập phiên làm việc mộc...');
                    
                    // Save user info in localStorage for frontend reference consistency
                    localStorage.setItem('moc_viet_user', JSON.stringify(data.user));
                    
                    // Redirect to Home Dashboard which will now read the session
                    setTimeout(() => {
                        window.location.href = '<?= base_url('/') ?>';
                    }, 800);
                } else {
                    showAlert('danger', data.messages?.error || data.message || 'Mật danh hoặc Mật khẩu không trùng khớp.');
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = '<span>ĐĂNG NHẬP HỆ THỐNG</span> <i data-lucide="arrow-right-circle" class="w-4 h-4"></i>';
                    lucide.createIcons();
                }
            } catch (err) {
                console.error(err);
                showAlert('danger', 'Có lỗi kết nối máy chủ PHP. Hãy kiểm tra kết nối CSDL MySQL.');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<span>ĐĂNG NHẬP HỆ THỐNG</span> <i data-lucide="arrow-right-circle" class="w-4 h-4"></i>';
                lucide.createIcons();
            }
        }

        function showAlert(type, message) {
            const alertBlock = document.getElementById('alert-block');
            const alertIcon = document.getElementById('alert-icon');
            const alertMessage = document.getElementById('alert-message');

            alertBlock.classList.remove('hidden');
            alertMessage.innerText = message;

            if (type === 'success') {
                alertBlock.className = 'mb-5 p-3 rounded-xl border flex items-start gap-2.5 text-xs bg-emerald-50 border-emerald-200 text-emerald-800';
                alertIcon.setAttribute('data-lucide', 'check-circle-2');
            } else {
                alertBlock.className = 'mb-5 p-3 rounded-xl border flex items-start gap-2.5 text-xs bg-rose-50 border-rose-200 text-rose-800';
                alertIcon.setAttribute('data-lucide', 'alert-circle');
            }
            lucide.createIcons();
        }

        function hideAlert() {
            document.getElementById('alert-block').classList.add('hidden');
        }
    </script>
</body>
</html>
