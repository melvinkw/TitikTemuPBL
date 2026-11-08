<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/config/database.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/queries/petugas_dashboard_queries.php';

$currentUser = auth_require_role(['petugas']);

$dashboardError = null;

$dashboardData = [
    'stats' => [
        'pending_reports' => 0,
        'pending_claims' => 0,
        'active_students' => 0,
    ],
    'reports' => [],
    'recent_students' => [],
];

try {
    $dashboardData = get_petugas_dashboard_data();
    $databaseAvailable = true;
} catch (Throwable $error) {
    error_log('Gagal memuat data dashboard: ' . $error->getMessage());
    $dashboardError = 'Gagal memuat data dashboard. Silakan coba lagi nanti.';
}

$stats = $dashboardData['stats'];
$reports = $dashboardData['reports'];
$recentStudents = $dashboardData['recent_students'];

$navItems = [
    ['label' => 'Dashboard', 'href' => base_url('petugas/dashboard.php'), 'active' => true, 'available' => true],
    ['label' => 'Laporan Masuk', 'href' => null, 'active' => false, 'available' => false],
    ['label' => 'Lihat Data Master', 'href' => null, 'active' => false, 'available' => false],
    ['label' => 'Rekapitulasi', 'href' => null, 'active' => false, 'available' => false],
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#DCE3FD">

    <title> Dashboard Petugas | TitikTemu </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=League+Spartan:wght@600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= h(base_url('assets/css/app.css')) ?>">
</head>

<body class="min-h-screen bg-tt-primary font-body text-tt-navy antialiased">

    <!-- nav -->
    <header class="sticky top-0 z-30 border-b border-tt-outline bg-tt-primary">

        <nav class="mx-auto flex min-h-[76px] max-w-[1440px] items-center justify-between gap-6 px-5 py-3 sm:px-8 lg:px-11">

            <!-- nav burger -->
            <button id="menu-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-tt-outline md:hidden" type="button" aria-label="Buka menu" aria-expanded="false" aria-controls="main-menu">

                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <!-- nav links -->
            <div id="main-menu" class="absolute left-0 right-0 top-full hidden flex-col border-b border-tt-outline bg-tt-primary px-5 pb-4 shadow-sm md:static md:flex md:flex-1 md:flex-row md:items-center md:justify-center md:gap-3 md:border-0 md:bg-transparent md:px-0 md:pb-0 md:shadow-none">

                <?php foreach ($navItems as $item): ?>

                    <a href="<?= h($item['href']) ?>" class="rounded-lg px-3 py-3 text-sm font-semibold transition hover:bg-white/60 <?= $item['active'] ? 'text-tt-orange' : 'text-tt-navy' ?>">

                        <?= h($item['label']) ?>
                    </a>
                <?php endforeach; ?>

            </div>
            <form method="post" action="<?= h(base_url('logout.php')) ?>" class="shrink-0">
                <?= csrf_field() ?>
                <button
                    type="submit"
                    class="rounded-lg px-2 py-2 text-sm font-semibold text-tt-coral transition hover:bg-white/50"
                >
                    Logout
                </button>
            </form>
        </nav>
    </header>

    <!-- main content -->
    <main class="mx-auto flex w-full max-w-[1440px] flex-col gap-8 px-5 py-7 sm:px-8 lg:px-20 lg:py-9">

        <!-- header -->
        <section class="space-y-5">

            <div class="rounded-2xl px-4 py-2">

                <h1 class="font-display text-[28px] font-bold leading-tight">

                    Halo, <?= h($currentUser['nama'] ?? 'Petugas') ?>! 👋

                </h1>

                <p class="mt-2 text-xs leading-relaxed text-tt-navy/80">

                    Selamat datang kembali. Berikut ringkasan aktivitas
                    pengelolaan barang hilang dan temuan hari ini.

                </p>

            </div>

            <div class="space-y-2 px-4">

                <p class="text-sm font-semibold tracking-wide">
                    RUANG KERJA PETUGAS
                </p>

                <h2 class="font-display text-[32px] font-extrabold">
                    Dashboard Petugas
                </h2>

                <p class="text-xs leading-relaxed text-tt-navy/80">
                    Pantau laporan, verifikasi klaim, dan aktivitas
                    mahasiswa dalam satu tempat.
                </p>

            </div>

             <?php if ($dashboardError !== null): ?>
                <div role="alert" class="mx-4 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    <?= h($dashboardError) ?>
                </div>
            <?php endif; ?>

            <!-- STATISTICS -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

                <article class="rounded-2xl border border-tt-outline bg-white p-6 shadow-sm">

                    <p class="font-display text-3xl font-bold">
                        <?= number_format((int) $stats['pending_reports'], 0, ',', '.') ?>
                    </p>

                    <p class="mt-2 text-base leading-snug">
                        Laporan Menunggu Pemeriksaan
                    </p>

                </article>

                <article class="rounded-2xl border border-tt-outline bg-white p-6 shadow-sm">

                    <p class="font-display text-3xl font-bold">
                       <?= number_format((int) $stats['pending_claims'], 0, ',', '.') ?>
                    </p>

                    <p class="mt-2 text-base leading-snug">
                        Klaim Menunggu Verifikasi
                    </p>

                </article>

                <article class="rounded-2xl border border-tt-outline bg-white p-6 shadow-sm sm:col-span-2 xl:col-span-1">

                    <p class="font-display text-3xl font-bold">
                        <?= number_format((int) $stats['active_students'], 0, ',', '.') ?>
                    </p>

                    <p class="mt-2 text-base leading-snug">
                        Mahasiswa Aktif
                    </p>

                </article>

            </div>

        </section>

        <!-- tables -->
        <div class="space-y-10">

            <!-- laporan -->
            <section class="space-y-3">

                <div class="flex flex-wrap items-end justify-between gap-3 px-4">

                    <h2 class="font-display text-xl font-semibold">
                        Laporan yang Perlu Diperiksa
                    </h2>

                    <a href="<?= h(base_url('petugas/laporan/')) ?>" class="rounded-lg px-3 py-2 text-sm font-semibold transition hover:bg-white/60">

                        Lihat Semua &gt;&gt;

                    </a>

                </div>

                <div class="overflow-hidden rounded-2xl border border-tt-outline bg-white">

                    <div class="overflow-x-auto">

                        <table class="w-full min-w-[850px] border-collapse text-left">

                            <thead class="bg-tt-tablehead">

                                <tr class="font-display text-lg font-semibold">

                                    <th class="px-5 py-5">Jenis</th>
                                    <th class="px-5 py-5">Barang</th>
                                    <th class="px-5 py-5">Pelapor</th>
                                    <th class="px-5 py-5">Lokasi</th>
                                    <th class="px-5 py-5">Dibuat</th>
                                    <th class="px-5 py-5 text-center"> Aksi </th>

                                </tr>

                            </thead>

                            <tbody class="divide-y divide-tt-outline text-sm">

                                <?php if ($reports === []): ?>

                                    <tr>
                                        <td colspan="6" class="px-5 py-10 text-center text-slate-500">

                                            Belum ada laporan yang menunggu
                                            pemeriksaan.

                                        </td>
                                    </tr>

                                <?php else: ?>

                                    <?php foreach ($reports as $report): ?>

                                        <tr class="transition hover:bg-slate-50">

                                            <td class="whitespace-nowrap px-5 py-4">
                                                <?= h($report['jenis']) ?>
                                            </td>

                                            <td class="px-5 py-4 font-medium">
                                                <?= h($report['judul']) ?>
                                            </td>

                                            <td class="px-5 py-4">
                                                <?= h($report['pelapor']) ?>
                                            </td>

                                            <td class="px-5 py-4">
                                                <?= h($report['lokasi']) ?>
                                            </td>

                                            <td class="whitespace-nowrap px-5 py-4 tabular-nums">

                                                <?= h(format_date_time($report['created_at'])) ?>

                                            </td>

                                            <td class="px-5 py-4 text-center">

                                                <a href="<?= h(base_url('petugas/laporan/')) ?>" class="inline-flex min-h-10 items-center justify-center rounded-[10px] bg-tt-navy px-4 py-2 text-sm font-semibold text-white transition hover:bg-tt-navy/90">

                                                    Periksa

                                                </a>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </section>

            <!-- riwayat login -->
            <section class="space-y-3">

                <div class="flex flex-wrap items-end justify-between gap-3 px-4">

                    <div>

                        <h2 class="font-display text-xl font-semibold">
                            Login Mahasiswa Terbaru
                        </h2>

                        <p class="mt-1 text-xs text-tt-navy/80">
                            Pantau aktivitas login terbaru mahasiswa
                            yang terdaftar di TitikTemu.
                        </p>

                    </div>

                    <a href="<?= h(base_url('admin/pengguna/')) ?>" class="rounded-lg px-3 py-2 text-sm font-semibold transition hover:bg-white/60">

                        Lihat Semua &gt;&gt;

                    </a>

                </div>

                <div class="overflow-hidden rounded-2xl border border-tt-outline bg-white">

                    <div class="overflow-x-auto">

                        <table class="w-full min-w-[760px] border-collapse text-left">

                            <thead class="bg-tt-tablehead">

                                <tr class="font-display text-lg font-semibold">

                                    <th class="px-5 py-5">Nama</th>
                                    <th class="px-5 py-5">NIM</th>
                                    <th class="px-5 py-5">Prodi</th>
                                    <th class="px-5 py-5"> Login Terakhir </th>

                                </tr>

                            </thead>

                            <tbody class="divide-y divide-tt-outline text-sm">

                                <?php if ($recentStudents === []): ?>

                                    <tr>
                                        <td colspan="4" class="px-5 py-10 text-center text-slate-500">

                                            Belum ada aktivitas login mahasiswa.

                                        </td>
                                    </tr>

                                <?php else: ?>

                                    <?php foreach ($recentStudents as $student): ?>

                                        <tr class="transition hover:bg-slate-50">

                                            <td class="whitespace-nowrap px-5 py-4 font-medium">

                                                <?= h($student['nama']) ?>

                                            </td>

                                            <td class="whitespace-nowrap px-5 py-4 tabular-nums">

                                                <?= h($student['nim']) ?>

                                            </td>

                                            <td class="px-5 py-4">
                                                <?= h($student['prodi']) ?>
                                            </td>

                                            <td class="whitespace-nowrap px-5 py-4 tabular-nums">

                                                <?= h(format_date_time($student['last_login'])) ?>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </section>

        </div>

    </main>

    <!-- footer -->
    <footer class="mt-8 border-t border-tt-outline bg-tt-primary">

        <div class="mx-auto flex max-w-[1440px] flex-col gap-3 px-5 py-5 text-center text-xs text-tt-muted sm:px-8 md:flex-row md:items-center md:justify-between md:text-left lg:px-11">

            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-3">

                <strong class="font-extrabold">
                    TitikTemu.
                </strong>

                <span>Politeknik Negeri Malang</span>

            </div>

            <p>
                Platform informasi barang hilang dan barang temuan.
            </p>

            <p>
                © <?= date('Y') ?> TitikTemu
            </p>

        </div>

    </footer>

    <!-- burger script -->
    <script>
        const menuToggle = document.getElementById('menu-toggle');
        const mainMenu = document.getElementById('main-menu');

        menuToggle?.addEventListener('click', () => {

            const isExpanded =
                menuToggle.getAttribute('aria-expanded') === 'true';

            menuToggle.setAttribute(
                'aria-expanded',
                String(!isExpanded)
            );

            mainMenu?.classList.toggle('hidden', isExpanded);
            mainMenu?.classList.toggle('flex', !isExpanded);

        });
    </script>

</body>

</html>