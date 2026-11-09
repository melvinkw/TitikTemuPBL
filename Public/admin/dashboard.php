<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
/**
 * Public/admin/dashboard.php
 * Halaman Dashboard admin (Figma: Admin-Dashboard).
 */
require_once __DIR__ . '/../../App/config/bootstrap.php';

$pageTitle = 'Dashboard Admin';
$menuAktif = 'dashboard';
$namaUser = 'John Doe';

// Data dummy — nanti diganti dari database
$statistik = [
    ['angka' => 128, 'label' => 'Laporan Menunggu Pemeriksaan'],
    ['angka' => 128, 'label' => 'Mahasiswa Aktif'],
    ['angka' => 128, 'label' => 'Kandidat Kecocokan'],
    ['angka' => 128, 'label' => 'Email Menunggu / Gagal'],
];

$laporanPerluDiperiksa = [
    ['jenis' => 'Temuan',     'barang' => 'TWS Putih',  'pelapor' => 'Jorn Doe', 'lokasi' => 'LPR 8', 'dibuat' => '27-10-2026 17:01'],
    ['jenis' => 'Kehilangan', 'barang' => 'TWS Huawei', 'pelapor' => 'John Doe', 'lokasi' => 'LPR 8', 'dibuat' => '27-10-2026 13:01'],
];

$loginTerbaru = [
    ['nama' => 'John Doe', 'nim' => '254107060001', 'prodi' => 'D-IV Sistem Informasi Bisnis', 'login' => '27-10-2026 13:01'],
    ['nama' => 'Jorn Doe', 'nim' => '254107060002', 'prodi' => 'D-IV Teknik Informatika',      'login' => '27-10-2026 17:01'],
];

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/admin/navbar.php';
?>

<main class="flex w-full flex-col gap-6.5 px-4 pt-2.5 pb-10 md:px-20">

    <!-- Sapaan -->
    <div class="flex flex-col gap-2 p-4">
        <h1 class="font-display text-[28px] font-bold">Halo, <?= h($namaUser) ?>! 👋</h1>
        <p class="text-xs">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
    </div>

    <!-- Judul halaman -->
    <div class="flex flex-col gap-2 p-4">
        <p class="text-sm font-semibold">RUANG KERJA ADMINISTRATOR</p>
        <h2 class="font-display text-[28px] font-bold">Dashboard Admin</h2>
        <p class="text-xs">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
    </div>

    <!-- Kartu statistik -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
        <?php foreach ($statistik as $stat): ?>
            <div class="flex flex-col gap-2 rounded-2xl border border-tt-outline bg-white p-6">
                <p class="font-display text-[28px] font-bold"><?= h($stat['angka']) ?></p>
                <p class="text-base"><?= h($stat['label']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Laporan yang perlu diperiksa -->
    <section class="mt-2.5 flex flex-col gap-2.5">
        <div class="flex items-end justify-between gap-2.5">
            <h3 class="p-4 font-display text-xl font-semibold">Laporan yang Perlu Diperiksa</h3>
            <!-- Halaman daftar laporan belum ada -->
            <a href="#" class="px-2.75 py-3 text-sm font-semibold whitespace-nowrap">Lihat Semua &gt;&gt;</a>
        </div>

        <div class="overflow-x-auto rounded-[15px] border border-tt-outline bg-white">
            <table class="w-full min-w-225 text-left text-sm">
                <thead class="bg-tt-tablehead font-display text-xl">
                    <tr>
                        <th class="px-7.5 py-8 font-semibold whitespace-nowrap">Jenis</th>
                        <th class="px-7.5 py-8 font-semibold whitespace-nowrap">Barang</th>
                        <th class="px-7.5 py-8 font-semibold whitespace-nowrap">Pelapor</th>
                        <th class="px-7.5 py-8 font-semibold whitespace-nowrap">Lokasi</th>
                        <th class="px-7.5 py-8 font-semibold whitespace-nowrap">Dibuat</th>
                        <th class="px-7.5 py-8 font-semibold whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($laporanPerluDiperiksa as $laporan): ?>
                        <tr class="border-b border-tt-outline last:border-b-0">
                            <td class="px-7.5 py-4"><?= h($laporan['jenis']) ?></td>
                            <td class="px-7.5 py-4"><?= h($laporan['barang']) ?></td>
                            <td class="px-7.5 py-4"><?= h($laporan['pelapor']) ?></td>
                            <td class="px-7.5 py-4"><?= h($laporan['lokasi']) ?></td>
                            <td class="px-7.5 py-4 whitespace-nowrap"><?= h($laporan['dibuat']) ?></td>
                            <td class="px-7.5 py-4">
                                <!-- Halaman detail laporan admin belum dibuat -->
                                <a href="#" class="inline-flex h-10.75 items-center rounded-[10px] bg-tt-navy px-2.75 font-semibold text-white">Periksa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Login mahasiswa terbaru -->
    <section class="mt-2.5 flex flex-col gap-2.5">
        <div class="flex items-end justify-between gap-2.5">
            <div class="flex max-w-232.5 flex-col gap-2 p-4">
                <h3 class="font-display text-xl font-semibold">Login mahasiswa terbaru</h3>
                <p class="text-xs">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
            </div>
            <!-- Halaman daftar mahasiswa belum ada -->
            <a href="#" class="px-2.75 py-3 text-sm font-semibold whitespace-nowrap">Lihat Semua &gt;&gt;</a>
        </div>

        <div class="overflow-x-auto rounded-[15px] border border-tt-outline bg-white">
            <table class="w-full min-w-225 text-left text-sm">
                <thead class="bg-tt-tablehead font-display text-xl">
                    <tr>
                        <th class="px-7.5 py-8 font-semibold whitespace-nowrap">Nama</th>
                        <th class="px-7.5 py-8 font-semibold whitespace-nowrap">NIM</th>
                        <th class="px-7.5 py-8 font-semibold whitespace-nowrap">Prodi</th>
                        <th class="px-7.5 py-8 font-semibold whitespace-nowrap">Login Terakhir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($loginTerbaru as $mhs): ?>
                        <tr class="border-b border-tt-outline last:border-b-0">
                            <td class="h-18 px-7.5"><?= h($mhs['nama']) ?></td>
                            <td class="h-18 px-7.5"><?= h($mhs['nim']) ?></td>
                            <td class="h-18 px-7.5"><?= h($mhs['prodi']) ?></td>
                            <td class="h-18 px-7.5 whitespace-nowrap"><?= h($mhs['login']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
