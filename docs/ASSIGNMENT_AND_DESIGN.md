# Đề bài, Phân tích đề bài & Giải thích thiết kế code

Tài liệu này ghi lại **đề bài gốc**, **phân tích đề bài** và **giải thích tại sao trong code lại làm như vậy** (các quyết định thiết kế và triển khai).

---

## 1. Đề bài (Yêu cầu dự án)

### 1.1 Mô tả chung

> Bạn cần xây dựng một ứng dụng web đơn giản bằng PHP (không sử dụng framework) để quản lý thông tin sinh viên. Ứng dụng bao gồm các chức năng cơ bản: đăng nhập, đăng ký, thêm/sửa/xóa/tìm kiếm sinh viên, và phân quyền (admin và user thông thường).

### 1.2 Xác thực và phiên làm việc

- Trang đăng ký tài khoản (username, password, email, role: admin hoặc user).
- Trang đăng nhập.
- Sử dụng session để quản lý phiên đăng nhập.
- Sau khi đăng nhập thành công, chuyển hướng đến trang chủ (dashboard).
- Có nút đăng xuất.

### 1.3 Quản lý sinh viên

- **Danh sách sinh viên**: hiển thị dưới dạng bảng (các trường: ID, Mã sinh viên, Họ tên, Ngày sinh, Giới tính, Email, Số điện thoại, Địa chỉ).
- Thêm sinh viên mới (form nhập liệu).
- Sửa thông tin sinh viên.
- Xóa sinh viên (xác nhận trước khi xóa).
- Tìm kiếm sinh viên theo Họ tên hoặc Mã sinh viên.

### 1.4 Phân quyền

- **User thường**: chỉ được xem danh sách sinh viên và thông tin cá nhân của mình.
- **Admin**: được thực hiện tất cả các thao tác CRUD trên sinh viên và xem danh sách người dùng.

### 1.5 Cơ sở dữ liệu

- Sử dụng MySQL.
- Tạo ít nhất 2 bảng:
  - **users** (id, username, password (mã hóa bằng password_hash), email, role).
  - **students** (id, student_code, full_name, birthday, gender, email, phone, address, **user_id – liên kết với người tạo**).

### 1.6 Yêu cầu kỹ thuật

- Viết code PHP thuần (không dùng Laravel, CodeIgniter...).
- Sử dụng PDO hoặc MySQLi để kết nối và truy vấn database (không dùng mysql_* cũ).
- Mã hóa mật khẩu bằng `password_hash()` và kiểm tra bằng `password_verify()`.
- Validate dữ liệu đầu vào (cả client-side bằng HTML5/JS và server-side bằng PHP).
- Xử lý lỗi hợp lý (hiển thị thông báo thân thiện).
- Giao diện đơn giản nhưng sạch sẽ, responsive cơ bản (có thể dùng Bootstrap).
- Code phải được tổ chức rõ ràng: chia file (ví dụ: config.php, header.php, footer.php, các file xử lý riêng...).
- Viết comment giải thích các phần quan trọng.

### 1.7 Deliverables và thời gian

- Toàn bộ source code (thư mục dự án).
- File SQL để tạo database và bảng + dữ liệu mẫu.
- File README.md hướng dẫn cách cài đặt và chạy dự án (cấu hình database, import SQL...).
- Video demo ngắn (3–5 phút) hoặc ảnh chụp màn hình các chức năng chính.
- **Thời gian**: 1 tuần.

### 1.8 Tiêu chí chấm điểm

| Hạng mục                         | Tỷ lệ |
|----------------------------------|-------|
| Hoàn thiện chức năng             | 60%   |
| Bảo mật và validate dữ liệu      | 15%   |
| Tổ chức code và comment          | 10%   |
| Giao diện và trải nghiệm người dùng | 10% |
| README và hướng dẫn cài đặt      | 5%    |

---

## 2. Phân tích đề bài

### 2.1 Luồng nghiệp vụ chính

1. **Đăng ký** → tạo tài khoản (admin hoặc user).
2. **Đăng nhập** → lưu session, chuyển về trang chủ.
3. **Trang chủ** = danh sách sinh viên (bảng + tìm kiếm).
4. **User thường**: chỉ xem danh sách SV + xem thông tin cá nhân (profile); không thêm/sửa/xóa SV.
5. **Admin**: CRUD sinh viên + xem danh sách users + xem profile.

### 2.2 Quan hệ giữa User và Sinh viên (quan trọng)

Đề bài nói rõ:

- **students.user_id** = **“liên kết với người tạo”** → nghĩa là **người thêm** bản ghi sinh viên vào hệ thống (created_by), **không** phải “user này là sinh viên này”.
- **User thường**: “chỉ được xem danh sách sinh viên và **thông tin cá nhân của mình**” → “thông tin cá nhân” = thông tin **tài khoản** (username, email, role, v.v.), tức trang profile, **không** phải “sinh viên thuộc về user”.
- **Admin**: “được thực hiện **tất cả** các thao tác CRUD trên sinh viên” → **chỉ admin** mới thêm/sửa/xóa sinh viên.

