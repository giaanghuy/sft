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

# Chờ thêm vài giây để MySQL ổn định (tránh lỗi khi chạy init ngay lập tức)
sleep 3

cd /var/www/html

# DB_FORCE_INIT=1: xoá hết DB và init lại từ đầu (dùng khi có config env trên Coolify)
run_init() {
    if [ ! -f "database/database.sql" ]; then
        echo "ERROR: database/database.sql not found."
        exit 1
    fi
    if [ ! -f "scripts/init_database.php" ]; then
        echo "ERROR: scripts/init_database.php not found."
        exit 1
    fi
    php scripts/init_database.php || { echo "ERROR: init_database.php failed"; exit 1; }
    php scripts/init_users.php || { echo "ERROR: init_users.php failed"; exit 1; }
    php scripts/init_students.php || { echo "WARN: init_students.php failed (non-fatal)"; }
    echo "Schema and seed done."
}

FORCE_INIT=$(echo "${DB_FORCE_INIT:-0}" | tr '[:upper:]' '[:lower:]')
if [ "$FORCE_INIT" = "1" ] || [ "$FORCE_INIT" = "true" ] || [ "$FORCE_INIT" = "yes" ]; then
    echo "DB_FORCE_INIT enabled. Dropping database and re-initializing..."
    php -r "
    try {
        \$pdo = new PDO('mysql:host=db;charset=utf8mb4', 'root', getenv('DB_ROOT_PASS') ?: 'rootpassword');
        \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        \$pdo->exec('DROP DATABASE IF EXISTS student_management');
        \$pdo->exec('CREATE DATABASE student_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        \$pdo->exec(\"GRANT ALL PRIVILEGES ON student_management.* TO 'student_user'@'%'\");
        \$pdo->exec('FLUSH PRIVILEGES');
        echo \"Database reset OK.\n\";
        exit(0);
    } catch (Throwable \$e) {
        echo 'Reset failed: ' . \$e->getMessage() . \"\n\";
        exit(1);
    }
    " || { echo "ERROR: Database reset failed"; exit 1; }
    run_init
else
    # Chạy seed khi: bảng users chưa có HOẶC chưa có user nào
    if ! php -r "
    try {
        \$pdo = new PDO('mysql:host=db;dbname=student_management;charset=utf8mb4', 'student_user', 'student_password');
        \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        \$count = (int)\$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        if (\$count > 0) exit(0);
        exit(1);
    } catch (Throwable \$e) {
        exit(1);
    }
    " 2>/dev/null; then
        echo "Schema/seed needed (no users). Running init..."
        run_init
    fi
fi

echo "Starting Apache..."
exec docker-php-entrypoint apache2-foreground

