<?php
/**
 * ============================================================================
 * TRANG ĐĂNG NHẬP
 * ============================================================================
 * 
 * File này xử lý đăng nhập người dùng vào hệ thống.
 * 
 * Chức năng:
 * - Form đăng nhập (username, password)
 * - Xác thực thông tin đăng nhập
 * - Tạo session sau khi đăng nhập thành công
 * - Rate limiting để chống brute force attack
 * 
 * Security:
 * - CSRF protection
 * - Rate limiting (tối đa 5 lần thử, khóa 5 phút)
 * - Password verification bằng password_verify()
 * - Session regeneration sau khi đăng nhập thành công
 * - Sử dụng prepared statements để chống SQL injection
 * 
 * @author github.com/lehuygiang28
 * @version 1.0
 */

require_once __DIR__ . '/../src/config/config.php';

// ===== KIỂM TRA TRẠNG THÁI ĐĂNG NHẬP =====
// Nếu đã đăng nhập, chuyển hướng đến trang chủ
if (isLoggedIn()) {
    redirect('/index.php');
}

$error = '';

// Xử lý form đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kiểm tra CSRF token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!validateCSRFToken($csrf_token)) {
        $error = 'Token bảo mật không hợp lệ. Vui lòng thử lại!';
    } else {
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Kiểm tra rate limiting (chống brute force)
        if (!checkLoginRateLimit($username)) {
            $error = 'Bạn đã đăng nhập sai quá nhiều lần. Vui lòng thử lại sau 5 phút!';
        } elseif (empty($username) || empty($password)) {
            $error = 'Vui lòng điền đầy đủ thông tin!';
        } else {
            try {
                $pdo = getDBConnection();
                // Sử dụng prepared statement để chống SQL injection
                $stmt = $pdo->prepare("SELECT id, username, password, email, role FROM users WHERE username = ? LIMIT 1");
                $stmt->execute([$username]);
                $user = $stmt->fetch();
                
                // Kiểm tra user tồn tại và password đúng
                if ($user && password_verify($password, $user['password'])) {
                    // Reset số lần thử đăng nhập khi thành công
                    resetLoginAttempts($username);
                    
                    // Regenerate session ID để chống session fixation
                    session_regenerate_id(true);
                    
                    // Đăng nhập thành công - lưu thông tin vào session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['last_activity'] = time(); // Track last activity
                    
                    // Chuyển hướng đến trang chủ
                    redirect('/index.php');
                } else {
                    // Tăng số lần thử đăng nhập khi sai
                    incrementLoginAttempts($username);
                    $error = 'Tên đăng nhập hoặc mật khẩu không đúng!';
                }
            } catch (PDOException $e) {
                // Không hiển thị lỗi chi tiết cho user (security best practice)
                error_log("Login error: " . $e->getMessage());
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
    <title>Đăng nhập - Quản lý Sinh viên</title>
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
        
        .login-card {
            max-width: 420px;
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
        
        .form-control {
            border-radius: 0.5rem;
            border: 1px solid #cbd5e1;
            padding: 0.625rem 1rem;
            transition: all 0.2s ease;
        }
        
        .form-control:focus {
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
            <div class="col-md-5">
                <div class="card login-card">
                    <div class="card-header bg-primary text-white text-center">
                        <h4><i class="bi bi-box-arrow-in-right"></i> Đăng nhập</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle"></i> <?php echo $error; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" id="loginForm">
                            <!-- CSRF Token để chống CSRF attack -->
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="bi bi-person"></i> Tên đăng nhập
                                </label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($username ?? ''); ?>" 
                                       required autofocus autocomplete="username"
                                       maxlength="50" pattern="[a-zA-Z0-9_]+" 
                                       title="Chỉ cho phép chữ cái, số và dấu gạch dưới">
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock"></i> Mật khẩu
                                </label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       required autocomplete="current-password"
                                       minlength="6">
                                <div class="form-text">Tối thiểu 6 ký tự</div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg" id="loginBtn">
                                    <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
                                </button>
                                <a href="/register.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-person-plus"></i> Chưa có tài khoản? Đăng ký
                                </a>
                            </div>
                        </form>
                        
                        <script>
                            // Disable submit button khi đang xử lý để tránh double submit
                            document.getElementById('loginForm').addEventListener('submit', function(e) {
                                const btn = document.getElementById('loginBtn');
                                btn.disabled = true;
                                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...';
                            });
                        </script>
                        
                        <hr>
                        <div class="text-center">
                            <small class="text-muted">
                                <strong>Tài khoản mẫu:</strong><br>
                                Admin: admin / admin123<br>
                                User: user1 / user123<br>
                                User: user2 / user123
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS 5.3.8 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>

