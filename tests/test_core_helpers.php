<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| CORE UTILITIES TEST
|--------------------------------------------------------------------------
| Pengujian fungsi dasar TitikTemu tanpa membutuhkan database.
*/

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$_SERVER['SCRIPT_NAME'] = '/PBL_TitikTemu/Public/index.php';
$_SERVER['REQUEST_METHOD'] = 'POST';

require_once dirname(__DIR__) . '/App/helpers/core.php';

$passed = 0;
$failed = 0;

function test_result(string $name, bool $condition): void
{
    global $passed, $failed;

    if ($condition) {
        $passed++;
        echo "[LULUS] {$name}\n";
    } else {
        $failed++;
        echo "[GAGAL] {$name}\n";
    }
}

function expect_exception(callable $callback): bool
{
    try {
        $callback();
        return false;
    } catch (Throwable) {
        return true;
    }
}


// 1. HTML Escape
test_result(
    'h() mengamankan HTML',
    h('<script>"&') === '&lt;script&gt;&quot;&amp;'
);


// 2. Text Length
test_result(
    'text_length() menghitung panjang teks',
    text_length('TitikTemu') === 9
);


// 3. Base URL
test_result(
    'base_url() mengenali folder Public',
    base_url('/login.php') === '/PBL_TitikTemu/Public/login.php'
);


// 4. Safe Return Path
test_result(
    'safe_return_path() menerima alamat lokal',
    safe_return_path('/mahasiswa/dashboard.php') === '/mahasiswa/dashboard.php'
);

test_result(
    'safe_return_path() menolak URL eksternal',
    safe_return_path('https://example.com') === '/dashboard.php'
);

test_result(
    'safe_return_path() menolak protocol-relative URL',
    safe_return_path('//example.com') === '/dashboard.php'
);

test_result(
    'safe_return_path() menolak karakter baris baru',
    safe_return_path("/login.php\r\nLocation: evil.com") === '/dashboard.php'
);


// 5. CSRF Token
$token = csrf_token();

test_result(
    'csrf_token() menghasilkan token 64 karakter hexadecimal',
    preg_match('/^[a-f0-9]{64}$/', $token) === 1
);

test_result(
    'csrf_token() menggunakan token session yang sama',
    csrf_token() === $token
);

test_result(
    'csrf_field() menghasilkan hidden input',
    str_contains(csrf_field(), 'name="csrf_token"')
);

$_POST['csrf_token'] = $token;

verify_csrf();

test_result(
    'verify_csrf() menerima token yang benar',
    true
);


// 6. Flash Message
flash([
    'type' => 'success',
    'message' => 'Laporan berhasil disimpan.'
]);

$message = flash();

test_result(
    'flash() dapat menyimpan dan membaca pesan',
    is_array($message)
    && $message['type'] === 'success'
);

test_result(
    'flash() menghapus pesan setelah dibaca',
    flash() === null
);


// 7. Require POST
require_post();

test_result(
    'require_post() menerima metode POST',
    true
);


// 8. Date Time
test_result(
    'format_date_time() menangani nilai kosong',
    format_date_time(null) === '—'
);

test_result(
    'format_date_time() memformat tanggal',
    format_date_time('2026-11-01 10:30:00+07:00') === '01-11-2026 10:30'
);

test_result(
    'datetime_local_value() memformat nilai input',
    datetime_local_value('2026-11-01 10:30:00+07:00') === '2026-11-01T10:30'
);


// 9. Parse Date Time
$parsed = parse_datetime_local('2026-10-20T10:30');

test_result(
    'parse_datetime_local() menerima tanggal valid',
    str_contains($parsed, '+07:00')
);

test_result(
    'parse_datetime_local() menolak tanggal tidak valid',
    expect_exception(
        static fn() => parse_datetime_local('2026-02-30T10:30')
    )
);

$future = (new DateTimeImmutable(
    '+2 days',
    new DateTimeZone('Asia/Jakarta')
))->format('Y-m-d\TH:i');

test_result(
    'parse_datetime_local() menolak tanggal terlalu jauh di masa depan',
    expect_exception(
        static fn() => parse_datetime_local($future)
    )
);


// Hasil akhir
echo "\n====================================\n";
echo "HASIL PENGUJIAN CORE UTILITIES\n";
echo "====================================\n";
echo "Lulus : {$passed}\n";
echo "Gagal : {$failed}\n";

exit($failed === 0 ? 0 : 1);
