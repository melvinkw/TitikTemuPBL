<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/Public/partials/ui.php';
require_once APP_ROOT . '/App/queries/dashboard_queries.php';

$user = auth_require_role(['mahasiswa']);
$data = dashboard_fetch_student_data((int) $user['id_pengguna']);
$stats = $data['stats'];
$pageTitle = 'Dashboard Mahasiswa';
$navRole = 'mahasiswa';
$activeNav = 'dashboard';
require APP_ROOT . '/Public/partials/head.php';
?>
<body class="min-h-screen bg-tt-lavender text-tt-navy">
    <?php require APP_ROOT . '/Public/partials/top_nav.php'; ?>

    <main id="beranda" class="mx-auto max-w-[1440px] px-5 pb-12 pt-7 sm:px-8 xl:px-[80px]">
        <section class="mb-5">
            <h1 class="font-display text-[20px] font-bold">Halo, <?= h($user['nama']) ?>! <span aria-hidden="true">👋</span></h1>
            <p class="mt-1.5 text-[11px] leading-5 text-tt-navy/80">Pantau laporan dan temukan informasi barang yang tersedia di lingkungan Politeknik Negeri Malang.</p>
        </section>

        <section class="tt-student-hero mb-4">
            <div class="relative z-10 max-w-[590px]">
                <p class="text-[10px] font-semibold tracking-[.04em]">LOST &amp; FOUND POLINEMA</p>
                <h2 class="mt-2 max-w-[400px] font-display text-[38px] font-extrabold leading-[1.02] sm:text-[44px]">Barang Hilang? Temukan di <span class="text-tt-orange">TitikTemu.</span></h2>
                <p class="mt-2 max-w-[490px] text-[11px] leading-5 text-slate-600">Platform lost &amp; found untuk membantu warga Polinema melaporkan, mencari, dan menemukan kembali barang yang hilang.</p>
                <div class="mt-5 flex flex-wrap gap-2">
                    <span class="inline-flex h-[38px] cursor-not-allowed items-center rounded-[8px] bg-tt-navy px-4 text-[11px] font-semibold text-white opacity-50" aria-disabled="true" title="Halaman form laporan belum dibuat">+ Buat Laporan</span>
                    <a href="#barang-temuan" class="tt-btn-secondary tt-focus h-[38px] rounded-[8px] px-4 text-[11px]">Cari Barang <?= tt_icon('arrow', 13) ?></a>
                </div>
            </div>
            <div class="tt-hero-art" aria-hidden="true">
                <div class="tt-hero-sun"></div>
                <div class="tt-hero-building"><span></span><i></i><b></b></div>
                <div class="tt-hero-package"><span></span><i></i></div>
                <div class="tt-hero-search"><?= tt_icon('search', 52) ?></div>
            </div>
        </section>

        <section class="mb-10 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <?= tt_metric_card('Laporan Kehilangan Saya', (int) ($stats['own_lost'] ?? 0)) ?>
            <?= tt_metric_card('Laporan Temuan Saya', (int) ($stats['own_found'] ?? 0)) ?>
            <?= tt_metric_card('Laporan Menunggu Pemeriksaan', (int) ($stats['pending'] ?? 0)) ?>
            <?= tt_metric_card('Laporan Selesai', (int) ($stats['completed'] ?? 0)) ?>
        </section>

        <section id="barang-temuan" class="mb-10 scroll-mt-24">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-2">
                <div>
                    <h2 class="font-display text-[19px] font-bold">Barang Temuan Terbaru</h2>
                    <p class="mt-1 text-[11px] text-tt-navy/75">Barang yang telah diverifikasi dan tersedia untuk diklaim.</p>
                </div>
                <span class="text-[11px] font-semibold text-tt-navy/60" aria-disabled="true">Lihat Semua &gt;&gt;</span>
            </div>

            <?php if (empty($data['found_items'])): ?>
                <?= tt_empty_state('Belum ada barang temuan tersedia', 'Barang temuan yang sudah disetujui petugas akan muncul pada bagian ini.', 'box') ?>
            <?php else: ?>
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <?php foreach ($data['found_items'] as $item): ?>
                        <?php $photoUrl = tt_storage_public_url($item['bucket_name'] ?? null, $item['file_path'] ?? null); ?>
                        <article class="tt-item-card">
                            <?php if ($photoUrl !== null): ?>
                                <img src="<?= h($photoUrl) ?>" alt="Foto <?= h($item['judul']) ?>" class="tt-item-photo" loading="lazy">
                            <?php else: ?>
                                <div class="tt-item-photo tt-item-placeholder"><?= tt_icon('box', 42) ?><span>Foto belum tersedia</span></div>
                            <?php endif; ?>
                            <div class="px-3 pb-4 pt-3">
                                <h3 class="truncate text-[13px] font-bold text-tt-navy"><?= h($item['judul']) ?></h3>
                                <p class="mt-1 line-clamp-2 min-h-8 text-[11px] leading-4 text-slate-500"><?= h($item['deskripsi']) ?></p>
                                <p class="mt-2 flex items-center gap-1 text-[10px] text-slate-500"><?= tt_icon('location', 12) ?> <?= h($item['lokasi_kejadian']) ?></p>
                                <p class="mt-1 flex items-center gap-1 text-[10px] text-slate-500"><?= tt_icon('calendar', 12) ?> <?= h(format_date_time($item['tanggal_waktu_kejadian'])) ?></p>
                                <div class="mt-3 flex items-center justify-between gap-2">
                                    <span class="rounded-full bg-[#eee8ff] px-2.5 py-1 text-[10px] font-semibold text-[#6e45a8]"><?= h($item['nama_kategori']) ?></span>
                                    <span class="text-[10px] font-semibold text-tt-navy/55" aria-disabled="true">Lihat Detail →</span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section id="laporan-kehilangan" class="mb-11 scroll-mt-24">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-2">
                <div>
                    <h2 class="font-display text-[19px] font-bold">Barang Kehilangan Terbaru</h2>
                    <p class="mt-1 text-[11px] text-tt-navy/75">Daftar laporan kehilangan yang telah disetujui petugas.</p>
                </div>
                <span class="text-[11px] font-semibold text-tt-navy/60" aria-disabled="true">Lihat Semua &gt;&gt;</span>
            </div>
            <div class="tt-table-wrap">
                <?php if (empty($data['lost_reports'])): ?>
                    <?= tt_empty_state('Belum ada laporan kehilangan aktif', 'Laporan kehilangan yang sudah disetujui petugas akan muncul di sini.', 'search') ?>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="tt-data-table">
                            <thead><tr><th>Nama Barang</th><th>Kategori</th><th>Lokasi</th><th>Tanggal &amp; Waktu</th><th>Status</th><th>Aksi</th></tr></thead>
                            <tbody>
                            <?php foreach ($data['lost_reports'] as $report): ?>
                                <tr>
                                    <td class="font-semibold"><?= h($report['judul']) ?></td>
                                    <td><?= h($report['nama_kategori']) ?></td>
                                    <td><?= h($report['lokasi_kejadian']) ?></td>
                                    <td><?= h(format_date_time($report['tanggal_waktu_kejadian'])) ?></td>
                                    <td><?= tt_status_badge((string) $report['status']) ?></td>
                                    <td><span class="text-[11px] font-semibold text-tt-navy/55" aria-disabled="true">Lihat Detail →</span></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="mb-10">
            <div class="text-center">
                <p class="text-[10px] font-semibold tracking-[.06em]">MUDAH DAN TERARAH</p>
                <h2 class="mt-1 font-display text-[22px] font-bold">Bagaimana cara menggunakan TitikTemu?</h2>
                <p class="mx-auto mt-1 max-w-lg text-[11px] text-tt-navy/75">Ikuti langkah sederhana untuk melaporkan dan menemukan barang.</p>
            </div>
            <div class="mt-5 grid gap-3 md:grid-cols-3">
                <article class="tt-step-card"><span class="tt-step-icon"><?= tt_icon('file', 20) ?></span><h3>1. Laporkan</h3><p>Isi informasi barang yang hilang atau ditemukan dengan jelas.</p></article>
                <article class="tt-step-card"><span class="tt-step-icon"><?= tt_icon('search', 20) ?></span><h3>2. Diperiksa</h3><p>Petugas memeriksa data dan foto yang dikirimkan.</p></article>
                <article class="tt-step-card"><span class="tt-step-icon"><?= tt_icon('check', 20) ?></span><h3>3. Verifikasi</h3><p>Ikuti proses verifikasi dan klaim sesuai status laporan.</p></article>
            </div>
        </section>

        <section class="tt-student-cta">
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-[.06em]">Mulai sekarang</p>
                <h2 class="mt-1 font-display text-[20px] font-bold">Barangmu bisa ditemukan kembali.</h2>
                <p class="mt-1 text-[11px] text-tt-navy/75">Buat laporan agar proses pencarian dapat dimulai.</p>
            </div>
            <span class="rounded-[8px] bg-tt-navy px-4 py-2.5 text-[11px] font-semibold text-white" aria-disabled="true">+ Buat Laporan</span>
        </section>
    </main>

    <?php require APP_ROOT . '/Public/partials/footer.php'; ?>
</body>
</html>
