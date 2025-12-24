# Ứng dụng Quản lý Sinh viên - PHP Thuần

Ứng dụng web quản lý thông tin sinh viên được xây dựng bằng PHP thuần (không sử dụng framework) với các chức năng đăng ký, đăng nhập, CRUD sinh viên và phân quyền người dùng.

**Tác giả**: [github.com/lehuygiang28](https://github.com/lehuygiang28)

## Tính năng

### Xác thực người dùng
- ✅ Đăng ký tài khoản mới (username, password, email, role)
- ✅ Đăng nhập với session
- ✅ Đăng xuất
- ✅ Phân quyền: Admin và User thường

### Quản lý Sinh viên
- ✅ Xem danh sách sinh viên (bảng với đầy đủ thông tin)
- ✅ Thêm sinh viên mới (chỉ Admin)
- ✅ Sửa thông tin sinh viên (chỉ Admin)
- ✅ Xóa sinh viên với xác nhận (chỉ Admin)
- ✅ Tìm kiếm sinh viên theo Họ tên hoặc Mã sinh viên

### Phân quyền
- **User thường**: Chỉ xem danh sách sinh viên do mình tạo
- **Admin**: 
  - Thực hiện tất cả thao tác CRUD trên sinh viên
  - Xem danh sách người dùng
  - Xem tất cả sinh viên (của tất cả người dùng)

## Yêu cầu

- **Docker** >= 20.10
- **Docker Compose** >= 2.0

## Cài đặt và Chạy

### Quick Start

```bash
# 1. Clone hoặc tải source code
git clone <repository-url>
cd sft

# 2. Build và khởi động containers (bao gồm phpMyAdmin cho dev)
docker-compose --profile dev up -d

# 3. Đợi vài giây để database khởi động và import dữ liệu

# 4. Truy cập ứng dụng
# Mở trình duyệt: http://localhost:3000
# Hoặc truy cập trực tiếp: http://localhost:3000/login.php

# 5. Truy cập phpMyAdmin để quản lý database
# URL: http://localhost:8080
# Đăng nhập với:
#   - Server: db (hoặc để trống)
#   - Username: root
#   - Password: rootpassword
# Hoặc đăng nhập với user thường:
#   - Username: student_user
#   - Password: student_password
```

### Khởi tạo Database

Nếu database chưa được tạo tự động (thường xảy ra khi volume đã tồn tại từ trước), bạn cần chạy script khởi tạo:

```bash
# Khởi tạo database và các bảng
docker-compose exec web php scripts/init_database.php
```

### Seed Dữ liệu Mẫu

Sau khi database đã được khởi tạo, bạn cần chạy lệnh để seed dữ liệu mẫu:

```bash
# Seed dữ liệu từ JSON (users và students)
docker-compose exec web php scripts/seed_data.php
```

Sau khi seed, bạn có thể đăng nhập với:

**Admins:**
- `admin` / `admin123` (admin@vtc.edu.vn)
- `admin2` / `admin123` (admin2@vtc.edu.vn)

**Users:**
- `nguyenvana` / `user123` (nguyenvana@vtc.edu.vn)
- `tranthibinh` / `user123` (tranthibinh@vtc.edu.vn)
- `levancuong` / `user123` (levancuong@vtc.edu.vn)
- Và nhiều users khác...

Dữ liệu mẫu bao gồm **10 users** và **20 sinh viên** với thông tin thực tế ở Hà Nội.

> **Lưu ý**: Dữ liệu mẫu **KHÔNG** tự động seed khi container khởi động. Bạn phải chạy lệnh seed thủ công sau khi container đã sẵn sàng.

### Quản lý Database với phpMyAdmin

phpMyAdmin đã được cấu hình sẵn trong docker-compose để quản lý database một cách trực quan (tương tự XAMPP).

```bash
# Khởi động phpMyAdmin (chạy cùng với các services khác)
docker-compose --profile dev up -d

# Hoặc chỉ khởi động phpMyAdmin sau khi đã có web và db
docker-compose --profile dev up -d phpmyadmin

# Truy cập phpMyAdmin
# URL: http://localhost:8080
```

**Thông tin đăng nhập phpMyAdmin:**

- **Root user** (toàn quyền):
  - Server: `db` (hoặc để trống)
  - Username: `root`
  - Password: `rootpassword`

- **Application user** (quyền hạn chế):
  - Server: `db` (hoặc để trống)
  - Username: `student_user`
  - Password: `student_password`

> **Lưu ý**: phpMyAdmin chỉ chạy khi sử dụng profile `dev`. Để chạy tất cả services bao gồm phpMyAdmin, dùng: `docker-compose --profile dev up -d`

### Các lệnh Docker hữu ích

```bash
# Xem logs
docker-compose logs -f

# Xem logs của service cụ thể
docker-compose logs -f web
docker-compose logs -f db
docker-compose logs -f phpmyadmin

# Dừng containers
docker-compose stop

# Khởi động lại
docker-compose restart

# Dừng và xóa containers (giữ data)
docker-compose down

# Dừng và xóa tất cả (bao gồm volumes - mất data)
docker-compose down -v

# Dừng và xóa containers
docker-compose down

# Dừng và xóa cả volumes (xóa dữ liệu database)
docker-compose down -v

# Rebuild containers
docker-compose up -d --build
```

Xem các file tài liệu để biết thêm chi tiết:
- [docs/DOCKER.md](docs/DOCKER.md) - Hướng dẫn về Docker
- [docs/PHPMYADMIN.md](docs/PHPMYADMIN.md) - Hướng dẫn sử dụng phpMyAdmin

## Cấu hình Port

Ứng dụng sử dụng các port sau để tránh conflict:

- **Web Server**: `http://localhost:3000` (port 3000)
- **Database**: `localhost:3307` (port 3307)

Nếu các port này đã được sử dụng, bạn có thể thay đổi trong file `docker-compose.yml`:

```yaml
web:
  ports:
    - "YOUR_PORT:80"  # Thay YOUR_PORT bằng port bạn muốn

db:
  ports:
    - "YOUR_PORT:3306"  # Thay YOUR_PORT bằng port bạn muốn
```

## Cấu trúc thư mục

```
sft/
├── src/                    # Source code chính
│   ├── config/            # Cấu hình
│   │   └── config.php    # File cấu hình database và các hàm tiện ích
│   └── includes/          # Các file include chung
│       ├── header.php    # Header chung
│       └── footer.php    # Footer chung
│
├── public/                # Entry points - có thể truy cập từ web
│   ├── index.php         # Trang chủ - Danh sách sinh viên
│   ├── login.php         # Trang đăng nhập
│   ├── register.php      # Trang đăng ký
│   ├── auth/             # Xác thực
│   │   └── logout.php   # Xử lý đăng xuất
│   ├── students/         # Quản lý sinh viên
│   │   ├── add.php      # Thêm sinh viên
│   │   ├── edit.php     # Sửa sinh viên
│   │   └── delete.php   # Xóa sinh viên
│   └── users/            # Quản lý người dùng
│       └── index.php    # Danh sách người dùng
│
├── database/              # Các file database
│   └── database.sql     # File SQL tạo database và dữ liệu mẫu
│
├── scripts/               # Script tiện ích
│   ├── init_users.php   # Script tự động tạo tài khoản mẫu
│   └── init_students.php # Script tự động tạo dữ liệu students mẫu
│
├── tests/                 # Unit tests
│   ├── bootstrap.php    # Bootstrap cho tests
│   ├── ConfigTest.php   # Tests cho các hàm trong config.php
│   ├── DatabaseTest.php # Tests cho database connection và queries
│   └── run_all_tests.php # Script chạy tất cả tests
│
├── Dockerfile
├── docker-compose.yml
├── .dockerignore
├── docs/                 # Tài liệu
│   ├── DOCKER.md       # Hướng dẫn Docker
│   ├── PHPMYADMIN.md   # Hướng dẫn phpMyAdmin
│   ├── STRUCTURE.md    # Chi tiết về cấu trúc thư mục
│   ├── INSTALLATION.md # Hướng dẫn cài đặt chi tiết
│   └── FEATURES.md     # Danh sách tính năng
└── README.md
```

Xem file [docs/STRUCTURE.md](docs/STRUCTURE.md) để biết chi tiết về cấu trúc và lý do tổ chức như vậy.

## Cấu trúc Database

### Bảng `users`
- `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `username` (VARCHAR(50), UNIQUE)
- `password` (VARCHAR(255)) - Mã hóa bằng password_hash()
- `email` (VARCHAR(100), UNIQUE)
- `role` (ENUM: 'admin', 'user')
- `created_at` (TIMESTAMP)

### Bảng `students`
- `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `student_code` (VARCHAR(20), UNIQUE)
- `full_name` (VARCHAR(100))
- `birthday` (DATE)
- `gender` (ENUM: 'Nam', 'Nữ', 'Khác')
- `email` (VARCHAR(100))
- `phone` (VARCHAR(20))
- `address` (TEXT)
- `user_id` (INT, FOREIGN KEY -> users.id)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)

