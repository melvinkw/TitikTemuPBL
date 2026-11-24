<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/queries/staff_queries.php';

/**
 * Penulisan akun staff (admin dan petugas).
 *
 * Validasi di sini mengikuti constraint tabel pengguna:
 *   chk_pengguna_identitas        -> staff wajib punya username, nim harus NULL
 *   chk_staff_username_format     -> ^[A-Za-z][A-Za-z0-9._-]{3,49}$
 *   uq_pengguna_username_lower    -> username unik tanpa membedakan huruf besar-kecil
 *   uq_pengguna_email_lower       -> email unik tanpa membedakan huruf besar-kecil
 *   chk_pengguna_password_by_role -> password staff wajib terisi
 *
 * Kesalahan yang boleh ditampilkan ke admin dilempar sebagai
 * InvalidArgumentException, sedangkan kondisi data sebagai RuntimeException.
 */

/** Teks wajib dengan batas panjang karakter. */
function staff_required_text(string $value, string $label, int $maxLength): string
{
    $value = trim($value);

    if ($value === '') {
        throw new InvalidArgumentException("{$label} wajib diisi.");
    }

    if (text_length($value) > $maxLength) {
        throw new InvalidArgumentException(
            "{$label} maksimal {$maxLength} karakter."
        );
    }

    return $value;
}

/** Teks opsional; kosong disimpan sebagai NULL. */
function staff_optional_text(?string $value, string $label, int $maxLength): ?string
{
    $value = trim((string) $value);

    if ($value === '') {
        return null;
    }

    if (text_length($value) > $maxLength) {
        throw new InvalidArgumentException(
            "{$label} maksimal {$maxLength} karakter."
        );
    }

    return $value;
}

/** Memastikan role termasuk pilihan yang dikenal tabel pengguna. */
function staff_validate_role(string $role): string
{
    if (!in_array($role, ['admin', 'petugas'], true)) {
        throw new InvalidArgumentException('Role akun tidak dikenal.');
    }

    return $role;
}

/** Memvalidasi username sesuai format yang diwajibkan database. */
function staff_validate_username(PDO $pdo, string $username, ?int $ignoreId): string
{
    if (preg_match('/^[A-Za-z][A-Za-z0-9._-]{3,49}$/', $username) !== 1) {
        throw new InvalidArgumentException(
            'Username 4-50 karakter, diawali huruf, dan hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.'
        );
    }

    if (staff_username_taken($pdo, $username, $ignoreId)) {
        throw new InvalidArgumentException('Username sudah dipakai akun lain.');
    }

    return $username;
}

/** Memvalidasi email opsional dan memastikan belum dipakai akun lain. */
function staff_validate_email(PDO $pdo, ?string $email, ?int $ignoreId): ?string
{
    $email = staff_optional_text($email, 'Email', 254);

    if ($email === null) {
        return null;
    }

    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        throw new InvalidArgumentException('Format email tidak valid.');
    }

    if (staff_email_taken($pdo, $email, $ignoreId)) {
        throw new InvalidArgumentException('Email sudah dipakai akun lain.');
    }

    return $email;
}

/** Memvalidasi password baru dan mengembalikan hash-nya. */
function staff_hash_password(string $password): string
{
    if (text_length($password) < 8) {
        throw new InvalidArgumentException('Password minimal 8 karakter.');
    }

    // bcrypt memotong input setelah 72 byte; tolak lebih awal agar tidak
    // ada karakter yang diam-diam diabaikan.
    if (strlen($password) > 72) {
        throw new InvalidArgumentException('Password maksimal 72 karakter.');
    }

    return password_hash($password, PASSWORD_DEFAULT);
}

