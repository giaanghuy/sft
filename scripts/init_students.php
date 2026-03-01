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
    
    // Lấy user_id của admin và user1 để gán cho students
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $admin = $stmt->fetch();
    
    $stmt->execute(['user1']);
    $user1 = $stmt->fetch();
    
    if (!$admin || !$user1) {
        echo "✗ Lỗi: Users chưa được tạo. Vui lòng chạy init_users.php trước.\n";
        exit(1);
    }
    
    $adminId = $admin['id'];
    $user1Id = $user1['id'];
    
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
            'user_id' => $user1Id
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
            'user_id' => $user1Id
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

