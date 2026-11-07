<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';

$user = auth_require_login();
if ($user['role'] === 'petugas') {
    redirect('/petugas/dashboard.php');
}
$roleLabels = [
    'mahasiswa' => 'Mahasiswa',
    'petugas' => 'Petugas',
    'admin' => 'Administrator',
];
$roleLabel = $roleLabels[$user['role']] ?? 'Pengguna';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | TitikTemu</title>
    <link rel="stylesheet" href="<?= h(base_url('/assets/css/app.css')) ?>">
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <main class="mx-auto max-w-3xl px-5 py-12">
        <section class="rounded-2xl bg-white p-8 shadow-sm ring-1 ring-slate-200">
            <p class="text-sm font-semibold text-blue-700">TitikTemu</p>
            <h1 class="mt-2 text-2xl font-bold">Login berhasil</h1>
            <p class="mt-2 text-slate-600">
                Halo, <?= h($user['nama']) ?>. Autentikasi berhasil dan session TitikTemu sudah aktif.
            </p>

            <dl class="mt-6 grid gap-3 sm:grid-cols-2">
                <div class="rounded-lg bg-slate-50 p-4">
                    <dt class="text-sm text-slate-500">Jenis akun</dt>
                    <dd class="mt-1 font-semibold"><?= h($roleLabel) ?></dd>
                </div>
                <div class="rounded-lg bg-slate-50 p-4">
                    <dt class="text-sm text-slate-500">Identitas</dt>
                    <dd class="mt-1 font-semibold"><?= h($user['nim'] ?? $user['username'] ?? '-') ?></dd>
                </div>
            </dl>

            <p class="mt-5 text-sm text-slate-500">
                Halaman ini hanya dashboard sementara untuk menguji autentikasi. Nantinya arahkan setiap role
                ke dashboard sesuai desain Figma.
            </p>

            <form class="mt-6" method="post" action="<?= h(base_url('/logout.php')) ?>">
                <?= csrf_field() ?>
                <button class="rounded-lg bg-slate-900 px-4 py-2.5 font-semibold text-white" type="submit">
                    Keluar
                </button>
            </form>
        </section>
    </main>
</body>
</html>
