<?php
/**
 * Public/index.php
 * Halaman Beranda public (Figma: Public-Beranda).
 * Bisa dibuka tanpa login. Kalau sudah login, langsung diarahkan ke dashboard sesuai role.
 */
require_once __DIR__ . '/../App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';

if (auth_current_user() !== null) {
    redirect('/dashboard.php');
}

$pageTitle = 'Beranda';
$menuAktif = 'beranda';

// Data dummy — nanti diganti dari database
// Barang temuan = yang sudah diterima petugas.
$barangTemuan = [
    ['nama' => 'Tumbler Hitam', 'deskripsi' => 'Ini tumbler warna hitam pekat', 'lokasi' => 'LPR 8', 'waktu' => '24 Oktober 2026 11:45 AM'],
    ['nama' => 'Tumbler Hitam', 'deskripsi' => 'Ini tumbler warna hitam pekat', 'lokasi' => 'LPR 8', 'waktu' => '24 Oktober 2026 11:45 AM'],
    ['nama' => 'Tumbler Hitam', 'deskripsi' => 'Ini tumbler warna hitam pekat', 'lokasi' => 'LPR 8', 'waktu' => '24 Oktober 2026 11:45 AM'],
    ['nama' => 'Tumbler Hitam', 'deskripsi' => 'Ini tumbler warna hitam pekat', 'lokasi' => 'LPR 8', 'waktu' => '24 Oktober 2026 11:45 AM'],
];

$barangHilang = [
    ['nama' => 'MacBook Pro Silver', 'kategori' => 'Elektronik', 'lokasi' => 'LPR 8', 'waktu' => '27-10-2026 13:01', 'status' => 'Aktif'],
    ['nama' => 'STNK motor', 'kategori' => 'Identitas', 'lokasi' => 'LPR 8', 'waktu' => '27-10-2026 13:01', 'status' => 'Aktif'],
    ['nama' => 'Jam tangan', 'kategori' => 'Aksesoris', 'lokasi' => 'LPR 8', 'waktu' => '27-10-2026 13:01', 'status' => 'Aktif'],
];

// Warna label kategori (sesuai Figma)
$warnaKategori = [
    'Elektronik' => 'bg-[#EBE5F2] text-[#8A38F5]',
    'Identitas'  => 'bg-[#FFF5EF] text-[#E48F31]',
    'Aksesoris'  => 'bg-[#F0EAF1] text-[#DF38F5]',
];

