<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

/**
 * Mengambil data rekapitulasi laporan berdasarkan periode.
 *
 * Periode menggunakan created_at, dengan batas awal inklusif
 * dan batas akhir eksklusif.
 */
function get_petugas_rekapitulasi(
    PDO $pdo,
    string $tanggalMulai,
    string $tanggalAkhir
): array {
    $params = [
        'tanggal_mulai' => $tanggalMulai,
        'tanggal_akhir' => $tanggalAkhir,
    ];

    // 1. Statistik utama
    $statsSql = <<<'SQL'
        SELECT
            COUNT(*) AS total_laporan,

            COUNT(*) FILTER (
                WHERE status = 'selesai'
            ) AS laporan_selesai,

            COUNT(DISTINCT id_kategori)
                AS total_kategori,

            COUNT(
                DISTINCT NULLIF(BTRIM(lokasi_kejadian), '')
            ) AS total_lokasi

        FROM pengumuman
        WHERE created_at >= CAST(:tanggal_mulai AS TIMESTAMPTZ)
          AND created_at < CAST(:tanggal_akhir AS TIMESTAMPTZ)
    SQL;

    $statsStatement = $pdo->prepare($statsSql);
    $statsStatement->execute($params);

    $stats = $statsStatement->fetch(PDO::FETCH_ASSOC);

    // 2. Jumlah berdasarkan jenis dan status
    $statusSql = <<<'SQL'
        SELECT
            jenis,
            status,
            COUNT(*) AS jumlah

        FROM pengumuman

        WHERE created_at >= CAST(:tanggal_mulai AS TIMESTAMPTZ)
          AND created_at < CAST(:tanggal_akhir AS TIMESTAMPTZ)

        GROUP BY jenis, status
        ORDER BY jenis, status
    SQL;

    $statusStatement = $pdo->prepare($statusSql);
    $statusStatement->execute($params);

    $perJenisStatus = $statusStatement->fetchAll(PDO::FETCH_ASSOC);

    // 3. Jumlah berdasarkan kategori
    $kategoriSql = <<<'SQL'
        SELECT
            k.nama_kategori AS nama,
            COUNT(p.id_pengumuman) AS total,

            COUNT(p.id_pengumuman) FILTER (
                WHERE p.status = 'selesai'
            ) AS selesai

        FROM pengumuman AS p

        INNER JOIN kategori AS k
            ON k.id_kategori = p.id_kategori

        WHERE p.created_at >= CAST(:tanggal_mulai AS TIMESTAMPTZ)
          AND p.created_at < CAST(:tanggal_akhir AS TIMESTAMPTZ)

        GROUP BY k.id_kategori, k.nama_kategori
        ORDER BY total DESC, k.nama_kategori ASC
    SQL;

    $kategoriStatement = $pdo->prepare($kategoriSql);
    $kategoriStatement->execute($params);

    $perKategori = $kategoriStatement->fetchAll(PDO::FETCH_ASSOC);

    // 4. Jumlah berdasarkan lokasi kejadian
    $lokasiSql = <<<'SQL'
        SELECT
            BTRIM(p.lokasi_kejadian) AS nama,
            COUNT(*) AS total,

            COUNT(*) FILTER (
                WHERE p.status = 'selesai'
            ) AS selesai

        FROM pengumuman AS p

        WHERE p.created_at >= CAST(:tanggal_mulai AS TIMESTAMPTZ)
          AND p.created_at < CAST(:tanggal_akhir AS TIMESTAMPTZ)
          AND NULLIF(BTRIM(p.lokasi_kejadian), '') IS NOT NULL

        GROUP BY BTRIM(p.lokasi_kejadian)
        ORDER BY total DESC, nama ASC
    SQL;

    $lokasiStatement = $pdo->prepare($lokasiSql);
    $lokasiStatement->execute($params);

    $perLokasi = $lokasiStatement->fetchAll(PDO::FETCH_ASSOC);

    return [
        'stats' => [
            'total_laporan' => (int) ($stats['total_laporan'] ?? 0),
            'laporan_selesai' => (int) ($stats['laporan_selesai'] ?? 0),
            'total_kategori' => (int) ($stats['total_kategori'] ?? 0),
            'total_lokasi' => (int) ($stats['total_lokasi'] ?? 0),
        ],
        'per_jenis_status' => $perJenisStatus,
        'per_kategori' => $perKategori,
        'per_lokasi' => $perLokasi,
    ];
}
