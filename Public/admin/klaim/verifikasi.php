<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';

/**
 * Halaman Verifikasi Klaim untuk admin.
 * Markup dan pemuatan data sama persis dengan halaman petugas
 * (App/views/components/verifikasi-klaim.php); yang berbeda hanya
 * label ruang kerja, navbar, dan endpoint proses.
 */

auth_require_role(['admin']);

$pageTitle = 'Verifikasi Klaim';
$menuAktif = 'verifikasi-klaim';

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/admin/navbar.php';

$klaimRuangKerja = 'RUANG KERJA ADMINISTRATOR';
$klaimHalamanUrl = 'admin/klaim/verifikasi.php';
$klaimProsesUrl = 'admin/klaim/proses.php';

require APP_ROOT . '/App/views/components/verifikasi-klaim.php';

require_once APP_ROOT . '/App/views/components/footer.php';
