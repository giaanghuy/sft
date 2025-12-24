<?php
/**
 * File header chung cho tất cả các trang
 * 
 * @author github.com/lehuygiang28
 * @version 1.0
 */
require_once __DIR__ . '/../config/config.php';

if (!isLoggedIn()) {
    redirect('/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Quản lý Sinh viên'; ?></title>
    <!-- Bootstrap CSS 5.3.8 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* ===== CUSTOM THEME - Professional Student Management System ===== */
        :root {
            /* Professional Color Palette - Education & Trust */
            --bs-primary: #2563eb;        /* Professional Blue - Trust, Education */
            --bs-primary-rgb: 37, 99, 235;
            --bs-primary-dark: #1e40af;   /* Darker blue for hover */
            --bs-secondary: #0891b2;      /* Teal - Modern but professional */
            --bs-success: #059669;        /* Green - Success, Growth */
            --bs-danger: #dc2626;        /* Red - Alert, Important */
            --bs-warning: #d97706;        /* Orange - Warning */
            --bs-info: #0284c7;          /* Sky Blue - Information */
            --bs-dark: #0f172a;          /* Dark Slate - Text */
            --bs-light: #f8fafc;         /* Light Gray - Background */
            
            /* Professional Gradients */
            --gradient-primary: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            --gradient-secondary: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);
            --gradient-success: linear-gradient(135deg, #059669 0%, #047857 100%);
            --gradient-header: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            
            /* Shadows - Modern & Layered */
            --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            
            /* Transitions */
            --transition-fast: all 0.15s ease;
            --transition-base: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            
            /* Border Radius */
            --radius-sm: 0.375rem;
            --radius-md: 0.5rem;
            --radius-lg: 0.75rem;
            --radius-xl: 1rem;
            --radius-2xl: 1.5rem;
            
            /* Spacing */
            --spacing-xs: 0.25rem;
            --spacing-sm: 0.5rem;
            --spacing-md: 1rem;
            --spacing-lg: 1.5rem;
            --spacing-xl: 2rem;
        }
        
        /* ===== GLOBAL STYLES ===== */
        * {
            scroll-behavior: smooth;
        }
        
        body {
            background: #f8fafc;
            background-image: 
                radial-gradient(at 0% 0%, rgba(37, 99, 235, 0.03) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(8, 145, 178, 0.03) 0px, transparent 50%);
            background-attachment: fixed;
            font-family: 'Inter', 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', sans-serif;
            font-size: 0.9375rem;
            line-height: 1.6;
            color: #0f172a;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* ===== NAVBAR STYLES - Professional & Clean ===== */
        .navbar {
            background: var(--gradient-header) !important;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.1);
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            padding: 0.875rem 0;
            transition: var(--transition-base);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.35rem;
            letter-spacing: -0.5px;
            color: #ffffff !important;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .navbar-brand i {
            font-size: 1.5rem;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }
        
        .nav-link {
            transition: var(--transition-base);
            border-radius: var(--radius-md);
            margin: 0 0.25rem;
            padding: 0.5rem 1rem !important;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9) !important;
            position: relative;
            cursor: pointer;
        }
        
        .nav-link::before {
            content: '';
            position: absolute;
            bottom: 0.25rem;
            left: 50%;
            transform: translateX(-50%) scaleX(0);
            width: 60%;
            height: 2px;
            background: #ffffff;
            border-radius: 2px;
            transition: var(--transition-base);
            pointer-events: none;
        }
        
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.15);
            color: #ffffff !important;
            transform: translateY(-2px);
        }
        
        .nav-link:hover::before {
            transform: translateX(-50%) scaleX(1);
        }
        
        .nav-link.dropdown-toggle {
            pointer-events: auto;
            user-select: none;
        }
        
        .navbar-toggler {
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: var(--radius-md);
            padding: 0.375rem 0.75rem;
        }
        
        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.25);
        }
        
        .dropdown-menu {
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.1), 0 2px 4px -1px rgba(15, 23, 42, 0.06);
            border-radius: var(--radius-lg);
            padding: 0.5rem;
            margin-top: 0.5rem;
            background: #ffffff;
            min-width: 180px;
            z-index: 1050 !important;
        }
        
        .dropdown-item {
            border-radius: var(--radius-md);
            padding: 0.625rem 1rem;
            transition: var(--transition-base);
            color: #334155;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        
        .dropdown-item:hover,
        .dropdown-item:focus {
            background: var(--bs-primary);
            color: #ffffff;
            transform: translateX(2px);
        }
        
        .dropdown-item i {
            font-size: 1rem;
        }
        
        .dropdown-toggle {
            cursor: pointer;
        }
        
        .dropdown-toggle::after {
            margin-left: 0.5rem;
            vertical-align: 0.15em;
        }
        
        .navbar-nav .dropdown-menu {
            position: absolute !important;
            top: 100% !important;
            right: 0 !important;
            left: auto !important;
        }
        
        .nav-item.dropdown {
            position: relative;
        }
        
        .nav-item.dropdown .nav-link {
            pointer-events: auto !important;
        }
        
        .dropdown-toggle:focus {
            outline: 2px solid rgba(255, 255, 255, 0.5);
            outline-offset: 2px;
        }
        
        .dropdown-menu.show {
            display: block !important;
        }
        
        .navbar {
            z-index: 1030;
        }
        
        .navbar-nav {
            z-index: 1031;
        }
        
        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-top: 2rem;
            margin-bottom: 2rem;
            min-height: calc(100vh - 200px);
            animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
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
        
        /* ===== CARD STYLES - Professional & Clean ===== */
        .card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08), 0 1px 2px rgba(15, 23, 42, 0.06);
            border-radius: var(--radius-lg);
            transition: var(--transition-base);
            overflow: hidden;
            position: relative;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--gradient-primary);
            opacity: 0;
            transition: var(--transition-base);
        }
        
        .card:hover {
            box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.1), 0 2px 4px -1px rgba(15, 23, 42, 0.06);
            transform: translateY(-2px);
            border-color: #cbd5e1;
        }
        
        .card:hover::before {
            opacity: 1;
        }
        
        .card-header {
            background: var(--gradient-primary) !important;
            color: #ffffff;
            font-weight: 600;
            font-size: 1.0625rem;
            padding: 1.125rem 1.5rem;
            border-bottom: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .card-header.bg-white {
            background: #ffffff !important;
            color: var(--bs-dark);
            border-bottom: 1px solid #e2e8f0;
            padding: 1.25rem 1.5rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* ===== TABLE STYLES - Modern & Professional ===== */
        .table-responsive {
            border-radius: var(--radius-lg);
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
            background: #ffffff;
        }
        
        .table {
            margin-bottom: 0;
            background: #ffffff;
            width: 100%;
        }
        
        .table thead {
            background: var(--gradient-primary);
        }
        
        .table thead th {
            background: transparent;
            color: #ffffff;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8125rem;
            letter-spacing: 0.8px;
            padding: 1.125rem 1.25rem;
            border: none;
            position: relative;
            white-space: nowrap;
            vertical-align: middle;
        }
        
        .table thead th:first-child {
            border-top-left-radius: var(--radius-lg);
            padding-left: 1.5rem;
        }
        
        .table thead th:last-child {
            border-top-right-radius: var(--radius-lg);
            padding-right: 1.5rem;
        }
        
        .table tbody {
            background: #ffffff;
        }
        
        .table tbody tr {
            transition: all 0.2s ease;
            border-bottom: 1px solid #f1f5f9;
            background: #ffffff;
        }
        
        .table tbody tr:last-child {
            border-bottom: none;
        }
        
        .table tbody tr:hover {
            background-color: #f8fafc;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.08);
            transform: scale(1.001);
        }
        
        .table tbody td {
            padding: 1.125rem 1.25rem;
            vertical-align: middle;
            color: #334155;
            font-size: 0.9375rem;
            border-top: none;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .table tbody td:first-child {
            padding-left: 1.5rem;
            font-weight: 500;
            color: #64748b;
        }
        
        .table tbody td:last-child {
            padding-right: 1.5rem;
        }
        
        .table tbody td strong {
            color: var(--bs-primary);
            font-weight: 600;
        }
        
        .table-striped > tbody > tr:nth-of-type(odd) > * {
            background-color: #fafbfc;
        }
        
        .table-striped > tbody > tr:nth-of-type(odd):hover > * {
            background-color: #f8fafc;
        }
        
        /* Table action buttons */
        .table tbody td .btn {
            margin: 0 0.25rem;
            padding: 0.375rem 0.625rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            box-shadow: none;
        }
        
        .table tbody td .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .table tbody td .btn-warning {
            background-color: #f59e0b;
            border-color: #f59e0b;
            color: #ffffff;
        }
        
        .table tbody td .btn-warning:hover {
            background-color: #d97706;
            border-color: #d97706;
        }
        
        .table tbody td .btn-danger {
            background-color: #ef4444;
            border-color: #ef4444;
            color: #ffffff;
        }
        
        .table tbody td .btn-danger:hover {
            background-color: #dc2626;
            border-color: #dc2626;
        }
        
        /* Responsive table */
        @media (max-width: 768px) {
            .table thead th,
            .table tbody td {
                padding: 0.75rem 0.5rem;
                font-size: 0.875rem;
            }
            
            .table thead th:first-child,
            .table tbody td:first-child {
                padding-left: 1rem;
            }
            
            .table thead th:last-child,
            .table tbody td:last-child {
                padding-right: 1rem;
            }
        }
        
        /* ===== BUTTON STYLES - Modern & Interactive ===== */
        .btn {
            transition: var(--transition-base);
            border-radius: var(--radius-md);
            font-weight: 500;
            padding: 0.625rem 1.25rem;
            position: relative;
            overflow: hidden;
            border: none;
            box-shadow: var(--shadow-sm);
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
        
        .btn:active {
            transform: translateY(0);
            box-shadow: var(--shadow-sm);
        }
        
        .btn-primary {
            background: var(--bs-primary);
            color: #ffffff;
            border-color: var(--bs-primary);
        }
        
        .btn-primary:hover {
            background: var(--bs-primary-dark);
            border-color: var(--bs-primary-dark);
            color: #ffffff;
        }
        
        .btn-warning {
            background: var(--bs-warning);
            color: #ffffff;
            border-color: var(--bs-warning);
        }
        
        .btn-warning:hover {
            background: #b45309;
            border-color: #b45309;
            color: #ffffff;
        }
        
        .btn-danger {
            background: var(--bs-danger);
            color: #ffffff;
            border-color: var(--bs-danger);
        }
        
        .btn-danger:hover {
            background: #b91c1c;
            border-color: #b91c1c;
            color: #ffffff;
        }
        
        .btn-outline-primary {
            border: 2px solid var(--bs-primary);
            color: var(--bs-primary);
            background: transparent;
        }
        
        .btn-outline-primary:hover {
            background: var(--bs-primary);
            color: #ffffff;
            border-color: var(--bs-primary);
        }
        
        .btn-outline-secondary {
            border: 2px solid #64748b;
            color: #64748b;
            background: transparent;
        }
        
        .btn-outline-secondary:hover {
            background: #64748b;
            color: #ffffff;
            border-color: #64748b;
        }
        
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
        
        .btn-lg {
            padding: 0.875rem 1.75rem;
            font-size: 1.0625rem;
        }
        
        /* ===== FORM STYLES - Modern & Clean ===== */
        .form-control, .form-select {
            border-radius: var(--radius-md);
            transition: var(--transition-base);
            border: 2px solid #e2e8f0;
            padding: 0.625rem 1rem;
            font-size: 0.9375rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.25rem rgba(99, 102, 241, 0.15);
            transform: translateY(-1px);
            background-color: #ffffff;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--bs-dark);
            margin-bottom: 0.5rem;
            font-size: 0.9375rem;
        }
        
        .form-text {
            font-size: 0.8125rem;
            color: #64748b;
        }
        
        .input-group-text {
            border-radius: var(--radius-md);
            border: 2px solid #e2e8f0;
        }
        
        /* ===== ALERT STYLES - Modern & Animated ===== */
        .alert {
            border-radius: var(--radius-lg);
            border: none;
            padding: 1rem 1.25rem;
            animation: slideDown 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-md);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-15px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .alert-success {
            background-color: #ecfdf5;
            color: #065f46;
            border-left: 4px solid var(--bs-success);
            border-color: #a7f3d0;
        }
        
        .alert-danger {
            background-color: #fef2f2;
            color: #991b1b;
            border-left: 4px solid var(--bs-danger);
            border-color: #fecaca;
        }
        
        .alert-info {
            background-color: #eff6ff;
            color: #1e40af;
            border-left: 4px solid var(--bs-info);
            border-color: #bfdbfe;
        }
        
        .alert-warning {
            background-color: #fffbeb;
            color: #92400e;
            border-left: 4px solid var(--bs-warning);
            border-color: #fde68a;
        }
        
        .alert i {
            font-size: 1.25rem;
        }
        
        /* ===== BADGE STYLES - Modern & Pill ===== */
        .badge {
            font-weight: 600;
            padding: 0.4em 0.75em;
            border-radius: var(--radius-xl);
            font-size: 0.75rem;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }
        
        .badge.bg-warning {
            background-color: var(--bs-warning) !important;
        }
        
        .badge.bg-secondary {
            background-color: #64748b !important;
        }
        
        .badge.bg-primary {
            background-color: var(--bs-primary) !important;
        }
        
        /* ===== LOADING SPINNER ===== */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
        
        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .main-content {
                margin-top: 10px;
            }
            
            .card {
                margin-bottom: 1rem;
            }
            
            .table {
                font-size: 0.875rem;
            }
        }
        
        /* ===== UTILITY CLASSES ===== */
        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .shadow-sm-hover:hover {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        }
        
        /* ===== PAGINATION STYLES - Modern & Clean ===== */
        .pagination {
            margin: 0;
            gap: 0.5rem;
        }
        
        .pagination .page-link {
            color: var(--bs-primary);
            border: 2px solid #e2e8f0;
            border-radius: var(--radius-md);
            transition: var(--transition-base);
            padding: 0.5rem 0.875rem;
            font-weight: 500;
            background: #ffffff;
        }
        
        .pagination .page-link:hover {
            background: var(--bs-primary);
            color: #ffffff;
            border-color: var(--bs-primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
            color: #ffffff;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        }
        
        .pagination .page-item.disabled .page-link {
            color: #94a3b8;
            cursor: not-allowed;
            opacity: 0.5;
            background: #f1f5f9;
            border-color: #e2e8f0;
        }
        
        .pagination-sm .page-link {
            padding: 0.375rem 0.625rem;
            font-size: 0.875rem;
        }
        
        /* Form select inline cho pagination */
        #perPageForm {
            min-width: 150px;
        }
        
        #perPageForm .form-select-sm {
            width: auto;
            min-width: 80px;
            border-radius: var(--radius-md);
        }
        
        /* Responsive cho pagination controls */
        @media (max-width: 768px) {
            #perPageForm {
                min-width: 120px;
            }
            
            .pagination-sm .page-link {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
        }
        
        /* ===== FOOTER STYLES ===== */
        footer {
            background-color: #ffffff !important;
            border-top: 1px solid #e2e8f0;
            margin-top: 3rem;
            color: #64748b;
        }
        
        /* ===== UTILITY CLASSES ===== */
        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .shadow-sm-hover:hover {
            box-shadow: var(--shadow-md) !important;
        }
        
        /* ===== LOADING SPINNER ===== */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: 0.15em;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="/index.php">
                <i class="bi bi-mortarboard-fill"></i> Quản lý Sinh viên
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/index.php">
                            <i class="bi bi-house-door"></i> Trang chủ
                        </a>
                    </li>
                    <?php if (isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/students/add.php">
                            <i class="bi bi-person-plus"></i> Thêm sinh viên
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/users/index.php">
                            <i class="bi bi-people"></i> Quản lý người dùng
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                            <?php if (isAdmin()): ?>
                                <span class="badge bg-warning text-dark">Admin</span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li>
                                <a class="dropdown-item" href="/profile.php">
                                    <i class="bi bi-person"></i> Thông tin cá nhân
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="/auth/logout.php">
                                    <i class="bi bi-box-arrow-right"></i> Đăng xuất
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid main-content">
        <div class="container">

