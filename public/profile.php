<?php
/**
 * ============================================================================
 * TRANG THÔNG TIN CÁ NHÂN
 * ============================================================================
 * 
 * File này hiển thị thông tin cá nhân của người dùng đang đăng nhập.
 * 
 * Chức năng:
 * - Hiển thị thông tin tài khoản (username, email, role, ngày tạo)
 * - Hiển thị số lượng sinh viên đã tạo (nếu là user thường)
 * - Hiển thị thống kê (nếu là admin)
 * 
 * Security:
 * - Chỉ hiển thị thông tin của chính người dùng đang đăng nhập
 * - Sử dụng prepared statements để chống SQL injection
 * - Output được escape để chống XSS
 * 
 * @author github.com/lehuygiang28
 * @version 1.0
 */

require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../src/includes/header.php';

$pageTitle = 'Thông tin cá nhân';
$pdo = getDBConnection();

// Lấy thông tin người dùng hiện tại
$stmt = $pdo->prepare("SELECT id, username, email, role, created_at FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error_message'] = 'Không tìm thấy thông tin người dùng!';
    redirect('/index.php');
}

// Đếm số lượng sinh viên (chỉ cho admin)
$studentCount = 0;
$totalUsers = 0;
if (isAdmin()) {
    // Admin: đếm tổng số sinh viên
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM students");
    $result = $stmt->fetch();
    $studentCount = $result['count'];
    
    // Đếm tổng số users
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCountResult = $stmt->fetch();
    $totalUsers = $userCountResult['count'];
}
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <!-- Thông tin tài khoản -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person-circle"></i> Thông tin tài khoản</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong><i class="bi bi-person"></i> Tên đăng nhập:</strong>
                    </div>
                    <div class="col-sm-8">
                        <?php echo htmlspecialchars($user['username']); ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong><i class="bi bi-envelope"></i> Email:</strong>
                    </div>
                    <div class="col-sm-8">
                        <?php echo htmlspecialchars($user['email']); ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong><i class="bi bi-shield-check"></i> Vai trò:</strong>
                    </div>
                    <div class="col-sm-8">
                        <?php if ($user['role'] === 'admin'): ?>
                            <span class="badge bg-warning text-dark">Admin</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">User</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong><i class="bi bi-calendar"></i> Ngày tạo tài khoản:</strong>
                    </div>
                    <div class="col-sm-8">
                        <?php echo formatDate($user['created_at'], 'd/m/Y H:i:s'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thống kê (chỉ Admin) -->
        <?php if (isAdmin()): ?>
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Thống kê</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h3 class="mb-0"><?php echo $studentCount; ?></h3>
                                <p class="mb-0"><i class="bi bi-people"></i> Tổng số sinh viên</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h3 class="mb-0"><?php echo $totalUsers; ?></h3>
                                <p class="mb-0"><i class="bi bi-person-badge"></i> Tổng số người dùng</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Nút quay lại -->
        <div class="mt-3 text-center">
            <a href="/index.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại trang chủ
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../src/includes/footer.php'; ?>

