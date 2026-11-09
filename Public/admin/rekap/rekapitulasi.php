<?php
/**
 * Public/admin/rekap/rekapitulasi.php
 * Halaman Rekapitulasi Barang (Figma: Admin-Rekapitulasi).
 * Masih tampilan statis — filter bulan belum berfungsi dan data masih contoh.
 *
 * Teks yang di Figma salah salin sudah dibetulkan:
 *   kolom ketiga tabel jenis & status "Selesai" → "Jumlah",
 *   kolom pertama tabel kategori/lokasi "Jenis" → "Kategori" / "Lokasi".
 */
require_once __DIR__ . '/../../../App/config/bootstrap.php';

$pageTitle = 'Rekapitulasi Barang';
$menuAktif = 'rekapitulasi';

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

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/admin/navbar.php';
?>

<main class="flex w-full flex-col gap-6.5 px-4 pt-2.5 pb-10 md:px-20">

    <div class="flex flex-col gap-4">
        <!-- Judul halaman -->
        <div class="flex flex-col gap-2 p-4">
            <p class="text-sm font-semibold">RUANG KERJA ADMINISTRATOR</p>
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

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
