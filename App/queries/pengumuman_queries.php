<?php

declare(strict_types=1);

/**
 * Menambahkan satu laporan kehilangan/penemuan ke tabel pengumuman.
 * Status awal selalu 'menunggu' karena laporan harus diverifikasi petugas.
 *
 * @return int ID pengumuman yang baru dibuat.
 */
function insert_pengumuman_record(PDO $pdo, array $data): int
{
    $sql = <<<'SQL'
        INSERT INTO pengumuman (
            id_pengguna,
            id_kategori,
            id_lokasi,
            id_pos,
            jenis,
            judul,
            deskripsi,
            email_kontak,
            lokasi_kejadian,
            tanggal_waktu_kejadian,
            detail_lokasi,
            status
        ) VALUES (
            :id_pengguna,
            :id_kategori,
            NULL,
            :id_pos,
            :jenis,
            :judul,
            :deskripsi,
            :email_kontak,
            :lokasi_kejadian,
            :tanggal_waktu_kejadian,
            NULL,
            'menunggu'
        )
        RETURNING id_pengumuman
        SQL;

    $statement = $pdo->prepare($sql);
    $statement->execute([
        'id_pengguna' => $data['id_pengguna'],
        'id_kategori' => $data['id_kategori'],
        'id_pos' => $data['id_pos'],
        'jenis' => $data['jenis'],
        'judul' => $data['judul'],
        'deskripsi' => $data['deskripsi'],
        'email_kontak' => $data['email_kontak'],
        'lokasi_kejadian' => $data['lokasi_kejadian'],
        'tanggal_waktu_kejadian' => $data['tanggal_waktu_kejadian'],
    ]);

    $idPengumuman = $statement->fetchColumn();

    if ($idPengumuman === false) {
        throw new RuntimeException('Database tidak mengembalikan ID laporan baru.');
    }

    return (int) $idPengumuman;
}

/** Menyimpan metadata file yang sudah berhasil diunggah ke Supabase Storage. */
function insert_foto_pengumuman_record(
    PDO $pdo,
    int $idPengumuman,
    string $bucketName,
    string $filePath,
    string $originalName,
    string $mimeType,
    int $sizeBytes,
    int $urutan
): void {
    $sql = <<<'SQL'
        INSERT INTO foto_pengumuman (
            id_pengumuman,
            bucket_name,
            file_path,
            nama_file,
            mime_type,
            ukuran_byte,
            urutan
        ) VALUES (
            :id_pengumuman,
            :bucket_name,
            :file_path,
            :nama_file,
            :mime_type,
            :ukuran_byte,
            :urutan
        )
        SQL;

    $statement = $pdo->prepare($sql);
    $statement->execute([
        'id_pengumuman' => $idPengumuman,
        'bucket_name' => $bucketName,
        'file_path' => $filePath,
        'nama_file' => $originalName,
        'mime_type' => $mimeType,
        'ukuran_byte' => $sizeBytes,
        'urutan' => $urutan,
    ]);
}

/** Mencatat status awal laporan pada tabel riwayat_status_pengumuman. */
function insert_riwayat_awal_pengumuman(
    PDO $pdo,
    int $idPengumuman,
    int $idPengguna
): void {
    $sql = <<<'SQL'
        INSERT INTO riwayat_status_pengumuman (
            id_pengumuman,
            status_sebelumnya,
            status_baru,
            id_pengubah,
            catatan
        ) VALUES (
            :id_pengumuman,
            NULL,
            'menunggu',
            :id_pengubah,
            'Laporan dibuat dan menunggu verifikasi petugas.'
        )
        SQL;

    $statement = $pdo->prepare($sql);
    $statement->execute([
        'id_pengumuman' => $idPengumuman,
        'id_pengubah' => $idPengguna,
    ]);
}