/** Menambah akun staff baru dan mengembalikan ID-nya. */
function create_staff_account(
    PDO $pdo,
    string $role,
    string $username,
    string $nama,
    ?string $email,
    string $password
): int {
    $role = staff_validate_role($role);
    $username = staff_validate_username($pdo, trim($username), null);
    $nama = staff_required_text($nama, 'Nama lengkap', 100);
    $email = staff_validate_email($pdo, $email, null);
    $passwordHash = staff_hash_password($password);

    $sql = <<<'SQL'
        INSERT INTO pengguna (
            nim,
            username,
            password_hash,
            nama,
            email,
            role,
            is_active
        )
        VALUES (
            NULL,
            :username,
            :password_hash,
            :nama,
            :email,
            :role,
            TRUE
        )
        RETURNING id_pengguna
        SQL;

    $statement = $pdo->prepare($sql);
    $statement->execute([
        'username' => $username,
        'password_hash' => $passwordHash,
        'nama' => $nama,
        'email' => $email,
        'role' => $role,
    ]);

    return (int) $statement->fetchColumn();
}

/**
 * Memperbarui akun staff. Password hanya diubah kalau diisi.
 * Admin tidak boleh menurunkan role akunnya sendiri agar tidak kehilangan akses.
 */
function update_staff_account(
    PDO $pdo,
    int $idPengguna,
    int $idAdmin,
    string $role,
    string $username,
    string $nama,
    ?string $email,
    string $password
): void {
    if ($idPengguna < 1) {
        throw new InvalidArgumentException('ID akun tidak valid.');
    }

    $akun = find_staff_account($pdo, $idPengguna);

    if ($akun === null) {
        throw new RuntimeException('Akun staff tidak ditemukan.');
    }

    $role = staff_validate_role($role);

    if ($idPengguna === $idAdmin && $role !== 'admin') {
        throw new RuntimeException(
            'Role akun sendiri tidak dapat diubah dari administrator.'
        );
    }

    $username = staff_validate_username($pdo, trim($username), $idPengguna);
    $nama = staff_required_text($nama, 'Nama lengkap', 100);
    $email = staff_validate_email($pdo, $email, $idPengguna);

    $passwordHash = null;

    if (trim($password) !== '') {
        $passwordHash = staff_hash_password($password);
    }

    $sql = <<<'SQL'
        UPDATE pengguna
        SET
            username = :username,
            nama = :nama,
            email = :email,
            role = :role,
            updated_at = NOW()
        SQL;

    $params = [
        'username' => $username,
        'nama' => $nama,
        'email' => $email,
        'role' => $role,
        'id_pengguna' => $idPengguna,
    ];

    if ($passwordHash !== null) {
        $sql .= ",\n            password_hash = :password_hash";
        $params['password_hash'] = $passwordHash;
    }

    $sql .= "\n        WHERE id_pengguna = :id_pengguna";

    $statement = $pdo->prepare($sql);
    $statement->execute($params);
}

/** Mengaktifkan atau menonaktifkan akun staff; akun sendiri tidak dapat dinonaktifkan. */
function set_staff_active(PDO $pdo, int $idPengguna, bool $aktif, int $idAdmin): void
{
    if ($idPengguna < 1) {
        throw new InvalidArgumentException('ID akun tidak valid.');
    }

    if ($idPengguna === $idAdmin && !$aktif) {
        throw new RuntimeException('Akun sendiri tidak dapat dinonaktifkan.');
    }

    $sql = <<<'SQL'
        UPDATE pengguna
        SET is_active = :is_active, updated_at = NOW()
        WHERE id_pengguna = :id_pengguna
          AND role IN ('petugas', 'admin')
        SQL;

    $statement = $pdo->prepare($sql);
    $statement->execute([
        // pdo_pgsql mengirim nilai sebagai teks; PHP false menjadi '' dan
        // ditolak PostgreSQL, jadi boolean dikirim sebagai '1'/'0'.
        'is_active' => $aktif ? '1' : '0',
        'id_pengguna' => $idPengguna,
    ]);

    if ($statement->rowCount() === 0) {
        throw new RuntimeException('Akun staff tidak ditemukan.');
    }
}
