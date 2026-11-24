<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once __DIR__ . '/petugas_dashboard_queries.php';

/**
 * Ringkasan dashboard admin.
 *
 * Statistik laporan/klaim/mahasiswa memakai query dashboard staff yang sama
 * dengan petugas (App/queries/petugas_dashboard_queries.php) supaya angkanya
 * tidak pernah berbeda antar halaman. Admin hanya menambahkan antrean email.
 */
function get_admin_dashboard_data(): array
{
    $pdo = db();

    $data = get_petugas_dashboard_data();

    $emailSql = <<<'SQL'
        SELECT
            COUNT(*) FILTER (
                WHERE status_kirim = 'menunggu'
            ) AS menunggu,

            COUNT(*) FILTER (
                WHERE status_kirim = 'gagal'
            ) AS gagal

        FROM notifikasi_email
        SQL;

    $email = $pdo->query($emailSql)->fetch(PDO::FETCH_ASSOC);

    $matchSql = <<<'SQL'
        SELECT COUNT(*) AS menunggu_review
        FROM kecocokan
        WHERE status_kecocokan = 'menunggu_review'
        SQL;

    $match = $pdo->query($matchSql)->fetch(PDO::FETCH_ASSOC);

    $data['stats']['email_pending'] = (int) ($email['menunggu'] ?? 0);
    $data['stats']['email_failed'] = (int) ($email['gagal'] ?? 0);
    $data['stats']['match_candidates'] = (int) ($match['menunggu_review'] ?? 0);

    return $data;
}
