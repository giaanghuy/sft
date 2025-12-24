# Hướng dẫn Cài đặt Chi tiết

**Tác giả**: [github.com/lehuygiang28](https://github.com/lehuygiang28)

## Yêu cầu Hệ thống

### Phương pháp 1: Sử dụng Docker (Khuyến nghị)

#### Yêu cầu:
- **Docker** >= 20.10
- **Docker Compose** >= 2.0
- **RAM**: Tối thiểu 2GB
- **Disk**: Tối thiểu 1GB trống

#### Cài đặt Docker (nếu chưa có):

**Windows:**
1. Tải Docker Desktop từ: https://www.docker.com/products/docker-desktop
2. Cài đặt và khởi động Docker Desktop
3. Đảm bảo Docker đang chạy (icon Docker xuất hiện ở system tray)

**Linux:**
```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install docker.io docker-compose
sudo systemctl start docker
sudo systemctl enable docker

# Thêm user vào group docker (không cần sudo)
sudo usermod -aG docker $USER
# Logout và login lại
```

**macOS:**
1. Tải Docker Desktop từ: https://www.docker.com/products/docker-desktop
2. Cài đặt và khởi động Docker Desktop

### Phương pháp 2: Cài đặt Thủ công (Không khuyến nghị)

#### Yêu cầu:
- **PHP** >= 7.4 (khuyến nghị PHP 8.1+)
- **MySQL** >= 5.7 hoặc **MariaDB** >= 10.3
- **Apache** hoặc **Nginx** với mod_rewrite
- **PDO MySQL** extension
- **mbstring** extension

## Cài đặt với Docker (Khuyến nghị)

### Bước 1: Tải Source Code

```bash
# Clone repository hoặc giải nén file ZIP
git clone <repository-url>
cd sft
```

### Bước 2: Khởi động Containers

```bash
# Build và khởi động tất cả services
docker-compose up -d

# Xem logs để kiểm tra
docker-compose logs -f
```

### Bước 3: Kiểm tra Trạng thái

```bash
# Kiểm tra containers đang chạy
docker-compose ps

# Kết quả mong đợi:
# NAME                      STATUS
# student_management_db     Up
# student_management_web    Up
```

### Bước 4: Truy cập Ứng dụng

Mở trình duyệt và truy cập:
- **URL**: http://localhost:3000
- **Trang đăng nhập**: http://localhost:3000/login.php

### Bước 5: Đăng nhập

Sử dụng tài khoản mẫu:
- **Admin**: `admin` / `admin123`
- **User**: `user1` / `user123`
- **User**: `user2` / `user123`

## Cài đặt Thủ công (Không khuyến nghị)

### Bước 1: Cài đặt Web Server và Database

**Ubuntu/Debian:**
```bash
sudo apt-get update
sudo apt-get install apache2 mysql-server php php-mysql php-mbstring
sudo systemctl start apache2
sudo systemctl start mysql
```

**Windows:**
- Cài đặt XAMPP hoặc WAMP
- Khởi động Apache và MySQL từ control panel

### Bước 2: Tạo Database

```bash
# Đăng nhập MySQL
mysql -u root -p

# Chạy script SQL
mysql -u root -p < database/database.sql
```

Hoặc sử dụng phpMyAdmin:
1. Mở phpMyAdmin: http://localhost/phpmyadmin
2. Import file `database/database.sql`

### Bước 3: Cấu hình Database

Sửa file `src/config/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'student_management');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
```

### Bước 4: Cấu hình Web Server

**Apache:**
1. Tạo virtual host trỏ đến thư mục `public/`
2. Bật mod_rewrite
3. Cấu hình DocumentRoot: `/path/to/sft/public`

**Nginx:**
```nginx
server {
    listen 80;
    server_name localhost;
    root /path/to/sft/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### Bước 5: Khởi tạo Dữ liệu Mẫu

```bash
# Chạy script khởi tạo users
php scripts/init_users.php

# Chạy script khởi tạo students
php scripts/init_students.php
```

### Bước 6: Set Quyền

```bash
# Linux/Mac
chmod -R 755 public/
chmod -R 755 src/
chown -R www-data:www-data public/ src/
```

## Kiểm tra Cài đặt

### Kiểm tra Database

```bash
# Với Docker
docker-compose exec db mysql -u student_user -pstudent_password student_management -e "SELECT COUNT(*) FROM users;"

# Với cài đặt thủ công
mysql -u root -p student_management -e "SELECT COUNT(*) FROM users;"
```

Kết quả mong đợi: Có ít nhất 3 users (admin, user1, user2)

### Kiểm tra PHP Extensions

```bash
# Với Docker
docker-compose exec web php -m | grep -E "pdo|mysql|mbstring"

# Với cài đặt thủ công
php -m | grep -E "pdo|mysql|mbstring"
```

Kết quả mong đợi: pdo, pdo_mysql, mbstring

### Kiểm tra Kết nối Database

Truy cập: http://localhost:3000/login.php

Nếu trang load được và có thể đăng nhập → Cài đặt thành công!

## Troubleshooting

### Lỗi "Connection refused"

**Nguyên nhân**: Database chưa sẵn sàng

**Giải pháp**:
```bash
# Với Docker
docker-compose logs db
docker-compose restart db

# Đợi 10-15 giây rồi thử lại
```

### Lỗi "Access denied for user"

**Nguyên nhân**: Sai thông tin đăng nhập database

**Giải pháp**: Kiểm tra lại `src/config/config.php` hoặc `docker-compose.yml`

### Lỗi "Table doesn't exist"

**Nguyên nhân**: Database chưa được import

**Giải pháp**:
```bash
# Với Docker
docker-compose down -v
docker-compose up -d

# Với cài đặt thủ công
mysql -u root -p < database/database.sql
```

### Lỗi "Headers already sent"

**Nguyên nhân**: Có output trước khi gửi header

**Giải pháp**: Đã được fix trong code, nếu vẫn gặp:
1. Kiểm tra file không có whitespace trước `<?php`
2. Kiểm tra file không có output trước `header()`

## Cấu hình Nâng cao

### Thay đổi Port

Sửa `docker-compose.yml`:
```yaml
web:
  ports:
    - "8080:80"  # Thay đổi 8080 thành port bạn muốn

db:
  ports:
    - "3306:3306"  # Thay đổi 3306 thành port bạn muốn
```

### Thay đổi Database Password

Sửa `docker-compose.yml`:
```yaml
db:
  environment:
    MYSQL_ROOT_PASSWORD: your_new_password
    MYSQL_PASSWORD: your_new_password
```

Và cập nhật `src/config/config.php` hoặc environment variables.

### Sử dụng Environment File

Tạo file `.env`:
```env
DB_HOST=db
DB_NAME=student_management
DB_USER=student_user
DB_PASS=student_password
```

Sửa `docker-compose.yml`:
```yaml
web:
  env_file:
    - .env
```

## Hỗ trợ

Nếu gặp vấn đề, vui lòng:
1. Kiểm tra logs: `docker-compose logs`
2. Xem file [README.md](README.md) để biết thêm chi tiết
3. Xem file [DOCKER.md](DOCKER.md) để biết về Docker

