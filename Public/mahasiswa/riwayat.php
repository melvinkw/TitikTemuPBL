<?php
/**
 * Public/mahasiswa/riwayat.php
 * Riwayat laporan milik mahasiswa (Figma: Mahasiswa-Riwayat Laporan).
 * Ada 3 tabel: laporan temuan, laporan kehilangan, dan laporan klaim.
 */
require_once __DIR__ . '/../../App/config/bootstrap.php';

$pageTitle = 'Riwayat Laporan';
$menuAktif = 'riwayat';

// Data dummy — nanti diganti dari database
$laporanTemuan = [
    ['nama' => 'MacBook Pro Silver', 'kategori' => 'Elektronik', 'lokasi' => 'LPR 8', 'waktu' => '27-10-2026 13:01', 'status' => 'Aktif'],
    ['nama' => 'MacBook Pro Silver', 'kategori' => 'Elektronik', 'lokasi' => 'LPR 8', 'waktu' => '27-10-2026 13:01', 'status' => 'Aktif'],
];

$laporanHilang = [
    ['nama' => 'MacBook Pro Silver', 'kategori' => 'Elektronik', 'lokasi' => 'LPR 8', 'waktu' => '27-10-2026 13:01', 'status' => 'Aktif'],
    ['nama' => 'MacBook Pro Silver', 'kategori' => 'Elektronik', 'lokasi' => 'LPR 8', 'waktu' => '27-10-2026 13:01', 'status' => 'Aktif'],
];

$laporanKlaim = [
    ['nama' => 'MacBook Pro Silver', 'lokasi' => 'LPR 8', 'pemohon' => 'John Doe', 'bukti' => 'Warnanya putih', 'waktu' => '27-10-2026 13:01', 'status' => 'Menunggu Verifikasi', 'catatan' => '-'],
];

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/mahasiswa/navbar.php';
?>

<main class="w-full px-4 py-8 md:px-20">

    <!-- Judul halaman -->
    <p class="text-xs font-semibold">LOST &amp; FOUND POLINEMA</p>
    <h1 class="mt-2 font-display text-2xl font-bold">Riwayat Laporan Saya</h1>
    <p class="mt-2 text-xs">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>

    <!-- ===== Tabel 1: Laporan temuan ===== -->
    <h2 class="mt-12 font-display text-lg font-semibold">Laporan Temuan Barang</h2>
    <p class="mt-2 text-xs">Daftar barang temuan yang pernah kamu laporkan.</p>

    <div class="mt-6 overflow-x-auto rounded-xl border border-indigo-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-tt-tablehead font-display text-base">
                <tr>
                    <th class="px-6 py-5 font-semibold">Nama Barang</th>
                    <th class="px-6 py-5 font-semibold">Kategori</th>
                    <th class="px-6 py-5 font-semibold">Lokasi</th>
                    <th class="px-6 py-5 font-semibold">Tanggal &amp; Waktu</th>
                    <th class="px-6 py-5 font-semibold">Status</th>
                    <th class="px-6 py-5 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($laporanTemuan as $laporan): ?>
                    <tr class="border-t border-indigo-100">
                        <td class="px-6 py-4"><?= h($laporan['nama']) ?></td>
                        <td class="px-6 py-4">
                            <span class="rounded-full bg-purple-100 px-2 py-1 text-xs font-semibold text-purple-600"><?= h($laporan['kategori']) ?></span>
                        </td>
                        <td class="px-6 py-4"><?= h($laporan['lokasi']) ?></td>
                        <td class="px-6 py-4"><?= h($laporan['waktu']) ?></td>
                        <td class="px-6 py-4">
                            <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-700"><?= h($laporan['status']) ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="#" class="font-semibold hover:text-tt-orange">Lihat Detail &rarr;</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ===== Tabel 2: Laporan kehilangan ===== -->
    <h2 class="mt-12 font-display text-lg font-semibold">Laporan Kehilangan Barang</h2>
    <p class="mt-2 text-xs">Daftar barang hilang yang pernah kamu laporkan.</p>

    <div class="mt-6 overflow-x-auto rounded-xl border border-indigo-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-tt-tablehead font-display text-base">
                <tr>
                    <th class="px-6 py-5 font-semibold">Nama Barang</th>
                    <th class="px-6 py-5 font-semibold">Kategori</th>
                    <th class="px-6 py-5 font-semibold">Lokasi</th>
                    <th class="px-6 py-5 font-semibold">Tanggal &amp; Waktu</th>
                    <th class="px-6 py-5 font-semibold">Status</th>
                    <th class="px-6 py-5 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($laporanHilang as $laporan): ?>
                    <tr class="border-t border-indigo-100">
                        <td class="px-6 py-4"><?= h($laporan['nama']) ?></td>
                        <td class="px-6 py-4">
                            <span class="rounded-full bg-purple-100 px-2 py-1 text-xs font-semibold text-purple-600"><?= h($laporan['kategori']) ?></span>
                        </td>
                        <td class="px-6 py-4"><?= h($laporan['lokasi']) ?></td>
                        <td class="px-6 py-4"><?= h($laporan['waktu']) ?></td>
                        <td class="px-6 py-4">
                            <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-700"><?= h($laporan['status']) ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="#" class="font-semibold hover:text-tt-orange">Lihat Detail &rarr;</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ===== Tabel 3: Laporan klaim ===== -->
    <h2 class="mt-12 font-display text-lg font-semibold">Laporan Klaim Barang</h2>
    <p class="mt-2 text-xs">Daftar klaim barang yang pernah kamu ajukan.</p>

    <div class="mt-6 overflow-x-auto rounded-xl border border-indigo-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-tt-tablehead font-display text-base">
                <tr>
                    <th class="px-6 py-5 font-semibold">Nama Barang</th>
                    <th class="px-6 py-5 font-semibold">Pemohon</th>
                    <th class="px-6 py-5 font-semibold">Bukti Kepemilikan</th>
                    <th class="px-6 py-5 font-semibold">Tanggal &amp; Waktu</th>
                    <th class="px-6 py-5 font-semibold">Status</th>
                    <th class="px-6 py-5 font-semibold">Catatan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($laporanKlaim as $klaim): ?>
                    <tr class="border-t border-indigo-100">
                        <td class="px-6 py-4">
                            <p class="font-semibold"><?= h($klaim['nama']) ?></p>
                            <p class="text-xs"><?= h($klaim['lokasi']) ?></p>
                        </td>
                        <td class="px-6 py-4"><?= h($klaim['pemohon']) ?></td>
                        <td class="px-6 py-4"><?= h($klaim['bukti']) ?></td>
                        <td class="px-6 py-4"><?= h($klaim['waktu']) ?></td>
                        <td class="px-6 py-4">
                            <span class="rounded-full bg-yellow-100 px-2 py-1 text-xs font-semibold text-yellow-700"><?= h($klaim['status']) ?></span>
                        </td>
                        <td class="px-6 py-4"><?= h($klaim['catatan']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
