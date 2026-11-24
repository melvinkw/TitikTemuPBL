<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

/**
 * Klaim yang sudah disetujui petugas dan menunggu diambil mahasiswa.
 * Status klaim 'siap_diambil' dengan barang temuan berstatus 'diproses'.
 */
function get_petugas_pickup_queue(PDO $pdo, int $limit = 100): array
{
    $limit = max(1, min(100, $limit));

    $sql = <<<SQL
        SELECT
            c.id_klaim,
            c.tanggal_verifikasi,

            p.judul,
            p.lokasi_kejadian,

            k.nama_kategori,

            u.nama AS nama_mahasiswa,
            u.nim,

            pos.nama_pos,
            lokasi.nama_lokasi

        FROM klaim AS c

        INNER JOIN pengumuman AS p
            ON p.id_pengumuman = c.id_pengumuman

        INNER JOIN kategori AS k
            ON k.id_kategori = p.id_kategori

        INNER JOIN pengguna AS u
            ON u.id_pengguna = c.id_pengguna

        LEFT JOIN pos_penyerahan AS pos
            ON pos.id_pos = p.id_pos

        LEFT JOIN lokasi AS lokasi
            ON lokasi.id_lokasi = pos.id_lokasi

        WHERE c.status_verifikasi = 'siap_diambil'
          AND p.jenis = 'temuan'
          AND p.status = 'diproses'

        ORDER BY
            c.tanggal_verifikasi ASC,
            c.id_klaim ASC

        LIMIT {$limit}
    SQL;

    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Klaim yang sudah dikonfirmasi diterima mahasiswa beserta feedbacknya.
 */
function get_petugas_received_claims(PDO $pdo, int $limit = 20): array
{
    $limit = max(1, min(50, $limit));

    $sql = <<<SQL
        SELECT
            c.tanggal_diterima,
            c.rating_feedback,
            c.komentar_feedback,

            p.judul,

            u.nama AS nama_mahasiswa,
            u.nim

        FROM klaim AS c

        INNER JOIN pengumuman AS p
            ON p.id_pengumuman = c.id_pengumuman

        INNER JOIN pengguna AS u
            ON u.id_pengguna = c.id_pengguna

        WHERE c.status_verifikasi = 'diterima'

        ORDER BY
            c.tanggal_diterima DESC,
            c.id_klaim DESC

        LIMIT {$limit}
    SQL;

    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}