**Kết luận phân tích:**

- **Không** có quan hệ 1-1 kiểu “1 user = 1 sinh viên”.
- **Tất cả** sinh viên đều do **admin** tạo (trong thực tế `user_id` sẽ là id của admin – người tạo).
- User thường chỉ **xem** danh sách SV và **xem/sửa profile** của mình; không sở hữu bản ghi sinh viên nào.

### 2.3 Bảng và trường dữ liệu

- **users**: id, username, password (hash), email, role.
- **students**: id, student_code, full_name, birthday, gender, email, phone, address, **user_id** (FK → users.id, ý nghĩa: người tạo bản ghi).

### 2.4 Yêu cầu bảo mật và validate

- Mật khẩu: `password_hash()` / `password_verify()`.
- Validate cả client (HTML5, JS) và server (PHP).
- PDO prepared statements (không dùng mysql_*), tránh SQL injection.
- Cần có xử lý lỗi và thông báo rõ ràng.

---

## 3. Giải thích tại sao trong code lại làm như vậy

Phần này ánh xạ từ **đề bài & phân tích** sang **cách triển khai** trong code.

### 3.1 Ý nghĩa `user_id` trong bảng `students`

- **Đề bài**: “user_id – liên kết với **người tạo**”.
- **Trong code**:
  - `user_id` = id của user **tạo** bản ghi sinh viên (ai thêm SV vào hệ thống).
  - Chỉ admin có quyền thêm SV → khi thêm SV, `user_id` = `$_SESSION['user_id']` (admin đang đăng nhập).
  - Trong seed (`init_students.php`): **tất cả** sinh viên mẫu đều gán `user_id = admin` vì chỉ admin tạo SV; không gán SV cho user1/user2 với nghĩa “user là sinh viên”.
- **Schema**: Cột `user_id` trong `database.sql` có comment: “Người tạo bản ghi (liên kết users.id). Chỉ admin được thêm SV nên thường là admin.”

### 3.2 Phân quyền: User vs Admin

- **User thường**:
  - Được xem: danh sách sinh viên (index), tìm kiếm, trang profile (thông tin cá nhân).
  - Không được: thêm/sửa/xóa sinh viên, vào trang danh sách users.
  - Trong code: các trang `students/add.php`, `edit.php`, `delete.php` và `users/index.php` đều kiểm tra `isAdmin()`; nếu không phải admin thì redirect hoặc từ chối.
- **Admin**:
  - Được: toàn bộ CRUD sinh viên, xem danh sách users, xem profile.
  - Trong code: sau khi kiểm tra `isAdmin()`, cho phép hiển thị nút “Thêm sinh viên”, link Sửa/Xóa, và menu “Danh sách người dùng”.

### 3.3 Session và chuyển hướng sau đăng nhập

- **Đề bài**: “Sử dụng session để quản lý phiên đăng nhập”, “Sau khi đăng nhập thành công, chuyển hướng đến trang chủ (dashboard)”.
- **Trong code**:
  - `login.php`: sau khi `password_verify()` đúng, gán `$_SESSION['user_id']`, `$_SESSION['username']`, `$_SESSION['role']` rồi `header('Location: /')` hoặc `header('Location: /index.php')` (trang chủ = danh sách SV).
  - Mọi trang cần đăng nhập đều require `config.php` và kiểm tra `isLoggedIn()`; chưa đăng nhập thì redirect về `login.php`.
  - Đăng xuất: `auth/logout.php` hủy session và chuyển về `login.php`.

### 3.4 Tổ chức file (config, header, footer, xử lý riêng)

- **Đề bài**: “Code phải được tổ chức rõ ràng: chia file (ví dụ: config.php, header.php, footer.php, các file xử lý riêng...)”.
- **Trong code**:
  - **`src/config/config.php`**: kết nối DB (PDO), hằng số (DB_HOST, DB_NAME, ...), hàm dùng chung (getDBConnection, isLoggedIn, isAdmin, redirect, sanitize, validate, CSRF, ...).
  - **`src/includes/header.php`**, **`footer.php`**: layout chung (HTML đầu/cuối, menu, Bootstrap), gọi ở đầu/cuối mỗi trang.
  - **`public/`**: entry point theo URL – mỗi chức năng một file (login.php, register.php, index.php, profile.php, students/add.php, edit.php, delete.php, users/index.php, auth/logout.php).
  - **`database/database.sql`**: tạo database, bảng users & students, index; không chứa dữ liệu mẫu (dữ liệu mẫu dùng script PHP).
  - **`scripts/`**: init_database.php (tạo bảng/import SQL), init_users.php, init_students.php (seed) – dùng khi cài đặt hoặc deploy (Docker entrypoint).

