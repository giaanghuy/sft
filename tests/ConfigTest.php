<?php
/**
 * Unit Tests cho các hàm trong config.php
 * 
 * Chạy tests: php tests/ConfigTest.php
 * Hoặc với PHPUnit: phpunit tests/ConfigTest.php
 */

require_once __DIR__ . '/bootstrap.php';

class ConfigTest {
    private $testCount = 0;
    private $passCount = 0;
    private $failCount = 0;
    
    /**
     * Chạy tất cả tests
     */
    public function runAll() {
        echo "=== BẮT ĐẦU UNIT TESTS ===\n\n";
        
        $this->testSanitize();
        $this->testValidateEmail();
        $this->testValidatePhone();
        $this->testFormatDate();
        $this->testGenerateCSRFToken();
        $this->testValidateCSRFToken();
        
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
     * Test hàm sanitize()
     */
    private function testSanitize() {
        echo "\n--- Test sanitize() ---\n";
        
        // Test 1: XSS protection
        // sanitize() sử dụng strip_tags() trước, sau đó htmlspecialchars()
        // strip_tags() sẽ loại bỏ <script> tags, còn lại "alert("XSS")Hello"
        // htmlspecialchars() sẽ escape quotes thành &quot;
        $input = '<script>alert("XSS")</script>Hello';
        $result = sanitize($input);
        // Kết quả: strip_tags loại bỏ <script>, còn "alert("XSS")Hello", sau đó htmlspecialchars escape quotes
        $this->assert(strpos($result, '<script>') === false && strpos($result, 'Hello') !== false, 'sanitize() loại bỏ script tags và giữ lại text');
        
        // Test 2: HTML entities
        $input = '<b>Bold</b> & "quotes"';
        $result = sanitize($input);
        $this->assert(strpos($result, '<b>') === false, 'sanitize() loại bỏ HTML tags');
        
        // Test 3: Trim whitespace
        $input = '  Hello World  ';
        $result = sanitize($input);
        $this->assert($result === 'Hello World', 'sanitize() trim whitespace');
        
        // Test 4: Empty string
        $input = '';
        $result = sanitize($input);
        $this->assert($result === '', 'sanitize() xử lý empty string');
    }
    
    /**
     * Test hàm validateEmail()
     */
    private function testValidateEmail() {
        echo "\n--- Test validateEmail() ---\n";
        
        // Test 1: Valid email
        $this->assert(validateEmail('test@example.com') === true, 'validateEmail() chấp nhận email hợp lệ');
        
        // Test 2: Invalid email
        $this->assert(validateEmail('invalid-email') === false, 'validateEmail() từ chối email không hợp lệ');
        
        // Test 3: Email với subdomain
        $this->assert(validateEmail('user@mail.example.com') === true, 'validateEmail() chấp nhận email với subdomain');
        
        // Test 4: Empty email
        $this->assert(validateEmail('') === false, 'validateEmail() từ chối email rỗng');
    }
    
    /**
     * Test hàm validatePhone()
     */
    private function testValidatePhone() {
        echo "\n--- Test validatePhone() ---\n";
        
        // Test 1: Valid 10 digits
        $this->assert(validatePhone('0123456789') === true, 'validatePhone() chấp nhận số 10 chữ số');
        
        // Test 2: Valid 11 digits
        $this->assert(validatePhone('09123456789') === true, 'validatePhone() chấp nhận số 11 chữ số');
        
        // Test 3: Invalid - too short
        $this->assert(validatePhone('123456789') === false, 'validatePhone() từ chối số quá ngắn');
        
        // Test 4: Invalid - too long
        $this->assert(validatePhone('012345678901') === false, 'validatePhone() từ chối số quá dài');
        
        // Test 5: Invalid - contains letters
        $this->assert(validatePhone('012345678a') === false, 'validatePhone() từ chối số có chữ cái');
    }
    
    /**
     * Test hàm formatDate()
     */
    private function testFormatDate() {
        echo "\n--- Test formatDate() ---\n";
        
        // Test 1: Default format
        $date = '2000-01-15';
        $result = formatDate($date);
        $this->assert($result === '15/01/2000', 'formatDate() format đúng định dạng mặc định');
        
        // Test 2: Custom format
        $date = '2000-01-15';
        $result = formatDate($date, 'Y-m-d');
        $this->assert($result === '2000-01-15', 'formatDate() format đúng định dạng tùy chỉnh');
        
        // Test 3: Empty date
        $result = formatDate('');
        $this->assert($result === '', 'formatDate() trả về empty string cho date rỗng');
        
        // Test 4: DateTime format
        $date = '2000-01-15 10:30:00';
        $result = formatDate($date, 'd/m/Y H:i');
        $this->assert($result === '15/01/2000 10:30', 'formatDate() format datetime đúng');
    }
    
    /**
     * Test hàm generateCSRFToken()
     */
    private function testGenerateCSRFToken() {
        echo "\n--- Test generateCSRFToken() ---\n";
        
        // Test 1: Generate token
        $token1 = generateCSRFToken();
        $this->assert(!empty($token1), 'generateCSRFToken() tạo token không rỗng');
        
        // Test 2: Token length (hex của 32 bytes = 64 chars)
        $this->assert(strlen($token1) === 64, 'generateCSRFToken() tạo token đúng độ dài (64 chars)');
        
        // Test 3: Same token on second call
        $token2 = generateCSRFToken();
        $this->assert($token1 === $token2, 'generateCSRFToken() trả về cùng token trong cùng session');
        
        // Test 4: Token format (hex)
        $this->assert(ctype_xdigit($token1), 'generateCSRFToken() tạo token đúng format hex');
    }
    
    /**
     * Test hàm validateCSRFToken()
     */
    private function testValidateCSRFToken() {
        echo "\n--- Test validateCSRFToken() ---\n";
        
        // Generate token first
        $token = generateCSRFToken();
        
        // Test 1: Valid token
        $this->assert(validateCSRFToken($token) === true, 'validateCSRFToken() xác thực token hợp lệ');
        
        // Test 2: Invalid token
        $this->assert(validateCSRFToken('invalid_token') === false, 'validateCSRFToken() từ chối token không hợp lệ');
        
        // Test 3: Empty token
        $this->assert(validateCSRFToken('') === false, 'validateCSRFToken() từ chối token rỗng');
    }
}

// Chạy tests nếu file được gọi trực tiếp
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    $test = new ConfigTest();
    $success = $test->runAll();
    exit($success ? 0 : 1);
}

