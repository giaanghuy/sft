<?php
/**
 * ============================================================================
 * FILE XỬ LÝ XÓA SINH VIÊN (CHỈ DÀNH CHO ADMIN)
 * ============================================================================
 * 
 * File này xử lý việc xóa sinh viên khỏi hệ thống.
 * 
 * Chức năng:
 * - Xóa sinh viên theo ID từ URL parameter
 * - Kiểm tra sinh viên tồn tại trước khi xóa
 * - Hiển thị thông báo thành công/lỗi
 * 
 * Security:
 * - Chỉ admin mới được truy cập
 * - Validate ID hợp lệ
 * - Kiểm tra sinh viên tồn tại
 * - Sử dụng prepared statements để chống SQL injection
 * - Foreign key constraint sẽ tự động xử lý các bản ghi liên quan
 * 
 * Lưu ý: File này không có UI, chỉ xử lý logic và redirect
 * 
 * @author github.com/lehuygiang28
 * @version 1.0
 */

require_once __DIR__ . '/../../src/config/config.php';

// ===== KIỂM TRA QUYỀN TRUY CẬP =====
// Chỉ admin mới được truy cập
if (!isAdmin()) {
    $_SESSION['error_message'] = 'Bạn không có quyền thực hiện thao tác này!';
    redirect('/index.php');
}

// Lấy ID sinh viên từ URL và validate
$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validate ID hợp lệ
if ($student_id <= 0) {
    $_SESSION['error_message'] = 'ID sinh viên không hợp lệ!';
    redirect('/index.php');
}

try {
    $pdo = getDBConnection();
    
    // Kiểm tra sinh viên có tồn tại không (sử dụng prepared statement)
    $stmt = $pdo->prepare("SELECT id FROM students WHERE id = ? LIMIT 1");
    $stmt->execute([$student_id]);
    
    if ($stmt->fetch()) {
        // Xóa sinh viên (sử dụng prepared statement để chống SQL injection)
        // Foreign key constraint sẽ tự động xử lý các bản ghi liên quan nếu có
        $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
        $stmt->execute([$student_id]);
        
        // Chuyển hướng về trang chủ với thông báo thành công
        $_SESSION['success_message'] = 'Xóa sinh viên thành công!';
    } else {
        $_SESSION['error_message'] = 'Không tìm thấy sinh viên!';
    }
} catch (PDOException $e) {
    // Không hiển thị lỗi chi tiết cho user (security best practice)
    error_log("Delete student error: " . $e->getMessage());
    $_SESSION['error_message'] = 'Có lỗi xảy ra. Vui lòng thử lại sau!';
}

redirect('/index.php');

