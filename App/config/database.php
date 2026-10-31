<?php

declare (strict_types=1);

function db(): PDO
{
    static $connection = null;

    //menggunakan kembali koneksi selama requestnya berjalan
    if($connection instanceof PDO){
        return $connection;
    }

    $required=[
        'DB_HOST',
        'DB_PORT',
        'DB_DATABASE',
        'DB_USERNAME',
        'DB_PASSWORD'
    ];

    foreach($required as $key){
        if(!isset($_ENV[$key]) || $_ENV[$key] === ''){
            throw new RuntimeException("Environment variable $key is not set.");
        }
    }

    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s;sslmode=%s',
        $_ENV['DB_HOST'],
        $_ENV['DB_PORT'],
        $_ENV['DB_DATABASE'],
        $_ENV['DB_SSLMODE'] ?? 'prefer'
    );

     try {
        $connection = new PDO(
            $dsn,
            $_ENV['DB_USERNAME'],
            $_ENV['DB_PASSWORD'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );

        return $connection;

    } catch (PDOException $error) {
        error_log(
            'Database connection error: ' . $error->getMessage()
        );

        throw new RuntimeException(
            'Koneksi database gagal. Periksa konfigurasi database.',
            0,
            $error
        );
    }
}