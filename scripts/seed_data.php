<?php
/**
 * Script seed dữ liệu mẫu từ file JSON vào database
 * Script này đọc file database/sample_data.json và import users và students
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

    // Đảm bảo PHP sử dụng UTF-8
    mb_internal_encoding('UTF-8');
    mb_http_output('UTF-8');

    echo "=== SEED DỮ LIỆU MẪU TỪ JSON ===\n\n";

    // Đọc file JSON
    $jsonFile = __DIR__ . '/../database/sample_data.json';
    
    if (!file_exists($jsonFile)) {
        echo "✗ Lỗi: Không tìm thấy file {$jsonFile}\n";
        exit(1);
    }

    $jsonContent = file_get_contents($jsonFile);
    $data = json_decode($jsonContent, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "✗ Lỗi: Không thể parse JSON. " . json_last_error_msg() . "\n";
        exit(1);
    }

    // ===== SEED USERS =====
    echo "--- SEED USERS ---\n";
    $userCount = 0;
    $userUpdated = 0;

    if (isset($data['users']) && is_array($data['users'])) {
        foreach ($data['users'] as $user) {
            // Validate dữ liệu
            if (empty($user['username']) || empty($user['password']) || empty($user['email']) || empty($user['role'])) {
                echo "⚠ Bỏ qua user không hợp lệ: " . json_encode($user) . "\n";
                continue;
            }

            // Kiểm tra xem user đã tồn tại chưa
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$user['username']]);
            $existingUser = $stmt->fetch();

            $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);

            if (!$existingUser) {
                // User chưa tồn tại, tạo mới
                $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $user['username'],
                    $hashedPassword,
                    $user['email'],
                    $user['role']
                ]);
                echo "✓ Đã tạo user: {$user['username']} ({$user['role']})\n";
                $userCount++;
            } else {
                // User đã tồn tại, cập nhật password
                $stmt = $pdo->prepare("UPDATE users SET password = ?, email = ?, role = ? WHERE username = ?");
                $stmt->execute([
                    $hashedPassword,
                    $user['email'],
                    $user['role'],
                    $user['username']
                ]);
                echo "✓ Đã cập nhật user: {$user['username']}\n";
                $userUpdated++;
            }
        }
    }

    echo "→ Tổng: {$userCount} users mới, {$userUpdated} users đã cập nhật\n\n";

    // ===== SEED STUDENTS =====
    echo "--- SEED STUDENTS ---\n";
    $studentCount = 0;
    $studentSkipped = 0;

    if (isset($data['students']) && is_array($data['students'])) {
        // Tạo mapping username -> user_id
        $userMapping = [];
        $stmt = $pdo->prepare("SELECT id, username FROM users");
        $stmt->execute();
        $users = $stmt->fetchAll();
        foreach ($users as $u) {
            $userMapping[$u['username']] = $u['id'];
        }

        foreach ($data['students'] as $student) {
            // Validate dữ liệu
            if (empty($student['student_code']) || empty($student['full_name']) || 
                empty($student['birthday']) || empty($student['gender']) || 
                empty($student['email']) || empty($student['phone']) || 
                empty($student['address']) || empty($student['created_by'])) {
                echo "⚠ Bỏ qua student không hợp lệ: " . json_encode($student) . "\n";
                continue;
            }

            // Kiểm tra created_by có tồn tại không
            if (!isset($userMapping[$student['created_by']])) {
                echo "⚠ Bỏ qua student {$student['student_code']}: User '{$student['created_by']}' không tồn tại\n";
                $studentSkipped++;
                continue;
            }

            $userId = $userMapping[$student['created_by']];

            // Kiểm tra xem student_code đã tồn tại chưa
            $stmt = $pdo->prepare("SELECT id FROM students WHERE student_code = ?");
            $stmt->execute([$student['student_code']]);
            
            if ($stmt->fetch()) {
                echo "⊘ Đã tồn tại: {$student['student_code']} - {$student['full_name']}\n";
                $studentSkipped++;
                continue;
            }

            // Insert student mới
            $stmt = $pdo->prepare("INSERT INTO students (student_code, full_name, birthday, gender, email, phone, address, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $student['student_code'],
                $student['full_name'],
                $student['birthday'],
                $student['gender'],
                $student['email'],
                $student['phone'],
                $student['address'],
                $userId
            ]);

            echo "✓ Đã tạo student: {$student['student_code']} - {$student['full_name']}\n";
            $studentCount++;
        }
    }

    echo "→ Tổng: {$studentCount} students mới, {$studentSkipped} students đã tồn tại/bỏ qua\n\n";

    // Reset AUTO_INCREMENT nếu cần
    $stmt = $pdo->query("SELECT MAX(id) as max_id FROM students");
    $maxId = $stmt->fetch()['max_id'];
    if ($maxId) {
        $nextId = $maxId + 1;
        $pdo->exec("ALTER TABLE students AUTO_INCREMENT = {$nextId}");
        echo "✓ Đã reset AUTO_INCREMENT của bảng students về {$nextId}\n";
    }

    echo "\n=== HOÀN TẤT SEED DỮ LIỆU ===\n";
    echo "✓ Tổng cộng: " . ($userCount + $userUpdated) . " users, {$studentCount} students mới\n";

} catch (PDOException $e) {
    error_log("Lỗi khi seed dữ liệu: " . $e->getMessage());
    echo "✗ Lỗi khi seed dữ liệu: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    error_log("Lỗi không mong đợi: " . $e->getMessage());
    echo "✗ Lỗi không mong đợi: " . $e->getMessage() . "\n";
    exit(1);
}

