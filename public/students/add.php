<?php
/**
 * ============================================================================
 * TRANG THÊM SINH VIÊN MỚI (CHỈ DÀNH CHO ADMIN)
 * ============================================================================
 * 
 * File này cho phép admin thêm sinh viên mới vào hệ thống.
 * 
 * Chức năng:
 * - Form nhập thông tin sinh viên (mã SV, họ tên, ngày sinh, giới tính, email, SĐT, địa chỉ)
 * - Validate dữ liệu đầu vào (client-side và server-side)
 * - Kiểm tra mã sinh viên không trùng lặp
 * - Tự động gán user_id là người tạo (admin)
 * 
 * Security:
 * - Chỉ admin mới được truy cập
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

$pageTitle = 'Thêm Sinh viên';
$error = '';
$success = '';

// Xử lý form thêm sinh viên
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
                $pdo = getDBConnection();
                
                // Kiểm tra mã sinh viên đã tồn tại chưa (sử dụng prepared statement)
                $stmt = $pdo->prepare("SELECT id FROM students WHERE student_code = ? LIMIT 1");
                $stmt->execute([$student_code]);
                if ($stmt->fetch()) {
                    $error = 'Mã sinh viên đã tồn tại!';
                } else {
                    // Thêm sinh viên mới (sử dụng prepared statement để chống SQL injection)
                    $stmt = $pdo->prepare("INSERT INTO students (student_code, full_name, birthday, gender, email, phone, address, user_id) 
                                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$student_code, $full_name, $birthday, $gender, $email, $phone, $address, $_SESSION['user_id']]);
                    
                    // Chuyển hướng về trang chủ với thông báo thành công
                    $_SESSION['success_message'] = 'Thêm sinh viên thành công!';
                    redirect('/index.php');
                }
            } catch (PDOException $e) {
                // Không hiển thị lỗi chi tiết cho user (security best practice)
                error_log("Add student error: " . $e->getMessage());
                $error = 'Có lỗi xảy ra. Vui lòng thử lại sau!';
            }
        }
    }
}
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person-plus"></i> Thêm Sinh viên mới</h5>
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
                
                <form method="POST" action="" id="addStudentForm">
                    <!-- CSRF Token để chống CSRF attack -->
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="student_code" class="form-label">
                                <i class="bi bi-tag"></i> Mã sinh viên <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="student_code" name="student_code" 
                                   value="<?php echo htmlspecialchars($student_code ?? ''); ?>" 
                                   required maxlength="20" pattern="[A-Z0-9]+" 
                                   title="Chỉ cho phép chữ in hoa và số"
                                   placeholder="VD: SV001">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">Họ tên <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?php echo htmlspecialchars($full_name ?? ''); ?>" 
                                   required maxlength="100">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="birthday" class="form-label">Ngày sinh <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="birthday" name="birthday" 
                                   value="<?php echo htmlspecialchars($birthday ?? ''); ?>" 
                                   required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="gender" class="form-label">Giới tính <span class="text-danger">*</span></label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="">-- Chọn giới tính --</option>
                                <option value="Nam" <?php echo (isset($gender) && $gender === 'Nam') ? 'selected' : ''; ?>>Nam</option>
                                <option value="Nữ" <?php echo (isset($gender) && $gender === 'Nữ') ? 'selected' : ''; ?>>Nữ</option>
                                <option value="Khác" <?php echo (isset($gender) && $gender === 'Khác') ? 'selected' : ''; ?>>Khác</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                                   required maxlength="100">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($phone ?? ''); ?>" 
                                   required pattern="[0-9]{10,11}" 
                                   title="Số điện thoại phải có 10-11 chữ số">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Địa chỉ <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="address" name="address" rows="3" 
                                  required><?php echo htmlspecialchars($address ?? ''); ?></textarea>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="/index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="bi bi-check-circle"></i> Thêm sinh viên
                        </button>
                    </div>
                </form>
                
                <script>
                    // Disable submit button khi đang xử lý để tránh double submit
                    document.getElementById('addStudentForm').addEventListener('submit', function(e) {
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