// Langkah cara menggunakan TitikTemu
$langkah = [
    ['judul' => '1. Laporkan', 'teks' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'],
    ['judul' => '2. Diperiksa', 'teks' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'],
    ['judul' => '3. Verifikasi', 'teks' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'],
];

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/public/navbar.php';
?>

<main class="w-full px-4 py-8 md:px-20">

    <!-- Banner -->
    <div class="rounded-xl border border-indigo-200 bg-white p-6">
        <div class="flex flex-col items-center gap-6 md:flex-row">
            <div class="flex-1">
                <p class="text-xs font-semibold">LOST &amp; FOUND POLINEMA</p>
                <h1 class="mt-2 font-display text-4xl font-extrabold leading-tight md:text-5xl">
                    Barang Hilang?<br>
                    Temukan di<br>
                    <span class="text-tt-orange">TitikTemu.</span>
                </h1>
                <p class="mt-4 text-xs text-gray-500">Platform lost &amp; found untuk membantu warga Polinema melaporkan, mencari, dan menemukan kembali barang yang hilang.</p>
            </div>
            <div class="w-full flex-1">
                <img src="<?= base_url('assets/images/ilustrasi-hero.png') ?>" alt="Ilustrasi TitikTemu">
            </div>
        </div>

        <div class="mt-6 flex gap-2">
            <!-- Buat laporan wajib login dulu -->
            <a href="<?= base_url('login.php') ?>" class="rounded-lg bg-tt-navy px-3 py-3 text-xs font-semibold text-white hover:bg-tt-orange hover:text-tt-navy">+ Buat Laporan</a>
            <a href="<?= base_url('daftar-barang.php') ?>" class="rounded-lg border border-tt-navy px-3 py-3 text-xs font-semibold hover:bg-indigo-100">Cari Barang &gt;&gt;</a>
        </div>
    </div>

    <!-- Barang temuan terbaru -->
    <div class="mt-12 flex items-end justify-between">
        <div>
            <h2 class="font-display text-xl font-semibold">Barang Temuan Terbaru</h2>
            <p class="mt-2 text-xs">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
        </div>
        <a href="<?= base_url('daftar-barang.php') ?>" class="text-xs font-semibold hover:text-tt-orange">Lihat Semua &gt;&gt;</a>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:px-6">
        <?php foreach ($barangTemuan as $barang): ?>
            <div class="rounded-xl border border-indigo-200 bg-white p-4">
                <img src="<?= base_url('assets/images/foto-barang.png') ?>" alt="<?= h($barang['nama']) ?>" class="h-48 w-full rounded-lg object-cover">
                <h3 class="mt-4 font-display text-lg font-semibold"><?= h($barang['nama']) ?></h3>
                <p class="text-sm"><?= h($barang['deskripsi']) ?></p>
                <p class="mt-3 text-sm"><?= h($barang['lokasi']) ?></p>
                <p class="mt-3 text-sm"><?= h($barang['waktu']) ?></p>
                <a href="<?= base_url('Detail-Temuan.php') ?>" class="mt-6 inline-block text-sm font-semibold hover:text-tt-orange">Lihat Detail &rarr;</a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Barang kehilangan terbaru -->
    <div class="mt-12 flex items-end justify-between">
        <div>
            <h2 class="font-display text-xl font-semibold">Barang Kehilangan Terbaru</h2>
            <p class="mt-2 text-xs">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
        </div>
        <a href="<?= base_url('daftar-barang.php') ?>" class="text-xs font-semibold hover:text-tt-orange">Lihat Semua &gt;&gt;</a>
    </div>

    <div class="mt-4 overflow-x-auto rounded-xl border border-indigo-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-tt-tablehead font-display text-base">
                <tr>
                    <th class="px-6 py-5 font-semibold">Nama Barang</th>
                    <th class="px-6 py-5 font-semibold">Kategori</th>
                    <th class="px-6 py-5 font-semibold">Lokasi Kehilangan</th>
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
                            <span class="rounded-full px-2 py-1 text-xs font-semibold <?= $warnaKategori[$barang['kategori']] ?? 'bg-gray-100 text-gray-600' ?>"><?= h($barang['kategori']) ?></span>
                        </td>
                        <td class="px-6 py-4"><?= h($barang['lokasi']) ?></td>
                        <td class="px-6 py-4"><?= h($barang['waktu']) ?></td>
                        <td class="px-6 py-4">
                            <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-700"><?= h($barang['status']) ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="<?= base_url('Detail-Kehilangan.php') ?>" class="font-semibold hover:text-tt-orange">Lihat Detail &rarr;</a>
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
        <?php foreach ($langkah as $item): ?>
            <div class="rounded-xl border border-indigo-200 bg-white p-8">
                <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-tt-navy">
                    <img src="<?= base_url('assets/images/icon-centang.svg') ?>" alt="" class="h-5 w-5">
                </div>
                <h3 class="mt-3 font-display text-lg font-semibold"><?= h($item['judul']) ?></h3>
                <p class="mt-2 text-xs"><?= h($item['teks']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Ajakan login -->
    <div class="mt-12 mb-8 flex flex-col gap-6 rounded-xl border border-indigo-200 bg-white p-8 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-xs font-semibold">MULAI SEKARANG</p>
            <h2 class="mt-2 font-display text-3xl font-bold">Barangmu bisa ditemukan kembali.</h2>
            <p class="mt-2 text-xs">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
        </div>
        <a href="<?= base_url('login.php') ?>" class="shrink-0 self-start rounded-lg bg-tt-navy px-4 py-3 text-xs font-semibold text-white hover:bg-tt-orange hover:text-tt-navy md:self-auto">Login untuk melapor &rarr;</a>
    </div>

</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