## Bảo mật

### Bảo mật Database
- ✅ **SQL Injection Protection**: Sử dụng PDO với prepared statements cho tất cả queries
- ✅ **Password Hashing**: Mã hóa mật khẩu bằng `password_hash()` (bcrypt) và kiểm tra bằng `password_verify()`
- ✅ **Input Sanitization**: Làm sạch tất cả input trước khi xử lý

### Bảo mật Session
- ✅ **Session Security**: 
  - `session.cookie_httponly = 1` (chống XSS qua cookie)
  - `session.use_only_cookies = 1` (không dùng URL)
  - Regenerate session ID định kỳ và sau khi login
- ✅ **Session Management**: Quản lý phiên đăng nhập với timeout

### Bảo mật XSS
- ✅ **Output Escaping**: Tất cả output được escape bằng `htmlspecialchars()`
- ✅ **Input Sanitization**: Sử dụng `strip_tags()` và `trim()` để làm sạch input

### Bảo mật CSRF
- ✅ **CSRF Protection**: Tất cả forms có CSRF token và validation

### Rate Limiting
- ✅ **Brute Force Protection**: 
  - Tối đa 5 lần thử đăng nhập
  - Khóa tài khoản 5 phút sau khi vượt quá giới hạn

### Phân quyền
- ✅ **Role-Based Access Control**: Kiểm tra quyền truy cập cho từng trang
- ✅ **User thường**: Chỉ xem sinh viên do mình tạo
- ✅ **Admin**: Toàn quyền CRUD và xem tất cả dữ liệu

