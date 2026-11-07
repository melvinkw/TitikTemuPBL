<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/actions/auth_actions.php';

$errorMessage = '';
$identifierValue = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    try {
        require_post();
        verify_csrf();

        $identifierValue = is_string($_POST['identifier'] ?? null)
            ? trim($_POST['identifier'])
            : '';
        $password = is_string($_POST['password'] ?? null)
            ? $_POST['password']
            : '';

        $result = authenticate_user($identifierValue, $password);

        if (($result['success'] ?? false) === true && is_array($result['user'] ?? null)) {
            auth_login($result['user']);
            redirect('/dashboard.php');
        }

        $errorMessage = (string) ($result['message'] ?? 'Login gagal. Periksa kembali data Anda.');
    } catch (Throwable $error) {
        // Jangan mencatat input login, password, cookie, atau response SIAKAD.
        error_log('[TitikTemu] Authentication process failed: ' . get_class($error));
        $errorMessage = 'Login belum dapat diproses. Periksa koneksi lalu coba lagi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk | TitikTemu</title>
    <link rel="stylesheet" href="<?= h(base_url('/assets/css/app.css')) ?>">
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <main class="mx-auto flex min-h-screen max-w-md items-center px-5 py-10">
        <section class="w-full rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
            <p class="text-sm font-semibold tracking-wide text-blue-700">TitikTemu</p>
            <h1 class="mt-2 text-2xl font-bold">Masuk ke akun</h1>
            <p class="mt-2 text-sm text-slate-600">
                Mahasiswa menggunakan akun SIAKAD. Petugas dan admin menggunakan akun TitikTemu.
            </p>

            <?php if ($errorMessage !== ''): ?>
                <div class="mt-5 rounded-lg bg-red-50 p-3 text-sm text-red-700" role="alert">
                    <?= h($errorMessage) ?>
                </div>
            <?php endif; ?>

            <form class="mt-6 space-y-4" method="post" action="<?= h(base_url('/login.php')) ?>">
                <?= csrf_field() ?>

                <div>
                    <label class="mb-1 block text-sm font-medium" for="identifier">NIM / Username</label>
                    <input
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 outline-none focus:border-blue-600"
                        id="identifier"
                        name="identifier"
                        type="text"
                        value="<?= h($identifierValue) ?>"
                        autocomplete="username"
                        required
                    >
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium" for="password">Password</label>
                    <input
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 outline-none focus:border-blue-600"
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button
                    class="w-full rounded-lg bg-blue-700 px-4 py-3 font-semibold text-white hover:bg-blue-800"
                    type="submit"
                >
                    Masuk
                </button>
            </form>
        </section>
    </main>
</body>
</html>
