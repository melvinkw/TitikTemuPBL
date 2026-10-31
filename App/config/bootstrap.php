<?php

declare(strict_types=1);

// Menentukan root project
$rootPath = dirname(__DIR__, 2);

if (!defined('APP_ROOT')) {
    define('APP_ROOT', $rootPath);
}

// Memuat Composer Autoloader
require_once APP_ROOT . '/vendor/autoload.php';

// Membaca konfigurasi .env
$dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
$dotenv->safeLoad();

// Memulai session untuk aplikasi web
if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path' => '/',
    ]);

    session_start();
}
