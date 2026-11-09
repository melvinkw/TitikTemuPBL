<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/helpers/core.php';
require_once dirname(__DIR__) . '/queries/master_queries.php';
require_once dirname(__DIR__) . '/queries/pengumuman_queries.php';
require_once dirname(__DIR__) . '/services/supabase_storage.php';

const TITIKTEMU_MAX_REPORT_PHOTOS = 3;
const TITIKTEMU_MAX_REPORT_PHOTO_SIZE = 5 * 1024 * 1024;
const TITIKTEMU_MAX_REPORT_DESCRIPTION_LENGTH = 5000;

/** Mengubah nilai POST menjadi integer positif yang valid. */
function report_positive_id(mixed $value, string $fieldLabel): int
{
    if (!is_string($value) && !is_int($value)) {
        throw new InvalidArgumentException($fieldLabel . ' wajib dipilih.');
    }

    $id = filter_var((string) $value, FILTER_VALIDATE_INT);

    if ($id === false || $id < 1) {
        throw new InvalidArgumentException($fieldLabel . ' tidak valid.');
    }

    return (int) $id;
}

/** Membaca field teks POST tanpa menerima array atau tipe tak terduga. */
function report_post_text(array $post, string $key, string $label): string
{
    $value = $post[$key] ?? null;

    if (!is_string($value)) {
        throw new InvalidArgumentException($label . ' wajib diisi.');
    }

    return trim($value);
}

/**
 * Memeriksa dan menormalkan file foto[] dari $_FILES.
 * Validasi MIME dilakukan dari isi file menggunakan Fileinfo, bukan ekstensi.
 *
 * @return array<int, array{tmp_name:string, original_name:string, mime_type:string, extension:string, size:int}>
 */
