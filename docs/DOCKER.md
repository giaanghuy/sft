# Hướng dẫn chạy ứng dụng bằng Docker

Tài liệu này hướng dẫn cách chạy ứng dụng Quản lý Sinh viên bằng Docker và Docker Compose.

**Tác giả**: [github.com/lehuygiang28](https://github.com/lehuygiang28)

## Quick Start

```bash
# 1. Build và khởi động containers
docker-compose up -d

# 2. Đợi vài giây để database khởi động và import dữ liệu

# 3. Truy cập ứng dụng
# Mở trình duyệt: http://localhost:3000

# 4. Đăng nhập
# Admin: admin / admin123
# User: user1 / user123
```

> **Lưu ý**: Nếu không đăng nhập được, vui lòng đăng ký tài khoản mới từ trang register.php

## Yêu cầu

- Docker >= 20.10
- Docker Compose >= 2.0

## Cấu trúc Docker

- **Dockerfile**: Định nghĩa image PHP + Apache
- **docker-compose.yml**: Định nghĩa các services (web server và database)
- **.dockerignore**: Loại trừ các file không cần thiết khi build image

## Cách chạy

### Bước 1: Build và khởi động containers

```bash
# Build và khởi động tất cả services
docker-compose up -d

# Hoặc build lại nếu có thay đổi
docker-compose up -d --build
```

Lệnh này sẽ:
- Build image PHP + Apache từ Dockerfile
- Tạo và khởi động container MySQL
- Tạo và khởi động container web server
- Tự động import file `database.sql` vào MySQL khi container database khởi động lần đầu

### Bước 2: Truy cập ứng dụng

Mở trình duyệt và truy cập:
```
http://localhost:3000
```

### Bước 3: Đăng nhập

**Tài khoản mẫu** sẽ được **tự động tạo** khi container khởi động với password hash đúng:
- Admin: `admin` / `admin123`
- User: `user1` / `user123`
- User: `user2` / `user123`

> **Lưu ý**: Script `init_users.php` tự động chạy khi container web khởi động để đảm bảo password hash luôn đúng. Không cần chạy script thủ công!

## Các lệnh Docker hữu ích

### Xem logs
```bash
# Xem logs của tất cả services
docker-compose logs

# Xem logs của service cụ thể
docker-compose logs web
docker-compose logs db

# Xem logs real-time
docker-compose logs -f
```

### Dừng containers
```bash
# Dừng nhưng không xóa containers
docker-compose stop

# Dừng và xóa containers
docker-compose down

# Dừng và xóa cả volumes (xóa dữ liệu database)
docker-compose down -v
```

### Khởi động lại
```bash
# Khởi động lại tất cả services
docker-compose restart

# Khởi động lại service cụ thể
docker-compose restart web
docker-compose restart db
```

### Truy cập vào container
```bash
# Truy cập vào container web
docker-compose exec web bash

# Truy cập vào MySQL
docker-compose exec db mysql -u root -prootpassword student_management

# Hoặc sử dụng MySQL client
docker-compose exec db mysql -u student_user -pstudent_password student_management
```

### Khởi tạo tài khoản mẫu

Tài khoản mẫu được **tự động tạo** khi container khởi động bởi script `init_users.php`. Script này:
- Tự động tạo tài khoản admin và user mẫu nếu chưa có
- Đảm bảo password hash luôn đúng
- Cập nhật password hash nếu tài khoản đã tồn tại

Nếu cần chạy lại script thủ công:

```bash
# Chạy script khởi tạo users
docker-compose exec web php scripts/init_users.php
```


## Cấu hình

### Thay đổi port

Nếu port 3000 đã được sử dụng, bạn có thể thay đổi trong `docker-compose.yml`:

```yaml
web:
  ports:
    - "YOUR_PORT:80"  # Thay đổi YOUR_PORT thành port bạn muốn (ví dụ: 3001, 8000)

db:
  ports:
    - "YOUR_PORT:3306"  # Thay đổi YOUR_PORT thành port bạn muốn (ví dụ: 3308, 3309)
```

### Thay đổi thông tin database

Cập nhật trong `docker-compose.yml`:

```yaml
db:
  environment:
    MYSQL_ROOT_PASSWORD: your_root_password
    MYSQL_DATABASE: your_database_name
    MYSQL_USER: your_username
    MYSQL_PASSWORD: your_password
```

Và cập nhật environment variables trong service `web`:

```yaml
web:
  environment:
    - DB_HOST=db
    - DB_NAME=your_database_name
    - DB_USER=your_username
    - DB_PASS=your_password
```

### Thay đổi PHP version

Cập nhật trong `Dockerfile`:

```dockerfile
FROM php:8.2-apache  # Thay đổi version ở đây
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

### Database không kết nối được

1. Kiểm tra database đã sẵn sàng:
```bash
docker-compose exec db mysqladmin ping -h localhost -u root -prootpassword
```

2. Kiểm tra network:
```bash
docker network ls
docker network inspect sft_student_network
```

3. Kiểm tra environment variables trong container web:
```bash
docker-compose exec web env | grep DB_
```

### File không được cập nhật

Nếu bạn thay đổi code nhưng không thấy thay đổi trên web:

```bash
# Restart container web
docker-compose restart web

# Hoặc rebuild
docker-compose up -d --build web
```

### Database bị mất dữ liệu

Nếu bạn chạy `docker-compose down -v`, volume sẽ bị xóa. Để giữ lại dữ liệu:

```bash
# Chỉ dừng containers, không xóa volumes
docker-compose down

# Hoặc backup database trước khi xóa
docker-compose exec db mysqldump -u root -prootpassword student_management > backup.sql
```

## Production

Để deploy lên production, bạn nên:

1. Sử dụng environment file (`.env`) thay vì hardcode trong `docker-compose.yml`
2. Sử dụng secrets management cho passwords
3. Cấu hình SSL/HTTPS
4. Sử dụng reverse proxy (nginx)
5. Backup database định kỳ
6. Monitor logs và performance

## Cấu trúc volumes

- `db_data`: Lưu trữ dữ liệu MySQL (persistent)
- Code được mount từ host vào container để dễ phát triển

## Liên kết

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)

