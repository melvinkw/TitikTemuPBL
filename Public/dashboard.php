<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';

$user = auth_require_login();

$destinationByRole = [
    'mahasiswa' => '/mahasiswa/dashboard.php',
    'petugas'   => '/petugas/dashboard.php',
    'admin'     => '/admin/dashboard.php',
];

$destination = $destinationByRole[$user['role']] ?? null;

if ($destination === null) {
    http_response_code(403);
    exit('Role pengguna tidak dikenali.');
}

redirect($destination);