function normalize_report_uploads(?array $fileBag): array
{
    if ($fileBag === null) {
        return [];
    }

    $names = $fileBag['name'] ?? null;
    $temporaryNames = $fileBag['tmp_name'] ?? null;
    $errors = $fileBag['error'] ?? null;

    if (!is_array($names) || !is_array($temporaryNames) || !is_array($errors)) {
        throw new InvalidArgumentException('Format unggahan foto tidak valid.');
    }

    if (!class_exists('finfo')) {
        throw new RuntimeException('Ekstensi PHP Fileinfo belum aktif.');
    }

    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
    ];

    $fileInfo = new finfo(FILEINFO_MIME_TYPE);
    $normalized = [];

    foreach ($names as $index => $submittedName) {
        $uploadError = (int) ($errors[$index] ?? UPLOAD_ERR_NO_FILE);

        if ($uploadError === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        if ($uploadError !== UPLOAD_ERR_OK) {
            if (in_array($uploadError, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
                throw new InvalidArgumentException('Ukuran foto terlalu besar. Maksimal 5 MB per file.');
            }

            throw new InvalidArgumentException('Salah satu file gagal diunggah. Pilih ulang file tersebut.');
        }

        $temporaryFile = $temporaryNames[$index] ?? '';

        if (!is_string($temporaryFile) || $temporaryFile === '' || !is_uploaded_file($temporaryFile)) {
            throw new InvalidArgumentException('File unggahan tidak dikenali. Silakan pilih ulang file.');
        }

        $actualSize = filesize($temporaryFile);

        if ($actualSize === false || $actualSize < 1) {
            throw new InvalidArgumentException('File kosong atau tidak dapat dibaca.');
        }

        if ($actualSize > TITIKTEMU_MAX_REPORT_PHOTO_SIZE) {
            throw new InvalidArgumentException('Ukuran foto maksimal 5 MB per file.');
        }

        $mimeType = $fileInfo->file($temporaryFile);

        if (!is_string($mimeType) || !isset($allowedMimeTypes[$mimeType])) {
            throw new InvalidArgumentException('Format file harus JPG, PNG, WebP, atau PDF.');
        }

        $originalName = is_string($submittedName) ? $submittedName : 'file';
        $originalName = str_replace('\\', '/', $originalName);
        $originalName = basename($originalName);
        $originalName = preg_replace('/[\x00-\x1F\x7F]/u', '', $originalName) ?? 'file';
        $originalName = trim($originalName);

        if ($originalName === '') {
            $originalName = 'file.' . $allowedMimeTypes[$mimeType];
        }

        if (function_exists('mb_substr')) {
            $originalName = mb_substr($originalName, 0, 255, 'UTF-8');
        } else {
            $originalName = substr($originalName, 0, 255);
        }

        $normalized[] = [
            'tmp_name' => $temporaryFile,
            'original_name' => $originalName,
            'mime_type' => $mimeType,
            'extension' => $allowedMimeTypes[$mimeType],
            'size' => (int) $actualSize,
        ];
    }

    if (count($normalized) > TITIKTEMU_MAX_REPORT_PHOTOS) {
        throw new InvalidArgumentException('Maksimal 3 file untuk setiap laporan.');
    }

    return $normalized;
}

/**
 * Memproses satu laporan mahasiswa.
 *
 * Urutan proses:
 * 1. Validasi input dan file.
 * 2. Mulai transaksi PostgreSQL.
 * 3. Pastikan kategori/pos masih aktif.
 * 4. Simpan pengumuman dengan status menunggu.
 * 5. Unggah foto ke bucket private laporan-pending.
 * 6. Simpan metadata foto dan riwayat status.
 * 7. Commit transaksi.
 *
 * Jika proses database gagal setelah file terunggah, objek Storage dihapus kembali.
 *
 * @return int ID laporan yang berhasil dibuat.
 */
function submit_pengumuman_report(int $idPengguna, array $post, array $files): int
{
    if ($idPengguna < 1) {
        throw new InvalidArgumentException('Sesi pengguna tidak valid. Silakan login kembali.');
    }

    $jenis = report_post_text($post, 'jenis', 'Jenis laporan');

    if (!in_array($jenis, ['hilang', 'temuan'], true)) {
        throw new InvalidArgumentException('Jenis laporan tidak dikenal.');
    }

    $idKategori = report_positive_id($post['id_kategori'] ?? null, 'Kategori barang');
    $judul = report_post_text($post, 'judul', 'Nama barang');
    $emailKontak = report_post_text($post, 'email_kontak', 'Email kontak');
    $deskripsi = report_post_text($post, 'deskripsi', 'Deskripsi barang');
    $lokasiKejadian = report_post_text($post, 'lokasi_kejadian', 'Lokasi kejadian');
    $tanggalInput = report_post_text($post, 'tanggal_waktu_kejadian', 'Tanggal dan waktu kejadian');

    if ($judul === '' || text_length($judul) > 150) {
        throw new InvalidArgumentException('Nama barang wajib diisi dan maksimal 150 karakter.');
    }

    if (
        $emailKontak === ''
        || text_length($emailKontak) > 254
        || filter_var($emailKontak, FILTER_VALIDATE_EMAIL) === false
    ) {
        throw new InvalidArgumentException('Masukkan alamat email kontak yang valid.');
    }

    if ($deskripsi === '' || text_length($deskripsi) > TITIKTEMU_MAX_REPORT_DESCRIPTION_LENGTH) {
        throw new InvalidArgumentException('Deskripsi wajib diisi dan maksimal 5.000 karakter.');
    }

    if ($lokasiKejadian === '' || text_length($lokasiKejadian) > 200) {
        throw new InvalidArgumentException('Lokasi kejadian wajib diisi dan maksimal 200 karakter.');
    }

    try {
        $tanggalWaktu = parse_datetime_local($tanggalInput);
    } catch (RuntimeException $error) {
        throw new InvalidArgumentException($error->getMessage(), 0, $error);
    }

    $idPos = null;

    if ($jenis === 'temuan') {
        $idPos = report_positive_id($post['id_pos'] ?? null, 'Titik penyerahan');
    }

    $photoFiles = normalize_report_uploads(
        isset($files['foto']) && is_array($files['foto'])
            ? $files['foto']
            : null
    );

    if ($jenis === 'temuan' && $photoFiles === []) {
        throw new InvalidArgumentException('Foto wajib diunggah untuk laporan barang temuan.');
    }

    $pdo = db();
    $uploadedPaths = [];
    $bucketName = null;

    try {
        $pdo->beginTransaction();

        if (!kategori_aktif_tersedia($pdo, $idKategori)) {
            throw new InvalidArgumentException('Kategori sudah tidak tersedia. Muat ulang halaman lalu pilih kategori lain.');
        }

        if ($jenis === 'temuan' && ($idPos === null || !pos_aktif_tersedia($pdo, $idPos))) {
            throw new InvalidArgumentException('Titik penyerahan sudah tidak tersedia. Muat ulang halaman lalu pilih pos lain.');
        }

        $idPengumuman = insert_pengumuman_record($pdo, [
            'id_pengguna' => $idPengguna,
            'id_kategori' => $idKategori,
            'id_pos' => $idPos,
            'jenis' => $jenis,
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'email_kontak' => $emailKontak,
            'lokasi_kejadian' => $lokasiKejadian,
            'tanggal_waktu_kejadian' => $tanggalWaktu,
        ]);

        if ($photoFiles !== []) {
            $bucketName = supabase_storage_pending_bucket();

            foreach ($photoFiles as $index => $photo) {
                $filePath = 'pengumuman/'
                    . $idPengumuman
                    . '/'
                    . bin2hex(random_bytes(16))
                    . '.'
                    . $photo['extension'];

                supabase_storage_upload_file(
                    $bucketName,
                    $filePath,
                    $photo['tmp_name'],
                    $photo['mime_type']
                );

                // Simpan path untuk kompensasi jika transaksi DB akhirnya gagal.
                $uploadedPaths[] = $filePath;

                insert_foto_pengumuman_record(
                    $pdo,
                    $idPengumuman,
                    $bucketName,
                    $filePath,
                    $photo['original_name'],
                    $photo['mime_type'],
                    $photo['size'],
                    $index + 1
                );
            }
        }

        insert_riwayat_awal_pengumuman($pdo, $idPengumuman, $idPengguna);
        $pdo->commit();

        return $idPengumuman;
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        if ($bucketName !== null && $uploadedPaths !== []) {
            $cleanupSuccess = supabase_storage_delete_objects($bucketName, $uploadedPaths);

            if (!$cleanupSuccess) {
                error_log(
                    '[TitikTemu] File Storage mungkin tertinggal setelah rollback. '
                    . 'Bucket: ' . $bucketName
                    . '; jumlah file: ' . count($uploadedPaths)
                );
            }
        }

        throw $error;
    }
}
