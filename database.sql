-- DATABASE SCHEMA FOR CODESIGNER / CODEIGNITER 4 SYSTEMS
-- Hệ Thống Quản Lý Nhân Sự & Công Việc Mộc Việt Co., Ltd
CREATE DATABASE IF NOT EXISTS `moc_viet_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `moc_viet_db`;

-- 1. DANH MỤC QUYỀN (Permissions)
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` VARCHAR(50) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 2. DANH MỤC CHỨC VỤ (Positions)
CREATE TABLE IF NOT EXISTS `positions` (
  `id` VARCHAR(50) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 3. DANH MỤC CÔNG VIỆC / SẢN PHẨM (Job Categories)
CREATE TABLE IF NOT EXISTS `job_categories` (
  `id` VARCHAR(50) NOT NULL,
  `name` VARCHAR(110) NOT NULL,
  `description` TEXT,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 4. BẢNG NHÂN SỰ (Personnel)
CREATE TABLE IF NOT EXISTS `users` (
  `id` VARCHAR(50) NOT NULL,
  `phone` VARCHAR(15) NOT NULL UNIQUE,
  `username` VARCHAR(50) NULL,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `dob` DATE DEFAULT NULL,
  `address` TEXT,
  `identity_card` VARCHAR(20) NOT NULL,
  `avatar` LONGTEXT DEFAULT NULL,
  `role` ENUM('admin', 'manager', 'staff') NOT NULL DEFAULT 'staff',
  `position_id` VARCHAR(50) DEFAULT NULL,
  `custom_permissions` JSON DEFAULT NULL,
  -- Lưu trữ mảng JSON chứa các ID quyền hạn được gán
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`position_id`) REFERENCES `positions`(`id`) ON DELETE
  SET
    NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 5. BẢNG CÔNG VIỆC / DỰ ÁN SẢN PHẨM (Tasks)
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` VARCHAR(50) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `job_category_id` VARCHAR(50) DEFAULT NULL,
  `status` ENUM('pending', 'in_progress', 'completed') NOT NULL DEFAULT 'pending',
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `created_by` VARCHAR(50) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`job_category_id`) REFERENCES `job_categories`(`id`) ON DELETE
  SET
    NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 6. BẢNG TRUNG GIAN PHÂN CÔNG NHÂN VIÊN - CÔNG VIỆC (Task Assignments)
CREATE TABLE IF NOT EXISTS `task_assignments` (
  `task_id` VARCHAR(50) NOT NULL,
  `user_id` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`task_id`, `user_id`),
  FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- 7. BẢNG NHẬT KÝ TIẾN ĐỘ HẰNG NGÀY (Daily Progress Logs)
CREATE TABLE IF NOT EXISTS `daily_progress_logs` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `task_id` VARCHAR(50) NOT NULL,
  `user_id` VARCHAR(50) NOT NULL,
  `date` DATE NOT NULL,
  `notes` TEXT NOT NULL,
  `progress_percent` INT NOT NULL DEFAULT 0,
  -- 0 đến 100
  `image` LONGTEXT DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `approved_by` VARCHAR(50) DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `auto_approved` TINYINT(1) DEFAULT 0,
  -- 1 nếu tự động duyệt do hết ngày
  PRIMARY KEY (`id`),
  FOREIGN KEY (`task_id`) REFERENCES `tasks`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`) ON DELETE
  SET
    NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- MẪU DỮ LIỆU BAN ĐẦU
INSERT INTO
  `permissions` (`id`, `name`, `description`)
VALUES
  (
    'p1',
    'Xem tất cả công việc',
    'Quyền xem toàn bộ danh sách công việc trong hệ thống'
  ),
  (
    'p2',
    'Quản lý công việc',
    'Quyền thêm, sửa, xóa và phân công giao việc'
  ),
  (
    'p3',
    'Duyệt tiến độ hằng ngày',
    'Quyền phê duyệt hoặc từ chối báo cáo công việc hằng ngày của nhân sự'
  ),
  (
    'p4',
    'Quản lý nhân sự',
    'Quyền thêm, sửa, phân quyền tài khoản nhân viên'
  ),
  (
    'p5',
    'Quản lý danh mục',
    'Quyền quản lý danh mục công việc, chức vụ'
  ),
  (
    'p6',
    'Xem báo cáo thống kê',
    'Quyền xem biểu đồ hiệu suất, tổng hợp báo cáo công việc'
  );

INSERT INTO
  `positions` (`id`, `name`, `description`)
VALUES
  (
    'pos1',
    'Thợ cả (Quản đốc)',
    'Chịu trách nhiệm kỹ thuật chính và thi công lắp ráp'
  ),
  (
    'pos2',
    'Thợ phụ',
    'Hỗ trợ chà nhám, chuẩn bị nguyên vật liệu và hỗ trợ lắp đặt'
  ),
  (
    'pos3',
    'Thiết kế kỹ thuật',
    'Vẽ bản phối cảnh, bóc tách khối lượng và thiết kế chi tiết'
  );

INSERT INTO
  `job_categories` (`id`, `name`, `description`)
VALUES
  (
    'cat1',
    'Đóng đồ gỗ mỹ nghệ',
    'Chế tác khung xương, gia công thô các chi tiết gỗ'
  ),
  (
    'cat2',
    'Chà nhám & Xử lý bề mặt',
    'Làm phẳng bề mặt gỗ, mài cạnh sắc, trám trét các lỗi nhỏ'
  ),
  (
    'cat3',
    'Sơn lót & Phủ PU',
    'Sơn màu tạo vân, sơn bóng bảo vệ bề mặt chống ẩm mốc'
  );

-- Mật khẩu mặc định đã mã hóa pass_verify() tương ứng hoặc '123' dạng thô.
-- Ở đây chèn mật khẩu mã hóa sẵn bằng phương pháp password_hash("123", PASSWORD_BCRYPT) 
INSERT INTO
  `users` (
    `id`,
    `phone`,
    `username`,
    `password`,
    `name`,
    `dob`,
    `address`,
    `identity_card`,
    `role`,
    `position_id`,
    `custom_permissions`
  )
VALUES
  (
    'u1',
    '0901234567',
    'admin',
    '$2y$10$iMGeC6y6kU2b9V5Ufev7PeyQz.wL39tL9sYIbeE4mZp0bQ92POnS2',
    'Trần Thế Khoa',
    '1988-10-15',
    '128 Nguyễn Trãi, Quận 5, TP. HCM',
    '079088012345',
    'admin',
    'pos3',
    '["p1", "p2", "p3", "p4", "p5", "p6"]'
  ),
  (
    'u2',
    '0987654321',
    'manager',
    '$2y$10$iMGeC6y6kU2b9V5Ufev7PeyQz.wL39tL9sYIbeE4mZp0bQ92POnS2',
    'Lê Văn Tựu',
    '1990-05-12',
    '45 Lê Lợi, Quận Gò Vấp, TP. HCM',
    '079090112233',
    'manager',
    'pos1',
    '["p1", "p2", "p3", "p6"]'
  ),
  (
    'u3',
    '0911111111',
    'staff_phuc',
    '$2y$10$iMGeC6y6kU2b9V5Ufev7PeyQz.wL39tL9sYIbeE4mZp0bQ92POnS2',
    'Phạm Hồng Phúc',
    '1994-11-20',
    '71/2 Đường số 8, Quận Thủ Đức, TP. HCM',
    '123456789',
    'staff',
    'pos1',
    '["p1"]'
  );