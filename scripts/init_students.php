<?php
/**
 * Script tự động tạo dữ liệu mẫu students
 * Script này sẽ được chạy sau khi init_users.php hoàn thành
 * Đảm bảo users đã tồn tại trước khi insert students
 * 
 * @author github.com/lehuygiang28
 * @version 1.0
 */

// Không cần session cho script này
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

require_once __DIR__ . '/../src/config/config.php';

try {
    $pdo = getDBConnection();
    
    // Đảm bảo encoding UTF-8 cho connection
    $pdo->exec("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'");
    $pdo->exec("SET CHARACTER SET utf8mb4");
    $pdo->exec("SET character_set_connection=utf8mb4");
    
    // Đảm bảo PHP sử dụng UTF-8 (mb_http_output deprecated từ PHP 8.1)
    mb_internal_encoding('UTF-8');
    if (function_exists('mb_http_output')) {
        @mb_http_output('UTF-8');
    }
    
    echo "=== Khởi tạo dữ liệu mẫu students ===\n";
    
    // Kiểm tra xem đã có students chưa
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM students");
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        echo "✓ Dữ liệu students đã tồn tại ({$result['count']} bản ghi). Bỏ qua.\n";
        exit(0);
    }
    
    // user_id = người "tạo" bản ghi sinh viên (hiển thị "Người tạo" trên danh sách).
    // Dữ liệu mẫu gán hết cho admin cho thống nhất.
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $admin = $stmt->fetch();
    
    if (!$admin) {
        echo "✗ Lỗi: User admin chưa tồn tại. Vui lòng chạy init_users.php trước.\n";
        exit(1);
    }
    
    $adminId = $admin['id'];
    
    // Danh sách students mẫu - Thông tin thực tế ở Hà Nội
    $sampleStudents = [
        [
            'student_code' => 'SV001',
            'full_name' => 'Nguyễn Văn An',
            'birthday' => '2000-01-15',
            'gender' => 'Nam',
            'email' => 'nguyenvanan@vtc.edu.vn',
            'phone' => '0912345678',
            'address' => '18 Tam Trinh, P. Tương Mai, Hà Nội',
            'user_id' => $adminId
        ],
        [
            'student_code' => 'SV002',
            'full_name' => 'Trần Thị Bình',
            'birthday' => '2001-03-20',
            'gender' => 'Nữ',
            'email' => 'tranthibinh@vtc.edu.vn',
            'phone' => '0987654321',
            'address' => '45 Nguyễn Chí Thanh, P. Láng Thượng, Q. Đống Đa, Hà Nội',
            'user_id' => $adminId
        ],
        [
            'student_code' => 'SV003',
            'full_name' => 'Lê Văn Cường',
            'birthday' => '2000-07-10',
            'gender' => 'Nam',
            'email' => 'levancuong@vtc.edu.vn',
            'phone' => '0923456789',
            'address' => '123 Phố Huế, P. Ngô Thì Nhậm, Q. Hai Bà Trưng, Hà Nội',
            'user_id' => $adminId
        ],
        [
            'student_code' => 'SV004',
            'full_name' => 'Phạm Thị Dung',
            'birthday' => '2001-11-25',
            'gender' => 'Nữ',
            'email' => 'phamthidung@vtc.edu.vn',
            'phone' => '0934567890',
            'address' => '78 Láng Hạ, P. Láng Hạ, Q. Đống Đa, Hà Nội',
            'user_id' => $adminId
        ],
        [
            'student_code' => 'SV005',
            'full_name' => 'Hoàng Văn Em',
            'birthday' => '2000-05-30',
            'gender' => 'Nam',
            'email' => 'hoangvanem@vtc.edu.vn',
            'phone' => '0945678901',
            'address' => '56 Giải Phóng, P. Đồng Tâm, Q. Hai Bà Trưng, Hà Nội',
            'user_id' => $adminId
        ],
        [
            'student_code' => 'SV006',
            'full_name' => 'Vũ Thị Phương',
            'birthday' => '2001-02-14',
            'gender' => 'Nữ',
            'email' => 'vuthiphuong@vtc.edu.vn',
            'phone' => '0956789012',
            'address' => '22 Trần Đại Nghĩa, P. Bách Khoa, Q. Hai Bà Trưng, Hà Nội',
            'user_id' => $adminId
        ],
        [
            'student_code' => 'SV007',
            'full_name' => 'Đặng Văn Hải',
            'birthday' => '2000-09-08',
            'gender' => 'Nam',
            'email' => 'dangvanhai@vtc.edu.vn',
            'phone' => '0967890123',
            'address' => '91 Chùa Láng, P. Láng Thượng, Q. Đống Đa, Hà Nội',
            'user_id' => $adminId
        ],
        [
            'student_code' => 'SV008',
            'full_name' => 'Ngô Thị Lan',
            'birthday' => '2001-12-03',
            'gender' => 'Nữ',
            'email' => 'ngothilan@vtc.edu.vn',
            'phone' => '0978901234',
            'address' => '15 Tây Sơn, P. Quang Trung, Q. Đống Đa, Hà Nội',
            'user_id' => $adminId
        ],
        [
            'student_code' => 'SV009',
            'full_name' => 'Bùi Văn Kiên',
            'birthday' => '2000-04-19',
            'gender' => 'Nam',
            'email' => 'buivankien@vtc.edu.vn',
            'phone' => '0989012345',
            'address' => '88 Lạc Long Quân, P. Nghĩa Đô, Q. Cầu Giấy, Hà Nội',
            'user_id' => $adminId
        ],
        [
            'student_code' => 'SV010',
            'full_name' => 'Đinh Thị Mai',
            'birthday' => '2001-06-22',
            'gender' => 'Nữ',
            'email' => 'dinhthimai@vtc.edu.vn',
            'phone' => '0990123456',
            'address' => '42 Xuân Thủy, P. Dịch Vọng Hậu, Q. Cầu Giấy, Hà Nội',
            'user_id' => $adminId
        ],
        [
            'student_code' => 'SV011',
            'full_name' => 'Phan Văn Nam',
            'birthday' => '2000-08-11',
            'gender' => 'Nam',
            'email' => 'phanvannam@vtc.edu.vn',
            'phone' => '0901234567',
            'address' => '7 Hoàng Quốc Việt, P. Nghĩa Đô, Q. Cầu Giấy, Hà Nội',
            'user_id' => $adminId
        ],
        [
            'student_code' => 'SV012',
            'full_name' => 'Trương Thị Oanh',
            'birthday' => '2001-01-27',
            'gender' => 'Nữ',
            'email' => 'truongthioanh@vtc.edu.vn',
            'phone' => '0912345679',
            'address' => '156 Đội Cấn, P. Đội Cấn, Q. Ba Đình, Hà Nội',
            'user_id' => $adminId
        ],
        [
            'student_code' => 'SV013',
            'full_name' => 'Hồ Văn Phúc',
            'birthday' => '2000-10-05',
            'gender' => 'Nam',
            'email' => 'hovanphuc@vtc.edu.vn',
            'phone' => '0923456780',
            'address' => '25 Liễu Giai, P. Ngọc Khánh, Q. Ba Đình, Hà Nội',
            'user_id' => $adminId
        ],
        [
            'student_code' => 'SV014',
            'full_name' => 'Lý Thị Quỳnh',
            'birthday' => '2001-07-16',
            'gender' => 'Nữ',
            'email' => 'lythiquynh@vtc.edu.vn',
            'phone' => '0934567891',
            'address' => '89 Kim Mã, P. Kim Mã, Q. Ba Đình, Hà Nội',
            'user_id' => $adminId
        ],
        [
            'student_code' => 'SV015',
            'full_name' => 'Chu Văn Sơn',
            'birthday' => '2000-03-09',
            'gender' => 'Nam',
            'email' => 'chuvanson@vtc.edu.vn',
            'phone' => '0945678902',
            'address' => '33 Điện Biên Phủ, P. Điện Biên, Q. Ba Đình, Hà Nội',
            'user_id' => $adminId
        ],
        [
            'student_code' => 'SV016',
            'full_name' => 'Tạ Thị Thu',
            'birthday' => '2001-11-30',
            'gender' => 'Nữ',
            'email' => 'tathithu@vtc.edu.vn',
            'phone' => '0956789013',
            'address' => '61 Nguyễn Thái Học, P. Điện Biên, Q. Ba Đình, Hà Nội',
            'user_id' => $adminId
        ],
        [
            'student_code' => 'SV017',
            'full_name' => 'Ông Văn Tuấn',
            'birthday' => '2000-05-17',
            'gender' => 'Nam',
            'email' => 'ongvantuan@vtc.edu.vn',
            'phone' => '0967890124',
            'address' => '12 Ngọc Hà, P. Đội Cấn, Q. Ba Đình, Hà Nội',
            'user_id' => $adminId
        ],
        [
            'student_code' => 'SV018',
            'full_name' => 'Dương Thị Uyên',
            'birthday' => '2001-09-24',
            'gender' => 'Nữ',
            'email' => 'duongthiyen@vtc.edu.vn',
            'phone' => '0978901235',
            'address' => '48 Vạn Phúc, P. Vạn Phúc, Q. Hà Đông, Hà Nội',
            'user_id' => $adminId
        ],
        [
            'student_code' => 'SV019',
            'full_name' => 'Quách Văn Việt',
            'birthday' => '2000-12-01',
            'gender' => 'Nam',
            'email' => 'quachvanviet@vtc.edu.vn',
            'phone' => '0989012346',
            'address' => '72 Quang Trung, P. Quang Trung, Q. Hà Đông, Hà Nội',
            'user_id' => $adminId
        ],
        [
            'student_code' => 'SV020',
            'full_name' => 'Kiều Thị Xoan',
            'birthday' => '2001-04-12',
            'gender' => 'Nữ',
            'email' => 'kieuthixoan@vtc.edu.vn',
            'phone' => '0990123457',
            'address' => '38 Phùng Hưng, P. Trung Hòa, Q. Cầu Giấy, Hà Nội',
            'user_id' => $adminId
        ]
    ];
    
    $inserted = 0;
    foreach ($sampleStudents as $student) {
        // Kiểm tra xem student_code đã tồn tại chưa
        $stmt = $pdo->prepare("SELECT id FROM students WHERE student_code = ?");
        $stmt->execute([$student['student_code']]);
        
        if (!$stmt->fetch()) {
            // Đảm bảo dữ liệu được encode đúng UTF-8
            $student_code = mb_convert_encoding($student['student_code'], 'UTF-8', 'UTF-8');
            $full_name = mb_convert_encoding($student['full_name'], 'UTF-8', 'UTF-8');
            $email = mb_convert_encoding($student['email'], 'UTF-8', 'UTF-8');
            $phone = mb_convert_encoding($student['phone'], 'UTF-8', 'UTF-8');
            $address = mb_convert_encoding($student['address'], 'UTF-8', 'UTF-8');
            
            $stmt = $pdo->prepare("INSERT INTO students (student_code, full_name, birthday, gender, email, phone, address, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $student_code,
                $full_name,
                $student['birthday'],
                $student['gender'],
                $email,
                $phone,
                $address,
                $student['user_id']
            ]);
            
            echo "✓ Đã tạo sinh viên: {$student_code} - {$full_name}\n";
            $inserted++;
        }
    }
    
    if ($inserted > 0) {
        echo "\n✓ Hoàn tất khởi tạo dữ liệu mẫu students! ({$inserted} bản ghi)\n";
    } else {
        echo "\n✓ Không có dữ liệu mới cần tạo.\n";
    }
    echo "\n";
    
} catch (PDOException $e) {
    echo "✗ Lỗi khi khởi tạo students: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "✗ Lỗi không mong đợi: " . $e->getMessage() . "\n";
    exit(1);
}

