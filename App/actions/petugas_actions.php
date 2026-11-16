<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

/**
 * Memutuskan laporan yang masih berstatus menunggu.
 * Keputusan: setujui atau tolak.
 *
 * Laporan hilang yang disetujui menjadi aktif.
 * Laporan temuan yang disetujui menjadi tersedia.
 * Laporan yang ditolak menjadi ditolak.
 */
function verify_pengumuman_report(
    PDO $pdo,
    int $idPengumuman,
    int $idVerifikator,
    string $keputusan,
    ?string $catatan
): string {
    if ($idPengumuman < 1 || $idVerifikator < 1) {
        throw new InvalidArgumentException('Identitas laporan atau petugas tidak valid.');
    }

    if (!in_array($keputusan, ['setujui', 'tolak'], true)) {
        throw new InvalidArgumentException('Keputusan verifikasi tidak valid.');
    }

    $catatan = $catatan !== null ? trim($catatan) : null;
    if ($catatan !== null && function_exists('mb_strlen') && mb_strlen($catatan, 'UTF-8') > 2000) {
        throw new InvalidArgumentException('Catatan maksimal 2.000 karakter.');
    }
    if ($catatan !== null && !function_exists('mb_strlen') && strlen($catatan) > 2000) {
        throw new InvalidArgumentException('Catatan maksimal 2.000 karakter.');
    }
    if ($keputusan === 'tolak' && ($catatan === null || $catatan === '')) {
        throw new InvalidArgumentException('Catatan wajib diisi ketika laporan ditolak.');
    }

    try {
        $pdo->beginTransaction();

        $lock = $pdo->prepare(
            "SELECT jenis, status
             FROM pengumuman
             WHERE id_pengumuman = :id_pengumuman
             FOR UPDATE"
        );
        $lock->execute(['id_pengumuman' => $idPengumuman]);
        $report = $lock->fetch(PDO::FETCH_ASSOC);

        if (!is_array($report)) {
            throw new RuntimeException('Laporan tidak ditemukan.');
        }

        if ($report['status'] !== 'menunggu') {
            throw new RuntimeException('Laporan ini sudah diproses atau tidak lagi menunggu verifikasi.');
        }

        if ($keputusan === 'tolak') {
            $statusBaru = 'ditolak';
        } elseif ($report['jenis'] === 'hilang') {
            $statusBaru = 'aktif';
        } elseif ($report['jenis'] === 'temuan') {
            $statusBaru = 'tersedia';
        } else {
            throw new RuntimeException('Jenis laporan pada database tidak dikenali.');
        }

        $update = $pdo->prepare(
            "UPDATE pengumuman
             SET status = :status,
                 id_verifikator = :id_verifikator,
                 catatan_verifikasi = :catatan_verifikasi,
                 tanggal_verifikasi = NOW(),
                 updated_at = NOW()
             WHERE id_pengumuman = :id_pengumuman
               AND status = 'menunggu'"
        );
        $update->execute([
            'status' => $statusBaru,
            'id_verifikator' => $idVerifikator,
            'catatan_verifikasi' => $catatan,
            'id_pengumuman' => $idPengumuman,
        ]);

        if ($update->rowCount() !== 1) {
            throw new RuntimeException('Status laporan berubah. Muat ulang halaman dan periksa kembali.');
        }

        $history = $pdo->prepare(
            "INSERT INTO riwayat_status_pengumuman (
                id_pengumuman,
                status_sebelumnya,
                status_baru,
                id_pengubah,
                catatan
             ) VALUES (
                :id_pengumuman,
                'menunggu',
                :status_baru,
                :id_pengubah,
                :catatan
             )"
        );
        $history->execute([
            'id_pengumuman' => $idPengumuman,
            'status_baru' => $statusBaru,
            'id_pengubah' => $idVerifikator,
            'catatan' => $catatan,
        ]);

        $pdo->commit();
        return $statusBaru;
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}
