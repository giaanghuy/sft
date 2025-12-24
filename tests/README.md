# Unit Tests Documentation

## Tổng quan

Dự án này bao gồm unit tests để đảm bảo các hàm và chức năng hoạt động đúng như mong đợi.

## Cấu trúc Tests

```
tests/
├── bootstrap.php          # Bootstrap file cho tests
├── ConfigTest.php         # Tests cho các hàm utility trong config.php
├── DatabaseTest.php       # Tests cho database connection và queries
├── run_all_tests.php     # Script chạy tất cả tests
└── README.md             # File này
```

## Chạy Tests

### Với Docker (Khuyến nghị)

```bash
# Chạy tất cả tests
docker-compose exec web php tests/run_all_tests.php

# Chạy từng test riêng
docker-compose exec web php tests/ConfigTest.php
docker-compose exec web php tests/DatabaseTest.php
```

### Không dùng Docker

```bash
# Đảm bảo database đang chạy và cấu hình đúng trong config.php

# Chạy tất cả tests
php tests/run_all_tests.php

# Chạy từng test riêng
php tests/ConfigTest.php
php tests/DatabaseTest.php
```

## Test Coverage

### ConfigTest.php

Tests cho các hàm utility trong `src/config/config.php`:

1. **testSanitize()**
   - ✓ Loại bỏ script tags (XSS protection)
   - ✓ Loại bỏ HTML tags
   - ✓ Trim whitespace
   - ✓ Xử lý empty string

2. **testValidateEmail()**
   - ✓ Chấp nhận email hợp lệ
   - ✓ Từ chối email không hợp lệ
   - ✓ Chấp nhận email với subdomain
   - ✓ Từ chối email rỗng

3. **testValidatePhone()**
   - ✓ Chấp nhận số 10 chữ số
   - ✓ Chấp nhận số 11 chữ số
   - ✓ Từ chối số quá ngắn (< 10)
   - ✓ Từ chối số quá dài (> 11)
   - ✓ Từ chối số có chữ cái

4. **testFormatDate()**
   - ✓ Format đúng định dạng mặc định (d/m/Y)
   - ✓ Format đúng định dạng tùy chỉnh
   - ✓ Xử lý empty date
   - ✓ Format datetime đúng

5. **testGenerateCSRFToken()**
   - ✓ Tạo token không rỗng
   - ✓ Token có độ dài đúng (64 chars)
   - ✓ Trả về cùng token trong cùng session
   - ✓ Token đúng format hex

6. **testValidateCSRFToken()**
   - ✓ Xác thực token hợp lệ
   - ✓ Từ chối token không hợp lệ
   - ✓ Từ chối token rỗng

### DatabaseTest.php

Tests cho database connection và queries:

1. **testConnection()**
   - ✓ getDBConnection() trả về PDO object
   - ✓ Trả về instance của PDO

2. **testTablesExist()**
   - ✓ Bảng users tồn tại
   - ✓ Bảng students tồn tại

3. **testPreparedStatements()**
   - ✓ Prepared statement xử lý SQL injection attempt an toàn
   - ✓ Prepared statement hoạt động với valid input
   - ✓ Trả về kết quả đúng format

4. **testUTF8Encoding()**
   - ✓ Database sử dụng utf8mb4 charset
   - ✓ UTF-8 encoding hoạt động đúng với tiếng Việt

## Kết quả mong đợi

Khi chạy tests thành công, bạn sẽ thấy:

```
╔════════════════════════════════════════════════════════════╗
║         STUDENT MANAGEMENT SYSTEM - UNIT TESTS            ║
╚════════════════════════════════════════════════════════════╝

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
CONFIG TESTS
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
=== BẮT ĐẦU UNIT TESTS ===

--- Test sanitize() ---
✓ PASS: sanitize() loại bỏ script tags
✓ PASS: sanitize() loại bỏ HTML tags
...

=== KẾT QUẢ ===
Tổng số tests: 20
Passed: 20
Failed: 0
Tỷ lệ thành công: 100.00%

╔════════════════════════════════════════════════════════════╗
║                      TỔNG KẾT                              ║
╚════════════════════════════════════════════════════════════╝
✓ TẤT CẢ TESTS ĐÃ PASS!
✓ Hệ thống hoạt động đúng như mong đợi.
```

## Troubleshooting

### Lỗi "Cannot connect to database"

**Nguyên nhân**: Database chưa khởi động hoặc cấu hình sai

**Giải pháp**:
```bash
# Với Docker
docker-compose up -d db
docker-compose exec web php tests/DatabaseTest.php

# Kiểm tra cấu hình trong src/config/config.php
```

### Lỗi "Class not found"

**Nguyên nhân**: Thiếu require bootstrap.php

**Giải pháp**: Đảm bảo file test có:
```php
require_once __DIR__ . '/bootstrap.php';
```

### Tests fail nhưng code hoạt động đúng

**Nguyên nhân**: Test case không chính xác hoặc environment khác nhau

**Giải pháp**: 
1. Kiểm tra lại test case
2. Đảm bảo test environment giống production
3. Kiểm tra database có dữ liệu mẫu chưa

## Thêm Tests Mới

Để thêm test mới:

1. Tạo file test mới trong thư mục `tests/`
2. Include `bootstrap.php`
3. Tạo class extends hoặc implement test interface
4. Thêm method test với prefix `test`
5. Sử dụng `assert()` helper để kiểm tra
6. Thêm vào `run_all_tests.php` để chạy tự động

Ví dụ:
```php
<?php
require_once __DIR__ . '/bootstrap.php';

class MyTest {
    private $testCount = 0;
    private $passCount = 0;
    private $failCount = 0;
    
    private function assert($condition, $message) {
        $this->testCount++;
        if ($condition) {
            $this->passCount++;
            echo "✓ PASS: {$message}\n";
        } else {
            $this->failCount++;
            echo "✗ FAIL: {$message}\n";
        }
    }
    
    public function runAll() {
        $this->testMyFunction();
        // ... more tests
        return $this->failCount === 0;
    }
    
    private function testMyFunction() {
        $this->assert(true, 'My test');
    }
}
```

## Best Practices

1. **Test Isolation**: Mỗi test phải độc lập, không phụ thuộc vào test khác
2. **Clear Names**: Tên test phải mô tả rõ ràng điều gì đang được test
3. **One Assertion**: Mỗi test nên kiểm tra một điều cụ thể
4. **Clean Up**: Xóa dữ liệu test sau khi test xong (nếu có)
5. **Documentation**: Comment giải thích test case phức tạp

## CI/CD Integration

Tests có thể được tích hợp vào CI/CD pipeline:

```yaml
# .github/workflows/tests.yml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Run tests
        run: |
          docker-compose up -d
          docker-compose exec -T web php tests/run_all_tests.php
```




