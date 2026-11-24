<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

/**
 * Mengambil laporan yang masih menunggu verifikasi petugas.
 *
 * Filter opsional yang didukung:
 *   'judul'       => potongan judul barang (pencarian teks)
 *   'jenis'       => 'temuan' atau 'hilang'
 *   'lokasi'      => potongan lokasi kejadian
 *   'id_kategori' => id kategori barang
 */
function get_petugas_pending_reports(PDO $pdo, array $filters = []): array
{
    $conditions = ["p.status = 'menunggu'"];
    $params = [];

    $judul = trim((string) ($filters['judul'] ?? ''));

    if ($judul !== '') {
        $conditions[] = 'p.judul ILIKE :judul';
        $params['judul'] = '%' . petugas_laporan_like_escape($judul) . '%';
    }

    $jenis = (string) ($filters['jenis'] ?? '');

    if (in_array($jenis, ['temuan', 'hilang'], true)) {
        $conditions[] = 'p.jenis = :jenis';
        $params['jenis'] = $jenis;
    }

    $lokasi = trim((string) ($filters['lokasi'] ?? ''));

    if ($lokasi !== '') {
        $conditions[] = 'p.lokasi_kejadian ILIKE :lokasi';
        $params['lokasi'] = '%' . petugas_laporan_like_escape($lokasi) . '%';
    }

    $idKategori = (int) ($filters['id_kategori'] ?? 0);

    if ($idKategori > 0) {
        $conditions[] = 'p.id_kategori = :id_kategori';
        $params['id_kategori'] = $idKategori;
    }

    $where = implode("\n          AND ", $conditions);

    $sql = <<<SQL
        SELECT
            p.id_pengumuman,
            p.jenis,
            p.judul,
            p.deskripsi,
            p.lokasi_kejadian,
            p.tanggal_waktu_kejadian,
            p.created_at,
            u.nama AS nama_pelapor,
            k.nama_kategori,
            f.bucket_name AS foto_bucket,
            f.file_path AS foto_path
        FROM pengumuman AS p
        INNER JOIN pengguna AS u ON u.id_pengguna = p.id_pengguna
        INNER JOIN kategori AS k ON k.id_kategori = p.id_kategori
        LEFT JOIN LATERAL (
            SELECT fp.bucket_name, fp.file_path
            FROM foto_pengumuman AS fp
            WHERE fp.id_pengumuman = p.id_pengumuman
            ORDER BY fp.urutan ASC
            LIMIT 1
        ) AS f ON TRUE
        WHERE {$where}
        ORDER BY p.created_at ASC, p.id_pengumuman ASC
        SQL;

    $statement = $pdo->prepare($sql);
    $statement->execute($params);

    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

/** Melindungi karakter khusus LIKE agar input pengguna dicari apa adanya. */
function petugas_laporan_like_escape(string $value): string
{
    return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
}

/** Menghitung seluruh laporan yang masih menunggu verifikasi. */
function count_petugas_pending_reports(PDO $pdo): int
{
    $sql = <<<'SQL'
        SELECT COUNT(*)
        FROM pengumuman
        WHERE status = 'menunggu'
        SQL;

    return (int) $pdo->query($sql)->fetchColumn();
}

/** Mengambil satu laporan, seluruh foto, dan riwayat statusnya. */
function get_petugas_report_detail(PDO $pdo, int $idPengumuman): ?array
{
    $sql = <<<'SQL'
        SELECT
            p.id_pengumuman,
            p.id_pengguna,
            p.id_kategori,
            p.id_pos,
            p.jenis,
            p.judul,
            p.deskripsi,
            p.email_kontak,
            p.lokasi_kejadian,
            p.tanggal_waktu_kejadian,
            p.detail_lokasi,
            p.status,
            p.catatan_verifikasi,
            p.tanggal_verifikasi,
            p.created_at,
            u.nama AS nama_pelapor,
            u.nim AS nim_pelapor,
            k.nama_kategori,
            pp.nama_pos,
            pp.detail_lokasi AS detail_pos,
            l.nama_lokasi AS lokasi_pos
        FROM pengumuman AS p
        INNER JOIN pengguna AS u ON u.id_pengguna = p.id_pengguna
        INNER JOIN kategori AS k ON k.id_kategori = p.id_kategori
        LEFT JOIN pos_penyerahan AS pp ON pp.id_pos = p.id_pos
        LEFT JOIN lokasi AS l ON l.id_lokasi = pp.id_lokasi
        WHERE p.id_pengumuman = :id_pengumuman
        LIMIT 1
    SQL;

    $statement = $pdo->prepare($sql);
    $statement->execute(['id_pengumuman' => $idPengumuman]);
    $report = $statement->fetch(PDO::FETCH_ASSOC);

    if (!is_array($report)) {
        return null;
    }

    $photoStatement = $pdo->prepare(
        'SELECT id_foto, bucket_name, file_path, nama_file, mime_type, urutan
         FROM foto_pengumuman
         WHERE id_pengumuman = :id_pengumuman
         ORDER BY urutan ASC'
    );
    $photoStatement->execute(['id_pengumuman' => $idPengumuman]);
    $report['photos'] = $photoStatement->fetchAll(PDO::FETCH_ASSOC);

    $historySql = <<<'SQL'
        SELECT
            r.status_sebelumnya,
            r.status_baru,
            r.catatan,
            r.created_at,
            COALESCE(u.nama, 'Sistem') AS nama_pengubah
        FROM riwayat_status_pengumuman AS r
        LEFT JOIN pengguna AS u ON u.id_pengguna = r.id_pengubah
        WHERE r.id_pengumuman = :id_pengumuman
        ORDER BY r.created_at ASC, r.id_riwayat ASC
    SQL;

    $historyStatement = $pdo->prepare($historySql);
    $historyStatement->execute(['id_pengumuman' => $idPengumuman]);
    $report['history'] = $historyStatement->fetchAll(PDO::FETCH_ASSOC);

    return $report;
}
