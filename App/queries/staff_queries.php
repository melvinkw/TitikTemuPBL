<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

/**
 * Membaca akun staff (admin dan petugas) untuk halaman Akun Staff.
 * Akun mahasiswa tidak pernah ikut karena dikelola lewat SIAKAD.
 */
function get_all_staff_accounts(PDO $pdo): array
{
    $sql = <<<'SQL'
        SELECT
            id_pengguna,
            username,
            nama,
            email,
            role,
            is_active,
            last_login,
            created_at
        FROM pengguna
        WHERE role IN ('petugas', 'admin')
        ORDER BY is_active DESC, role ASC, nama ASC
        SQL;

    return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

/** Mengambil satu akun staff berdasarkan ID; null kalau bukan admin/petugas. */
function find_staff_account(PDO $pdo, int $idPengguna): ?array
{
    $sql = <<<'SQL'
        SELECT
            id_pengguna,
            username,
            nama,
            email,
            role,
            is_active,
            last_login
        FROM pengguna
        WHERE id_pengguna = :id_pengguna
          AND role IN ('petugas', 'admin')
        LIMIT 1
        SQL;

    $statement = $pdo->prepare($sql);
    $statement->execute(['id_pengguna' => $idPengguna]);

    $row = $statement->fetch(PDO::FETCH_ASSOC);

    return $row === false ? null : $row;
}

/**
 * Memeriksa apakah username sudah dipakai akun lain (tanpa membedakan
 * huruf besar-kecil, mengikuti unique index uq_pengguna_username_lower).
 */
function staff_username_taken(
    PDO $pdo,
    string $username,
    ?int $ignoreIdPengguna = null
): bool {
    $sql = 'SELECT 1 FROM pengguna WHERE LOWER(username) = LOWER(:username)';
    $params = ['username' => $username];

    if ($ignoreIdPengguna !== null) {
        $sql .= ' AND id_pengguna <> :id_pengguna';
        $params['id_pengguna'] = $ignoreIdPengguna;
    }

    $statement = $pdo->prepare($sql . ' LIMIT 1');
    $statement->execute($params);

    return $statement->fetchColumn() !== false;
}

/** Memeriksa apakah email sudah dipakai akun lain (tanpa membedakan huruf besar-kecil). */
function staff_email_taken(
    PDO $pdo,
    string $email,
    ?int $ignoreIdPengguna = null
): bool {
    $sql = 'SELECT 1 FROM pengguna WHERE LOWER(email) = LOWER(:email)';
    $params = ['email' => $email];

    if ($ignoreIdPengguna !== null) {
        $sql .= ' AND id_pengguna <> :id_pengguna';
        $params['id_pengguna'] = $ignoreIdPengguna;
    }

    $statement = $pdo->prepare($sql . ' LIMIT 1');
    $statement->execute($params);

    return $statement->fetchColumn() !== false;
}
