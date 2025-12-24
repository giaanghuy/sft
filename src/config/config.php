<?php
/**
 * File cấu hình kết nối database và các thiết lập chung
 * 
 * @author github.com/lehuygiang28
 * @version 1.0
 */

// Set default charset cho PHP
ini_set('default_charset', 'UTF-8');
mb_internal_encoding('UTF-8');
// mb_http_output đã deprecated trong PHP 8.1+, chỉ set khi function tồn tại
if (function_exists('mb_http_output')) {
    @mb_http_output('UTF-8');
}

// Bật output buffering để tránh lỗi "headers already sent"
// Chỉ bật khi chạy từ web, không phải CLI
if (php_sapi_name() !== 'cli' && !ob_get_level()) {
    ob_start();
}

// Set HTTP header cho UTF-8 (chỉ khi chạy từ web, không phải CLI)
// Chỉ set header nếu chưa có output và chưa có redirect
if (php_sapi_name() !== 'cli' && !headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}

// Bắt đầu session nếu chưa có (chỉ khi chạy từ web, không phải CLI)
if (php_sapi_name() !== 'cli' && session_status() === PHP_SESSION_NONE) {
    // Cấu hình session security
    ini_set('session.cookie_httponly', 1); // Chống XSS qua cookie
    ini_set('session.use_only_cookies', 1); // Chỉ dùng cookie, không dùng URL
    ini_set('session.cookie_secure', 0); // Set 1 nếu dùng HTTPS
    
    session_start();
    
    // Regenerate session ID định kỳ để chống session fixation
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } elseif (time() - $_SESSION['created'] > 1800) { // 30 phút
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

// Đường dẫn gốc của ứng dụng
define('BASE_PATH', dirname(__DIR__, 2));
define('SRC_PATH', BASE_PATH . '/src');

// Cấu hình database
// Hỗ trợ environment variables khi chạy trong Docker
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'student_management');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

/**
 * Kết nối database sử dụng PDO
 * @return PDO|null Đối tượng PDO hoặc null nếu lỗi
 */
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        
        // Đảm bảo charset UTF-8 cho kết nối
        $pdo->exec("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'");
        $pdo->exec("SET CHARACTER SET utf8mb4");
        $pdo->exec("SET character_set_connection=utf8mb4");
        
        return $pdo;
    } catch (PDOException $e) {
        die("Lỗi kết nối database: " . $e->getMessage());
    }
}

/**
 * Kiểm tra người dùng đã đăng nhập chưa
 * @return bool True nếu đã đăng nhập, false nếu chưa
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Kiểm tra người dùng có phải admin không
 * @return bool True nếu là admin, false nếu không
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Chuyển hướng đến trang khác
 * @param string $url URL cần chuyển hướng đến (có thể là đường dẫn tuyệt đối hoặc tương đối)
 */
function redirect($url) {
    // Xóa output buffer nếu có để đảm bảo redirect hoạt động
    if (ob_get_level()) {
        ob_clean();
    }
    
    // Kiểm tra headers đã được gửi chưa
    if (headers_sent()) {
        // Nếu headers đã được gửi, sử dụng JavaScript redirect
        echo '<script>window.location.href = "' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '";</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"></noscript>';
        exit();
    }
    
    // Nếu URL không bắt đầu bằng http:// hoặc https://, thêm base path
    if (!preg_match('/^https?:\/\//', $url) && strpos($url, '/') === 0) {
        // Đường dẫn tuyệt đối từ root
        header("Location: " . $url);
    } else {
        header("Location: " . $url);
    }
    exit();
}

/**
 * Làm sạch dữ liệu đầu vào để tránh XSS
 * @param string $data Dữ liệu cần làm sạch
 * @return string Dữ liệu đã được làm sạch
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 * @param string $email Email cần kiểm tra
 * @return bool True nếu hợp lệ, false nếu không
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate số điện thoại (đơn giản - chỉ kiểm tra độ dài và ký tự số)
 * @param string $phone Số điện thoại cần kiểm tra
 * @return bool True nếu hợp lệ, false nếu không
 */
function validatePhone($phone) {
    // Loại bỏ whitespace trước khi validate
    $phone = trim($phone);
    // Kiểm tra pattern: 10-11 chữ số
    return preg_match('/^[0-9]{10,11}$/', $phone) === 1;
}

