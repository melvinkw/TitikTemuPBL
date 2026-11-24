<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/queries/master_queries.php';

/**
 * Penulisan data master (kategori, lokasi, pos penyerahan).
 *
 * Semua fungsi memvalidasi input dan melempar InvalidArgumentException untuk
 * kesalahan yang boleh ditampilkan ke admin, atau RuntimeException untuk
 * kondisi data yang tidak memenuhi aturan. Halaman pemanggil menangkap
 * keduanya dan mengubahnya menjadi pesan flash.
 */

/** Membersihkan teks wajib dan memastikan panjangnya wajar. */
function master_required_text(string $value, string $label, int $maxLength): string
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

/** Membersihkan teks opsional; nilai kosong disimpan sebagai NULL. */
function master_optional_text(?string $value, string $label, int $maxLength): ?string
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

/** Memastikan ID baris data master valid sebelum dipakai. */
function master_require_id(int $id, string $label): int
{
    if ($id < 1) {
        throw new InvalidArgumentException("ID {$label} tidak valid.");
    }

    return $id;
}

/** Mengubah pelanggaran UNIQUE menjadi pesan yang mudah dipahami. */
function master_unique_error(PDOException $error, string $message): RuntimeException
{
    if ($error->getCode() === '23505') {
        return new RuntimeException($message, 0, $error);
    }

    return new RuntimeException(
        'Data master gagal disimpan karena kesalahan database.',
        0,
        $error
    );
}

/** Menambah kategori barang baru dan mengembalikan ID-nya. */
function create_kategori(PDO $pdo, string $nama, ?string $deskripsi): int
{
    $nama = master_required_text($nama, 'Nama kategori', 50);
    $deskripsi = master_optional_text($deskripsi, 'Deskripsi', 2000);

    $sql = <<<'SQL'
        INSERT INTO kategori (nama_kategori, deskripsi)
        VALUES (:nama_kategori, :deskripsi)
        RETURNING id_kategori
        SQL;

    try {
        $statement = $pdo->prepare($sql);
        $statement->execute([
            'nama_kategori' => $nama,
            'deskripsi' => $deskripsi,
        ]);
    } catch (PDOException $error) {
        throw master_unique_error(
            $error,
            'Nama kategori sudah dipakai.'
        );
    }

    return (int) $statement->fetchColumn();
}

/** Memperbarui nama dan deskripsi kategori. */
function update_kategori(PDO $pdo, int $idKategori, string $nama, ?string $deskripsi): void
{
    $idKategori = master_require_id($idKategori, 'kategori');
    $nama = master_required_text($nama, 'Nama kategori', 50);
    $deskripsi = master_optional_text($deskripsi, 'Deskripsi', 2000);

    $sql = <<<'SQL'
        UPDATE kategori
        SET nama_kategori = :nama_kategori,
            deskripsi = :deskripsi,
            updated_at = NOW()
        WHERE id_kategori = :id_kategori
        SQL;

    try {
        $statement = $pdo->prepare($sql);
        $statement->execute([
            'nama_kategori' => $nama,
            'deskripsi' => $deskripsi,
            'id_kategori' => $idKategori,
        ]);
    } catch (PDOException $error) {
        throw master_unique_error(
            $error,
            'Nama kategori sudah dipakai.'
        );
    }

    if ($statement->rowCount() !== 1) {
        throw new RuntimeException('Kategori tidak ditemukan.');
    }
}

/** Mengaktifkan atau menonaktifkan kategori tanpa menghapus datanya. */
function set_kategori_active(PDO $pdo, int $idKategori, bool $aktif): void
{
    $idKategori = master_require_id($idKategori, 'kategori');

    $statement = $pdo->prepare(
        'UPDATE kategori
         SET is_active = :is_active,
             updated_at = NOW()
         WHERE id_kategori = :id_kategori'
    );
    $statement->execute([
        'is_active' => $aktif ? '1' : '0',
        'id_kategori' => $idKategori,
    ]);

    if ($statement->rowCount() !== 1) {
        throw new RuntimeException('Kategori tidak ditemukan.');
    }
}

/** Menambah lokasi (titik penyerahan) baru dan mengembalikan ID-nya. */
function create_lokasi(PDO $pdo, string $nama, ?string $deskripsi): int
{
    $nama = master_required_text($nama, 'Nama lokasi', 100);
    $deskripsi = master_optional_text($deskripsi, 'Deskripsi', 2000);

    $sql = <<<'SQL'
        INSERT INTO lokasi (nama_lokasi, deskripsi)
        VALUES (:nama_lokasi, :deskripsi)
        RETURNING id_lokasi
        SQL;

    try {
        $statement = $pdo->prepare($sql);
        $statement->execute([
            'nama_lokasi' => $nama,
            'deskripsi' => $deskripsi,
        ]);
    } catch (PDOException $error) {
        throw master_unique_error(
            $error,
            'Nama lokasi sudah dipakai.'
        );
    }

    return (int) $statement->fetchColumn();
}

/** Memperbarui nama dan deskripsi lokasi. */
function update_lokasi(PDO $pdo, int $idLokasi, string $nama, ?string $deskripsi): void
{
    $idLokasi = master_require_id($idLokasi, 'lokasi');
    $nama = master_required_text($nama, 'Nama lokasi', 100);
    $deskripsi = master_optional_text($deskripsi, 'Deskripsi', 2000);

    $sql = <<<'SQL'
        UPDATE lokasi
        SET nama_lokasi = :nama_lokasi,
            deskripsi = :deskripsi,
            updated_at = NOW()
        WHERE id_lokasi = :id_lokasi
        SQL;

    try {
        $statement = $pdo->prepare($sql);
        $statement->execute([
            'nama_lokasi' => $nama,
            'deskripsi' => $deskripsi,
            'id_lokasi' => $idLokasi,
        ]);
    } catch (PDOException $error) {
        throw master_unique_error(
            $error,
            'Nama lokasi sudah dipakai.'
        );
    }

    if ($statement->rowCount() !== 1) {
        throw new RuntimeException('Lokasi tidak ditemukan.');
    }
}

