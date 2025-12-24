<?php
/**
 * ============================================================================
 * TRANG SỬA THÔNG TIN SINH VIÊN (CHỈ DÀNH CHO ADMIN)
 * ============================================================================
 * 
 * File này cho phép admin sửa thông tin sinh viên đã có trong hệ thống.
 * 
 * Chức năng:
 * - Hiển thị form với thông tin hiện tại của sinh viên
 * - Cho phép cập nhật tất cả thông tin (trừ user_id)
 * - Validate dữ liệu đầu vào
 * - Kiểm tra mã sinh viên không trùng lặp (trừ chính nó)
 * 
 * Security:
 * - Chỉ admin mới được truy cập
 * - Validate ID hợp lệ
 * - Kiểm tra sinh viên tồn tại
 * - CSRF protection
 * - Input validation và sanitization
 * - Sử dụng prepared statements để chống SQL injection
 * 
 * @author github.com/lehuygiang28
 * @version 1.0
 */

require_once __DIR__ . '/../../src/config/config.php';
require_once __DIR__ . '/../../src/includes/header.php';

// ===== KIỂM TRA QUYỀN TRUY CẬP =====
// Chỉ admin mới được truy cập trang này
if (!isAdmin()) {
    $_SESSION['error_message'] = 'Bạn không có quyền truy cập trang này!';
    redirect('/index.php');
}

$pageTitle = 'Sửa thông tin Sinh viên';
$error = '';
$success = '';
$pdo = getDBConnection();

// Lấy ID sinh viên từ URL
$student_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($student_id <= 0) {
    redirect('/index.php');
}

// Lấy thông tin sinh viên hiện tại
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    redirect('/index.php');
}

// Xử lý form sửa sinh viên
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra CSRF token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($csrf_token)) {
        $error = 'Token bảo mật không hợp lệ. Vui lòng thử lại!';
    } else {
        // Làm sạch và validate input
        $student_code = sanitize($_POST['student_code'] ?? '');
        $full_name = sanitize($_POST['full_name'] ?? '');
        $birthday = $_POST['birthday'] ?? '';
        $gender = sanitize($_POST['gender'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        
        // Validate dữ liệu đầu vào
        if (empty($student_code) || empty($full_name) || empty($birthday) || 
            empty($gender) || empty($email) || empty($phone) || empty($address)) {
            $error = 'Vui lòng điền đầy đủ thông tin!';
        } elseif (strlen($student_code) > 20) {
            $error = 'Mã sinh viên không được vượt quá 20 ký tự!';
        } elseif (strlen($full_name) > 100) {
            $error = 'Họ tên không được vượt quá 100 ký tự!';
        } elseif (!validateEmail($email)) {
            $error = 'Email không hợp lệ!';
        } elseif (!validatePhone($phone)) {
            $error = 'Số điện thoại không hợp lệ! (10-11 chữ số)';
        } elseif (!in_array($gender, ['Nam', 'Nữ', 'Khác'])) {
            $error = 'Giới tính không hợp lệ!';
        } else {
            try {
                // Kiểm tra mã sinh viên đã tồn tại chưa (trừ chính nó) - sử dụng prepared statement
                $stmt = $pdo->prepare("SELECT id FROM students WHERE student_code = ? AND id != ? LIMIT 1");
                $stmt->execute([$student_code, $student_id]);
                if ($stmt->fetch()) {
                    $error = 'Mã sinh viên đã tồn tại!';
                } else {
                    // Cập nhật thông tin sinh viên (sử dụng prepared statement để chống SQL injection)
                    $stmt = $pdo->prepare("UPDATE students SET student_code = ?, full_name = ?, birthday = ?, 
                                          gender = ?, email = ?, phone = ?, address = ? WHERE id = ?");
                    $stmt->execute([$student_code, $full_name, $birthday, $gender, $email, $phone, $address, $student_id]);
                    
                    // Chuyển hướng về trang chủ với thông báo thành công
                    $_SESSION['success_message'] = 'Cập nhật thông tin sinh viên thành công!';
                    redirect('/index.php');
                }
            } catch (PDOException $e) {
                // Không hiển thị lỗi chi tiết cho user (security best practice)
                error_log("Edit student error: " . $e->getMessage());
                $error = 'Có lỗi xảy ra. Vui lòng thử lại sau!';
            }
        }
    }
}
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-pencil"></i> Sửa thông tin Sinh viên</h5>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="editStudentForm">
                    <!-- CSRF Token để chống CSRF attack -->
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="student_code" class="form-label">
                                <i class="bi bi-tag"></i> Mã sinh viên <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="student_code" name="student_code" 
                                   value="<?php echo htmlspecialchars($student['student_code']); ?>" 
                                   required maxlength="20" pattern="[A-Z0-9]+"
                                   title="Chỉ cho phép chữ in hoa và số">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">Họ tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?php echo htmlspecialchars($student['full_name']); ?>" 
                                   required maxlength="100">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="birthday" class="form-label">Ngày sinh <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="birthday" name="birthday" 
                                   value="<?php echo htmlspecialchars($student['birthday']); ?>" 
                                   required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="gender" class="form-label">Giới tính <span class="text-danger">*</span></label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="">-- Chọn giới tính --</option>
                                <option value="Nam" <?php echo ($student['gender'] === 'Nam') ? 'selected' : ''; ?>>Nam</option>
                                <option value="Nữ" <?php echo ($student['gender'] === 'Nữ') ? 'selected' : ''; ?>>Nữ</option>
                                <option value="Khác" <?php echo ($student['gender'] === 'Khác') ? 'selected' : ''; ?>>Khác</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($student['email']); ?>" 
                                   required maxlength="100">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($student['phone']); ?>" 
                                   required pattern="[0-9]{10,11}" 
                                   title="Số điện thoại phải có 10-11 chữ số">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Địa chỉ <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="address" name="address" rows="3" 
                                  required><?php echo htmlspecialchars($student['address']); ?></textarea>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="/index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Quay lại
                        </a>
                        <button type="submit" class="btn btn-warning" id="submitBtn">
                            <i class="bi bi-check-circle"></i> Cập nhật
                        </button>
                    </div>
                </form>
                
                <script>
                    // Disable submit button khi đang xử lý để tránh double submit
                    document.getElementById('editStudentForm').addEventListener('submit', function(e) {
                        const btn = document.getElementById('submitBtn');
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';
                    });
                </script>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../src/includes/footer.php'; ?>

