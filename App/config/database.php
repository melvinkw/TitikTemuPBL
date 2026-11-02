<?php

declare(strict_types=1);

function db(): PDO
{static $connection = null;

    if ($connection instanceof PDO) {
        return $connection;
    }

    $requiredVariables = [
        'DB_HOST',
        'DB_PORT',
        'DB_NAME',
        'DB_USER',
        'DB_PASSWORD',
    ];

    foreach ($requiredVariables as $variable) {
        if (
            !isset($_ENV[$variable])
            || $_ENV[$variable] === ''
        ) {
            throw new RuntimeException(
                "Konfigurasi {$variable} belum tersedia di .env"
            );
        }
    }

    $host = $_ENV['DB_HOST'];
    $port = $_ENV['DB_PORT'];
    $database = $_ENV['DB_NAME'];
    $username = $_ENV['DB_USER'];
    $password = $_ENV['DB_PASSWORD'];
    $sslMode = $_ENV['DB_SSLMODE'] ?? 'require';

    // Menyusun DSN untuk koneksi PostgreSQL.
    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s;sslmode=%s',
        $host,
        $port,
        $database,
        $sslMode
    );

    try {
        $connection = new PDO(
            $dsn,
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

                PDO::ATTR_DEFAULT_FETCH_MODE =>
                PDO::FETCH_ASSOC,

                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        return $connection;
    } catch (PDOException $error) {

        error_log(
            'Database connection error: '
                . $error->getMessage()
        );

        throw new RuntimeException(
            'Koneksi database gagal. Periksa konfigurasi .env.',
            0,
            $error
        );
    }
}
