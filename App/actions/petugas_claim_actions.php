<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

/**
 * Memproses keputusan verifikasi klaim.
 *
 * Setujui:
 * klaim menunggu -> siap_diambil
 * barang tersedia -> diproses
 *
 * Tolak:
 * klaim menunggu -> ditolak
 * barang tetap tersedia
 */
function verify_petugas_claim(
    PDO $pdo,
    int $idKlaim,
    int $idPetugas,
    string $keputusan,
    ?string $catatan
): string {

    if ($idKlaim < 1 || $idPetugas < 1) {
        throw new InvalidArgumentException(
            'Identitas klaim atau petugas tidak valid.'
        );
    }

    if (!in_array($keputusan, ['setujui', 'tolak'], true)) {
        throw new InvalidArgumentException(
            'Keputusan verifikasi tidak valid.'
        );
    }

    $catatan = trim($catatan ?? '');

    if (strlen($catatan) > 2000) {
        throw new InvalidArgumentException(
            'Catatan maksimal 2.000 karakter.'
        );
    }

    if ($keputusan === 'tolak' && $catatan === '') {
        throw new InvalidArgumentException(
            'Catatan wajib diisi ketika klaim ditolak.'
        );
    }

    try {
        $pdo->beginTransaction();

        // Pastikan akun petugas masih aktif.
        $staffQuery = $pdo->prepare(
            "SELECT role, is_active
             FROM pengguna
             WHERE id_pengguna = :id"
        );

        $staffQuery->execute([
            'id' => $idPetugas,
        ]);

        $staff = $staffQuery->fetch(PDO::FETCH_ASSOC);

        if (
            !$staff
            || !in_array($staff['role'], ['petugas', 'admin'], true)
            || !in_array(
                $staff['is_active'],
                [true, 1, '1', 't', 'true'],
                true
            )
        ) {
            throw new RuntimeException(
                'Akun petugas tidak aktif atau tidak memiliki izin.'
            );
        }

        // Kunci klaim dan barang untuk mencegah
        // dua petugas memproses klaim yang sama bersamaan.
        $lock = $pdo->prepare(
            "SELECT
                c.id_klaim,
                c.id_pengguna,
                c.status_verifikasi,
                p.id_pengguna AS pemilik_laporan,
                p.jenis,
                p.status AS status_barang

             FROM klaim c

             INNER JOIN pengumuman p
                ON p.id_pengumuman = c.id_pengumuman

             WHERE c.id_klaim = :id_klaim

             FOR UPDATE OF c, p"
        );

        $lock->execute([
            'id_klaim' => $idKlaim,
        ]);

        $claim = $lock->fetch(PDO::FETCH_ASSOC);

        if (!$claim) {
            throw new RuntimeException(
                'Klaim tidak ditemukan.'
            );
        }

        if ($claim['status_verifikasi'] !== 'menunggu') {
            throw new RuntimeException(
                'Klaim sudah diproses sebelumnya.'
            );
        }

        if (
            $claim['jenis'] !== 'temuan'
            || $claim['status_barang'] !== 'tersedia'
            || (int) $claim['pemilik_laporan'] === (int) $claim['id_pengguna']
        ) {
            throw new RuntimeException(
                'Barang tidak lagi memenuhi syarat verifikasi klaim.'
            );
        }

        if ($keputusan === 'setujui') {
            $statusKlaim = 'siap_diambil';
            $statusBarang = 'diproses';
        } else {
            $statusKlaim = 'ditolak';
            $statusBarang = 'tersedia';
        }

        // Update klaim.
        $updateClaim = $pdo->prepare(
            "UPDATE klaim
             SET status_verifikasi = :status,
                 id_petugas = :id_petugas,
                 tanggal_verifikasi = NOW(),
                 catatan_verifikasi = :catatan,
                 updated_at = NOW()
             WHERE id_klaim = :id_klaim
               AND status_verifikasi = 'menunggu'"
        );

        $updateClaim->execute([
            'status' => $statusKlaim,
            'id_petugas' => $idPetugas,
            'catatan' => $catatan !== '' ? $catatan : null,
            'id_klaim' => $idKlaim,
        ]);

        if ($updateClaim->rowCount() !== 1) {
            throw new RuntimeException(
                'Klaim berubah. Silakan muat ulang halaman.'
            );
        }

        // Barang hanya berubah ketika klaim disetujui.
        if ($keputusan === 'setujui') {
            $updateReport = $pdo->prepare(
                "UPDATE pengumuman
                 SET status = 'diproses',
                     updated_at = NOW()
                 WHERE id_pengumuman = (
                     SELECT id_pengumuman
                     FROM klaim
                     WHERE id_klaim = :id_klaim
                 )
                 AND jenis = 'temuan'
                 AND status = 'tersedia'"
            );

            $updateReport->execute([
                'id_klaim' => $idKlaim,
            ]);

            if ($updateReport->rowCount() !== 1) {
                throw new RuntimeException(
                    'Status barang berubah. Verifikasi dibatalkan.'
                );
            }

            // Catat riwayat status barang.
            $history = $pdo->prepare(
                "INSERT INTO riwayat_status_pengumuman (
                    id_pengumuman,
                    status_sebelumnya,
                    status_baru,
                    id_pengubah,
                    catatan
                )
                SELECT
                    id_pengumuman,
                    'tersedia',
                    'diproses',
                    :id_petugas,
                    :catatan
                FROM klaim
                WHERE id_klaim = :id_klaim"
            );

            $history->execute([
                'id_petugas' => $idPetugas,
                'catatan' => $catatan !== ''
                    ? $catatan
                    : 'Klaim disetujui. Barang siap diambil pemilik.',
                'id_klaim' => $idKlaim,
            ]);
        }

        $pdo->commit();

        return $statusKlaim;
    } catch (Throwable $error) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        // Unique index uq_klaim_aktif_per_barang
        // tetap menjadi perlindungan terakhir.
        if (
            $error instanceof PDOException
            && $error->getCode() === '23505'
        ) {
            throw new RuntimeException(
                'Barang sudah memiliki klaim aktif atau sudah diterima.',
                0,
                $error
            );
        }

        throw $error;
    }
}
