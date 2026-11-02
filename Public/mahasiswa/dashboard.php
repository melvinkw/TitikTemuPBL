<?php
/**
 * Public/mahasiswa/dashboard.php
 * Halaman Dashboard mahasiswa (Figma: Mahasiswa-Beranda).
 *
 * Modal Buat Laporan bisa dilihat lewat URL:
 *   dashboard.php?modal=hilang   atau   dashboard.php?modal=temuan
 */
require_once __DIR__ . '/../../App/config/bootstrap.php';

$pageTitle = 'Dashboard';
$menuAktif = 'dashboard';
$namaUser = 'John Doe'; // Nama dummy — nanti diambil dari data login

// Data dummy — nanti diganti dari database
// Barang temuan di sini = yang sudah disetujui/diterima satpam atau admin.
// milik_saya = true kalau laporan ini dibuat oleh user yang sedang login.
$barangTemuan = [
    ['nama' => 'Tumbler Hitam', 'deskripsi' => 'Ini tumbler warna hitam pekat', 'lokasi' => 'LPR 8', 'waktu' => '24 Oktober 2026 12:45', 'milik_saya' => true],
    ['nama' => 'Tumbler Hitam', 'deskripsi' => 'Ini tumbler warna hitam pekat', 'lokasi' => 'LPR 8', 'waktu' => '24 Oktober 2026 12:45', 'milik_saya' => false],
    ['nama' => 'Tumbler Hitam', 'deskripsi' => 'Ini tumbler warna hitam pekat', 'lokasi' => 'LPR 8', 'waktu' => '24 Oktober 2026 12:45', 'milik_saya' => false],
    ['nama' => 'Tumbler Hitam', 'deskripsi' => 'Ini tumbler warna hitam pekat', 'lokasi' => 'LPR 8', 'waktu' => '24 Oktober 2026 12:45', 'milik_saya' => false],
];

$barangHilang = [
    ['nama' => 'MacBook Pro Silver', 'kategori' => 'Elektronik', 'lokasi' => 'LPR 8', 'waktu' => '27-10-2026 13:01', 'status' => 'Aktif'],
];

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/mahasiswa/navbar.php';
?>