### 3.5 Bảo mật và validate

- **Mật khẩu**: đăng ký/đổi mật khẩu dùng `password_hash($password, PASSWORD_DEFAULT)`; đăng nhập dùng `password_verify($password, $user['password'])`.
- **SQL**: mọi truy vấn có dữ liệu từ người dùng đều dùng PDO prepared statement (`$stmt->prepare()` + `execute([...])`), không nối chuỗi SQL.
- **XSS**: hiển thị ra HTML đều dùng `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` (hoặc hàm sanitize tương đương).
- **CSRF**: form thêm/sửa/xóa có token; xử lý POST kiểm tra `validateCSRFToken($_POST['csrf_token'])`.
- **Validate**: server-side trong từng file xử lý (login, register, add student, edit student): kiểm tra required, format email, độ dài, role trong danh sách cho phép; client-side dùng HTML5 (required, type email, pattern) và có thể thêm JS để thông báo sớm.

### 3.6 Hiển thị danh sách sinh viên và “Người tạo”

- **Đề bài**: bảng có các cột ID, Mã SV, Họ tên, Ngày sinh, Giới tính, Email, SĐT, Địa chỉ; không bắt buộc cột “Người tạo” nhưng `user_id` có trong schema.
- **Trong code**: danh sách SV dùng `SELECT s.*, u.username AS created_by FROM students s LEFT JOIN users u ON s.user_id = u.id` để hiển thị thêm cột “Người tạo” (created_by) cho rõ ai đã thêm bản ghi – phù hợp với ý nghĩa “user_id = người tạo”.

### 3.7 Xóa sinh viên và xác nhận

- **Đề bài**: “Xóa sinh viên (xác nhận trước khi xóa)”.
- **Trong code**: trang xóa (hoặc link xóa) gửi GET/POST đến `students/delete.php`; trước khi DELETE trong DB có trang hoặc dialog xác nhận (form với nút “Xác nhận xóa”); chỉ admin mới vào được delete.php và thực hiện xóa.

### 3.8 Dữ liệu mẫu (seed)

- **Đề bài**: “File SQL để tạo database và bảng **+ dữ liệu mẫu**”.
- **Trong code**:
  - `database/database.sql` chỉ tạo schema (CREATE TABLE); dữ liệu mẫu do script PHP đảm nhiệm để dễ đổi theo môi trường (Docker, local) và dùng `password_hash()` cho user.
  - `scripts/init_users.php`: tạo vài user (admin, user1, user2) với mật khẩu hash.
  - `scripts/init_students.php`: tạo danh sách sinh viên mẫu; **tất cả** đều `user_id = admin` vì theo đề chỉ admin tạo SV, không có “user = sinh viên”.

### 3.9 Docker và entrypoint

- **README**: hướng dẫn chạy bằng Docker (docker-compose) và chạy script init/seed.
- **Trong code**: `docker-entrypoint.sh` đợi DB sẵn sàng, kiểm tra nếu chưa có bảng (ví dụ bảng `users` hoặc số bản ghi students = 0) thì chạy `init_database.php`, `init_users.php`, `init_students.php` để lần deploy đầu (hoặc volume mới) tự có schema và dữ liệu mẫu; sau đó start Apache. Như vậy đúng với yêu cầu “README hướng dẫn cài đặt và chạy” và “file SQL + dữ liệu mẫu” (SQL qua init_database, dữ liệu mẫu qua init_*.php).

---

## 4. Tóm tắt ánh xạ Đề bài → Code

| Đề bài / Phân tích | Cách làm trong code |
|--------------------|----------------------|
| user_id = “liên kết với người tạo” | user_id = người tạo bản ghi SV; chỉ admin thêm SV → user_id = admin khi thêm; seed toàn bộ SV với user_id = admin. |
| User thường chỉ xem danh sách + thông tin cá nhân | User chỉ vào index (danh sách SV), profile; không vào add/edit/delete SV và không vào users/index. |
| Admin CRUD sinh viên + xem danh sách users | isAdmin() bảo vệ students/add, edit, delete và users/index; hiển thị nút/link tương ứng chỉ cho admin. |
| Session, chuyển hướng sau login, đăng xuất | Session lưu user_id, username, role; login xong redirect về /; logout hủy session và redirect login. |
| password_hash / password_verify | Dùng trong register và login. |
| PDO, không mysql_* | Toàn bộ truy vấn qua PDO prepared statements. |
| Validate client + server, xử lý lỗi | HTML5 + JS ở form; PHP validate và gửi thông báo lỗi/thành công qua session hoặc hiển thị trên trang. |
| Chia file rõ ràng, comment | src/config, includes (header/footer), public theo chức năng, database/, scripts/; comment ở đầu file và đoạn quan trọng. |

---

*Tài liệu này dùng để đối chiếu đề bài với thiết kế và triển khai, giúp bảo đảm code đúng yêu cầu và dễ bảo trì.*