/** Mengaktifkan atau menonaktifkan lokasi. */
function set_lokasi_active(PDO $pdo, int $idLokasi, bool $aktif): void
{
    $idLokasi = master_require_id($idLokasi, 'lokasi');

    $statement = $pdo->prepare(
        'UPDATE lokasi
         SET is_active = :is_active,
             updated_at = NOW()
         WHERE id_lokasi = :id_lokasi'
    );
    $statement->execute([
        'is_active' => $aktif ? '1' : '0',
        'id_lokasi' => $idLokasi,
    ]);

    if ($statement->rowCount() !== 1) {
        throw new RuntimeException('Lokasi tidak ditemukan.');
    }
}

/**
 * Memastikan jenis pos termasuk pilihan yang dikenal database.
 * Nilainya mengikuti CHECK constraint pos_penyerahan_jenis_pos_check.
 */
function master_valid_jenis_pos(string $jenis): string
{
    $jenis = trim($jenis);

    if (!array_key_exists($jenis, get_jenis_pos_options())) {
        throw new InvalidArgumentException('Jenis pos penyerahan tidak valid.');
    }

    return $jenis;
}

/** Memastikan lokasi induk pos benar-benar ada. */
function master_require_lokasi(PDO $pdo, int $idLokasi): int
{
    $idLokasi = master_require_id($idLokasi, 'lokasi');

    $statement = $pdo->prepare(
        'SELECT EXISTS (
            SELECT 1 FROM lokasi WHERE id_lokasi = :id_lokasi
        )'
    );
    $statement->execute(['id_lokasi' => $idLokasi]);

    if (!(bool) $statement->fetchColumn()) {
        throw new InvalidArgumentException('Lokasi yang dipilih tidak ditemukan.');
    }

    return $idLokasi;
}

/** Menambah pos penyerahan baru dan mengembalikan ID-nya. */
function create_pos(
    PDO $pdo,
    string $namaPos,
    string $jenisPos,
    int $idLokasi,
    ?string $detailLokasi
): int {
    $namaPos = master_required_text($namaPos, 'Nama pos penyerahan', 100);
    $jenisPos = master_valid_jenis_pos($jenisPos);
    $idLokasi = master_require_lokasi($pdo, $idLokasi);
    $detailLokasi = master_optional_text(
        $detailLokasi,
        'Detail lokasi',
        150
    );

    $sql = <<<'SQL'
        INSERT INTO pos_penyerahan (
            nama_pos,
            jenis_pos,
            id_lokasi,
            detail_lokasi
        ) VALUES (
            :nama_pos,
            :jenis_pos,
            :id_lokasi,
            :detail_lokasi
        )
        RETURNING id_pos
        SQL;

    try {
        $statement = $pdo->prepare($sql);
        $statement->execute([
            'nama_pos' => $namaPos,
            'jenis_pos' => $jenisPos,
            'id_lokasi' => $idLokasi,
            'detail_lokasi' => $detailLokasi,
        ]);
    } catch (PDOException $error) {
        throw master_unique_error(
            $error,
            'Nama pos penyerahan sudah dipakai pada lokasi tersebut.'
        );
    }

    return (int) $statement->fetchColumn();
}

/** Memperbarui data pos penyerahan. */
function update_pos(
    PDO $pdo,
    int $idPos,
    string $namaPos,
    string $jenisPos,
    int $idLokasi,
    ?string $detailLokasi
): void {
    $idPos = master_require_id($idPos, 'pos penyerahan');
    $namaPos = master_required_text($namaPos, 'Nama pos penyerahan', 100);
    $jenisPos = master_valid_jenis_pos($jenisPos);
    $idLokasi = master_require_lokasi($pdo, $idLokasi);
    $detailLokasi = master_optional_text(
        $detailLokasi,
        'Detail lokasi',
        150
    );

    $sql = <<<'SQL'
        UPDATE pos_penyerahan
        SET nama_pos = :nama_pos,
            jenis_pos = :jenis_pos,
            id_lokasi = :id_lokasi,
            detail_lokasi = :detail_lokasi,
            updated_at = NOW()
        WHERE id_pos = :id_pos
        SQL;

    try {
        $statement = $pdo->prepare($sql);
        $statement->execute([
            'nama_pos' => $namaPos,
            'jenis_pos' => $jenisPos,
            'id_lokasi' => $idLokasi,
            'detail_lokasi' => $detailLokasi,
            'id_pos' => $idPos,
        ]);
    } catch (PDOException $error) {
        throw master_unique_error(
            $error,
            'Nama pos penyerahan sudah dipakai pada lokasi tersebut.'
        );
    }

    if ($statement->rowCount() !== 1) {
        throw new RuntimeException('Pos penyerahan tidak ditemukan.');
    }
}

/** Mengaktifkan atau menonaktifkan pos penyerahan. */
function set_pos_active(PDO $pdo, int $idPos, bool $aktif): void
{
    $idPos = master_require_id($idPos, 'pos penyerahan');

    $statement = $pdo->prepare(
        'UPDATE pos_penyerahan
         SET is_active = :is_active,
             updated_at = NOW()
         WHERE id_pos = :id_pos'
    );
    $statement->execute([
        'is_active' => $aktif ? '1' : '0',
        'id_pos' => $idPos,
    ]);

    if ($statement->rowCount() !== 1) {
        throw new RuntimeException('Pos penyerahan tidak ditemukan.');
    }
}
