<?php

declare(strict_types=1);

//Menghitung panjang teks dengan memperhatikan karakter UTF-8
function text_length(string $value): int
{
    return function_exists('mb_strlen')
        ? mb_strlen($value, 'UTF-8')
        : strlen($value);
}

//Mengamankan teks sebelum ditampilkan ke dalam HTML
function h(mixed $value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

//Membuat URL berdasarkan lokasi folder Public
function base_url(string $path = ''): string
{
    $script = str_replace(
        '\\',
        '/',
        $_SERVER['SCRIPT_NAME'] ?? '/index.php'
    );

    // Mendeteksi folder Public, termasuk huruf kapital.
    $pattern = '~/(public)(?=/|$)~i';

    if (preg_match($pattern, $script, $matches, PREG_OFFSET_CAPTURE)) {
        $position = $matches[0][1];
        $publicName = $matches[1][0];

        $base = substr($script, 0, $position)
            . '/'
            . $publicName;
    } else {
        // Digunakan jika Public menjadi document root.
        $base = '';
    }

    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

//Mengarahkan pengguna ke halaman lain dalam aplikasi
function redirect(string $path): never
{
    header('Location: ' . base_url($path));
    exit;
}

//Mencegah redirect menuju alamat eksternal
function safe_return_path(?string $path): string
{
    if (
        !$path
        || !str_starts_with($path, '/')
        || str_starts_with($path, '//')
        || str_contains($path, "\r")
        || str_contains($path, "\n")
    ) {
        return '/dashboard.php';
    }

    return $path;
}

//Membuat token keamanan untuk melindungi for
function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string) $_SESSION['_csrf_token'];
}

//Membuat hidden input untuk disertakan dalam form
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="'
        . h(csrf_token())
        . '">';
}

//Memeriksa token yang dikirim oleh form
function verify_csrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    $known = $_SESSION['_csrf_token'] ?? '';

    if (
        !is_string($submitted)
        || !is_string($known)
        || $known === ''
        || !hash_equals($known, $submitted)
    ) {
        http_response_code(419);

        exit('Token formulir tidak valid atau sesi sudah berakhir. '
            . 'Kembali ke halaman lalu coba lagi.');
    }
}

//Menyimpan pesan sementara untuk ditampilkan pada halaman berikutnya
function flash(?array $replacement = null): ?array
{
    if ($replacement !== null) {
        $_SESSION['flash'] = $replacement;
        return null;
    }

    $item = $_SESSION['flash'] ?? null;

    unset($_SESSION['flash']);

    return is_array($item) ? $item : null;
}

//Membatasi proses tertentu agar hanya menerima HTTP POST
function require_post(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        http_response_code(405);

        header('Allow: POST');

        exit('Metode request tidak diizinkan.');
    }
}

//Mengubah tanggal database menjadi format yang mudah dibaca
function format_date_time(?string $value): string
{
    if (!$value) {
        return '—';
    }

    try {
        return (new DateTimeImmutable($value))
            ->setTimezone(new DateTimeZone('Asia/Jakarta'))
            ->format('d-m-Y H:i');
    } catch (Throwable) {
        return (string) $value;
    }
}

//Mengubah tanggal dari database menjadi format input datetime-local
function datetime_local_value(?string $value): string
{
    $timezone = new DateTimeZone('Asia/Jakarta');

    if (!$value) {
        return (new DateTimeImmutable('now', $timezone))
            ->format('Y-m-d\TH:i');
    }

    try {
        return (new DateTimeImmutable($value))
            ->setTimezone($timezone)
            ->format('Y-m-d\TH:i');
    } catch (Throwable) {
        return (new DateTimeImmutable('now', $timezone))
            ->format('Y-m-d\TH:i');
    }
}

//Memvalidasi tanggal dari form sebelum disimpan ke database
function parse_datetime_local(string $value): string
{
    $value = trim($value);

    $timezone = new DateTimeZone('Asia/Jakarta');

    $date = DateTimeImmutable::createFromFormat(
        '!Y-m-d\TH:i',
        $value,
        $timezone
    );

    $errors = DateTimeImmutable::getLastErrors();

    if (
        !$date
        || (
            $errors !== false
            && (
                $errors['warning_count'] > 0
                || $errors['error_count'] > 0
            )
        )
        || $date->format('Y-m-d\TH:i') !== $value
    ) {
        throw new RuntimeException(
            'Tanggal dan waktu kejadian wajib diisi dengan benar.'
        );
    }

    // Waktu kejadian tidak boleh lebih dari 5 menit ke depan.
    if (
        $date > new DateTimeImmutable('+5 minutes', $timezone)
    ) {
        throw new RuntimeException(
            'Tanggal dan waktu kejadian tidak boleh jauh di masa depan.'
        );
    }

    return $date->format('Y-m-d H:i:sP');
}
