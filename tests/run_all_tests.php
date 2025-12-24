<?php
/**
 * Script chạy tất cả unit tests
 * 
 * Cách sử dụng:
 * php tests/run_all_tests.php
 * 
 * Hoặc với Docker:
 * docker-compose exec web php tests/run_all_tests.php
 */

require_once __DIR__ . '/bootstrap.php';

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║         STUDENT MANAGEMENT SYSTEM - UNIT TESTS            ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$allPassed = true;

// Chạy Config Tests
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "CONFIG TESTS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
require_once __DIR__ . '/ConfigTest.php';
$configTest = new ConfigTest();
$configPassed = $configTest->runAll();
$allPassed = $allPassed && $configPassed;

// Chạy Database Tests
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "DATABASE TESTS\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
require_once __DIR__ . '/DatabaseTest.php';
$dbTest = new DatabaseTest();
$dbPassed = $dbTest->runAll();
$allPassed = $allPassed && $dbPassed;

// Tổng kết
echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║                      TỔNG KẾT                              ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n";

if ($allPassed) {
    echo "✓ TẤT CẢ TESTS ĐÃ PASS!\n";
    echo "✓ Hệ thống hoạt động đúng như mong đợi.\n";
} else {
    echo "✗ MỘT SỐ TESTS ĐÃ FAIL!\n";
    echo "✗ Vui lòng kiểm tra lại code.\n";
}

echo "\n";

exit($allPassed ? 0 : 1);

