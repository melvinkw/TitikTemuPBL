<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/actions/pengumuman_actions.php';

// Endpoint ini hanya boleh dipanggil oleh mahasiswa yang sudah login.
$currentUser = auth_require_role(['mahasiswa']);

require_post();
verify_csrf();
$jenisKembali = $_POST['jenis'] ?? 'hilang';

if (!in_array($jenisKembali, ['hilang', 'temuan'], true)) {
    $jenisKembali = 'hilang';
}
try {
    $idPengumuman = submit_pengumuman_report(
        (int) $currentUser['id_pengguna'],
        $_POST,
        $_FILES
    );

    flash([
        'type' => 'success',
        'message' => 'Laporan berhasil dikirim dan sedang menunggu verifikasi petugas.',
        'id_pengumuman' => $idPengumuman,
    ]);

    redirect('/mahasiswa/dashboard.php');
} catch (InvalidArgumentException $error) {
    http_response_code(422);

    exit(
        'VALIDASI GAGAL: ' . h($error->getMessage())
    );

} catch (Throwable $error) {
    error_log(
        '[TitikTemu] Gagal menyimpan laporan: '
        . get_class($error)
        . ' - '
        . $error->getMessage()
    );

    http_response_code(500);

    exit(
        'KESALAHAN SERVER: '
        . h(get_class($error))
        . ' - '
        . h($error->getMessage())
    );
}
