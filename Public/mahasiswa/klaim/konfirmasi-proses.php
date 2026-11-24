<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/actions/penerimaan_klaim_actions.php';

$currentUser = auth_require_role(['mahasiswa']);

require_post();
verify_csrf();

$idKlaim = filter_var(
    $_POST['id_klaim'] ?? null,
    FILTER_VALIDATE_INT
);

$rating = filter_var(
    $_POST['rating'] ?? null,
    FILTER_VALIDATE_INT
);

$komentar = $_POST['komentar'] ?? '';

if (!is_string($komentar)) {
    $komentar = '';
}

if ($idKlaim === false || $idKlaim === null || $idKlaim < 1) {
    flash([
        'type' => 'error',
        'message' => 'ID klaim tidak valid.',
    ]);

    redirect('/mahasiswa/riwayat.php');
}

if ($rating === false || $rating === null || $rating < 1 || $rating > 5) {
    flash([
        'type' => 'error',
        'message' => 'Rating feedback wajib diisi antara 1 sampai 5.',
    ]);

    redirect('/mahasiswa/klaim/konfirmasi.php?id=' . $idKlaim);
}

try {
    confirm_mahasiswa_claim_received(
        db(),
        (int) $idKlaim,
        (int) $currentUser['id_pengguna'],
        (int) $rating,
        $komentar
    );

    flash([
        'type' => 'success',
        'message' => 'Terima kasih! Barang sudah ditandai diterima dan feedbackmu tersimpan.',
    ]);

    redirect('/mahasiswa/riwayat.php');
} catch (InvalidArgumentException | RuntimeException $error) {
    flash([
        'type' => 'error',
        'message' => $error->getMessage(),
    ]);

    redirect('/mahasiswa/klaim/konfirmasi.php?id=' . $idKlaim);
} catch (Throwable $error) {
    error_log(
        '[TitikTemu] Konfirmasi penerimaan klaim gagal: '
        . get_class($error)
        . ' - '
        . $error->getMessage()
    );

    flash([
        'type' => 'error',
        'message' => 'Terjadi kesalahan saat menyimpan konfirmasi.',
    ]);

    redirect('/mahasiswa/klaim/konfirmasi.php?id=' . $idKlaim);
}
