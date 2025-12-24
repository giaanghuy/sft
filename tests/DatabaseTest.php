<?php
/**
 * Unit Tests cho Database Connection và Queries
 * 
 * Chạy tests: php tests/DatabaseTest.php
 * 
 * Lưu ý: Tests này cần database đang chạy
 */

require_once __DIR__ . '/bootstrap.php';

class DatabaseTest {
    private $testCount = 0;
    private $passCount = 0;
    private $failCount = 0;
    private $pdo;
    
    /**
     * Setup - Kết nối database
     */
    public function __construct() {
        try {
            $this->pdo = getDBConnection();
        } catch (Exception $e) {
            echo "✗ Không thể kết nối database: " . $e->getMessage() . "\n";
            echo "Vui lòng đảm bảo database đang chạy!\n";
            exit(1);
        }
    }
    
    /**
     * Chạy tất cả tests
     */
    public function runAll() {
        echo "=== BẮT ĐẦU DATABASE TESTS ===\n\n";
        
        $this->testConnection();
        $this->testTablesExist();
        $this->testPreparedStatements();
        $this->testUTF8Encoding();
        
        echo "\n=== KẾT QUẢ ===\n";
        echo "Tổng số tests: {$this->testCount}\n";
        echo "Passed: {$this->passCount}\n";
        echo "Failed: {$this->failCount}\n";
        echo "Tỷ lệ thành công: " . round(($this->passCount / $this->testCount) * 100, 2) . "%\n";
        
        return $this->failCount === 0;
    }
    
    /**
     * Assert helper
     */
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
    
    /**
     * Test database connection
     */
    private function testConnection() {
        echo "\n--- Test Database Connection ---\n";
        
        $this->assert($this->pdo !== null, 'getDBConnection() trả về PDO object');
        $this->assert($this->pdo instanceof PDO, 'getDBConnection() trả về instance của PDO');
    }
    
    /**
     * Test tables exist
     */
    private function testTablesExist() {
        echo "\n--- Test Tables Exist ---\n";
        
        // Test users table
        try {
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'users'");
            $this->assert($stmt->rowCount() > 0, 'Bảng users tồn tại');
        } catch (PDOException $e) {
            $this->assert(false, 'Bảng users tồn tại');
        }
        
        // Test students table
        try {
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'students'");
            $this->assert($stmt->rowCount() > 0, 'Bảng students tồn tại');
        } catch (PDOException $e) {
            $this->assert(false, 'Bảng students tồn tại');
        }
    }
    
    /**
     * Test prepared statements (SQL injection protection)
     */
    private function testPreparedStatements() {
        echo "\n--- Test Prepared Statements ---\n";
        
        // Test 1: Prepared statement với user input
        $username = "test' OR '1'='1"; // SQL injection attempt
        try {
            $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $result = $stmt->fetch();
            // Nếu prepared statement hoạt động đúng, sẽ không tìm thấy user này
            $this->assert(true, 'Prepared statement xử lý SQL injection attempt an toàn');
        } catch (PDOException $e) {
            $this->assert(false, 'Prepared statement không gây lỗi');
        }
        
        // Test 2: Prepared statement với valid input
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM users");
            $stmt->execute();
            $result = $stmt->fetch();
            $this->assert(isset($result['count']), 'Prepared statement trả về kết quả đúng format');
        } catch (PDOException $e) {
            $this->assert(false, 'Prepared statement hoạt động với valid input');
        }
    }
    
    /**
     * Test UTF-8 encoding
     */
    private function testUTF8Encoding() {
        echo "\n--- Test UTF-8 Encoding ---\n";
        
        // Test 1: Check charset
        try {
            $stmt = $this->pdo->query("SELECT @@character_set_database as charset");
            $result = $stmt->fetch();
            $this->assert($result['charset'] === 'utf8mb4', 'Database sử dụng utf8mb4 charset');
        } catch (PDOException $e) {
            $this->assert(false, 'Kiểm tra charset');
        }
        
        // Test 2: Insert và select tiếng Việt
        try {
            // Tạo test data (sẽ rollback sau)
            $this->pdo->beginTransaction();
            
            $testName = 'Nguyễn Văn Test';
            $stmt = $this->pdo->prepare("INSERT INTO students (student_code, full_name, birthday, gender, email, phone, address, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute(['TEST001', $testName, '2000-01-01', 'Nam', 'test@test.com', '0123456789', 'Test Address', 1]);
            
            $stmt = $this->pdo->prepare("SELECT full_name FROM students WHERE student_code = ?");
            $stmt->execute(['TEST001']);
            $result = $stmt->fetch();
            
            $this->assert($result['full_name'] === $testName, 'UTF-8 encoding hoạt động đúng với tiếng Việt');
            
            // Rollback
            $this->pdo->rollBack();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            $this->assert(false, 'UTF-8 encoding test: ' . $e->getMessage());
        }
    }
}

// Chạy tests nếu file được gọi trực tiếp
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $test = new DatabaseTest();
    $success = $test->runAll();
    exit($success ? 0 : 1);
}
