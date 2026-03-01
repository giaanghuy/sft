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

# Trên deployment server (và khi volume DB đã có data cũ), MySQL không chạy lại init script
# nên bảng có thể chưa tồn tại. Tự động chạy schema + seed nếu thiếu bảng users.
cd /var/www/html
if ! php -r "
try {
    \$pdo = new PDO('mysql:host=db;dbname=student_management;charset=utf8mb4', 'student_user', 'student_password');
    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    \$pdo->query('SELECT 1 FROM users LIMIT 1');
    exit(0);
} catch (Throwable \$e) {
    exit(1);
}
" 2>/dev/null; then
    echo "Tables missing. Running schema and seed..."
    php scripts/init_database.php
    php scripts/init_users.php
    php scripts/init_students.php 2>/dev/null || true
    echo "Schema and seed done."
fi

echo "Starting Apache..."
exec docker-php-entrypoint apache2-foreground

