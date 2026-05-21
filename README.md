# Hướng Dẫn Tích Hợp CodeIgniter 4 - Hệ Thống Quản Lý Mộc Việt Co., Ltd

Thư mục này chứa toàn bộ mã nguồn backend tối ưu hóa bằng **CodeIgniter 4 (PHP)** để phục vụ đúng nhu cầu triển khai hệ thống quản lý mộc của quý doanh nghiệp.

## 📁 Cấu Trúc Các File Cung Cấp

- `database.sql` : Script khởi tạo cơ sở dữ liệu MySQL đầy đủ bảng (`users`, `tasks`, `positions`, `job_categories`, `daily_progress_logs`), phân quyền tự động, dữ liệu mẫu tối ưu.
- `app/Config/Routes.php` : Định tuyến API hoàn chỉnh (Đăng nhập, CRUD nhân sự, phân giao việc cho nhiều người, phê duyệt tiến độ dạng JSON, báo cáo xếp hạng năng lực).
- `app/Models/` :
  - `PositionModel.php` : Tra cứu chức vụ.
  - `JobCategoryModel.php` : Tra danh mục việc hoặc sản phẩm mộc.
  - `PersonnelModel.php` : Quản trị thông tin thợ, tính toán chức vị.
  - `TaskModel.php` : Logic phân chia nhiều thợ vào 1 việc dâng lên timeline (`task_assignments`).
  - `DailyProgressLogModel.php` : Quản lý nhật trình, có tích hợp **auto-approve các ngày cũ qua hạn** (`autoApproveOldLogs`) theo đúng yêu cầu nghiệp vụ.
- `app/Controllers/` :
  - `Auth.php` : Xác thực đăng nhập bằng số điện thoại/tên đăng nhập + mật khẩu mã hóa bảo mật.
  - `Personnel.php` : Xử lý hồ sơ thợ mộc, cập nhật chức vụ & tùy biến quyền.
  - `Task.php` : Quản trị công việc ghé tiến trình mộc, gán thợ.
  - `ProgressLog.php` : Thợ báo cáo công việc hàng ngày, ghi chú, **upload ảnh hiện trường lên server** bằng hàm native CI4 và tự động cập nhật tiến độ tổng quan.
  - `Report.php` : Thống kê năng suất, xuất bản bảng xếp hạng thợ tích điểm theo % năng suất đóng gỗ.

---

## 🚀 Các Bước Triển Khai Vào Thư Mục CodeIgniter 4 Của Bạn

### 1. Thiết Lập Cơ Sở Dữ Liệu
Hãy import file `database.sql` vào MySQL Server của bạn (qua phpMyAdmin hoặc MySQL Workbench):
```sql
SOURCE /path/to/database.sql;
```

### 2. Cấu Hình Biến Môi Trường `.env` trong CI4 của bạn
Mở file `.env` ở thư mục gốc của dự án CodeIgniter 4, cấu hình kết nối database:
```env
database.default.hostname = localhost
database.default.database = moc_viet_db
database.default.username = root
database.default.password = your_password_here
database.default.DBDriver = MySQLi
database.default.DBPrefix = 
database.default.port     = 3306
```

### 3. Sao chép File Vào Thư Mục CodeIgniter 4
Hãy chép đè hoặc đặt các file tương ứng theo sơ đồ cây:
- Sao chép toàn bộ các file trong thư mục `app/Models/` vào thư mục `app/Models/` của CI4.
- Sao chép toàn bộ các file trong thư mục `app/Controllers/` vào thư mục `app/Controllers/` của CI4.
- Sao chép file `app/Config/Routes.php` vào thư mục `app/Config/Routes.php` của CI4.

### 4. Phân Quyền Thư Mục Upload Ảnh Nhật Trình
Tạo thư mục lưu ảnh upload của thợ mộc trong thư mục công cộng `public` và cho phép cấp quyền ghi (chown / chmod):
```bash
mkdir -p public/uploads/progress_logs
chmod -R 777 public/uploads/progress_logs
```

---

## 🔒 Kiểm tra Lệ Luật Nghiệp Vụ Chính của Hệ Thống
1. **Auto-Approve Quá Hạn**: Khi lấy danh sách báo cáo tiến độ (`/api/logs`), hàm `autoApproveOldLogs()` của `DailyProgressLogModel` sẽ quét tất cả báo cáo có `date < CURRENT_DATE` đang ở chế độ `pending` và đổi sang `approved` tự động, đánh dấu trường `auto_approved = 1`.
2. **Giao Việc Cho Nhiều Thợ**: Sử dụng mảng ID thợ gửi dạng POST/PUT lên `/api/tasks`. Table liên kết `task_assignments` sẽ lo liệu tổ chức dữ liệu chính xác.
