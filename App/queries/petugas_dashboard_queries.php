<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

function get_petugas_dashboard_data(): array
{
    $pdo = db();

    $statsSql = <<<'SQL'
        SELECT
            (SELECT COUNT(*)
             FROM pengumuman
             WHERE status = 'menunggu') AS pending_reports,

            (SELECT COUNT(*)
             FROM klaim
             WHERE status_verifikasi = 'menunggu') AS pending_claims,

            (SELECT COUNT(*)
             FROM pengguna
             WHERE role = 'mahasiswa'
             AND is_active = TRUE) AS active_students
    SQL;

    $stats = $pdo->query($statsSql)->fetch(PDO::FETCH_ASSOC);

    $reportsSql = <<<'SQL'
        SELECT
            CASE p.jenis
                WHEN 'temuan' THEN 'Temuan'
                WHEN 'hilang' THEN 'Kehilangan'
                ELSE p.jenis
            END AS jenis,
            p.judul,
            u.nama AS pelapor,
            p.lokasi_kejadian AS lokasi,
            p.created_at
        FROM pengumuman AS p
        INNER JOIN pengguna AS u
            ON u.id_pengguna = p.id_pengguna
        WHERE p.status = 'menunggu'
        ORDER BY p.created_at ASC
        LIMIT 5
    SQL;

    $reports = $pdo->query($reportsSql)->fetchAll(PDO::FETCH_ASSOC);

    $studentsSql = <<<'SQL'
        SELECT
            nama,
            nim,
            COALESCE(NULLIF(BTRIM(prodi), ''), '—') AS prodi,
            last_login
        FROM pengguna
        WHERE role = 'mahasiswa'
          AND is_active = TRUE
          AND last_login IS NOT NULL
        ORDER BY last_login DESC
        LIMIT 5
    SQL;

    $students = $pdo->query($studentsSql)->fetchAll(PDO::FETCH_ASSOC);

    return [
        'stats' => [
            'pending_reports' => (int) ($stats['pending_reports'] ?? 0),
            'pending_claims' => (int) ($stats['pending_claims'] ?? 0),
            'active_students' => (int) ($stats['active_students'] ?? 0),
        ],
        'reports' => $reports,
        'recent_students' => $students,
    ];
}
