<?php
/**
 * ============================================================================
 * TRANG QUẢN LÝ NGƯỜI DÙNG (CHỈ DÀNH CHO ADMIN)
 * ============================================================================
 * 
 * File này hiển thị danh sách tất cả người dùng trong hệ thống.
 * Chỉ admin mới có quyền truy cập trang này.
 * 
 * Security:
 * - Kiểm tra quyền admin trước khi hiển thị
 * - Sử dụng prepared statements để chống SQL injection
 * - Output được escape để chống XSS
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

$pageTitle = 'Quản lý Người dùng';
$pdo = getDBConnection();

// ===== LẤY DANH SÁCH NGƯỜI DÙNG =====
// Sử dụng prepared statement để chống SQL injection
// Không cần parameters vì query không có user input
$sql = "SELECT id, username, email, role, created_at 
        FROM users 
        ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-people"></i> Danh sách Người dùng</h5>
            </div>
            <div class="card-body">
                <?php if (count($users) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên đăng nhập</th>
                                <th>Email</th>
                                <th>Vai trò</th>
                                <th>Ngày tạo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><strong style="color: var(--bs-primary);"><?php echo htmlspecialchars($user['username']); ?></strong></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php if ($user['role'] === 'admin'): ?>
                                        <span class="badge bg-warning text-dark">Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">User</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo formatDate($user['created_at'], 'd/m/Y H:i'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <p class="text-muted">
                        <i class="bi bi-info-circle"></i> Tổng số: <strong><?php echo count($users); ?></strong> người dùng
                    </p>
                </div>
                <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-inbox"></i> Không có người dùng nào.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../src/includes/footer.php'; ?>

