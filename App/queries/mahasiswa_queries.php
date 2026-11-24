<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

function mahasiswa_query_limit(int $limit, int $maximum = 50): int
{
    return max(1, min($maximum, $limit));
}
function mahasiswa_mark_ownership(array $rows, int $idPengguna): array
{
    foreach ($rows as $index => $row) {
        $rows[$index]['id_pengumuman'] =(int) $row['id_pengumuman'];

        $rows[$index]['id_pengguna'] =(int) $row['id_pengguna'];

        $rows[$index]['milik_saya'] =$rows[$index]['id_pengguna'] === $idPengguna;
    }

    return $rows;
}

/**
 * Mengambil seluruh foto laporan publik.
 * Hanya laporan yang sudah disetujui yang boleh diakses melalui fungsi ini.
 */
function get_mahasiswa_public_report_photos(
    PDO $pdo,
    int $idPengumuman
): array {
    $sql = <<<'SQL'
        SELECT
            f.id_foto,
            f.bucket_name,
            f.file_path,
            f.nama_file,
            f.mime_type,
            f.urutan
        FROM foto_pengumuman AS f
        INNER JOIN pengumuman AS p
            ON p.id_pengumuman = f.id_pengumuman
        WHERE p.id_pengumuman = :id_pengumuman
          AND (
              (p.jenis = 'hilang' AND p.status = 'aktif')
              OR
              (p.jenis = 'temuan' AND p.status = 'tersedia')
          )
        ORDER BY f.urutan ASC
        SQL;

    $statement = $pdo->prepare($sql);
    $statement->execute([
        'id_pengumuman' => $idPengumuman,
    ]);

    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Mengambil detail laporan milik mahasiswa yang sedang login.
 */
function get_mahasiswa_own_report_detail(
    PDO $pdo,
    int $idPengumuman,
    int $idPengguna
): ?array {
    $sql = <<<'SQL'
        SELECT
            p.id_pengumuman,
            p.id_pengguna,
            p.jenis,
            p.judul,
            p.deskripsi,
            p.lokasi_kejadian,
            p.tanggal_waktu_kejadian,
            p.status,
            p.catatan_verifikasi,
            p.created_at,
            k.nama_kategori,
            u.nama AS nama_pelapor
        FROM pengumuman AS p
        INNER JOIN kategori AS k
            ON k.id_kategori = p.id_kategori
        INNER JOIN pengguna AS u
            ON u.id_pengguna = p.id_pengguna
        WHERE p.id_pengumuman = :id_pengumuman
          AND p.id_pengguna = :id_pengguna
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

/**
 * Foto laporan pribadi.
 * Ownership diperiksa kembali pada query, bukan hanya dari URL.
 */
function get_mahasiswa_own_report_photos(
    PDO $pdo,
    int $idPengumuman,
    int $idPengguna
): array {
    $sql = <<<'SQL'
        SELECT
            f.id_foto,
            f.bucket_name,
            f.file_path,
            f.nama_file,
            f.mime_type,
            f.urutan
        FROM foto_pengumuman AS f
        INNER JOIN pengumuman AS p
            ON p.id_pengumuman = f.id_pengumuman
        WHERE p.id_pengumuman = :id_pengumuman
          AND p.id_pengguna = :id_pengguna
        ORDER BY f.urutan ASC
        SQL;

    $statement = $pdo->prepare($sql);
    $statement->execute([
        'id_pengumuman' => $idPengumuman,
        'id_pengguna' => $idPengguna,
    ]);

    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Mengambil riwayat perubahan status laporan milik mahasiswa.
 */
function get_mahasiswa_own_report_history(
    PDO $pdo,
    int $idPengumuman,
    int $idPengguna
): array {
    $sql = <<<'SQL'
        SELECT
            r.status_sebelumnya,
            r.status_baru,
            r.catatan,
            r.created_at,
            u.nama AS nama_pengubah
        FROM riwayat_status_pengumuman AS r
        INNER JOIN pengumuman AS p
            ON p.id_pengumuman = r.id_pengumuman
        LEFT JOIN pengguna AS u
            ON u.id_pengguna = r.id_pengubah
        WHERE p.id_pengumuman = :id_pengumuman
          AND p.id_pengguna = :id_pengguna
        ORDER BY r.created_at DESC, r.id_riwayat DESC
        SQL;

    $statement = $pdo->prepare($sql);
    $statement->execute([
        'id_pengumuman' => $idPengumuman,
        'id_pengguna' => $idPengguna,
    ]);

    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function get_mahasiswa_dashboard_stats(int $idPengguna): array
{
    if ($idPengguna < 1) {
        throw new InvalidArgumentException('ID pengguna tidak valid.');
    }

    $sql = <<<'SQL'
        SELECT
            COUNT(*) FILTER (
                WHERE jenis = 'hilang'
            ) AS laporan_hilang,

            COUNT(*) FILTER (
                WHERE jenis = 'temuan'
            ) AS laporan_temuan,

            COUNT(*) FILTER (
                WHERE status = 'menunggu'
            ) AS menunggu_pemeriksaan,

            COUNT(*) FILTER (
                WHERE status = 'selesai'
            ) AS laporan_selesai

        FROM pengumuman
        WHERE id_pengguna = :id_pengguna
    SQL;

    $statement = db()->prepare($sql);

    $statement->execute([
        'id_pengguna' => $idPengguna,
    ]);

    $stats = $statement->fetch(PDO::FETCH_ASSOC);

    if (!is_array($stats)) {
        return [
            'laporan_hilang' => 0,
            'laporan_temuan' => 0,
            'menunggu_pemeriksaan' => 0,
            'laporan_selesai' => 0,
        ];
    }

    foreach ($stats as $key => $value) {
        $stats[$key] = (int) $value;
    }

    return $stats;
}

function get_mahasiswa_recent_public_reports(
    int $idPengguna,
    string $jenis,
    int $limit = 4
): array {
    if ($idPengguna < 1) {
        throw new InvalidArgumentException('ID pengguna tidak valid.');
    }

    if (!in_array($jenis, ['hilang', 'temuan'], true)) {
        throw new InvalidArgumentException('Jenis laporan tidak valid.');
    }

    $status = $jenis === 'hilang'
        ? 'aktif'
        : 'tersedia';

    $limit = mahasiswa_query_limit($limit, 12);

    $sql = <<<SQL
        SELECT
            p.id_pengumuman,
            p.id_pengguna,
            p.jenis,
            p.judul,
            p.deskripsi,
            p.lokasi_kejadian,
            p.tanggal_waktu_kejadian,
            p.status,
            p.created_at,

            k.nama_kategori,

            foto.bucket_name AS foto_bucket,
            foto.file_path AS foto_path,
            foto.nama_file AS foto_nama,
            foto.mime_type AS foto_mime

        FROM pengumuman AS p

        INNER JOIN kategori AS k
            ON k.id_kategori = p.id_kategori

        LEFT JOIN LATERAL (
            SELECT
                f.bucket_name,
                f.file_path,
                f.nama_file,
                f.mime_type

            FROM foto_pengumuman AS f
            WHERE f.id_pengumuman = p.id_pengumuman

            ORDER BY f.urutan ASC
            LIMIT 1
        ) AS foto ON TRUE

        WHERE p.jenis = :jenis
          AND p.status = :status

        ORDER BY
            p.created_at DESC,
            p.id_pengumuman DESC

        LIMIT {$limit}
    SQL;

    $statement = db()->prepare($sql);

    $statement->execute([
        'jenis' => $jenis,
        'status' => $status,
    ]);

    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

    return mahasiswa_mark_ownership($rows, $idPengguna);
}


function search_mahasiswa_public_reports(
    array $filters,
    int $idPengguna,
    int $limit = 50
): array {
    if ($idPengguna < 1) {
        throw new InvalidArgumentException('ID pengguna tidak valid.');
    }

    $limit = mahasiswa_query_limit($limit);

    $conditions = [
        "(
            (p.jenis = 'hilang' AND p.status = 'aktif')
            OR
            (p.jenis = 'temuan' AND p.status = 'tersedia')
        )",
    ];

    $params = [];

    // Filter nama barang.
    $nama = is_string($filters['nama'] ?? null)
        ? trim($filters['nama'])
        : '';

    if ($nama !== '') {
        $conditions[] = "p.judul ILIKE :nama";
        $params['nama'] = '%' . $nama . '%';
    }

    // Filter jenis laporan.
    $jenis = is_string($filters['jenis'] ?? null)
        ? trim($filters['jenis'])
        : '';

    if (in_array($jenis, ['hilang', 'temuan'], true)) {
        $conditions[] = "p.jenis = :jenis";
        $params['jenis'] = $jenis;
    }

    // Filter lokasi kejadian.
    $lokasi = is_string($filters['lokasi'] ?? null)
        ? trim($filters['lokasi'])
        : '';

    if ($lokasi !== '') {
        $conditions[] = "p.lokasi_kejadian ILIKE :lokasi";
        $params['lokasi'] = '%' . $lokasi . '%';
    }

    // Filter kategori.
    $kategori = is_string($filters['kategori'] ?? null)
        ? trim($filters['kategori'])
        : '';

    if ($kategori !== '') {
        $conditions[] = "k.nama_kategori ILIKE :kategori";
        $params['kategori'] = '%' . $kategori . '%';
    }

    $whereSql = implode("\n AND ", $conditions);

    $sql = <<<SQL
        SELECT
            p.id_pengumuman,
            p.id_pengguna,
            p.jenis,
            p.judul,
            p.deskripsi,
            p.lokasi_kejadian,
            p.tanggal_waktu_kejadian,
            p.status,
            p.created_at,

            k.nama_kategori,

            foto.bucket_name AS foto_bucket,
            foto.file_path AS foto_path,
            foto.nama_file AS foto_nama,
            foto.mime_type AS foto_mime

        FROM pengumuman AS p

        INNER JOIN kategori AS k
            ON k.id_kategori = p.id_kategori

        LEFT JOIN LATERAL (
            SELECT
                f.bucket_name,
                f.file_path,
                f.nama_file,
                f.mime_type

            FROM foto_pengumuman AS f
            WHERE f.id_pengumuman = p.id_pengumuman

            ORDER BY f.urutan ASC
            LIMIT 1
        ) AS foto ON TRUE

        WHERE {$whereSql}

        ORDER BY
            p.created_at DESC,
            p.id_pengumuman DESC

        LIMIT {$limit}
    SQL;

    $statement = db()->prepare($sql);
    $statement->execute($params);

    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

    return mahasiswa_mark_ownership($rows, $idPengguna);
}

/**
 * Mengambil detail laporan milik mahasiswa yang sedang login.
 */
function get_mahasiswa_owned_report(
    PDO $pdo,
    int $idPengumuman,
    int $idPengguna
): ?array {
    $sql = <<<'SQL'
        SELECT
            p.id_pengumuman,
            p.id_pengguna,
            p.jenis,
            p.judul,
            p.deskripsi,
            p.lokasi_kejadian,
            p.detail_lokasi,
            p.tanggal_waktu_kejadian,
            p.status,
            p.catatan_verifikasi,
            p.created_at,
            p.updated_at,
            k.nama_kategori,
            u.nama AS nama_pelapor
        FROM pengumuman AS p
        INNER JOIN kategori AS k
            ON k.id_kategori = p.id_kategori
        INNER JOIN pengguna AS u
            ON u.id_pengguna = p.id_pengguna
        WHERE p.id_pengumuman = :id_pengumuman
          AND p.id_pengguna = :id_pengguna
        LIMIT 1
        SQL;

    $statement = $pdo->prepare($sql);

    $statement->execute([
        'id_pengumuman' => $idPengumuman,
        'id_pengguna' => $idPengguna,
    ]);

    $report = $statement->fetch(PDO::FETCH_ASSOC);

    return $report === false ? null : $report;
}

/**
 * Mengambil seluruh metadata foto suatu laporan.
 */
function get_mahasiswa_report_photos(
    PDO $pdo,
    int $idPengumuman
): array {
    $sql = <<<'SQL'
        SELECT
            id_foto,
            bucket_name,
            file_path,
            nama_file,
            mime_type,
            ukuran_byte,
            urutan
        FROM foto_pengumuman
        WHERE id_pengumuman = :id_pengumuman
        ORDER BY urutan ASC
        SQL;

    $statement = $pdo->prepare($sql);

    $statement->execute([
        'id_pengumuman' => $idPengumuman,
    ]);

    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Mengambil riwayat perubahan status laporan.
 */
function get_mahasiswa_report_status_history(
    PDO $pdo,
    int $idPengumuman
): array {
    $sql = <<<'SQL'
        SELECT
            r.id_riwayat,
            r.status_sebelumnya,
            r.status_baru,
            r.catatan,
            r.created_at,
            u.nama AS nama_pengubah
        FROM riwayat_status_pengumuman AS r
        LEFT JOIN pengguna AS u
            ON u.id_pengguna = r.id_pengubah
        WHERE r.id_pengumuman = :id_pengumuman
        ORDER BY r.created_at DESC, r.id_riwayat DESC
        SQL;

    $statement = $pdo->prepare($sql);

    $statement->execute([
        'id_pengumuman' => $idPengumuman,
    ]);

    return $statement->fetchAll(PDO::FETCH_ASSOC);
}