### Validation
- ✅ **Client-side Validation**: HTML5 validation và JavaScript
- ✅ **Server-side Validation**: PHP validation cho tất cả input
- ✅ **Email Validation**: Sử dụng `filter_var()` với `FILTER_VALIDATE_EMAIL`
- ✅ **Phone Validation**: Pattern validation cho số điện thoại Việt Nam

## Giao diện

- Sử dụng Bootstrap 5.3 cho responsive design
- Bootstrap Icons cho các icon
- Giao diện đơn giản, sạch sẽ, dễ sử dụng
- **Pagination**: Phân trang với limit/offset, có thể chọn số lượng/trang (5, 10, 20, 50, 100)
- Animations và transitions mượt mà

## Seed Dữ liệu Mẫu

Dự án sử dụng file JSON để quản lý dữ liệu mẫu, giúp dễ dàng thêm/sửa/xóa dữ liệu.

### File JSON

File `database/sample_data.json` chứa:
- **Users**: 10 tài khoản (2 admins, 8 users)
- **Students**: 20 sinh viên với thông tin Việt Nam thực tế

### Chạy Seed

**Lần đầu tiên sau khi khởi động container:**
```bash
# Seed dữ liệu từ JSON (users và students)
docker-compose exec web php scripts/seed_data.php
```

**Sau khi chỉnh sửa JSON:**
```bash
# 1. Chỉnh sửa file database/sample_data.json
# 2. Chạy lại seed (sẽ skip các bản ghi đã tồn tại)
docker-compose exec web php scripts/seed_data.php
```

**Xóa và seed lại từ đầu:**
```bash
# Xóa database và tạo lại
docker-compose down -v
docker-compose up -d

# Đợi container sẵn sàng, sau đó seed
docker-compose exec web php scripts/seed_data.php
```

### Format JSON

**Users:**
```json
{
  "username": "string",
  "password": "string",
  "email": "string",
  "role": "admin|user"
}
```

**Students:**
```json
{
  "student_code": "string",
  "full_name": "string",
  "birthday": "YYYY-MM-DD",
  "gender": "Nam|Nữ|Khác",
  "email": "string",
  "phone": "string",
  "address": "string",
  "created_by": "username"
}
```

> **Lưu ý**: `created_by` phải là username của user đã tồn tại trong database.

## Testing

### Chạy Unit Tests

```bash
# Với Docker
docker-compose exec web php tests/run_all_tests.php

# Hoặc chạy từng test riêng
docker-compose exec web php tests/ConfigTest.php
docker-compose exec web php tests/DatabaseTest.php
```

### Tests bao gồm:

1. **ConfigTest**: Tests cho các hàm utility
   - `sanitize()` - XSS protection
   - `validateEmail()` - Email validation
   - `validatePhone()` - Phone validation
   - `formatDate()` - Date formatting
   - `generateCSRFToken()` - CSRF token generation
   - `validateCSRFToken()` - CSRF token validation

2. **DatabaseTest**: Tests cho database
   - Database connection
   - Tables existence
   - Prepared statements (SQL injection protection)
   - UTF-8 encoding

### Kết quả mong đợi:

```
✓ TẤT CẢ TESTS ĐÃ PASS!
✓ Hệ thống hoạt động đúng như mong đợi.
```

## Troubleshooting

### Container không khởi động

```bash
# Kiểm tra logs
docker-compose logs

# Kiểm tra trạng thái containers
docker-compose ps

# Xóa và build lại
docker-compose down
docker-compose up -d --build
```

