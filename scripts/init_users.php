<?php
/**
 * Script tự động tạo tài khoản admin và user mẫu với password hash đúng
 * Script này sẽ được chạy khi container khởi động
 * 
 * Script này đảm bảo:
 * - Tạo tài khoản mẫu nếu chưa có
 * - Cập nhật password hash để đảm bảo luôn đúng
 * - Không cần người dùng phải chạy script thủ công
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
    
    echo "=== Khởi tạo tài khoản mẫu ===\n";
    
    // Danh sách tài khoản mẫu cần tạo
    $sampleUsers = [
        [
            'username' => 'admin',
            'password' => 'admin123',
            'email' => 'admin@vtc.edu.vn',
            'role' => 'admin'
        ],
        [
            'username' => 'user1',
            'password' => 'user123',
            'email' => 'nguyenvana@vtc.edu.vn',
            'role' => 'user'
        ],
        [
            'username' => 'user2',
            'password' => 'user123',
            'email' => 'levancuong@vtc.edu.vn',
            'role' => 'user'
        ]
    ];
    
    foreach ($sampleUsers as $user) {
        // Kiểm tra xem user đã tồn tại chưa
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$user['username']]);
        
        if (!$stmt->fetch()) {
            // User chưa tồn tại, tạo mới với password hash đúng
            $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
            
            // Đảm bảo dữ liệu được encode đúng UTF-8 trước khi insert
            $username = mb_convert_encoding($user['username'], 'UTF-8', 'UTF-8');
            $email = mb_convert_encoding($user['email'], 'UTF-8', 'UTF-8');
            
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $username,
                $hashedPassword,
                $email,
                $user['role']
            ]);
            
            echo "✓ Đã tạo tài khoản: {$user['username']} ({$user['role']})\n";
        } else {
            // User đã tồn tại, cập nhật password hash để đảm bảo đúng
            $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
            $stmt->execute([$hashedPassword, $user['username']]);
            
            echo "✓ Đã cập nhật password hash cho: {$user['username']}\n";
        }
    }
    
    echo "\n✓ Hoàn tất khởi tạo tài khoản mẫu!\n";
    echo "\nTài khoản mẫu:\n";
    echo "- Admin: admin / admin123 (admin@vtc.edu.vn)\n";
    echo "- User: user1 / user123 (nguyenvana@vtc.edu.vn)\n";
    echo "- User: user2 / user123 (levancuong@vtc.edu.vn)\n";
    echo "\n";
    
} catch (PDOException $e) {
    echo "✗ Lỗi khi khởi tạo users: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "✗ Lỗi không mong đợi: " . $e->getMessage() . "\n";
    exit(1);
}

