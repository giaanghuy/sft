# Cấu trúc thư mục dự án

**Tác giả**: [github.com/lehuygiang28](https://github.com/lehuygiang28)

Dự án được tổ chức theo cấu trúc khoa học và rõ ràng, tách biệt giữa code logic và entry points.

## Cấu trúc thư mục

```
sft/
├── src/                          # Source code chính
│   ├── config/                   # Cấu hình
│   │   └── config.php           # File cấu hình database và các hàm tiện ích
│   ├── includes/                 # Các file include chung
│   │   ├── header.php           # Header chung cho các trang
│   │   └── footer.php           # Footer chung cho các trang
│   ├── auth/                     # Logic xác thực (nếu cần tách riêng)
│   ├── students/                 # Logic xử lý sinh viên (nếu cần tách riêng)
│   └── users/                    # Logic xử lý người dùng (nếu cần tách riêng)
│
├── public/                       # Entry points - các file có thể truy cập từ web
│   ├── index.php                # Trang chủ - Danh sách sinh viên
│   ├── login.php                 # Trang đăng nhập
│   ├── register.php              # Trang đăng ký
│   ├── auth/                     # Các trang xác thực
│   │   └── logout.php           # Xử lý đăng xuất
│   ├── students/                 # Các trang quản lý sinh viên
│   │   ├── add.php              # Thêm sinh viên
│   │   ├── edit.php             # Sửa sinh viên
│   │   └── delete.php           # Xóa sinh viên
│   └── users/                    # Các trang quản lý người dùng
│       └── index.php            # Danh sách người dùng
│
├── database/                     # Các file database
│   └── database.sql             # File SQL tạo database và dữ liệu mẫu
│
├── scripts/                      # Các script tiện ích
│   ├── init_users.php           # Script tự động tạo tài khoản mẫu
│   └── init_students.php        # Script tự động tạo dữ liệu students mẫu
│
├── Dockerfile                    # Dockerfile cho PHP application
├── docker-compose.yml            # Docker Compose configuration
├── .dockerignore                # Docker ignore file
├── .htaccess                    # Apache configuration
├── README.md                    # Hướng dẫn chính
├── DOCKER.md                    # Hướng dẫn Docker
└── STRUCTURE.md                 # File này - mô tả cấu trúc

```

## Giải thích cấu trúc

### `src/` - Source Code
- Chứa tất cả logic và code backend
- Không thể truy cập trực tiếp từ web browser
- Được include/require bởi các file trong `public/`

### `public/` - Public Entry Points
- Chứa các file có thể truy cập trực tiếp từ web
- Đây là DocumentRoot của web server
- Mỗi file là một entry point, require/include các file từ `src/`

### `database/` - Database Files
- Chứa các file SQL
- Dễ quản lý và version control

### `scripts/` - Utility Scripts
- Các script tiện ích không phải là phần của ứng dụng web
- Có thể chạy từ command line

## Lợi ích của cấu trúc này

1. **Bảo mật**: Code trong `src/` không thể truy cập trực tiếp từ web
2. **Tổ chức rõ ràng**: Tách biệt logic và entry points
3. **Dễ bảo trì**: Dễ tìm và sửa code
4. **Scalable**: Dễ mở rộng khi thêm tính năng mới
5. **Best Practices**: Tuân theo các best practices của PHP

## Đường dẫn trong code

Tất cả các file trong `public/` sử dụng đường dẫn tuyệt đối từ root:
- `/index.php` - Trang chủ
- `/login.php` - Đăng nhập
- `/students/add.php` - Thêm sinh viên
- etc.

Các file trong `src/` sử dụng `__DIR__` để include các file khác một cách tương đối:
```php
require_once __DIR__ . '/../config/config.php';
```

## Migration từ cấu trúc cũ

Nếu bạn đang migrate từ cấu trúc cũ (tất cả file ở root), các file cũ vẫn có thể hoạt động nhưng nên được di chuyển vào cấu trúc mới để:
- Tăng tính bảo mật
- Dễ quản lý hơn
- Tuân theo best practices

