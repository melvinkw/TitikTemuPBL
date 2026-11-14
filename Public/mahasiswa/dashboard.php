<?php

declare(strict_types=1);
/**
 * Public/mahasiswa/dashboard.php
 * Halaman Dashboard mahasiswa (Figma: Mahasiswa-Beranda).
 *
 * Modal Buat Laporan bisa dilihat lewat URL:
 *   dashboard.php?modal=hilang   atau   dashboard.php?modal=temuan
 */
require_once __DIR__ . '/../../App/config/bootstrap.php';
require_once APP_ROOT . '/App/queries/master_queries.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/queries/mahasiswa_queries.php';


$currentUser = auth_require_role(['mahasiswa']);

$idPengguna = (int) $currentUser['id_pengguna'];

$kategoriAktif = get_kategori_aktif();
$posAktif = get_pos_aktif();
$flashMessage = flash();

$pageTitle = 'Dashboard';
$menuAktif = 'dashboard';
$namaUser = $currentUser['nama'];

$statistik = get_mahasiswa_dashboard_stats($idPengguna);

$barangTemuan = get_mahasiswa_recent_public_reports($idPengguna, 'temuan', 4);

$barangHilang = get_mahasiswa_recent_public_reports($idPengguna, 'hilang', 4);

$statusLabels = [
    'menunggu' => 'Menunggu Pemeriksaan',
    'aktif' => 'Aktif',
    'tersedia' => 'Barang Tersedia',
    'diproses' => 'Sedang Diproses',
    'selesai' => 'Selesai',
    'ditolak' => 'Ditolak',
    'ditutup' => 'Ditutup',
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
        <div class="flex md:flex-row flex-col items-center gap-6 ">
            <div class="flex-1">
                <p class="text-xs font-semibold">LOST &amp; FOUND POLINEMA</p>
                <h2 class="mt-2 font-display text-4xl font-extrabold leading-tight md:text-5xl">
                    Barang Hilang?<br>
                    Temukan di<br>
                    <span class="text-tt-orange">TitikTemu.</span>
                </h2>
                <p class="mt-4 text-xs text-gray-500">Platform lost &amp; found untuk membantu warga Polinema melaporkan, mencari, dan menemukan kembali barang yang hilang.</p>
            </div>
            <div class="flex-1 w-full">
                <img src="<?= base_url('assets/images/ilustrasi-hero.png') ?>" alt="Ilustrasi TitikTemu">
            </div>
        </div>

        <div class="mt-6 flex gap-2">
            <a href="?modal=hilang" class="rounded-lg bg-tt-navy px-3 py-3 text-xs font-semibold text-white hover:bg-tt-orange hover:text-tt-navy">+ Buat Laporan</a>
            <a href="<?= base_url('mahasiswa/daftar-barang.php') ?>" class="rounded-lg border border-tt-navy px-3 py-3 text-xs font-semibold hover:bg-indigo-100">Cari Barang &gt;&gt;</a>
        </div>
    </div>

    <!-- Kotak angka -->
    <div class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4">
        <div class="rounded-xl border border-indigo-200 bg-white p-4">
            <p class="text-xl font-bold"><?= number_format($statistik['laporan_hilang']) ?></p>
            <p class="mt-1 text-sm">Laporan Kehilangan Saya</p>
        </div>
        <div class="rounded-xl border border-indigo-200 bg-white p-4">
            <p class="text-xl font-bold"><?= number_format($statistik['laporan_temuan']) ?></p>
            <p class="mt-1 text-sm">Laporan Temuan Saya</p>
        </div>
        <div class="rounded-xl border border-indigo-200 bg-white p-4">
            <p class="text-xl font-bold"><?= number_format($statistik['menunggu_pemeriksaan']) ?></p>
            <p class="mt-1 text-sm">Laporan Menunggu Pemeriksaan</p>
        </div>
        <div class="rounded-xl border border-indigo-200 bg-white p-4">
            <p class="text-xl font-bold"><?= number_format($statistik['laporan_selesai']) ?></p>
            <p class="mt-1 text-sm">Laporan Selesai</p>
        </div>
    </div>

    <!-- Barang temuan terbaru -->

    <!-- Barang temuan terbaru -->
    <div class="mt-12 flex items-end justify-between">
        <div>
            <h2 class="font-display text-xl font-semibold">
                Barang Temuan Terbaru
            </h2>
            <p class="mt-2 text-xs">
                Barang temuan yang telah disetujui dan tersedia.
            </p>
        </div>

        <a
            href="<?= h(base_url('mahasiswa/daftar-barang.php?jenis=temuan')) ?>"
            class="text-xs font-semibold hover:text-tt-orange">
            Lihat Semua &gt;&gt;
        </a>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:px-6">

        <?php if ($barangTemuan === []): ?>

            <div class="col-span-full rounded-xl border border-indigo-200 bg-white p-8 text-center">
                <h3 class="font-display text-lg font-semibold">
                    Belum Ada Barang Temuan
                </h3>
                <p class="mt-2 text-sm text-gray-500">
                    Barang temuan yang telah disetujui akan muncul di sini.
                </p>
            </div>

        <?php else: ?>

            <?php foreach ($barangTemuan as $barang): ?>

                <?php
                $idBarang = (int) $barang['id_pengumuman'];

                $detailUrl = $barang['milik_saya']
                    ? base_url('mahasiswa/laporan/detail.php?id=' . $idBarang)
                    : base_url('mahasiswa/klaim/detail.php?id=' . $idBarang);
                ?>

                <div class="rounded-xl border border-indigo-200 bg-white p-4">

                    <!-- Foto akan dihubungkan melalui signed URL Storage. -->
                    <div class="flex h-48 w-full items-center justify-center rounded-lg bg-slate-100 text-sm text-slate-500">
                        Foto belum dapat ditampilkan
                    </div>

                    <h3 class="mt-4 font-display text-lg font-semibold">
                        <?= h($barang['judul']) ?>
                    </h3>

                    <p class="text-sm">
                        <?= h($barang['deskripsi']) ?>
                    </p>

                    <p class="mt-3 text-sm">
                        <?= h($barang['nama_kategori']) ?>
                    </p>

                    <p class="mt-2 text-sm">
                        <?= h($barang['lokasi_kejadian']) ?>
                    </p>

                    <p class="mt-2 text-xs text-gray-500">
                        <?= h(format_date_time($barang['tanggal_waktu_kejadian'])) ?>
                    </p>

                    <a
                        href="<?= h($detailUrl) ?>"
                        class="mt-6 inline-block text-sm font-semibold hover:text-tt-orange">
                        Lihat Detail &rarr;
                    </a>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>


    <!-- Barang kehilangan terbaru -->

    <!-- Barang kehilangan terbaru -->
    <div class="mt-12 flex items-end justify-between">
        <div>
            <h2 class="font-display text-xl font-semibold">
                Barang Kehilangan Terbaru
            </h2>
            <p class="mt-2 text-xs">
                Daftar laporan kehilangan yang telah disetujui.
            </p>
        </div>

        <a
            href="<?= h(base_url('mahasiswa/daftar-barang.php?jenis=hilang')) ?>"
            class="text-xs font-semibold hover:text-tt-orange">
            Lihat Semua &gt;&gt;
        </a>
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

                <?php if ($barangHilang === []): ?>

                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                            Belum ada laporan kehilangan yang tersedia.
                        </td>
                    </tr>

                <?php else: ?>

                    <?php foreach ($barangHilang as $barang): ?>

                        <?php
                        $idBarang = (int) $barang['id_pengumuman'];

                        $detailUrl = $barang['milik_saya']
                            ? base_url('mahasiswa/laporan/detail.php?id=' . $idBarang)
                            : base_url('Detail-Kehilangan.php?id=' . $idBarang);

                        $statusText = $statusLabels[$barang['status']]
                            ?? ucfirst($barang['status']);
                        ?>

                        <tr class="border-t border-indigo-100">

                            <td class="px-6 py-4">
                                <?= h($barang['judul']) ?>
                            </td>

                            <td class="px-6 py-4">
                                <span class="rounded-full bg-purple-100 px-2 py-1 text-xs font-semibold text-purple-600">
                                    <?= h($barang['nama_kategori']) ?>
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <?= h($barang['lokasi_kejadian']) ?>
                            </td>

                            <td class="px-6 py-4">
                                <?= h(format_date_time($barang['tanggal_waktu_kejadian'])) ?>
                            </td>

                            <td class="px-6 py-4">
                                <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-700">
                                    <?= h($statusText) ?>
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <a
                                    href="<?= h($detailUrl) ?>"
                                    class="font-semibold hover:text-tt-orange">
                                    Lihat Detail &rarr;
                                </a>
                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php endif; ?>

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
        <div class="rounded-xl border border-indigo-200 bg-white p-8">
            <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-tt-navy text-white">
                <!-- Ikon centang dalam lingkaran -->
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M8.5 12.5l2.5 2.5 4.5-5"></path>
                </svg>
            </div>
            <h3 class="mt-3 font-display text-lg font-semibold">1. Laporkan</h3>
            <p class="mt-2 text-xs">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
        </div>
        <div class="rounded-xl border border-indigo-200 bg-white p-8">
            <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-tt-navy text-white">
                <!-- Ikon centang dalam lingkaran -->
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M8.5 12.5l2.5 2.5 4.5-5"></path>
                </svg>
            </div>
            <h3 class="mt-3 font-display text-lg font-semibold">2. Diperiksa</h3>
            <p class="mt-2 text-xs">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
        </div>
        <div class="rounded-xl border border-indigo-200 bg-white p-8">
            <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-tt-navy text-white">
                <!-- Ikon centang dalam lingkaran -->
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="9"></circle>
                    <path d="M8.5 12.5l2.5 2.5 4.5-5"></path>
                </svg>
            </div>
            <h3 class="mt-3 font-display text-lg font-semibold">3. Verifikasi</h3>
            <p class="mt-2 text-xs">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
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

<?php if ($flashMessage !== null): ?>
    <?php $isError = ($flashMessage['type'] ?? '') === 'error'; ?>

    <div class="mb-5 rounded-lg border p-4
        <?= $isError
            ? 'border-red-300 bg-red-50 text-red-700'
            : 'border-green-300 bg-green-50 text-green-700' ?>">
        <?= h($flashMessage['message'] ?? '') ?>
    </div>
<?php endif; ?>

<?php
// Tampilkan modal kalau ada ?modal=hilang atau ?modal=temuan di URL
if (isset($_GET['modal'])) {
    $jenisLaporan = $_GET['modal'];
    $linkTutup = base_url('mahasiswa/dashboard.php');
    require_once APP_ROOT . '/App/views/components/mahasiswa/modal/modal-laporan.php';
}

require_once APP_ROOT . '/App/views/components/footer.php';
?>