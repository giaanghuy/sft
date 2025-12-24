<?php
/**
 * Entry point - Trang chủ - Danh sách sinh viên
 * 
 * @author github.com/lehuygiang28
 * @version 1.0
 */
require_once __DIR__ . '/../src/config/config.php';
require_once __DIR__ . '/../src/includes/header.php';

$pageTitle = 'Danh sách Sinh viên';
$pdo = getDBConnection();

// ===== XỬ LÝ PAGINATION =====
// Lấy tham số pagination từ URL
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = isset($_GET['per_page']) ? max(5, min(100, (int)$_GET['per_page'])) : 10; // Mặc định 10, min 5, max 100
$offset = ($currentPage - 1) * $perPage;

// ===== XỬ LÝ TÌM KIẾM =====
// Làm sạch input từ GET để tránh XSS
$search = sanitize($_GET['search'] ?? '');
$whereClause = '';
$params = [];

// ===== PHÂN QUYỀN TRUY CẬP =====
// User thường chỉ được xem sinh viên do mình tạo
// Admin được xem tất cả
if (!isAdmin()) {
    $whereClause = "WHERE s.user_id = ?";
    $params[] = $_SESSION['user_id'];
}

// ===== THÊM ĐIỀU KIỆN TÌM KIẾM =====
// Tìm kiếm theo họ tên hoặc mã sinh viên
if (!empty($search)) {
    if (!empty($whereClause)) {
        $whereClause .= " AND (s.full_name LIKE ? OR s.student_code LIKE ?)";
    } else {
        $whereClause = "WHERE (s.full_name LIKE ? OR s.student_code LIKE ?)";
    }
    // Sử dụng wildcard để tìm kiếm phần từ
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// ===== ĐẾM TỔNG SỐ SINH VIÊN =====
// Đếm tổng số để tính số trang
$countSql = "SELECT COUNT(*) as total 
             FROM students s 
             {$whereClause}";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalRecords = $countStmt->fetch()['total'];
$totalPages = max(1, ceil($totalRecords / $perPage));

// Đảm bảo currentPage không vượt quá totalPages
if ($currentPage > $totalPages) {
    $currentPage = $totalPages;
    $offset = ($currentPage - 1) * $perPage;
}

// ===== LẤY DANH SÁCH SINH VIÊN VỚI PAGINATION =====
// Sử dụng prepared statement để chống SQL injection
// JOIN với bảng users để lấy thông tin người tạo
// Sử dụng LIMIT và OFFSET cho pagination
$sql = "SELECT s.*, u.username as created_by 
        FROM students s 
        LEFT JOIN users u ON s.user_id = u.id 
        {$whereClause}
        ORDER BY s.id DESC
        LIMIT ? OFFSET ?";
$stmt = $pdo->prepare($sql);
// Thêm limit và offset vào params
$params[] = $perPage;
$params[] = $offset;
$stmt->execute($params);
$students = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-people"></i> Danh sách Sinh viên</h5>
                <?php if (isAdmin()): ?>
                <a href="/students/add.php" class="btn btn-primary">
                    <i class="bi bi-person-plus"></i> Thêm sinh viên
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <!-- Hiển thị thông báo từ session -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i> <?php echo $_SESSION['success_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle"></i> <?php echo $_SESSION['error_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
                
                <!-- Form tìm kiếm -->
                <form method="GET" action="" class="mb-4">
                    <!-- Giữ lại tham số per_page khi tìm kiếm -->
                    <input type="hidden" name="per_page" value="<?php echo $perPage; ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-10">
                            <input type="text" class="form-control" id="search" name="search" 
                                   placeholder="Tìm kiếm theo Họ tên hoặc Mã sinh viên..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-outline-primary w-100">
                                <i class="bi bi-search"></i> Tìm kiếm
                            </button>
                        </div>
                    </div>
                    <?php if (!empty($search)): ?>
                    <div class="mt-2">
                        <a href="/index.php?per_page=<?php echo $perPage; ?>" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Xóa bộ lọc
                        </a>
                    </div>
                    <?php endif; ?>
                </form>

                <!-- Bảng danh sách sinh viên -->
                <?php if (count($students) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Mã SV</th>
                                <th>Họ tên</th>
                                <th>Ngày sinh</th>
                                <th>Giới tính</th>
                                <th>Email</th>
                                <th>Số điện thoại</th>
                                <th>Địa chỉ</th>
                                <?php if (isAdmin()): ?>
                                <th>Người tạo</th>
                                <th>Thao tác</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                            <tr>
                                <!-- Hiển thị dữ liệu với htmlspecialchars để chống XSS -->
                                <td><?php echo htmlspecialchars($student['id']); ?></td>
                                <td><strong><?php echo htmlspecialchars($student['student_code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                <td><?php echo formatDate($student['birthday']); ?></td>
                                <td><?php echo htmlspecialchars($student['gender']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo htmlspecialchars($student['phone']); ?></td>
                                <td><?php echo htmlspecialchars($student['address']); ?></td>
                                <?php if (isAdmin()): ?>
                                <!-- Chỉ admin mới thấy cột người tạo và thao tác -->
                                <td><?php echo htmlspecialchars($student['created_by'] ?? 'N/A'); ?></td>
                                <td>
                                    <!-- Nút sửa -->
                                    <a href="/students/edit.php?id=<?php echo (int)$student['id']; ?>" 
                                       class="btn btn-sm btn-warning" title="Sửa">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <!-- Nút xóa với confirm dialog -->
                                    <a href="/students/delete.php?id=<?php echo (int)$student['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Bạn có chắc chắn muốn xóa sinh viên này?')" 
                                       title="Xóa">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Thông tin và Pagination Controls (Bottom Right) -->
                <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <!-- Thông tin bên trái -->
                    <p class="text-muted mb-0">
                        <i class="bi bi-info-circle"></i> 
                        Hiển thị <strong><?php echo count($students); ?></strong> / <strong><?php echo $totalRecords; ?></strong> sinh viên
                        <?php if ($totalPages > 1): ?>
                            (Trang <?php echo $currentPage; ?> / <?php echo $totalPages; ?>)
                        <?php endif; ?>
                    </p>
                    
                    <!-- Pagination Controls bên phải -->
                    <div class="d-flex align-items-center gap-2">
                        <!-- Chọn số lượng/trang -->
                        <form method="GET" action="" class="d-inline-flex align-items-center gap-2" id="perPageForm">
                            <?php if (!empty($search)): ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            <?php endif; ?>
                            <label for="per_page" class="form-label mb-0 small">Số lượng/trang:</label>
                            <select class="form-select form-select-sm" id="per_page" name="per_page" style="width: auto;" onchange="this.form.submit()">
                                <option value="5" <?php echo $perPage == 5 ? 'selected' : ''; ?>>5</option>
                                <option value="10" <?php echo $perPage == 10 ? 'selected' : ''; ?>>10</option>
                                <option value="20" <?php echo $perPage == 20 ? 'selected' : ''; ?>>20</option>
                                <option value="50" <?php echo $perPage == 50 ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?php echo $perPage == 100 ? 'selected' : ''; ?>>100</option>
                            </select>
                        </form>
                        
                        <!-- Pagination -->
                        <?php 
                        // Tạo query params để giữ lại khi chuyển trang
                        $queryParams = [];
                        if (!empty($search)) {
                            $queryParams['search'] = $search;
                        }
                        if ($perPage != 10) {
                            $queryParams['per_page'] = $perPage;
                        }
                        echo generatePagination($currentPage, $totalPages, '/index.php', $queryParams);
                        ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-inbox"></i> Không tìm thấy sinh viên nào.
                    <?php if (isAdmin()): ?>
                    <a href="/students/add.php" class="alert-link">Thêm sinh viên mới</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../src/includes/footer.php'; ?>

