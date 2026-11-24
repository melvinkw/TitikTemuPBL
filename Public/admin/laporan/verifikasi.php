<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/actions/petugas_actions.php';

/**
 * Endpoint keputusan pemeriksaan laporan untuk admin.
 *
 * Logika keputusan tidak diduplikasi: memakai verify_pengumuman_report()
 * dari App/actions/petugas_actions.php, satu-satunya tempat aturan
 * menunggu → aktif/tersedia/ditolak dijalankan.
 */

$currentUser = auth_require_role(['admin']);

require_post();
verify_csrf();

$idPengumuman = filter_var(
    $_POST['id_pengumuman'] ?? null,
    FILTER_VALIDATE_INT
);

if ($idPengumuman === false || $idPengumuman === null || $idPengumuman < 1) {
    flash([
        'type' => 'error',
        'message' => 'ID laporan tidak valid.',
    ]);

    redirect('/admin/laporan/daftar.php');
}

$keputusan = $_POST['keputusan'] ?? '';
$catatan = $_POST['catatan'] ?? '';

if (!is_string($keputusan) || !is_string($catatan)) {
    flash([
        'type' => 'error',
        'message' => 'Data formulir tidak valid.',
    ]);

    redirect('/admin/laporan/detail.php?id=' . $idPengumuman);
}

$catatan = trim($catatan);

try {
    $statusBaru = verify_pengumuman_report(
        db(),
        (int) $idPengumuman,
        (int) $currentUser['id_pengguna'],
        $keputusan,
        $catatan !== '' ? $catatan : null
    );

    flash([
        'type' => 'success',
        'message' => 'Laporan berhasil diproses. Status baru: ' . $statusBaru,
    ]);

    redirect('/admin/laporan/daftar.php');
} catch (InvalidArgumentException | RuntimeException $error) {
    flash([
        'type' => 'error',
        'message' => $error->getMessage(),
    ]);

    redirect('/admin/laporan/detail.php?id=' . $idPengumuman);
} catch (Throwable $error) {
    error_log(
        '[TitikTemu] Verifikasi laporan oleh admin gagal: '
            . get_class($error)
            . ' - '
            . $error->getMessage()
    );

    flash([
        'type' => 'error',
        'message' => 'Terjadi kesalahan saat memproses laporan. Silakan coba lagi.',
    ]);

    redirect('/admin/laporan/detail.php?id=' . $idPengumuman);
}
