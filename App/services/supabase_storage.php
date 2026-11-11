<?php

declare(strict_types=1);

/**
 * Membaca konfigurasi Supabase dari .env.
 * Secret key hanya digunakan di sisi PHP dan tidak boleh dikirim ke browser.
 */
function supabase_storage_config(): array
{
    $projectUrl = trim((string) ($_ENV['SUPABASE_URL'] ?? ''));
    $secretKey = trim((string) ($_ENV['SUPABASE_SECRET_KEY'] ?? ''));

    if ($projectUrl === '' || $secretKey === '') {
        throw new RuntimeException(
            'Konfigurasi Supabase Storage belum lengkap. Periksa SUPABASE_URL dan SUPABASE_SECRET_KEY.'
        );
    }

    $parts = parse_url($projectUrl);
    $scheme = strtolower((string) ($parts['scheme'] ?? ''));
    $host = strtolower((string) ($parts['host'] ?? ''));
    $isLocalHost = in_array($host, ['localhost', '127.0.0.1', '::1'], true);

    if (
        $host === ''
        || ($scheme !== 'https' && !($scheme === 'http' && $isLocalHost))
    ) {
        throw new RuntimeException('SUPABASE_URL harus menggunakan HTTPS.');
    }

    return [
        'url' => rtrim($projectUrl, '/'),
        'key' => $secretKey,
    ];
}

/** Mengambil nama bucket untuk menyimpan laporan yang belum diverifikasi. */
function supabase_storage_pending_bucket(): string
{
    $bucket = trim((string) ($_ENV['SUPABASE_BUCKET_PENDING_REPORTS'] ?? 'laporan-pending'));

    if (preg_match('/^[a-z0-9][a-z0-9_-]{1,62}$/', $bucket) !== 1) {
        throw new RuntimeException('Nama bucket laporan pending tidak valid.');
    }

    return $bucket;
}

/**
 * Mengubah path objek menjadi path URL yang aman.
 * Slash antar-folder dipertahankan, setiap nama folder/file di-encode.
 */
function supabase_storage_encode_path(string $path): string
{
    $path = trim($path, '/');
    $segments = explode('/', $path);

    if ($path === '') {
        throw new InvalidArgumentException('Path file Storage tidak boleh kosong.');
    }

    foreach ($segments as $segment) {
        if ($segment === '' || $segment === '.' || $segment === '..') {
            throw new InvalidArgumentException('Path file Storage tidak valid.');
        }
    }

    return implode('/', array_map('rawurlencode', $segments));
}

/**
 * Menjalankan satu request HTTP ke Supabase Storage.
 * API key baru (sb_secret_...) dikirim melalui header apikey.
 * Untuk kompatibilitas key lama service_role berbentuk JWT, Bearer juga dikirim.
 */
function supabase_storage_http_request(
    string $method,
    string $url,
    array $extraHeaders = [],
    ?string $body = null
): array {
    if (!function_exists('curl_init')) {
        throw new RuntimeException('Ekstensi PHP cURL belum aktif.');
    }

    $config = supabase_storage_config();
    $headers = [
        'apikey: ' . $config['key'],
        'Accept: application/json',
    ];

    if (str_starts_with($config['key'], 'eyJ')) {
        $headers[] = 'Authorization: Bearer ' . $config['key'];
    }

    $headers = array_merge($headers, $extraHeaders);
    $curl = curl_init($url);

    if ($curl === false) {
        throw new RuntimeException('Tidak dapat memulai koneksi ke Supabase Storage.');
    }

    $options = [
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => $headers,
    ];

    if ($body !== null) {
        $options[CURLOPT_POSTFIELDS] = $body;
    }

    curl_setopt_array($curl, $options);
    $responseBody = curl_exec($curl);
    $curlError = curl_error($curl);
    $httpStatus = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    if ($responseBody === false) {
        return [
            'success' => false,
            'status' => $httpStatus,
            'body' => '',
            'error' => $curlError !== '' ? $curlError : 'Koneksi ke Storage gagal.',
        ];
    }

    return [
        'success' => $httpStatus >= 200 && $httpStatus < 300,
        'status' => $httpStatus,
        'body' => (string) $responseBody,
        'error' => null,
    ];
}

/** Mengambil pesan error aman dari respons Storage untuk log server. */
function supabase_storage_error_message(array $response): string
{
    if (!empty($response['error'])) {
        return (string) $response['error'];
    }

    $decoded = json_decode((string) ($response['body'] ?? ''), true);

    if (is_array($decoded)) {
        foreach (['message', 'error', 'statusCode'] as $key) {
            if (isset($decoded[$key]) && is_scalar($decoded[$key])) {
                return (string) $decoded[$key];
            }
        }
    }

    return 'Supabase Storage mengembalikan HTTP ' . (int) ($response['status'] ?? 0) . '.';
}

/**
 * Mengunggah isi file sementara PHP ke bucket Supabase.
 * Fungsi melempar RuntimeException jika upload gagal.
 */
function supabase_storage_upload_file(
    string $bucket,
    string $path,
    string $temporaryFile,
    string $mimeType
): void {
    if (!is_file($temporaryFile) || !is_readable($temporaryFile)) {
        throw new RuntimeException('File sementara tidak dapat dibaca.');
    }

    $fileContents = file_get_contents($temporaryFile);

    if ($fileContents === false) {
        throw new RuntimeException('Gagal membaca file yang akan diunggah.');
    }

    $config = supabase_storage_config();
    $url = $config['url']
        . '/storage/v1/object/'
        . rawurlencode($bucket)
        . '/'
        . supabase_storage_encode_path($path);

    $response = supabase_storage_http_request(
        'POST',
        $url,
        [
            'Content-Type: ' . $mimeType,
            'Cache-Control: 3600',
            'x-upsert: false',
        ],
        $fileContents
    );

    if (!$response['success']) {
        $message = supabase_storage_error_message($response);
        error_log('[TitikTemu] Supabase upload failed: ' . $message);
        throw new RuntimeException('Foto gagal diunggah ke penyimpanan. Silakan coba lagi.');
    }
}

/**
 * Menghapus objek Storage setelah transaksi database gagal.
 * Return false jika pembersihan gagal agar pemanggil dapat mencatat log.
 */
function supabase_storage_delete_objects(string $bucket, array $paths): bool
{
    if ($paths === []) {
        return true;
    }

    try {
        $config = supabase_storage_config();
        $url = $config['url']
            . '/storage/v1/object/'
            . rawurlencode($bucket);

        $body = json_encode(
            ['prefixes' => array_values($paths)],
            JSON_THROW_ON_ERROR
        );

        $response = supabase_storage_http_request(
            'DELETE',
            $url,
            ['Content-Type: application/json'],
            $body
        );

        if (!$response['success']) {
            error_log(
                '[TitikTemu] Supabase cleanup failed: '
                    . supabase_storage_error_message($response)
            );
            return false;
        }

        return true;
    } catch (Throwable $error) {
        // Jangan sampai kegagalan cleanup menutupi error transaksi database yang asli.
        error_log(
            '[TitikTemu] Supabase cleanup exception: '
                . get_class($error)
        );
        return false;
    }
}
