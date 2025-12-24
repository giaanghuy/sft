<?php
/**
 * Script khởi tạo database và các bảng
 * Script này import file database.sql vào database
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
    echo "=== KHỞI TẠO DATABASE ===\n\n";
    
    // Đọc file SQL
    $sqlFile = __DIR__ . '/../database/database.sql';
    
    if (!file_exists($sqlFile)) {
        echo "✗ Lỗi: Không tìm thấy file {$sqlFile}\n";
        exit(1);
    }
    
    $sqlContent = file_get_contents($sqlFile);
    
    if (empty($sqlContent)) {
        echo "✗ Lỗi: File SQL rỗng\n";
        exit(1);
    }
    
    // Kết nối database (không chỉ định database name để có thể tạo database nếu chưa có)
    $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    // Đảm bảo charset UTF-8
    $pdo->exec("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'");
    
    // Kiểm tra và tạo database nếu chưa có
    try {
        $pdo->exec("USE " . DB_NAME);
        echo "✓ Database '" . DB_NAME . "' đã tồn tại\n";
    } catch (PDOException $e) {
        // Database chưa tồn tại, tạo mới
        echo "⚠ Database '" . DB_NAME . "' chưa tồn tại, đang tạo...\n";
        $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE " . DB_NAME);
        echo "✓ Đã tạo database '" . DB_NAME . "'\n";
    }
    
    // Parse và thực thi các câu lệnh SQL
    // Đơn giản hóa: tách theo dấu ; sau khi loại bỏ comment
    // Loại bỏ comment trước
    $sqlContent = preg_replace('/--.*$/m', '', $sqlContent); // Loại bỏ comment dòng
    $sqlContent = preg_replace('/\/\*.*?\*\//s', '', $sqlContent); // Loại bỏ comment block
    
    // Tách các câu lệnh SQL bằng dấu ;
    // Normalize whitespace trước
    $sqlContent = preg_replace('/\s+/', ' ', $sqlContent);
    
    $statements = array_filter(
        array_map('trim', explode(';', $sqlContent)),
        function($stmt) {
            return !empty($stmt);
        }
    );
    
    echo "Đang thực thi " . count($statements) . " câu lệnh SQL...\n\n";
    
    $successCount = 0;
    $errorCount = 0;
    $retryStatements = [];
    
    // Lần 1: Thực thi tất cả các câu lệnh
    foreach ($statements as $index => $statement) {
        $statement = trim($statement);
        if (empty($statement)) {
            continue;
        }
        
        // Bỏ qua các câu lệnh SET NAMES vì đã set ở trên
        if (preg_match('/^SET\s+(NAMES|CHARACTER\s+SET|character_set_connection)/i', $statement)) {
            continue;
        }
        
        // Bỏ qua USE database vì đã set ở trên
        if (preg_match('/^USE\s+/i', $statement)) {
            continue;
        }
        
        try {
            $pdo->exec($statement);
            $successCount++;
            
            // Hiển thị thông báo cho các câu lệnh quan trọng
            if (preg_match('/CREATE\s+(?:IF\s+NOT\s+EXISTS\s+)?(DATABASE|TABLE)/i', $statement, $matches)) {
                $type = strtolower($matches[1]);
                if ($type === 'database') {
                    echo "✓ Đã tạo database\n";
                } elseif ($type === 'table') {
                    if (preg_match('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?/i', $statement, $tableMatches)) {
                        echo "✓ Đã tạo bảng: {$tableMatches[1]}\n";
                    }
                }
            } elseif (preg_match('/CREATE\s+INDEX/i', $statement)) {
                if (preg_match('/ON\s+`?(\w+)`?\s*\(/i', $statement, $indexMatches)) {
                    echo "✓ Đã tạo index cho bảng: {$indexMatches[1]}\n";
                }
            }
        } catch (PDOException $e) {
            $errorMsg = $e->getMessage();
            
            // Bỏ qua lỗi nếu table/database/index đã tồn tại
            if (strpos($errorMsg, 'already exists') !== false || 
                strpos($errorMsg, 'Duplicate') !== false ||
                strpos($errorMsg, 'Duplicate key') !== false) {
                // Không hiển thị lỗi cho các object đã tồn tại
                continue;
            }
            
            // Bỏ qua lỗi nếu bảng chưa tồn tại (có thể do thứ tự thực thi)
            // Sẽ thử lại sau khi các bảng đã được tạo
            if (strpos($errorMsg, "doesn't exist") !== false) {
                // Lưu lại câu lệnh này để thử lại sau
                $retryStatements[] = $statement;
                continue;
            }
            
            $errorCount++;
            echo "⚠ Lỗi ở câu lệnh " . ($index + 1) . ": " . $errorMsg . "\n";
            echo "   SQL: " . substr($statement, 0, 100) . "...\n";
        }
    }
    
    // Lần 2: Thử lại các câu lệnh bị lỗi (thường là CREATE INDEX)
    if (!empty($retryStatements)) {
        echo "\n--- Thử lại các câu lệnh bị lỗi ---\n";
        foreach ($retryStatements as $statement) {
            try {
                $pdo->exec($statement);
                $successCount++;
                if (preg_match('/CREATE\s+INDEX/i', $statement)) {
                    if (preg_match('/ON\s+`?(\w+)`?\s*\(/i', $statement, $indexMatches)) {
                        echo "✓ Đã tạo index cho bảng: {$indexMatches[1]}\n";
                    }
                }
            } catch (PDOException $e) {
                $errorMsg = $e->getMessage();
                if (strpos($errorMsg, 'already exists') !== false || 
                    strpos($errorMsg, 'Duplicate') !== false) {
                    // Index đã tồn tại, không sao
                    continue;
                }
                $errorCount++;
                echo "⚠ Vẫn lỗi: " . $errorMsg . "\n";
            }
        }
    }
    
    echo "\n=== HOÀN TẤT ===\n";
    echo "✓ Thành công: {$successCount} câu lệnh\n";
    if ($errorCount > 0) {
        echo "⚠ Lỗi: {$errorCount} câu lệnh\n";
    }
    
    // Kiểm tra xem các bảng đã được tạo chưa
    echo "\n=== KIỂM TRA DATABASE ===\n";
    // Đảm bảo đang sử dụng đúng database
    try {
        $pdo->exec("USE " . DB_NAME);
    } catch (PDOException $e) {
        echo "✗ Không thể sử dụng database: " . $e->getMessage() . "\n";
        exit(1);
    }
    
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "✓ Các bảng đã được tạo:\n";
        foreach ($tables as $table) {
            $count = $pdo->query("SELECT COUNT(*) as cnt FROM `{$table}`")->fetch()['cnt'];
            echo "  - {$table} ({$count} bản ghi)\n";
        }
    } else {
        echo "⚠ Không có bảng nào được tìm thấy\n";
    }
    
} catch (PDOException $e) {
    error_log("Lỗi khi khởi tạo database: " . $e->getMessage());
    echo "✗ Lỗi khi khởi tạo database: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    error_log("Lỗi không mong đợi: " . $e->getMessage());
    echo "✗ Lỗi không mong đợi: " . $e->getMessage() . "\n";
    exit(1);
}

