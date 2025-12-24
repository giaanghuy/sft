<?php
/**
 * ============================================================================
 * FILE XỬ LÝ ĐĂNG XUẤT
 * ============================================================================
 * 
 * File này xử lý việc đăng xuất người dùng khỏi hệ thống.
 * 
 * Chức năng:
 * - Xóa tất cả dữ liệu session
 * - Hủy session
 * - Chuyển hướng về trang đăng nhập
 * 
 * Security:
 * - Regenerate session ID sau khi logout để tránh session fixation
 * - Xóa tất cả session data
 * 
 * @author github.com/lehuygiang28
 * @version 1.0
 */

require_once __DIR__ . '/../../src/config/config.php';

// ===== XỬ LÝ ĐĂNG XUẤT =====
// Xóa tất cả dữ liệu session
session_unset();

// Regenerate session ID để tránh session fixation
session_regenerate_id(true);

// Hủy session
session_destroy();

// Chuyển hướng đến trang đăng nhập
redirect('/login.php');

