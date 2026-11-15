<?php
declare(strict_types=1);

require_once __DIR__ . '/supabase_storage.php';

/**
 * Membuat signed URL sementara untuk objek pada bucket private.
 * Fungsi ini hanya dipanggil dari halaman petugas setelah role guard.
 */
function supabase_storage_create_signed_url(
    string $bucket,
    string $path,
    int $expiresIn = 300
): string {
    if ($expiresIn < 60 || $expiresIn > 3600) {
        throw new InvalidArgumentException('Masa berlaku signed URL harus 60 sampai 3.600 detik.');
    }

    $config = supabase_storage_config();
    $url = $config['url']
        . '/storage/v1/object/sign/'
        . rawurlencode($bucket)
        . '/'
        . supabase_storage_encode_path($path);

    $body = json_encode(['expiresIn' => $expiresIn], JSON_THROW_ON_ERROR);
    $response = supabase_storage_http_request(
        'POST',
        $url,
        ['Content-Type: application/json'],
        $body
    );

    if (!$response['success']) {
        error_log('[TitikTemu] Signed URL gagal dibuat: ' . supabase_storage_error_message($response));
        throw new RuntimeException('Foto laporan sementara tidak dapat ditampilkan.');
    }

    $decoded = json_decode($response['body'], true);
    $signedPath = is_array($decoded) ? ($decoded['signedURL'] ?? null) : null;

    if (!is_string($signedPath) || $signedPath === '') {
        throw new RuntimeException('Respons signed URL dari Storage tidak valid.');
    }

    if (preg_match('~^https://~i', $signedPath) === 1) {
        return $signedPath;
    }

    if (!str_starts_with($signedPath, '/')) {
        $signedPath = '/' . $signedPath;
    }

    return $config['url'] . '/storage/v1' . $signedPath;
}
