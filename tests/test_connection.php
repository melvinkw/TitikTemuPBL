<?php
declare(strict_types=1);

// Memanggil konfigurasi aplikasi
require_once dirname(__DIR__) . '/app/config/bootstrap.php';

// Memanggil koneksi database
require_once APP_ROOT . '/app/config/database.php';

try {
    // Membuat koneksi database
    $pdo = db();

    // Menguji query database
    $statement = $pdo->query(
        'SELECT
            current_database() AS database_name,
            current_user AS database_user,
            NOW() AS server_time'
    );

    $result = $statement->fetch();

    echo "====================================\n";
    echo "       TITIKTEMU DATABASE TEST      \n";
    echo "====================================\n";
    echo "Connection : SUCCESS\n";
    echo "Database   : {$result['database_name']}\n";
    echo "DB User    : {$result['database_user']}\n";
    echo "Server Time: {$result['server_time']}\n";
    echo "====================================\n";

} catch (Throwable $error) {
    echo "DATABASE CONNECTION FAILED\n";
    echo "Error: " . $error->getMessage() . "\n";

    exit(1);
}
