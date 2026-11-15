<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/config/database.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/actions/petugas_actions.php';

// 1. Pastikan hanya petugas yang dapat mengakses endpoint.
$currentUser = auth_require_role(['petugas']);

// 2. Endpoint hanya menerima POST.
require_post();

// 3. Periksa CSRF.
verify_csrf();

// 4. Ambil dan validasi ID laporan.
$idPengumuman = filter_var(
    $_POST['id_pengumuman'] ?? null,
    FILTER_VALIDATE_INT
);

if ($idPengumuman === false || $idPengumuman === null || $idPengumuman < 1) {
    flash([
        'type' => 'error',
        'message' => 'ID laporan tidak valid.',
    ]);

    redirect('/petugas/laporan/Laporan-Masuk.php');
}

// 5. Validasi keputusan.
$keputusan = $_POST['keputusan'] ?? '';

if (!is_string($keputusan)) {
    $keputusan = '';
}

// 6. Validasi catatan.
$catatan = $_POST['catatan'] ?? '';

if (!is_string($catatan)) {
    $catatan = '';
}

$catatan = trim($catatan);

try {
    // ID petugas diambil dari session tervalidasi.
    $idVerifikator = (int) $currentUser['id_pengguna'];

    // 7. Jalankan proses verifikasi.
    $statusBaru = verify_pengumuman_report(
        db(),
        $idPengumuman,
        $idVerifikator,
        $keputusan,
        $catatan !== '' ? $catatan : null
    );

    flash([
        'type' => 'success',
        'message' => 'Laporan berhasil diproses. Status baru: ' . $statusBaru,
    ]);

    redirect('/petugas/laporan/Laporan-Masuk.php');

} catch (InvalidArgumentException | RuntimeException $error) {

    // Error validasi atau kondisi bisnis yang aman ditampilkan.
    flash([
        'type' => 'error',
        'message' => $error->getMessage(),
    ]);

    redirect(
        '/petugas/laporan/Detail-Laporan.php?id=' . $idPengumuman
    );

} catch (Throwable $error) {

    // Detail teknis hanya masuk log server.
    error_log(
        '[TitikTemu] Verifikasi laporan gagal: '
        . get_class($error)
        . ' - '
        . $error->getMessage()
    );

    flash([
        'type' => 'error',
        'message' => 'Terjadi kesalahan saat memproses laporan. Silakan coba lagi.',
    ]);

    redirect(
        '/petugas/laporan/Detail-Laporan.php?id=' . $idPengumuman
    );
}
