<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/helpers/core.php';
require_once dirname(__DIR__) . '/queries/klaim_queries.php';
require_once dirname(__DIR__) . '/services/supabase_storage.php';

const TITIKTEMU_MAX_CLAIM_EVIDENCE_SIZE = 5 * 1024 * 1024;
const TITIKTEMU_MAX_CLAIM_DESCRIPTION_LENGTH = 3000;

/** Membaca satu field teks POST dan menolak tipe selain string. */
function claim_post_text(array $post, string $key, string $label): string
{
    $value = $post[$key] ?? null;

    if (!is_string($value)) {
        throw new InvalidArgumentException($label . ' wajib diisi.');
    }

    return trim($value);
}

/** Validasi ID laporan dari request. */
function claim_positive_id(mixed $value): int
{
    if (!is_string($value) && !is_int($value)) {
        throw new InvalidArgumentException('ID laporan tidak valid.');
    }

    $id = filter_var((string) $value, FILTER_VALIDATE_INT);

    if ($id === false || $id < 1) {
        throw new InvalidArgumentException('ID laporan tidak valid.');
    }

    return (int) $id;
}

/**
 * Memvalidasi satu file bukti dari input name="bukti".
 * Database saat ini hanya memiliki satu kolom bukti_path.
 *
 * @return array{tmp_name:string, original_name:string, mime_type:string, extension:string, size:int}
 */
function normalize_claim_evidence(?array $file): array
{
    if ($file === null || !isset($file['error'])) {
        throw new InvalidArgumentException('Foto bukti kepemilikan wajib diunggah.');
    }

    $error = (int) $file['error'];

    if ($error === UPLOAD_ERR_NO_FILE) {
        throw new InvalidArgumentException('Foto bukti kepemilikan wajib diunggah.');
    }

    if ($error !== UPLOAD_ERR_OK) {
        if (in_array($error, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
            throw new InvalidArgumentException('Ukuran file maksimal 5 MB.');
        }

        throw new InvalidArgumentException('File bukti gagal diunggah. Silakan pilih ulang.');
    }

    $temporaryFile = $file['tmp_name'] ?? null;

    if (
        !is_string($temporaryFile)
        || $temporaryFile === ''
        || !is_uploaded_file($temporaryFile)
    ) {
        throw new InvalidArgumentException('File unggahan tidak dikenali. Silakan pilih ulang.');
    }

    $size = filesize($temporaryFile);

    if ($size === false || $size < 1) {
        throw new InvalidArgumentException('File kosong atau tidak dapat dibaca.');
    }

    if ($size > TITIKTEMU_MAX_CLAIM_EVIDENCE_SIZE) {
        throw new InvalidArgumentException('Ukuran file maksimal 5 MB.');
    }

    if (!class_exists('finfo')) {
        throw new RuntimeException('Ekstensi PHP Fileinfo belum aktif.');
    }

    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($temporaryFile);
    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
    ];

    if (!is_string($mimeType) || !isset($allowedTypes[$mimeType])) {
        throw new InvalidArgumentException('Format bukti harus JPG, PNG, WebP, atau PDF.');
    }

    $originalName = $file['name'] ?? 'bukti';
    $originalName = is_string($originalName) ? $originalName : 'bukti';
    $originalName = basename(str_replace('\\', '/', $originalName));
    $originalName = preg_replace('/[\x00-\x1F\x7F]/u', '', $originalName) ?? 'bukti';
    $originalName = trim($originalName);

    if ($originalName === '') {
        $originalName = 'bukti.' . $allowedTypes[$mimeType];
    }

    $originalName = function_exists('mb_substr')
        ? mb_substr($originalName, 0, 255, 'UTF-8')
        : substr($originalName, 0, 255);

    return [
        'tmp_name' => $temporaryFile,
        'original_name' => $originalName,
        'mime_type' => $mimeType,
        'extension' => $allowedTypes[$mimeType],
        'size' => (int) $size,
    ];
}

/** Nama bucket klaim private, dapat dioverride melalui .env. */
function supabase_storage_claim_bucket(): string
{
    $bucket = trim((string) ($_ENV['SUPABASE_BUCKET_CLAIMS'] ?? 'klaim-private'));

    if (preg_match('/^[a-z0-9][a-z0-9_-]{1,62}$/', $bucket) !== 1) {
        throw new RuntimeException('Nama bucket bukti klaim tidak valid.');
    }

    return $bucket;
}

/**
 * Memproses pengajuan klaim mahasiswa.
 * Objek Storage dihapus kembali jika penyimpanan database gagal.
 */
function submit_mahasiswa_claim(int $idPengguna, array $post, array $files): int
{
    if ($idPengguna < 1) {
        throw new InvalidArgumentException('Sesi pengguna tidak valid. Silakan login kembali.');
    }

    $idPengumuman = claim_positive_id($post['id_pengumuman'] ?? null);
    $deskripsiBukti = claim_post_text($post, 'deskripsi_bukti', 'Deskripsi bukti');

    if (
        $deskripsiBukti === ''
        || text_length($deskripsiBukti) > TITIKTEMU_MAX_CLAIM_DESCRIPTION_LENGTH
    ) {
        throw new InvalidArgumentException('Deskripsi bukti wajib diisi dan maksimal 3.000 karakter.');
    }

    $evidence = normalize_claim_evidence(
        isset($files['bukti']) && is_array($files['bukti'])
            ? $files['bukti']
            : null
    );

    $pdo = db();
    $uploadedPath = null;
    $bucket = supabase_storage_claim_bucket();

    try {
        $pdo->beginTransaction();

        $report = get_mahasiswa_claimable_report($pdo, $idPengumuman, $idPengguna);

        if ($report === null) {
            throw new InvalidArgumentException(
                'Barang tidak tersedia untuk diklaim atau bukan barang temuan milik pengguna lain.'
            );
        }

        if (find_mahasiswa_active_claim($pdo, $idPengumuman, $idPengguna) !== null) {
            throw new InvalidArgumentException('Kamu sudah memiliki klaim aktif untuk barang ini.');
        }

        $uploadedPath = 'klaim/'
            . $idPengumuman
            . '/'
            . $idPengguna
            . '/'
            . bin2hex(random_bytes(16))
            . '.'
            . $evidence['extension'];

        supabase_storage_upload_file(
            $bucket,
            $uploadedPath,
            $evidence['tmp_name'],
            $evidence['mime_type']
        );

        $idKlaim = insert_klaim_record(
            $pdo,
            $idPengumuman,
            $idPengguna,
            $deskripsiBukti,
            $bucket,
            $uploadedPath
        );

        $pdo->commit();

        return $idKlaim;
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        if ($uploadedPath !== null) {
            supabase_storage_delete_objects($bucket, [$uploadedPath]);
        }

        // Unique index PostgreSQL tetap menjadi perlindungan terhadap request bersamaan.
        if ($error instanceof PDOException && $error->getCode() === '23505') {
            throw new InvalidArgumentException(
                'Klaim aktif untuk barang ini sudah pernah kamu ajukan.',
                0,
                $error
            );
        }

        throw $error;
    }
}
