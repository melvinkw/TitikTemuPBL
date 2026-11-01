<?php

declare(strict_types=1);

$rootPath = dirname(__DIR__, 2);

if (!defined('APP_ROOT')) {
    define('APP_ROOT', $rootPath);
}

require_once APP_ROOT . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
$dotenv->safeLoad();

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

require_once APP_ROOT . '/App/helpers/core.php';
