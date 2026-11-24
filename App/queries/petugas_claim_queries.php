<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

/**
 * Mengambil daftar klaim yang menunggu verifikasi.
 */
function get_petugas_pending_claims(PDO $pdo): array
{
    $sql = <<<'SQL'
        SELECT
            c.id_klaim,
            c.id_pengumuman,
            c.id_pengguna,
            c.deskripsi_bukti,
            c.bukti_bucket,
            c.bukti_path,
            c.tanggal_pengajuan,

            p.judul,
            p.jenis,
            p.status AS status_barang,
            p.lokasi_kejadian,

            k.nama_kategori,

            u.nama AS nama_mahasiswa,
            u.nim

        FROM klaim AS c

        INNER JOIN pengumuman AS p
            ON p.id_pengumuman = c.id_pengumuman

        INNER JOIN kategori AS k
            ON k.id_kategori = p.id_kategori

        INNER JOIN pengguna AS u
            ON u.id_pengguna = c.id_pengguna

        WHERE c.status_verifikasi = 'menunggu'

        ORDER BY
            c.tanggal_pengajuan ASC,
            c.id_klaim ASC

        LIMIT 100
    SQL;

    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Mengambil detail satu klaim.
 */
function get_petugas_claim_detail(
    PDO $pdo,
    int $idKlaim
): ?array {
    $sql = <<<'SQL'
        SELECT
            c.id_klaim,
            c.id_pengumuman,
            c.id_pengguna,
            c.id_petugas,
            c.deskripsi_bukti,
            c.bukti_bucket,
            c.bukti_path,
            c.status_verifikasi,
            c.tanggal_pengajuan,
            c.tanggal_verifikasi,
            c.catatan_verifikasi,

            p.judul,
            p.deskripsi AS deskripsi_barang,
            p.jenis,
            p.status AS status_barang,
            p.lokasi_kejadian,
            p.tanggal_waktu_kejadian,

            k.nama_kategori,

            u.nama AS nama_mahasiswa,
            u.nim

        FROM klaim AS c

        INNER JOIN pengumuman AS p
            ON p.id_pengumuman = c.id_pengumuman

        INNER JOIN kategori AS k
            ON k.id_kategori = p.id_kategori

        INNER JOIN pengguna AS u
            ON u.id_pengguna = c.id_pengguna

        WHERE c.id_klaim = :id_klaim

        LIMIT 1
    SQL;

    $statement = $pdo->prepare($sql);

    $statement->execute([
        'id_klaim' => $idKlaim,
    ]);

    $result = $statement->fetch(PDO::FETCH_ASSOC);

    return $result === false ? null : $result;
}
