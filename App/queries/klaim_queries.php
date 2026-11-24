<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

/**
 * Mengambil laporan temuan yang boleh dilihat untuk pengajuan klaim.
 * Laporan milik mahasiswa sendiri tidak pernah dapat diklaim.
 */
function get_mahasiswa_claimable_report(PDO $pdo, int $idPengumuman, int $idPengguna): ?array
{
    $sql = <<<'SQL'
        SELECT
            p.id_pengumuman,
            p.judul,
            p.deskripsi,
            p.lokasi_kejadian,
            p.tanggal_waktu_kejadian,
            p.created_at,
            p.status,
            k.nama_kategori,
            u.nama AS nama_pelapor
        FROM pengumuman AS p
        INNER JOIN kategori AS k
            ON k.id_kategori = p.id_kategori
        INNER JOIN pengguna AS u
            ON u.id_pengguna = p.id_pengguna
        WHERE p.id_pengumuman = :id_pengumuman
          AND p.jenis = 'temuan'
          AND p.status = 'tersedia'
          AND p.id_pengguna <> :id_pengguna
        LIMIT 1
        SQL;

    $statement = $pdo->prepare($sql);
    $statement->execute([
        'id_pengumuman' => $idPengumuman,
        'id_pengguna' => $idPengguna,
    ]);

    $row = $statement->fetch(PDO::FETCH_ASSOC);

    return $row === false ? null : $row;
}

/** Mengambil klaim aktif milik mahasiswa untuk satu laporan. */
function find_mahasiswa_active_claim(PDO $pdo, int $idPengumuman, int $idPengguna): ?array
{
    $sql = <<<'SQL'
        SELECT id_klaim, status_verifikasi, tanggal_pengajuan
        FROM klaim
        WHERE id_pengumuman = :id_pengumuman
          AND id_pengguna = :id_pengguna
          AND status_verifikasi IN ('menunggu', 'siap_diambil')
        ORDER BY tanggal_pengajuan DESC
        LIMIT 1
        SQL;

    $statement = $pdo->prepare($sql);
    $statement->execute([
        'id_pengumuman' => $idPengumuman,
        'id_pengguna' => $idPengguna,
    ]);

    $row = $statement->fetch(PDO::FETCH_ASSOC);

    return $row === false ? null : $row;
}

/** Menyimpan klaim. Bukti fisik lebih dahulu diunggah oleh action. */
function insert_klaim_record(
    PDO $pdo,
    int $idPengumuman,
    int $idPengguna,
    string $deskripsiBukti,
    string $buktiBucket,
    string $buktiPath
): int {
    $sql = <<<'SQL'
        INSERT INTO klaim (
            id_pengumuman,
            id_pengguna,
            deskripsi_bukti,
            bukti_bucket,
            bukti_path,
            status_verifikasi
        ) VALUES (
            :id_pengumuman,
            :id_pengguna,
            :deskripsi_bukti,
            :bukti_bucket,
            :bukti_path,
            'menunggu'
        )
        RETURNING id_klaim
        SQL;

    $statement = $pdo->prepare($sql);
    $statement->execute([
        'id_pengumuman' => $idPengumuman,
        'id_pengguna' => $idPengguna,
        'deskripsi_bukti' => $deskripsiBukti,
        'bukti_bucket' => $buktiBucket,
        'bukti_path' => $buktiPath,
    ]);

    $idKlaim = $statement->fetchColumn();

    if ($idKlaim === false) {
        throw new RuntimeException('Database tidak mengembalikan ID klaim baru.');
    }

    return (int) $idKlaim;
}

/**
 * Mengambil riwayat laporan dan klaim milik satu mahasiswa.
 * Kontak privat pelapor dan path Storage tidak dikirim ke halaman riwayat.
 */
function get_mahasiswa_claim_history(PDO $pdo, int $idPengguna): array
{
    $reportsSql = <<<'SQL'
        SELECT
            p.id_pengumuman,
            p.jenis,
            p.judul,
            p.lokasi_kejadian,
            p.tanggal_waktu_kejadian,
            p.status,
            k.nama_kategori
        FROM pengumuman AS p
        INNER JOIN kategori AS k
            ON k.id_kategori = p.id_kategori
        WHERE p.id_pengguna = :id_pengguna
        ORDER BY p.created_at DESC
        SQL;

    $reportsStatement = $pdo->prepare($reportsSql);
    $reportsStatement->execute(['id_pengguna' => $idPengguna]);
    $reports = $reportsStatement->fetchAll(PDO::FETCH_ASSOC);

    $claimsSql = <<<'SQL'
        SELECT
            c.id_klaim,
            c.status_verifikasi,
            c.tanggal_pengajuan,
            c.tanggal_diterima,
            c.rating_feedback,
            c.komentar_feedback,
            c.catatan_verifikasi,
            p.judul,
            p.lokasi_kejadian,
            pos.nama_pos
        FROM klaim AS c
        INNER JOIN pengumuman AS p
            ON p.id_pengumuman = c.id_pengumuman
        LEFT JOIN pos_penyerahan AS pos
            ON pos.id_pos = p.id_pos
        WHERE c.id_pengguna = :id_pengguna
        ORDER BY c.tanggal_pengajuan DESC
        SQL;

    $claimsStatement = $pdo->prepare($claimsSql);
    $claimsStatement->execute(['id_pengguna' => $idPengguna]);
    $claims = $claimsStatement->fetchAll(PDO::FETCH_ASSOC);

    return [
        'temuan' => array_values(array_filter(
            $reports,
            static fn (array $row): bool => $row['jenis'] === 'temuan'
        )),
        'hilang' => array_values(array_filter(
            $reports,
            static fn (array $row): bool => $row['jenis'] === 'hilang'
        )),
        'klaim' => $claims,
    ];
}

/**
 * Detail satu klaim milik mahasiswa untuk halaman konfirmasi penerimaan.
 * Hanya klaim milik pengguna yang sedang login yang dapat diambil.
 */
function get_mahasiswa_claim_confirmation_detail(
    PDO $pdo,
    int $idKlaim,
    int $idPengguna
): ?array {
    $sql = <<<'SQL'
        SELECT
            c.id_klaim,
            c.id_pengumuman,
            c.status_verifikasi,
            c.tanggal_pengajuan,
            c.tanggal_verifikasi,
            c.tanggal_diterima,
            c.rating_feedback,
            c.komentar_feedback,
            c.catatan_verifikasi,

            p.judul,
            p.deskripsi,
            p.lokasi_kejadian,
            p.tanggal_waktu_kejadian,

            k.nama_kategori,

            pos.nama_pos,
            pos.detail_lokasi AS detail_pos,

            lokasi.nama_lokasi

        FROM klaim AS c

        INNER JOIN pengumuman AS p
            ON p.id_pengumuman = c.id_pengumuman

        INNER JOIN kategori AS k
            ON k.id_kategori = p.id_kategori

        LEFT JOIN pos_penyerahan AS pos
            ON pos.id_pos = p.id_pos

        LEFT JOIN lokasi AS lokasi
            ON lokasi.id_lokasi = pos.id_lokasi

        WHERE c.id_klaim = :id_klaim
          AND c.id_pengguna = :id_pengguna

        LIMIT 1
        SQL;

    $statement = $pdo->prepare($sql);
    $statement->execute([
        'id_klaim' => $idKlaim,
        'id_pengguna' => $idPengguna,
    ]);

    $row = $statement->fetch(PDO::FETCH_ASSOC);

    return $row === false ? null : $row;
}
