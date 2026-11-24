<?php
/**
 * App/views/components/rekapitulasi.php
 * Isi halaman Rekapitulasi Barang (Figma: Admin-Rekapitulasi).
 * Dipakai bersama oleh:
 *   Public/admin/rekap/rekapitulasi.php   (admin)
 *   Public/petugas/rekapitulasi.php       (petugas)
 * Bedanya hanya label ruang kerja, navbar, dan role yang boleh membuka.
 *
 * Cara pakai di halaman (di dalam <main>):
 *   $labelRuangKerja = 'RUANG KERJA PETUGAS';   // opsional
 *   require APP_ROOT . '/App/views/components/rekapitulasi.php';
 *
 * Data di bawah masih data contoh. Nanti halaman cukup mengisi $statistik,
 * $perJenisStatus, $perKategori, dan $perLokasi dari database sebelum require,
 * maka data contoh ini tidak dipakai.
 */
$judulHalaman = 'Rekapitulasi Barang';
$deskripsiHalaman = 'Lihat jumlah laporan per bulan berdasarkan jenis, status, kategori, dan lokasi kejadian.';

$statistik = $statistik ?? [
    ['angka' => 128, 'label' => 'Total Laporan Pada Bulan Terpilih'],
    ['angka' => 128, 'label' => 'Laporan Berstatus Selesai'],
    ['angka' => 128, 'label' => 'Kategori Dengan Laporan'],
    ['angka' => 128, 'label' => 'Lokasi Kejadian Unik'],
];

$perJenisStatus = $perJenisStatus ?? [
    ['jenis' => 'Kehilangan', 'status' => 'Aktif',                'jumlah' => 1],
    ['jenis' => 'Kehilangan', 'status' => 'Ditutup',              'jumlah' => 1],
    ['jenis' => 'Temuan',     'status' => 'Menunggu Pemeriksaan', 'jumlah' => 1],
    ['jenis' => 'Temuan',     'status' => 'Barang Tersedia',      'jumlah' => 1],
];

$perKategori = $perKategori ?? [
    ['nama' => 'Elektronik', 'total' => 3, 'selesai' => 2],
];

$perLokasi = $perLokasi ?? [
    ['nama' => 'LPR 8', 'total' => 3, 'selesai' => 2],
];

// Halaman boleh mengisi $bulan (format YYYY-MM) dan $rekapError.
$bulan = $bulan ?? '';
$rekapError = $rekapError ?? null;

// Tabel kategori & lokasi bentuknya sama: judul => [judul kolom pertama, data]
$tabelRingkasan = [
    'Jumlah Berdasarkan Kategori' => ['Kategori', $perKategori],
    'Jumlah Berdasarkan Lokasi'   => ['Lokasi',   $perLokasi],
];
?>
<div class="flex flex-col gap-4">
    <?php require APP_ROOT . '/App/views/components/judul-halaman.php'; ?>

    <!-- Filter bulan -->
    <form method="get" class="tt-card flex flex-col gap-4 p-4.5 md:flex-row">
        <label class="flex-1">
            <span class="tt-label">Bulan</span>
            <span class="relative block">
                <input type="month" name="bulan" value="<?= h($bulan) ?>"
                       class="tt-input h-12 pr-11 [&::-webkit-calendar-picker-indicator]:opacity-0">
                <img src="<?= h(base_url('assets/images/icon-kalender.svg')) ?>" alt=""
                     class="pointer-events-none absolute top-3.25 right-3 h-5.75 w-6">
            </span>
        </label>
        <div class="flex flex-1 items-end md:px-2.5">
            <button type="submit" class="tt-btn w-full">Tampilkan Rekap</button>
        </div>
    </form>

    <?php if ($rekapError !== null): ?>
        <div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <?= h($rekapError) ?>
        </div>
    <?php endif; ?>

    <?php require APP_ROOT . '/App/views/components/kartu-statistik.php'; ?>
</div>

<!-- Jumlah berdasarkan jenis dan status -->
<section class="mt-2.5 flex flex-col gap-2.5">
    <h2 class="p-4 font-display text-xl font-semibold">Jumlah Berdasarkan Jenis dan Status</h2>

    <div class="tt-table-wrap">
        <table class="tt-table min-w-150">
            <thead>
                <tr>
                    <th class="w-1/3">Jenis</th>
                    <th class="w-1/3">Status</th>
                    <th class="w-1/3">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($perJenisStatus as $baris): ?>
                    <tr>
                        <td><?= h($baris['jenis']) ?></td>
                        <td><?= h($baris['status']) ?></td>
                        <td><?= h($baris['jumlah']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Jumlah berdasarkan kategori & lokasi -->
<?php foreach ($tabelRingkasan as $judulTabel => [$kolomPertama, $daftarBaris]): ?>
    <section class="mt-2.5 flex flex-col gap-2.5">
        <h2 class="p-4 font-display text-xl font-semibold"><?= h($judulTabel) ?></h2>

        <div class="tt-table-wrap">
            <table class="tt-table min-w-150">
                <thead>
                    <tr>
                        <th class="w-1/3"><?= h($kolomPertama) ?></th>
                        <th class="w-1/3">Total Laporan</th>
                        <th class="w-1/3">Selesai</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftarBaris as $baris): ?>
                        <tr>
                            <td><?= h($baris['nama']) ?></td>
                            <td><?= h($baris['total']) ?></td>
                            <td><?= h($baris['selesai']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endforeach; ?>