### Lỗi 404 Not Found

Nếu gặp lỗi 404 khi truy cập, lưu ý:
- DocumentRoot đã được set là `public/`, nên **KHÔNG** cần thêm `/public/` trong URL
- URL đúng: `http://localhost:3000/login.php` ✅
- URL sai: `http://localhost:3000/public/login.php` ❌

Nếu vẫn gặp lỗi, rebuild container:
```bash
docker-compose down
docker-compose up -d --build
```

### Database không kết nối được

1. Kiểm tra database đã sẵn sàng:
```bash
docker-compose exec db mysqladmin ping -h localhost -u root -prootpassword
```

2. Kiểm tra environment variables trong container web:
```bash
docker-compose exec web env | grep DB_
```

### Lỗi "Table doesn't exist" khi seed dữ liệu

Nếu gặp lỗi `Table 'student_management.users' doesn't exist` khi chạy seed:

1. **Khởi tạo database và các bảng:**
```bash
docker-compose exec web php scripts/init_database.php
```

2. **Sau đó seed dữ liệu:**
```bash
docker-compose exec web php scripts/seed_data.php
```

**Nguyên nhân:** MySQL chỉ chạy script trong `/docker-entrypoint-initdb.d/` khi volume còn trống. Nếu volume đã tồn tại từ trước, script sẽ không chạy lại.

**Giải pháp thay thế:** Xóa volume và tạo lại từ đầu:
```bash
# Xóa tất cả containers và volumes
docker-compose down -v

# Khởi động lại (database sẽ được tạo tự động)
docker-compose up -d

# Đợi vài giây, sau đó seed dữ liệu
docker-compose exec web php scripts/seed_data.php
```

### Port đã được sử dụng

Nếu gặp lỗi port đã được sử dụng, thay đổi port trong `docker-compose.yml`:

```yaml
web:
  ports:
    - "3001:80"  # Thay đổi 3001 thành port khác

db:
  ports:
    - "3308:3306"  # Thay đổi 3308 thành port khác
```

### Không đăng nhập được

Nếu không đăng nhập được với tài khoản mẫu:
1. Kiểm tra logs để xem script init_users.php đã chạy chưa:
```bash
docker-compose logs web | grep "Initializing sample users"
```

2. Chạy lại script khởi tạo thủ công:
```bash
docker-compose exec web php scripts/init_users.php
```

3. Hoặc đăng ký tài khoản mới từ trang `/register.php`

### Lỗi encoding tiếng Việt

Nếu tiếng Việt hiển thị sai (ví dụ: "Nguyá»...n" thay vì "Nguyễn"):

1. **Rebuild containers** để áp dụng cấu hình UTF-8 mới:
```bash
docker-compose down
docker-compose up -d --build
```

2. **Xóa và import lại database** (mất dữ liệu cũ):
```bash
docker-compose down -v
docker-compose up -d
```

Sau khi rebuild, dữ liệu mới sẽ được lưu đúng encoding UTF-8.

## Testing

Xem file [tests/README.md](tests/README.md) để biết chi tiết về unit tests. Xem thêm [docs/FEATURES.md](docs/FEATURES.md) để biết danh sách đầy đủ các tính năng.

### Chạy Tests

```bash
# Chạy tất cả tests
docker-compose exec web php tests/run_all_tests.php

# Chạy từng test riêng
docker-compose exec web php tests/ConfigTest.php
docker-compose exec web php tests/DatabaseTest.php
```

### Test Coverage

- **ConfigTest**: 20+ test cases cho các hàm utility
- **DatabaseTest**: Tests cho database connection, prepared statements, UTF-8 encoding

## Pagination

Trang danh sách sinh viên hỗ trợ pagination với các tính năng:

- **Limit/Offset based**: Sử dụng LIMIT và OFFSET trong SQL
- **Tùy chọn số lượng/trang**: 5, 10, 20, 50, 100 (mặc định: 10)
- **Giữ nguyên tìm kiếm**: Tìm kiếm được giữ lại khi chuyển trang
- **Hiển thị thông tin**: "Hiển thị X / Y sinh viên (Trang N / M)"
- **Pagination controls**: Previous/Next và số trang với ellipsis
- **Responsive**: Pagination hoạt động tốt trên mobile

## Production

Để deploy lên production, bạn nên:

1. Sử dụng environment file (`.env`) thay vì hardcode trong `docker-compose.yml`
2. Sử dụng secrets management cho passwords
3. Cấu hình SSL/HTTPS
4. Sử dụng reverse proxy (nginx)
5. Backup database định kỳ
6. Monitor logs và performance

## License

Dự án này được tạo cho mục đích học tập và demo.
