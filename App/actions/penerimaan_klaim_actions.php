<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/helpers/core.php';

const TITIKTEMU_MAX_CLAIM_FEEDBACK_LENGTH = 1000;

/**
 * Mengonfirmasi bahwa mahasiswa sudah menerima barang.
 *
 * Dalam satu transaksi:
 * 1. Kunci klaim dan laporan.
 * 2. Klaim 'siap_diambil' -> 'diterima' beserta tanggal dan feedback.
 * 3. Laporan temuan 'diproses' -> 'selesai'.
 * 4. Catat riwayat status laporan.
 *
 * @return array{id_klaim:int,id_pengumuman:int}
 */
function confirm_mahasiswa_claim_received(
    PDO $pdo,
    int $idKlaim,
    int $idPengguna,
    int $rating,
    ?string $komentar
): array {
    if ($idKlaim < 1 || $idPengguna < 1) {
        throw new InvalidArgumentException('Identitas klaim tidak valid.');
    }

    if ($rating < 1 || $rating > 5) {
        throw new InvalidArgumentException('Rating feedback harus antara 1 sampai 5.');
    }

    $komentar = trim($komentar ?? '');

    if (text_length($komentar) > TITIKTEMU_MAX_CLAIM_FEEDBACK_LENGTH) {
        throw new InvalidArgumentException(
            'Komentar feedback maksimal 1.000 karakter.'
        );
    }

    try {
        $pdo->beginTransaction();

        $lock = $pdo->prepare(
            "SELECT
                c.id_klaim,
                c.id_pengumuman,
                c.status_verifikasi,
                p.jenis,
                p.status AS status_barang

             FROM klaim AS c

             INNER JOIN pengumuman AS p
                ON p.id_pengumuman = c.id_pengumuman

             WHERE c.id_klaim = :id_klaim
               AND c.id_pengguna = :id_pengguna

             FOR UPDATE OF c, p"
        );

        $lock->execute([
            'id_klaim' => $idKlaim,
            'id_pengguna' => $idPengguna,
        ]);

        $claim = $lock->fetch(PDO::FETCH_ASSOC);

        if (!$claim) {
            throw new RuntimeException('Klaim tidak ditemukan.');
        }

        if ($claim['status_verifikasi'] === 'diterima') {
            throw new RuntimeException('Klaim ini sudah dikonfirmasi diterima.');
        }

        if ($claim['status_verifikasi'] !== 'siap_diambil') {
            throw new RuntimeException(
                'Klaim belum siap dikonfirmasi. Tunggu verifikasi petugas.'
            );
        }

        if (
            $claim['jenis'] !== 'temuan'
            || $claim['status_barang'] !== 'diproses'
        ) {
            throw new RuntimeException(
                'Status barang tidak sesuai untuk konfirmasi penerimaan.'
            );
        }

        $updateClaim = $pdo->prepare(
            "UPDATE klaim
             SET status_verifikasi = 'diterima',
                 tanggal_diterima = NOW(),
                 rating_feedback = :rating,
                 komentar_feedback = :komentar,
                 updated_at = NOW()
             WHERE id_klaim = :id_klaim
               AND status_verifikasi = 'siap_diambil'"
        );

        $updateClaim->execute([
            'rating' => $rating,
            'komentar' => $komentar !== '' ? $komentar : null,
            'id_klaim' => $idKlaim,
        ]);

        if ($updateClaim->rowCount() !== 1) {
            throw new RuntimeException(
                'Klaim berubah. Silakan muat ulang halaman.'
            );
        }

        $updateReport = $pdo->prepare(
            "UPDATE pengumuman
             SET status = 'selesai',
                 updated_at = NOW()
             WHERE id_pengumuman = :id_pengumuman
               AND jenis = 'temuan'
               AND status = 'diproses'"
        );

        $updateReport->execute([
            'id_pengumuman' => (int) $claim['id_pengumuman'],
        ]);

        if ($updateReport->rowCount() !== 1) {
            throw new RuntimeException(
                'Status barang berubah. Konfirmasi dibatalkan.'
            );
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
                'diproses',
                'selesai',
                :id_pengubah,
                :catatan
             )"
        );

        $history->execute([
            'id_pengumuman' => (int) $claim['id_pengumuman'],
            'id_pengubah' => $idPengguna,
            'catatan' => 'Barang dikonfirmasi diterima oleh pemilik klaim.',
        ]);

        $pdo->commit();

        return [
            'id_klaim' => $idKlaim,
            'id_pengumuman' => (int) $claim['id_pengumuman'],
        ];
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $error;
    }
}