/**
 * Tạo CSRF token và lưu vào session
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Kiểm tra CSRF token
 * @param string $token Token cần kiểm tra
 * @return bool True nếu hợp lệ, false nếu không
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Kiểm tra rate limiting cho login (chống brute force)
 * @param string $username Username đang đăng nhập
 * @return bool True nếu được phép đăng nhập, false nếu bị giới hạn
 */
function checkLoginRateLimit($username) {
    $key = 'login_attempts_' . md5($username);
    $maxAttempts = 5; // Số lần thử tối đa
    $lockoutTime = 300; // Thời gian khóa (5 phút)
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'last_attempt' => time()];
    }
    
    $attempts = &$_SESSION[$key];
    
    // Reset nếu đã hết thời gian khóa
    if (time() - $attempts['last_attempt'] > $lockoutTime) {
        $attempts = ['count' => 0, 'last_attempt' => time()];
    }
    
    // Kiểm tra số lần thử
    if ($attempts['count'] >= $maxAttempts) {
        return false;
    }
    
    return true;
}

/**
 * Tăng số lần thử đăng nhập
 * @param string $username Username đang đăng nhập
 */
function incrementLoginAttempts($username) {
    $key = 'login_attempts_' . md5($username);
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'last_attempt' => time()];
    }
    
    $_SESSION[$key]['count']++;
    $_SESSION[$key]['last_attempt'] = time();
}

/**
 * Reset số lần thử đăng nhập khi đăng nhập thành công
 * @param string $username Username đã đăng nhập thành công
 */
function resetLoginAttempts($username) {
    $key = 'login_attempts_' . md5($username);
    unset($_SESSION[$key]);
}

/**
 * Làm sạch và validate input cho database (chống SQL injection)
 * Chú ý: Vẫn cần dùng prepared statements, hàm này chỉ để làm sạch thêm
 * @param mixed $data Dữ liệu cần làm sạch
 * @return mixed Dữ liệu đã được làm sạch
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return trim(strip_tags($data));
}

/**
 * Format ngày tháng theo định dạng Việt Nam
 * @param string $date Ngày tháng cần format
 * @param string $format Định dạng (mặc định: d/m/Y)
 * @return string Ngày tháng đã format
 */
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) {
        return '';
    }
    return date($format, strtotime($date));
}

/**
 * Tạo pagination links
 * @param int $currentPage Trang hiện tại
 * @param int $totalPages Tổng số trang
 * @param string $baseUrl URL cơ sở (không có query string)
 * @param array $queryParams Các tham số query string cần giữ lại
 * @return string HTML của pagination
 */
function generatePagination($currentPage, $totalPages, $baseUrl, $queryParams = []) {
    if ($totalPages <= 1) {
        return '';
    }
    
    // Xây dựng query string
    $queryString = '';
    if (!empty($queryParams)) {
        $queryString = '&' . http_build_query($queryParams);
    }
    
    $html = '<nav aria-label="Page navigation" class="d-inline-block">';
    $html .= '<ul class="pagination pagination-sm mb-0">';
    
    // Nút Previous
    if ($currentPage > 1) {
        $prevPage = $currentPage - 1;
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . $baseUrl . '?page=' . $prevPage . $queryString . '">';
        $html .= '<i class="bi bi-chevron-left"></i> Trước</a></li>';
    } else {
        $html .= '<li class="page-item disabled">';
        $html .= '<span class="page-link"><i class="bi bi-chevron-left"></i> Trước</span></li>';
    }
    
    // Hiển thị các số trang
    $startPage = max(1, $currentPage - 2);
    $endPage = min($totalPages, $currentPage + 2);
    
    if ($startPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=1' . $queryString . '">1</a></li>';
        if ($startPage > 2) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    for ($i = $startPage; $i <= $endPage; $i++) {
        if ($i == $currentPage) {
            $html .= '<li class="page-item active">';
            $html .= '<span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . $baseUrl . '?page=' . $i . $queryString . '">' . $i . '</a></li>';
        }
    }
    
    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . $totalPages . $queryString . '">' . $totalPages . '</a></li>';
    }
    
    // Nút Next
    if ($currentPage < $totalPages) {
        $nextPage = $currentPage + 1;
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . $baseUrl . '?page=' . $nextPage . $queryString . '">';
        $html .= 'Sau <i class="bi bi-chevron-right"></i></a></li>';
    } else {
        $html .= '<li class="page-item disabled">';
        $html .= '<span class="page-link">Sau <i class="bi bi-chevron-right"></i></span></li>';
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}

