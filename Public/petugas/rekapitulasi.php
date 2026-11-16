<?php
/**
 * Public/petugas/rekapitulasi.php
 * Halaman Rekapitulasi Barang untuk petugas
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';

$currentUser = auth_require_role(['petugas']);

// Data dummy — nanti dihitung dari database sesuai bulan yang dipilih
$statistik = [
    ['angka' => 128, 'label' => 'Total Laporan Pada Bulan Terpilih'],
    ['angka' => 128, 'label' => 'Laporan Berstatus Selesai'],
    ['angka' => 128, 'label' => 'Kategori Dengan Laporan'],
    ['angka' => 128, 'label' => 'Lokasi Kejadian Unik'],
];

$perJenisStatus = [
    ['jenis' => 'Kehilangan', 'status' => 'Aktif',                'jumlah' => 1],
    ['jenis' => 'Kehilangan', 'status' => 'Ditutup',              'jumlah' => 1],
    ['jenis' => 'Temuan',     'status' => 'Menunggu Pemeriksaan', 'jumlah' => 1],
    ['jenis' => 'Temuan',     'status' => 'Barang Tersedia',      'jumlah' => 1],
];

$perKategori = [
    ['nama' => 'Elektronik', 'total' => 3, 'selesai' => 2],
];

$perLokasi = [
    ['nama' => 'LPR 8', 'total' => 3, 'selesai' => 2],
];

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#DCE3FD">

    <title>Rekapitulasi Barang | TitikTemu</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=League+Spartan:wght@600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= h(base_url('assets/css/app.css')) ?>">
</head>

<body class="min-h-screen bg-tt-primary font-body text-tt-navy antialiased">

    <!-- nav -->
    <header>
        <?php require APP_ROOT . '/Public/partials/components/petugas/navbar.php'; ?>
    </header>

    <!-- main content (lebar & jarak sama dengan halaman petugas lain) -->
    <main class="mx-auto flex w-full max-w-[1440px] flex-col gap-6.5 px-5 py-7 sm:px-8 lg:px-20 lg:py-9">


    <div class="flex flex-col gap-4">
        <!-- Judul halaman -->
        <div class="flex flex-col gap-2 p-4">
            <p class="text-sm font-semibold">RUANG KERJA PETUGAS</p>
            <h1 class="font-display text-[32px] font-extrabold">Rekapitulasi Barang</h1>
            <p class="text-xs">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
        </div>

        <!-- Filter bulan -->
        <form method="get" class="flex flex-col gap-4 rounded-2xl border border-tt-outline bg-white p-4.5 md:flex-row">
            <label class="flex flex-1 flex-col gap-2">
                <span class="font-display text-base font-semibold">Bulan</span>
                <span class="relative">
                    <input type="month" name="bulan"
                           class="h-12 w-full rounded-[10px] border border-tt-outline bg-white p-3 pr-11 text-sm outline-none focus:border-tt-navy [&::-webkit-calendar-picker-indicator]:opacity-0">
                    <img src="<?= h(base_url('assets/images/icon-kalender.svg')) ?>" alt="" width="24" height="23"
                         class="pointer-events-none absolute top-3.25 right-3">
                </span>
            </label>
            <div class="flex flex-1 items-end md:px-2.5">
                <button type="submit" class="h-10.75 w-full cursor-pointer rounded-[10px] bg-tt-navy px-2.75 text-sm font-semibold text-white">Tampilkan Rekap</button>
            </div>
        </form>

        <!-- Kartu statistik -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
            <?php foreach ($statistik as $stat): ?>
                <div class="flex flex-col gap-2 rounded-2xl border border-tt-outline bg-white p-6">
                    <p class="font-display text-[28px] font-bold"><?= h($stat['angka']) ?></p>
                    <p class="text-base"><?= h($stat['label']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Jumlah berdasarkan jenis dan status -->
    <section class="mt-2.5 flex flex-col gap-2.5">
        <h2 class="p-4 font-display text-xl font-semibold">Jumlah Berdasarkan Jenis dan Status</h2>

        <div class="overflow-x-auto rounded-[15px] border border-tt-outline bg-white">
            <table class="w-full min-w-150 text-left text-sm">
                <thead class="bg-tt-tablehead font-display text-xl">
                    <tr>
                        <th class="w-1/3 px-7.5 py-8 font-semibold whitespace-nowrap">Jenis</th>
                        <th class="w-1/3 px-7.5 py-8 font-semibold whitespace-nowrap">Status</th>
                        <th class="w-1/3 px-7.5 py-8 font-semibold whitespace-nowrap">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($perJenisStatus as $baris): ?>
                        <tr class="border-b border-tt-outline last:border-b-0">
                            <td class="h-18 px-7.5"><?= h($baris['jenis']) ?></td>
                            <td class="h-18 px-7.5"><?= h($baris['status']) ?></td>
                            <td class="h-18 px-7.5"><?= h($baris['jumlah']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Jumlah berdasarkan kategori -->
    <section class="mt-2.5 flex flex-col gap-2.5">
        <h2 class="p-4 font-display text-xl font-semibold">Jumlah Berdasarkan Kategori</h2>

        <div class="overflow-x-auto rounded-[15px] border border-tt-outline bg-white">
            <table class="w-full min-w-150 text-left text-sm">
                <thead class="bg-tt-tablehead font-display text-xl">
                    <tr>
                        <th class="w-1/3 px-7.5 py-8 font-semibold whitespace-nowrap">Kategori</th>
                        <th class="w-1/3 px-7.5 py-8 font-semibold whitespace-nowrap">Total Laporan</th>
                        <th class="w-1/3 px-7.5 py-8 font-semibold whitespace-nowrap">Selesai</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($perKategori as $baris): ?>
                        <tr class="border-b border-tt-outline last:border-b-0">
                            <td class="h-18 px-7.5"><?= h($baris['nama']) ?></td>
                            <td class="h-18 px-7.5"><?= h($baris['total']) ?></td>
                            <td class="h-18 px-7.5"><?= h($baris['selesai']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Jumlah berdasarkan lokasi -->
    <section class="mt-2.5 flex flex-col gap-2.5">
        <h2 class="p-4 font-display text-xl font-semibold">Jumlah Berdasarkan Lokasi</h2>

        <div class="overflow-x-auto rounded-[15px] border border-tt-outline bg-white">
            <table class="w-full min-w-150 text-left text-sm">
                <thead class="bg-tt-tablehead font-display text-xl">
                    <tr>
                        <th class="w-1/3 px-7.5 py-8 font-semibold whitespace-nowrap">Lokasi</th>
                        <th class="w-1/3 px-7.5 py-8 font-semibold whitespace-nowrap">Total Laporan</th>
                        <th class="w-1/3 px-7.5 py-8 font-semibold whitespace-nowrap">Selesai</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($perLokasi as $baris): ?>
                        <tr class="border-b border-tt-outline last:border-b-0">
                            <td class="h-18 px-7.5"><?= h($baris['nama']) ?></td>
                            <td class="h-18 px-7.5"><?= h($baris['total']) ?></td>
                            <td class="h-18 px-7.5"><?= h($baris['selesai']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

    <!-- footer -->
    <footer>
        <?php require APP_ROOT . '/Public/partials/components/petugas/footer.php'; ?>
    </footer>

</body>
</html>
