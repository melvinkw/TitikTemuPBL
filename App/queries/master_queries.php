<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

/**
 * Mengambil seluruh kategori yang masih aktif untuk dropdown laporan.
 * Data diurutkan berdasarkan nama agar pilihan konsisten.
 */
function get_kategori_aktif(): array
{
    $sql = <<<'SQL'
        SELECT id_kategori, nama_kategori
        FROM kategori
        WHERE is_active = TRUE
        ORDER BY nama_kategori ASC
        SQL;

    return db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Mengambil pos penyerahan yang aktif dan lokasi induknya juga aktif.
 * Pos ini hanya dipakai ketika mahasiswa membuat laporan barang temuan.
 */
function get_pos_aktif(): array
{
    $sql = <<<'SQL'
        SELECT
            p.id_pos,
            p.nama_pos,
            p.jenis_pos,
            p.id_lokasi,
            l.nama_lokasi,
            p.detail_lokasi
        FROM pos_penyerahan AS p
        INNER JOIN lokasi AS l
            ON l.id_lokasi = p.id_lokasi
        WHERE p.is_active = TRUE
          AND l.is_active = TRUE
        ORDER BY l.nama_lokasi ASC, p.nama_pos ASC
        SQL;

    return db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

/** Memastikan kategori masih aktif sebelum laporan disimpan. */
function kategori_aktif_tersedia(PDO $pdo, int $idKategori): bool
{
    $sql = <<<'SQL'
        SELECT EXISTS (
            SELECT 1
            FROM kategori
            WHERE id_kategori = :id_kategori
              AND is_active = TRUE
        )
        SQL;

    $statement = $pdo->prepare($sql);
    $statement->execute(['id_kategori' => $idKategori]);

    return (bool) $statement->fetchColumn();
}

/**
 * Memastikan pos dan lokasi induknya masih aktif.
 * Validasi ini mencegah form mengirim ID pos yang sudah dinonaktifkan.
 */
function pos_aktif_tersedia(PDO $pdo, int $idPos): bool
{
    $sql = <<<'SQL'
        SELECT EXISTS (
            SELECT 1
            FROM pos_penyerahan AS p
            INNER JOIN lokasi AS l
                ON l.id_lokasi = p.id_lokasi
            WHERE p.id_pos = :id_pos
              AND p.is_active = TRUE
              AND l.is_active = TRUE
        )
        SQL;

    $statement = $pdo->prepare($sql);
    $statement->execute(['id_pos' => $idPos]);

    return (bool) $statement->fetchColumn();
}
