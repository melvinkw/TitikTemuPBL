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
        $rows[$index]['id_pengumuman'] =
            (int) $row['id_pengumuman'];

        $rows[$index]['id_pengguna'] =
            (int) $row['id_pengguna'];

        $rows[$index]['milik_saya'] =
            $rows[$index]['id_pengguna'] === $idPengguna;
    }

    return $rows;
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
