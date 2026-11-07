<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

function save_or_update_active_student(string $nim, string $nama): ?array
{
    $sql = <<<'SQL'
        INSERT INTO pengguna (
            nim,
            username,
            password_hash,
            nama,
            role,
            is_active,
            last_login
        )
        VALUES (
            :nim,
            NULL,
            NULL,
            :nama,
            'mahasiswa',
            TRUE,
            NOW()
        )
        ON CONFLICT (nim)
        DO UPDATE SET
            nama = EXCLUDED.nama,
            last_login = NOW(),
            updated_at = NOW()
        WHERE pengguna.role = 'mahasiswa'
          AND pengguna.is_active = TRUE
        RETURNING
            id_pengguna,
            nim,
            username,
            nama,
            role,
            is_active
    SQL;

    $statement = db()->prepare($sql);
    $statement->execute([
        'nim' => $nim,
        'nama' => $nama,
    ]);

    $user = $statement->fetch();

    if (!is_array($user)) {
        return null;
    }

    $user['id_pengguna'] = (int) $user['id_pengguna'];
    $user['is_active'] = (bool) $user['is_active'];

    return $user;
}

/** Mengambil akun petugas/admin berdasarkan username tanpa membedakan huruf besar-kecil. */
function find_staff_by_username(string $username): ?array
{
    $sql = <<<'SQL'
        SELECT
            id_pengguna,
            nim,
            username,
            password_hash,
            nama,
            role,
            CASE WHEN is_active THEN 1 ELSE 0 END AS active_flag
        FROM pengguna
        WHERE LOWER(username) = LOWER(:username)
          AND role IN ('petugas', 'admin')
        LIMIT 1
    SQL;

    $statement = db()->prepare($sql);
    $statement->execute(['username' => $username]);
    $user = $statement->fetch();

    if (!is_array($user)) {
        return null;
    }

    $user['id_pengguna'] = (int) $user['id_pengguna'];
    $user['is_active'] = (int) $user['active_flag'] === 1;
    unset($user['active_flag']);

    return $user;
}

function update_staff_last_login(int $userId): void
{
    $statement = db()->prepare(
        "UPDATE pengguna
         SET last_login = NOW(), updated_at = NOW()
         WHERE id_pengguna = :id
           AND role IN ('petugas', 'admin')
           AND is_active = TRUE"
    );

    $statement->execute(['id' => $userId]);
}

function find_user_for_auth_session(int $userId): ?array
{
    $sql = <<<'SQL'
        SELECT
            id_pengguna,
            nim,
            username,
            nama,
            role,
            CASE WHEN is_active THEN 1 ELSE 0 END AS active_flag
        FROM pengguna
        WHERE id_pengguna = :id
        LIMIT 1
    SQL;

    $statement = db()->prepare($sql);
    $statement->execute(['id' => $userId]);
    $user = $statement->fetch();

    if (!is_array($user)) {
        return null;
    }

    $user['id_pengguna'] = (int) $user['id_pengguna'];
    $user['is_active'] = (int) $user['active_flag'] === 1;
    unset($user['active_flag']);

    return $user;
}
