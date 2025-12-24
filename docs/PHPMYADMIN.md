# phpMyAdmin - Quản lý Database

**Tác giả**: [github.com/lehuygiang28](https://github.com/lehuygiang28)

## Tổng quan

phpMyAdmin là công cụ quản lý database trực quan qua web interface, tương tự như phpMyAdmin trong XAMPP. Nó cho phép bạn:

- Xem và quản lý database, tables, records
- Chạy SQL queries
- Import/Export dữ liệu
- Quản lý users và permissions
- Xem cấu trúc database schema

## Khởi động phpMyAdmin

```bash
# Khởi động tất cả services bao gồm phpMyAdmin
docker-compose --profile dev up -d

# Hoặc chỉ khởi động phpMyAdmin (sau khi web và db đã chạy)
docker-compose --profile dev up -d phpmyadmin
```

## Truy cập

- **URL**: http://localhost:8080
- **Port**: 8080 (có thể thay đổi trong `docker-compose.yml`)

## Thông tin đăng nhập

### Root User (Toàn quyền)

Sử dụng root user khi cần:
- Tạo/xóa database
- Quản lý users và permissions
- Thực hiện các thao tác quản trị

**Thông tin đăng nhập:**
- **Server**: `db` (hoặc để trống, phpMyAdmin sẽ tự động detect)
- **Username**: `root`
- **Password**: `rootpassword`

### Application User (Quyền hạn chế)

Sử dụng application user để:
- Chỉ làm việc với database `student_management`
- Thực hiện CRUD operations trên tables
- Không thể tạo/xóa database hoặc quản lý users

**Thông tin đăng nhập:**
- **Server**: `db` (hoặc để trống)
- **Username**: `student_user`
- **Password**: `student_password`

## Các thao tác thường dùng

### 1. Xem danh sách Tables

1. Chọn database `student_management` ở sidebar bên trái
2. Xem danh sách tất cả tables: `users`, `students`

### 2. Xem dữ liệu trong Table

1. Click vào tên table (ví dụ: `students`)
2. Tab "Browse" sẽ hiển thị tất cả records
3. Có thể sắp xếp, tìm kiếm, và filter dữ liệu

### 3. Chạy SQL Query

1. Click tab "SQL" ở trên cùng
2. Nhập SQL query của bạn
3. Click "Go" để thực thi

**Ví dụ query:**
```sql
-- Xem tất cả students
SELECT * FROM students;

-- Xem students với thông tin người tạo
SELECT s.*, u.username as created_by 
FROM students s 
LEFT JOIN users u ON s.user_id = u.id;

-- Đếm số lượng students
SELECT COUNT(*) as total FROM students;
```

### 4. Import/Export dữ liệu

**Export:**
1. Chọn database hoặc table
2. Click tab "Export"
3. Chọn format (SQL, CSV, JSON, etc.)
4. Click "Go" để download

**Import:**
1. Chọn database
2. Click tab "Import"
3. Chọn file cần import
4. Click "Go" để import

### 5. Xem cấu trúc Table

1. Click vào tên table
2. Tab "Structure" hiển thị:
   - Tất cả columns và data types
   - Indexes
   - Foreign keys
   - Constraints

### 6. Sửa/Xóa Records

1. Click vào table
2. Tab "Browse"
3. Click icon "Edit" (✏️) hoặc "Delete" (🗑️) ở mỗi row
4. Hoặc chọn multiple rows và dùng "Delete" ở dưới bảng

## Lưu ý

- phpMyAdmin chỉ chạy khi sử dụng profile `dev`
- Không nên sử dụng phpMyAdmin trong production (chỉ dùng cho development)
- Luôn backup database trước khi thực hiện các thao tác quan trọng
- Cẩn thận khi xóa dữ liệu, đặc biệt là với foreign key constraints

## Troubleshooting

### phpMyAdmin không kết nối được với database

1. Kiểm tra database container đã chạy:
   ```bash
   docker-compose ps
   ```

2. Kiểm tra logs:
   ```bash
   docker-compose logs db
   docker-compose logs phpmyadmin
   ```

3. Đảm bảo database đã healthy:
   ```bash
   docker-compose ps db
   # Phải thấy "healthy" status
   ```

### Port 8080 đã được sử dụng

Thay đổi port trong `docker-compose.yml`:
```yaml
phpmyadmin:
  ports:
    - "8081:80"  # Thay 8080 thành 8081
```

Sau đó restart:
```bash
docker-compose --profile dev up -d phpmyadmin
```

## Tài liệu tham khảo

- [phpMyAdmin Official Documentation](https://www.phpmyadmin.net/docs/)
- [Docker Compose Profiles](https://docs.docker.com/compose/profiles/)

