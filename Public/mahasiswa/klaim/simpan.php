<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/actions/klaim_actions.php';

$currentUser = auth_require_role(['mahasiswa']);

require_post();
verify_csrf();

$idPengumuman = $_POST['id_pengumuman'] ?? null;
$idKembali = is_string($idPengumuman) && ctype_digit($idPengumuman)
    ? (int) $idPengumuman
    : 0;

try {
    $idKlaim = submit_mahasiswa_claim(
        (int) $currentUser['id_pengguna'],
        $_POST,
        $_FILES
    );

    flash([
        'type' => 'success',
        'message' => 'Klaim berhasil diajukan dan sedang menunggu verifikasi petugas.',
        'id_klaim' => $idKlaim,
    ]);

    redirect('/mahasiswa/riwayat.php');
} catch (InvalidArgumentException $error) {
    flash([
        'type' => 'error',
        'message' => $error->getMessage(),
    ]);

    if ($idKembali > 0) {
        redirect('/mahasiswa/klaim/detail.php?id=' . $idKembali);
    }

    redirect('/mahasiswa/daftar-barang.php');
} catch (Throwable $error) {
    error_log(
        '[TitikTemu] Gagal mengajukan klaim: '
        . get_class($error)
        . ' - '
        . $error->getMessage()
    );

    flash([
        'type' => 'error',
        'message' => 'Klaim belum dapat diproses. Silakan coba kembali beberapa saat lagi.',
    ]);

    if ($idKembali > 0) {
        redirect('/mahasiswa/klaim/detail.php?id=' . $idKembali);
    }

    redirect('/mahasiswa/daftar-barang.php');
}
