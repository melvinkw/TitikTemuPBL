<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';

$user = auth_require_login();

// Mengarahkan pengguna berdasarkan role.
switch ($user['role']) {
    case 'mahasiswa':
        redirect('/mahasiswa/dashboard.php');

    case 'petugas':
        redirect('/petugas/dashboard.php');

    case 'admin':
        // Dashboard admin belum tersedia.
        // Untuk sementara tampilkan halaman placeholder.
        break;

    default:
        http_response_code(403);
        exit('Role pengguna tidak dikenali.');
}

$roleLabels = [
    'mahasiswa' => 'Mahasiswa',
    'petugas' => 'Petugas',
    'admin' => 'Administrator',
];

$roleLabel = $roleLabels[$user['role']] ?? 'Pengguna';

?>