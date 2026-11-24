<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

/**
 * Detail laporan yang boleh dilihat publik (pengunjung tanpa login).
 *
 * Hanya laporan yang sudah disetujui yang dianggap publik:
 * - barang hilang  -> status 'aktif'
 * - barang temuan  -> status 'tersedia' atau 'diproses'
 *
 * Status 'diproses' tetap ditampilkan agar pemilik yang sedang
 * mengurus klaim bisa memantau barangnya.
 */
function get_public_report_detail(PDO $pdo, int $idPengumuman): ?array
{
    if ($idPengumuman < 1) {
        return null;
    }

    $sql = <<<'SQL'
        SELECT
            p.id_pengumuman,
            p.jenis,
            p.judul,
            p.deskripsi,
            p.lokasi_kejadian,
            p.tanggal_waktu_kejadian,
            p.status,
            p.created_at,

            k.nama_kategori,

            u.nama AS nama_pelapor,

            pos.nama_pos,
            lokasi.nama_lokasi

        FROM pengumuman AS p

        INNER JOIN kategori AS k
            ON k.id_kategori = p.id_kategori

        INNER JOIN pengguna AS u
            ON u.id_pengguna = p.id_pengguna

        LEFT JOIN pos_penyerahan AS pos
            ON pos.id_pos = p.id_pos

        LEFT JOIN lokasi AS lokasi
            ON lokasi.id_lokasi = pos.id_lokasi

        WHERE p.id_pengumuman = :id_pengumuman
          AND (
              (p.jenis = 'hilang' AND p.status = 'aktif')
              OR
              (p.jenis = 'temuan' AND p.status IN ('tersedia', 'diproses'))
          )

        LIMIT 1
    SQL;

    $statement = $pdo->prepare($sql);
    $statement->execute([
        'id_pengumuman' => $idPengumuman,
    ]);

    $result = $statement->fetch(PDO::FETCH_ASSOC);

    return $result === false ? null : $result;
}

/**
 * Foto laporan publik dengan aturan visibilitas yang sama.
 */
function get_public_report_photos(PDO $pdo, int $idPengumuman): array
{
    if ($idPengumuman < 1) {
        return [];
    }

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
              (p.jenis = 'temuan' AND p.status IN ('tersedia', 'diproses'))
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
 * Daftar laporan publik untuk halaman daftar barang tanpa login.
 */
function search_public_reports(array $filters, int $limit = 50): array
{
    $limit = max(1, min(50, $limit));

    $conditions = [
        "(
            (p.jenis = 'hilang' AND p.status = 'aktif')
            OR
            (p.jenis = 'temuan' AND p.status = 'tersedia')
        )",
    ];

    $params = [];

    $nama = is_string($filters['nama'] ?? null)
        ? trim($filters['nama'])
        : '';

    if ($nama !== '') {
        $conditions[] = 'p.judul ILIKE :nama';
        $params['nama'] = '%' . $nama . '%';
    }

    $jenis = is_string($filters['jenis'] ?? null)
        ? trim($filters['jenis'])
        : '';

    if (in_array($jenis, ['hilang', 'temuan'], true)) {
        $conditions[] = 'p.jenis = :jenis';
        $params['jenis'] = $jenis;
    }

    $lokasi = is_string($filters['lokasi'] ?? null)
        ? trim($filters['lokasi'])
        : '';

    if ($lokasi !== '') {
        $conditions[] = 'p.lokasi_kejadian ILIKE :lokasi';
        $params['lokasi'] = '%' . $lokasi . '%';
    }

    $kategori = is_string($filters['kategori'] ?? null)
        ? trim($filters['kategori'])
        : '';

    if ($kategori !== '') {
        $conditions[] = 'k.nama_kategori ILIKE :kategori';
        $params['kategori'] = '%' . $kategori . '%';
    }

    $whereSql = implode("\n AND ", $conditions);

    $sql = <<<SQL
        SELECT
            p.id_pengumuman,
            p.jenis,
            p.judul,
            p.deskripsi,
            p.lokasi_kejadian,
            p.tanggal_waktu_kejadian,
            p.status,
            p.created_at,

            k.nama_kategori,

            foto.bucket_name AS foto_bucket,
            foto.file_path AS foto_path

        FROM pengumuman AS p

        INNER JOIN kategori AS k
            ON k.id_kategori = p.id_kategori

        LEFT JOIN LATERAL (
            SELECT
                f.bucket_name,
                f.file_path

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

    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Laporan publik terbaru untuk beranda.
 *
 * Barang hilang  -> status 'aktif'
 * Barang temuan  -> status 'tersedia'
 */
function get_public_recent_reports(string $jenis, int $limit = 4): array
{
    if (!in_array($jenis, ['hilang', 'temuan'], true)) {
        throw new InvalidArgumentException('Jenis laporan tidak valid.');
    }

    $status = $jenis === 'hilang' ? 'aktif' : 'tersedia';

    $limit = max(1, min(12, $limit));

    $sql = <<<SQL
        SELECT
            p.id_pengumuman,
            p.jenis,
            p.judul,
            p.deskripsi,
            p.lokasi_kejadian,
            p.tanggal_waktu_kejadian,
            p.status,
            p.created_at,

            k.nama_kategori,

            foto.bucket_name AS foto_bucket,
            foto.file_path AS foto_path

        FROM pengumuman AS p

        INNER JOIN kategori AS k
            ON k.id_kategori = p.id_kategori

        LEFT JOIN LATERAL (
            SELECT
                f.bucket_name,
                f.file_path

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

    return $statement->fetchAll(PDO::FETCH_ASSOC);
}
