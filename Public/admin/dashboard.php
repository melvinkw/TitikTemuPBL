<?php
/**
 * Public/admin/dashboard.php
 * Halaman Dashboard admin (Figma: Admin-Dashboard).
 * Angka statistik dan isi tabel masih data contoh, nanti diganti hasil query database.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';

$currentUser = auth_require_role(['admin']);

$pageTitle = 'Dashboard Admin';
$menuAktif = 'dashboard';
$judulHalaman = 'Dashboard Admin';
$deskripsiHalaman = 'Pantau laporan yang menunggu pemeriksaan, kandidat kecocokan, dan aktivitas mahasiswa dari satu halaman.';

// Data contoh
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
        <p class="font-display text-[28px] font-bold">Halo, <?= h($currentUser['nama'] ?? '') ?>! 👋</p>
        <p class="text-xs">Selamat datang kembali. Berikut ringkasan aktivitas TitikTemu hari ini.</p>
    </div>

    <?php require APP_ROOT . '/App/views/components/judul-halaman.php'; ?>

    <?php require APP_ROOT . '/App/views/components/kartu-statistik.php'; ?>

    <!-- Laporan yang perlu diperiksa -->
    <section class="mt-2.5 flex flex-col gap-2.5">
        <div class="flex items-end justify-between gap-2.5">
            <h2 class="p-4 font-display text-xl font-semibold">Laporan yang Perlu Diperiksa</h2>
            <!-- Halaman daftar semua laporan belum ada -->
            <a href="#" class="px-2.75 py-3 text-sm font-semibold whitespace-nowrap hover:text-tt-orange">Lihat Semua &gt;&gt;</a>
        </div>

        <div class="tt-table-wrap">
            <table class="tt-table min-w-225">
                <thead>
                    <tr>
                        <th>Jenis</th>
                        <th>Barang</th>
                        <th>Pelapor</th>
                        <th>Lokasi</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($laporanPerluDiperiksa as $laporan): ?>
                        <tr>
                            <td><?= h($laporan['jenis']) ?></td>
                            <td><?= h($laporan['barang']) ?></td>
                            <td><?= h($laporan['pelapor']) ?></td>
                            <td><?= h($laporan['lokasi']) ?></td>
                            <td class="whitespace-nowrap"><?= h($laporan['dibuat']) ?></td>
                            <td>
                                <a href="<?= h(base_url('admin/laporan/detail.php')) ?>" class="tt-btn">Periksa</a>
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
                <h2 class="font-display text-xl font-semibold">Login Mahasiswa Terbaru</h2>
                <p class="text-xs">Mahasiswa yang terakhir masuk ke TitikTemu menggunakan akun SIAKAD.</p>
            </div>
            <!-- Halaman daftar semua mahasiswa belum ada -->
            <a href="#" class="px-2.75 py-3 text-sm font-semibold whitespace-nowrap hover:text-tt-orange">Lihat Semua &gt;&gt;</a>
        </div>

        <div class="tt-table-wrap">
            <table class="tt-table min-w-225">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NIM</th>
                        <th>Prodi</th>
                        <th>Login Terakhir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($loginTerbaru as $mahasiswa): ?>
                        <tr>
                            <td><?= h($mahasiswa['nama']) ?></td>
                            <td><?= h($mahasiswa['nim']) ?></td>
                            <td><?= h($mahasiswa['prodi']) ?></td>
                            <td class="whitespace-nowrap"><?= h($mahasiswa['login']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
