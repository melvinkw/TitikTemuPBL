<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';

/**
 * Halaman Verifikasi Klaim untuk petugas.
 * Seluruh markup dan pemuatan data ada di
 * App/views/components/verifikasi-klaim.php (dipakai juga oleh admin).
 */

auth_require_role(['petugas']);

$pageTitle = 'Verifikasi Klaim';
$menuAktif = 'verifikasi-klaim';

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/petugas/navbar.php';

$klaimRuangKerja = 'RUANG KERJA PETUGAS';
$klaimHalamanUrl = 'petugas/klaim/verifikasi.php';
$klaimProsesUrl = 'petugas/klaim/proses.php';

require APP_ROOT . '/App/views/components/verifikasi-klaim.php';

require_once APP_ROOT . '/App/views/components/footer.php';
