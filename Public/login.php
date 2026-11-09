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
    <meta name="theme-color" content="#DCE3FD">

    <title>Login | TitikTemu</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=League+Spartan:wght@600;700;800&display=swap" rel="stylesheet" >

    <link rel="stylesheet" href="<?= h(base_url('/assets/css/app.css')) ?>">
</head>

<body class="min-h-screen flex flex-col bg-tt-primary font-body text-tt-navy antialiased">

    <!-- nav (nanti diganti components) -->
    <header class="sticky top-0 z-30 border-b border-tt-outline bg-tt-primary">

        <nav class="mx-auto flex min-h-[76px] max-w-[1440px] items-center justify-end gap-6 px-5 py-3 sm:px-8 lg:px-11">

            <a
                href="<?= h(base_url('index.php')) ?>"
                class="text-sm font-semibold transition hover:text-tt-orange"
            >
                Beranda
            </a>

            <a
                href="<?= h(base_url('mahasiswa/daftar-barang.php')) ?>"
                class="text-sm font-semibold transition hover:text-tt-orange"
            >
                Daftar Barang
            </a>

            <span class="mx-2 h-5 w-px bg-tt-navy"></span>

            <a
                href="<?= h(base_url('login.php')) ?>"
                aria-current="page"
                class="rounded-xl bg-tt-navy px-4 py-3 text-sm font-semibold text-white transition hover:bg-tt-navy/90"
            >
                Login
            </a>

        </nav>

    </header>


    <!-- main content -->
    <main class="mx-auto flex w-full max-w-[1440px] flex-1 items-center px-5 py-10 sm:px-8 lg:px-11 lg:py-16">

        <div class="grid w-full grid-cols-1 gap-6 lg:grid-cols-2">

            <!-- panel kiri -->
            <section class="relative flex min-h-[520px] flex-col justify-between overflow-hidden rounded-2xl border border-tt-outline bg-gradient-to-br from-white via-white to-[#E5E5E9] p-7 sm:p-9 lg:min-h-[690px] lg:p-8 xl:p-9">

                <!-- circle -->
                <div class="pointer-events-none absolute -bottom-32 -right-32 h-72 w-72 rounded-full bg-white sm:h-80 sm:w-80"></div>

                <!-- logo titiktemu -->
                <div class="relative z-10 flex items-center gap-3">

                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-tt-orange font-display text-4xl font-extrabold text-tt-navy">
                        T.
                    </div>

                    <div>
                        <h2 class="font-display text-2xl font-extrabold leading-none">
                            TitikTemu
                        </h2>

                        <p class="mt-1 text-sm font-semibold">
                            LOST & FOUND POLINEMA
                        </p>
                    </div>

                </div>

                <div class="relative z-10 my-12 space-y-5 lg:my-0">

                    <p class="text-sm font-semibold tracking-wide">
                        TEMUKAN KEMBALI BARANGMU
                    </p>

                    <h1 class="max-w-xl font-display text-4xl font-extrabold leading-tight sm:text-5xl xl:text-[52px]">
                        Satu tempat untuk barang yang hilang dan ditemukan.
                    </h1>

                    <p class="max-w-md pt-3 text-xs leading-relaxed text-tt-navy/80 sm:text-sm">
                        Masuk untuk membuat laporan, memantau status,
                        dan mengajukan klaim barang temuan.
                    </p>

                    <ul class="space-y-3 pt-4 text-xs sm:text-sm">

                        <li class="flex items-center gap-3">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-tt-orange font-bold">
                                ✓
                            </span>
                            Laporan diverifikasi petugas
                        </li>

                        <li class="flex items-center gap-3">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-tt-orange font-bold">
                                ✓
                            </span>
                            Riwayat tersimpan di dashboard
                        </li>

                        <li class="flex items-center gap-3">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-tt-orange font-bold">
                                ✓
                            </span>
                            Kontak pelapor tetap privat
                        </li>

                    </ul>

                </div>

                <p class="relative z-10 text-xs leading-relaxed">
                    Politeknik Negeri Malang
                    Sistem Informasi barang hilang dan temuan
                </p>

            </section>


            <!-- panel form kanan -->
            <section class="flex min-h-[520px] items-center rounded-2xl border border-tt-outline bg-white p-7 sm:p-9 lg:min-h-[690px] lg:p-8 xl:p-9">

                <div class="mx-auto w-full max-w-xl space-y-8">

                    <!-- logo titiktemu -->
                    <div class="space-y-5">

                        <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-tt-navy font-display text-4xl font-extrabold text-white">
                            T.
                        </div>

                        <div class="space-y-2">

                            <p class="text-sm font-semibold tracking-wide">
                                LOST & FOUND POLINEMA
                            </p>

                            <h2 class="font-display text-2xl font-bold sm:text-3xl">
                                Pengaturan Akun Petugas
                            </h2>

                            <p class="text-xs leading-relaxed text-tt-navy/80 sm:text-sm">
                                Masukan NIM atau Username. Sistem akan
                                mengenali jenis akunmu secara otomatis.
                            </p>

                        </div>

                    </div>


                    <!-- error message -->
                    <?php if ($errorMessage !== ''): ?>

                        <div
                            class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
                            role="alert"
                        >
                            <?= h($errorMessage) ?>
                        </div>

                    <?php endif; ?>


                    <!-- form login -->
                    <form
                        class="space-y-4"
                        method="post"
                        action="<?= h(base_url('/login.php')) ?>"
                    >

                        <?= csrf_field() ?>


                        <!-- nim/username -->
                        <div class="space-y-2">

                            <label for="identifier" class="block text-sm font-semibold">
                                NIM atau Username
                            </label>

                            <input class="w-full rounded-xl border border-tt-outline px-3 py-3.5 text-sm outline-none transition placeholder:text-tt-navy/60 focus:border-tt-navy focus:ring-2 focus:ring-tt-navy/10" id="identifier"  type="text" placeholder="Masukan NIM atau Username" value="<?= h($identifierValue) ?>" autocomplete="username" required>

                        </div>


                        <!-- password -->
                        <div class="space-y-2">

                            <label
                                for="password"
                                class="block text-sm font-semibold"
                            >
                                Password
                            </label>

                            <input class="w-full rounded-xl border border-tt-outline px-3 py-3.5 text-sm outline-none transition placeholder:text-tt-navy/60 focus:border-tt-navy focus:ring-2 focus:ring-tt-navy/10" id="password" name="password" type="password" placeholder="Masukan Password" autocomplete="current-password" required >

                        </div>

                        <button class="w-full rounded-xl bg-tt-navy px-4 py-3.5 text-sm font-semibold text-white transition hover:bg-tt-navy/90 focus:outline-none focus:ring-2 focus:ring-tt-navy/30 focus:ring-offset-2" type="submit" >
                            Masuk
                        </button>

                    </form>

                </div>

            </section>

        </div>

    </main>


    <!-- footer (nanti diganti components) -->
    <footer class="border-t border-tt-outline bg-tt-primary">

        <div class="mx-auto flex max-w-[1440px] flex-col gap-3 px-5 py-6 text-center text-xs text-tt-muted sm:px-8 md:flex-row md:items-center md:justify-between md:text-left lg:px-11">

            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-3">

                <strong class="font-extrabold">
                    TitikTemu.
                </strong>

                <span>
                    Politeknik Negeri Malang
                </span>

            </div>

            <p>
                Platform informasi barang hilang dan barang temuan.
            </p>

            <p>
                © <?= date('Y') ?> TitikTemu
            </p>

        </div>

    </footer>

</body>

</html>