<main class="w-full px-4 py-8 md:px-20">

    <!-- Sapaan -->
    <h1 class="font-display text-2xl font-bold">Halo, <?= h($namaUser) ?>! 👋</h1>
    <p class="mt-2 text-xs">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>

    <!-- Banner -->
    <div class="mt-6 rounded-xl border border-indigo-200 bg-white p-6">
        <div class="flex flex-col items-center gap-6 md:flex-row">
            <div class="flex-1">
                <p class="text-xs font-semibold">LOST &amp; FOUND POLINEMA</p>
                <h2 class="mt-2 font-display text-4xl font-extrabold leading-tight md:text-5xl">
                    Barang Hilang?<br>
                    Temukan di<br>
                    <span class="text-tt-orange">TitikTemu.</span>
                </h2>
                <p class="mt-4 text-xs text-gray-500">Platform lost &amp; found untuk membantu warga Polinema melaporkan, mencari, dan menemukan kembali barang yang hilang.</p>
            </div>
            <img src="<?= base_url('assets/images/ilustrasi-hero.png') ?>" alt="Ilustrasi TitikTemu" class="w-full max-w-xl">
        </div>

        <div class="mt-6 flex gap-2">
            <a href="?modal=hilang" class="rounded-lg bg-tt-navy px-3 py-3 text-xs font-semibold text-white hover:bg-tt-orange hover:text-tt-navy">+ Buat Laporan</a>
            <a href="<?= base_url('mahasiswa/daftar-barang.php') ?>" class="rounded-lg border border-tt-navy px-3 py-3 text-xs font-semibold hover:bg-indigo-100">Cari Barang &gt;&gt;</a>
        </div>
    </div>

    <!-- Kotak angka -->
    <div class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-indigo-200 bg-white p-4">
            <p class="text-xl font-bold">128</p>
            <p class="mt-1 text-sm">Laporan Kehilangan Saya</p>
        </div>
        <div class="rounded-xl border border-indigo-200 bg-white p-4">
            <p class="text-xl font-bold">128</p>
            <p class="mt-1 text-sm">Laporan Temuan Saya</p>
        </div>
        <div class="rounded-xl border border-indigo-200 bg-white p-4">
            <p class="text-xl font-bold">128</p>
            <p class="mt-1 text-sm">Laporan Menunggu Pemeriksaan</p>
        </div>
        <div class="rounded-xl border border-indigo-200 bg-white p-4">
            <p class="text-xl font-bold">128</p>
            <p class="mt-1 text-sm">Laporan Selesai</p>
        </div>
    </div>

    <!-- Barang temuan terbaru -->
    <div class="mt-12 flex items-end justify-between">
        <div>
            <h2 class="font-display text-xl font-semibold">Barang Temuan Terbaru</h2>
            <p class="mt-2 text-xs">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
        </div>
        <a href="<?= base_url('mahasiswa/daftar-barang.php') ?>" class="text-xs font-semibold hover:text-tt-orange">Lihat Semua &gt;&gt;</a>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:px-6">
        <?php foreach ($barangTemuan as $barang): ?>
            <div class="rounded-xl border border-indigo-200 bg-white p-4">
                <img src="<?= base_url('assets/images/foto-barang.png') ?>" alt="<?= h($barang['nama']) ?>" class="h-48 w-full rounded-lg object-cover">
                <h3 class="mt-4 font-display text-lg font-semibold"><?= h($barang['nama']) ?></h3>
                <p class="text-sm"><?= h($barang['deskripsi']) ?></p>
                <p class="mt-3 text-sm"><?= h($barang['lokasi']) ?></p>
                <p class="mt-3 text-sm"><?= h($barang['waktu']) ?></p>
                <!-- Laporan sendiri → Detail Barang, laporan orang lain → Detail Klaim -->
                <?php if ($barang['milik_saya']): ?>
                    <a href="<?= base_url('mahasiswa/laporan/detail.php') ?>" class="mt-6 inline-block text-sm font-semibold hover:text-tt-orange">Lihat Detail &rarr;</a>
                <?php else: ?>
                    <a href="<?= base_url('mahasiswa/klaim/detail.php') ?>" class="mt-6 inline-block text-sm font-semibold hover:text-tt-orange">Lihat Detail &rarr;</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Barang kehilangan terbaru -->
    <div class="mt-12 flex items-end justify-between">
        <div>
            <h2 class="font-display text-xl font-semibold">Barang Kehilangan Terbaru</h2>
            <p class="mt-2 text-xs">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
        </div>
        <a href="<?= base_url('mahasiswa/daftar-barang.php') ?>" class="text-xs font-semibold hover:text-tt-orange">Lihat Semua &gt;&gt;</a>
    </div>

    <div class="mt-4 overflow-x-auto rounded-xl border border-indigo-200 bg-white">
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
                <?php foreach ($barangHilang as $barang): ?>
                    <tr class="border-t border-indigo-100">
                        <td class="px-6 py-4"><?= h($barang['nama']) ?></td>
                        <td class="px-6 py-4">
                            <span class="rounded-full bg-purple-100 px-2 py-1 text-xs font-semibold text-purple-600"><?= h($barang['kategori']) ?></span>
                        </td>
                        <td class="px-6 py-4"><?= h($barang['lokasi']) ?></td>
                        <td class="px-6 py-4"><?= h($barang['waktu']) ?></td>
                        <td class="px-6 py-4">
                            <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-700"><?= h($barang['status']) ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="#" class="font-semibold hover:text-tt-orange">Lihat Detail &rarr;</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Cara menggunakan -->
    <div class="mt-12 text-center">
        <p class="text-xs font-semibold">MUDAH DAN TERARAH</p>
        <h2 class="mt-2 font-display text-3xl font-bold">Bagaimana cara menggunakan TitikTemu?</h2>
        <p class="mt-2 text-xs">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-indigo-200 bg-white p-6">
            <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-tt-navy text-xl text-white">✓</div>
            <h3 class="mt-4 font-display text-lg font-semibold">1. Laporkan</h3>
            <p class="mt-3 text-xs">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
        </div>
        <div class="rounded-xl border border-indigo-200 bg-white p-6">
            <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-tt-navy text-xl text-white">✓</div>
            <h3 class="mt-4 font-display text-lg font-semibold">2. Diperiksa</h3>
            <p class="mt-3 text-xs">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
        </div>
        <div class="rounded-xl border border-indigo-200 bg-white p-6">
            <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-tt-navy text-xl text-white">✓</div>
            <h3 class="mt-4 font-display text-lg font-semibold">3. Verifikasi</h3>
            <p class="mt-3 text-xs">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
        </div>
    </div>

    <!-- Ajakan buat laporan -->
    <div class="mt-6 flex flex-col items-start justify-between gap-4 rounded-xl border border-indigo-200 bg-white p-6 md:flex-row md:items-center">
        <div>
            <p class="text-xs font-semibold">MULAI SEKARANG</p>
            <h2 class="mt-2 font-display text-3xl font-bold">Barangmu bisa ditemukan kembali.</h2>
            <p class="mt-2 text-xs">Lorem ipsum dolor sit amet, consectetur adipiscing elit</p>
        </div>
        <a href="?modal=hilang" class="rounded-lg bg-tt-navy px-3 py-3 text-xs font-semibold text-white hover:bg-tt-orange hover:text-tt-navy">+ Buat Laporan</a>
    </div>

</main>

<?php
// Tampilkan modal kalau ada ?modal=hilang atau ?modal=temuan di URL
if (isset($_GET['modal'])) {
    $jenisLaporan = $_GET['modal'];
    $linkTutup = base_url('mahasiswa/dashboard.php');
    require_once APP_ROOT . '/App/views/components/mahasiswa/modal/modal-laporan.php';
}

require_once APP_ROOT . '/App/views/components/footer.php';
?>
