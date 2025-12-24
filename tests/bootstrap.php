<?php
/**
 * Bootstrap file cho PHPUnit tests
 * 
 * File này được sử dụng để cấu hình môi trường test
 */

// Đường dẫn đến thư mục gốc của project
define('TEST_ROOT', __DIR__);
define('PROJECT_ROOT', dirname(__DIR__));

// Include autoloader nếu có, hoặc include trực tiếp config
require_once PROJECT_ROOT . '/src/config/config.php';

// Mock session cho testing
if (!isset($_SESSION)) {
    $_SESSION = [];
}

// Set test environment
ini_set('display_errors', 1);
error_reporting(E_ALL);
