#!/bin/bash
# set -e được bỏ để không dừng container nếu script seed gặp lỗi
# Chỉ dừng nếu database không kết nối được

echo "Waiting for database to be ready..."
# Đợi database sẵn sàng
until php -r "
try {
    \$pdo = new PDO('mysql:host=db;dbname=student_management;charset=utf8mb4', 'student_user', 'student_password');
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    \$pdo->exec(\"SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'\");
    echo 'Database is ready!\n';
    exit(0);
} catch (PDOException \$e) {
    exit(1);
}
" 2>/dev/null; do
    echo "Database is not ready yet. Waiting..."
    sleep 2
done

echo "Starting Apache..."
# Start Apache (luôn chạy dù script seed có lỗi hay không)
exec docker-php-entrypoint apache2-foreground

