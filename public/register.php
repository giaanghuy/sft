<?php
/**
 * ============================================================================
 * TRANG ĐĂNG KÝ TÀI KHOẢN MỚI
 * ============================================================================
 * 
 * File này xử lý đăng ký tài khoản mới cho người dùng.
 * 
 * Chức năng:
 * - Form đăng ký (username, email, password, confirm_password, role)
 * - Validate dữ liệu đầu vào
 * - Kiểm tra username và email không trùng lặp
 * - Mã hóa password bằng password_hash()
 * - Tạo tài khoản mới trong database
 * 
 * Security:
 * - CSRF protection
 * - Input validation và sanitization
 * - Password strength validation (tối thiểu 6 ký tự)
 * - Username pattern validation (chỉ chữ cái, số, dấu gạch dưới)
 * - Email validation
 * - Sử dụng prepared statements để chống SQL injection
 * 
 * @author github.com/lehuygiang28
 * @version 1.0
 */

require_once __DIR__ . '/../src/config/config.php';

$error = '';
$success = '';

// Xử lý form đăng ký
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra CSRF token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($csrf_token)) {
        $error = 'Token bảo mật không hợp lệ. Vui lòng thử lại!';
    } else {
        $username = sanitize($_POST['username'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $role = sanitize($_POST['role'] ?? 'user');
        
        // Validate dữ liệu đầu vào
        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            $error = 'Vui lòng điền đầy đủ thông tin!';
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            $error = 'Tên đăng nhập phải có từ 3-50 ký tự!';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $error = 'Tên đăng nhập chỉ được chứa chữ cái, số và dấu gạch dưới!';
        } elseif (!validateEmail($email)) {
            $error = 'Email không hợp lệ!';
        } elseif (strlen($password) < 6) {
            $error = 'Mật khẩu phải có ít nhất 6 ký tự!';
        } elseif ($password !== $confirm_password) {
            $error = 'Mật khẩu xác nhận không khớp!';
        } elseif (!in_array($role, ['admin', 'user'])) {
            $error = 'Vai trò không hợp lệ!';
        } else {
            try {
                $pdo = getDBConnection();
                
                // Kiểm tra username đã tồn tại chưa (sử dụng prepared statement)
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    $error = 'Tên đăng nhập đã tồn tại!';
                } else {
                    // Kiểm tra email đã tồn tại chưa
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        $error = 'Email đã được sử dụng!';
                    } else {
                        // Mã hóa mật khẩu bằng password_hash (bcrypt)
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Insert user mới (sử dụng prepared statement để chống SQL injection)
                        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$username, $hashedPassword, $email, $role]);
                        
                        $success = 'Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.';
                        // Xóa dữ liệu form sau khi đăng ký thành công
                        $username = $email = '';
                    }
                }
            } catch (PDOException $e) {
                // Không hiển thị lỗi chi tiết cho user (security best practice)
                error_log("Registration error: " . $e->getMessage());
                $error = 'Có lỗi xảy ra. Vui lòng thử lại sau!';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Quản lý Sinh viên</title>
    <!-- Bootstrap CSS 5.3.8 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --bs-primary: #2563eb;
            --bs-primary-dark: #1e40af;
            --gradient-primary: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        }
        
        body {
            background: #f8fafc;
            background-image: 
                radial-gradient(at 0% 0%, rgba(37, 99, 235, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(8, 145, 178, 0.05) 0px, transparent 50%);
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        
        .register-card {
            max-width: 550px;
            width: 100%;
            box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.1), 0 2px 4px -1px rgba(15, 23, 42, 0.06);
            border-radius: 1rem;
            overflow: hidden;
            animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            background: #ffffff;
            border: 1px solid #e2e8f0;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .card-header {
            background: var(--gradient-primary) !important;
            color: white;
            font-weight: 600;
            padding: 1.75rem 1.5rem;
            border-bottom: none;
            text-align: center;
        }
        
        .card-header h4 {
            margin: 0;
            font-size: 1.375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }
        
        .card-header i {
            font-size: 1.5rem;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .form-control, .form-select {
            border-radius: 0.5rem;
            border: 1px solid #cbd5e1;
            padding: 0.625rem 1rem;
            transition: all 0.2s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15);
        }
        
        .btn-primary {
            background: var(--bs-primary);
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        
        .btn-primary:hover {
            background: var(--bs-primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
        }
        
        .btn-outline-secondary {
            border-radius: 0.5rem;
            border: 1px solid #cbd5e1;
            transition: all 0.2s ease;
        }
        
        .btn-outline-secondary:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
        }
        
        .alert {
            border-radius: 0.5rem;
            border: none;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card register-card">
                    <div class="card-header bg-primary text-white text-center">
                        <h4><i class="bi bi-person-plus"></i> Đăng ký tài khoản</h4>
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
                        
                        <form method="POST" action="" id="registerForm">
                            <!-- CSRF Token để chống CSRF attack -->
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="bi bi-person"></i> Tên đăng nhập <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($username ?? ''); ?>" 
                                       required minlength="3" maxlength="50" 
                                       pattern="[a-zA-Z0-9_]+" autocomplete="username"
                                       title="Chỉ cho phép chữ cái, số và dấu gạch dưới">
                                <div class="form-text">Tối thiểu 3 ký tự, tối đa 50 ký tự. Chỉ chứa chữ cái, số và dấu gạch dưới</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($email ?? ''); ?>" 
                                       required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       required minlength="6">
                                <div class="form-text">Tối thiểu 6 ký tự</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       required minlength="6">
                            </div>
                            
                            <div class="mb-3">
                                <label for="role" class="form-label">Vai trò <span class="text-danger">*</span></label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="user" <?php echo (isset($role) && $role === 'user') ? 'selected' : ''; ?>>User</option>
                                    <option value="admin" <?php echo (isset($role) && $role === 'admin') ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg" id="registerBtn">
                                    <i class="bi bi-person-plus"></i> Đăng ký
                                </button>
                                <a href="/login.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-box-arrow-in-right"></i> Đã có tài khoản? Đăng nhập
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS 5.3.8 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script>
        // Validate form phía client và disable button khi submit
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const btn = document.getElementById('registerBtn');
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Mật khẩu xác nhận không khớp!');
                return false;
            }
            
            // Disable button khi đang xử lý để tránh double submit
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';
        });
        
        // Real-time password match validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const feedback = this.parentElement.querySelector('.invalid-feedback');
            
            if (confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Mật khẩu xác nhận không khớp!');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });
    </script>
</body>
</html>

