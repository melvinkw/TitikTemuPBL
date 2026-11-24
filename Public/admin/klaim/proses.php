<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/actions/petugas_claim_actions.php';

/**
 * Endpoint keputusan verifikasi klaim untuk admin.
 *
 * Aturan status tidak diduplikasi: verify_petugas_claim() sudah menerima
 * verifikator ber-role admin, jadi satu-satunya sumber logika tetap ada di
 * App/actions/petugas_claim_actions.php.
 */

$currentUser = auth_require_role(['admin']);

require_post();
verify_csrf();

$idKlaim = filter_var(
    $_POST['id_klaim'] ?? null,
    FILTER_VALIDATE_INT
);

if ($idKlaim === false || $idKlaim === null || $idKlaim < 1) {
    flash([
        'type' => 'error',
        'message' => 'ID klaim tidak valid.',
    ]);

    redirect('/admin/klaim/verifikasi.php');
}

$keputusan = $_POST['keputusan'] ?? '';
$catatan = $_POST['catatan'] ?? '';

if (!is_string($keputusan) || !is_string($catatan)) {
    flash([
        'type' => 'error',
        'message' => 'Data formulir tidak valid.',
    ]);

    redirect('/admin/klaim/verifikasi.php?id=' . $idKlaim);
}

try {
    $status = verify_petugas_claim(
        db(),
        (int) $idKlaim,
        (int) $currentUser['id_pengguna'],
        $keputusan,
        $catatan
    );

    flash([
        'type' => 'success',
        'message' => 'Klaim berhasil diproses dengan status: ' . $status,
    ]);

    redirect('/admin/klaim/verifikasi.php');
} catch (InvalidArgumentException | RuntimeException $error) {
    flash([
        'type' => 'error',
        'message' => $error->getMessage(),
    ]);

    redirect('/admin/klaim/verifikasi.php?id=' . $idKlaim);
} catch (Throwable $error) {
    error_log(
        '[TitikTemu] Verifikasi klaim admin gagal: '
        . get_class($error)
        . ' - '
        . $error->getMessage()
    );

    flash([
        'type' => 'error',
        'message' => 'Terjadi kesalahan saat memproses klaim.',
    ]);

    redirect('/admin/klaim/verifikasi.php?id=' . $idKlaim);
}
