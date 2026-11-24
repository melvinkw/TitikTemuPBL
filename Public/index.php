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

require_once APP_ROOT . '/App/queries/public_queries.php';
require_once APP_ROOT . '/App/services/supabase_storage.php';

$pageTitle = 'Beranda';
$menuAktif = 'beranda';

// Barang temuan = laporan yang sudah disetujui dan siap diklaim.
$barangTemuan = [];
$barangHilang = [];
$berandaError = null;

try {
    $barangTemuan = get_public_recent_reports('temuan', 4);
    $barangHilang = get_public_recent_reports('hilang', 3);
} catch (Throwable $error) {
    error_log(
        '[TitikTemu] Gagal memuat data beranda: '
        . $error->getMessage()
    );

    $berandaError = 'Data barang belum dapat dimuat. Silakan coba lagi.';
}

// Langkah cara menggunakan TitikTemu
$langkah = [
    ['judul' => '1. Laporkan', 'teks' => 'Laporkan barang yang hilang atau yang kamu temukan, lengkap dengan foto, lokasi, dan waktu kejadian.'],
    ['judul' => '2. Diperiksa', 'teks' => 'Petugas memeriksa laporanmu. Laporan yang disetujui akan tampil di daftar barang.'],
    ['judul' => '3. Verifikasi', 'teks' => 'Ajukan klaim dengan bukti kepemilikan. Setelah diverifikasi petugas, ambil barangmu di pos penyerahan.'],
];

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/public/navbar.php';
?>

<main class="w-full px-4 py-8 md:px-20">

    <!-- Banner -->
    <div class="rounded-2xl border border-tt-outline bg-white p-6">
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
            <a href="<?= base_url('daftar-barang.php') ?>" class="rounded-lg border border-tt-navy px-3 py-3 text-xs font-semibold hover:bg-tt-primary">Cari Barang &gt;&gt;</a>
        </div>
    </div>

    <!-- Barang temuan terbaru -->
    <div class="mt-12 flex items-end justify-between">
        <div>
            <h2 class="font-display text-xl font-semibold">Barang Temuan Terbaru</h2>
            <p class="mt-2 text-xs">Barang temuan yang telah disetujui dan tersedia.</p>
        </div>
        <a href="<?= base_url('daftar-barang.php') ?>" class="text-xs font-semibold hover:text-tt-orange">Lihat Semua &gt;&gt;</a>
    </div>

    <?php if ($berandaError !== null): ?>
        <div role="alert" class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <?= h($berandaError) ?>
        </div>
    <?php endif; ?>

    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:px-6">
        <?php if ($barangTemuan === []): ?>
            <p class="col-span-full rounded-xl border border-indigo-200 bg-white p-8 text-center text-sm text-gray-500">
                Belum ada barang temuan yang tayang.
            </p>
        <?php endif; ?>

        <?php foreach ($barangTemuan as $barang): ?>
            <?php
            $fotoTemuan = null;

            if (!empty($barang['foto_bucket']) && !empty($barang['foto_path'])) {
                try {
                    $fotoTemuan = supabase_storage_create_signed_url(
                        $barang['foto_bucket'],
                        $barang['foto_path']
                    );
                } catch (Throwable $fotoError) {
                    error_log(
                        '[TitikTemu] Gagal memuat foto beranda: '
                        . $fotoError->getMessage()
                    );
                }
            }
            ?>

            <div class="rounded-2xl border border-tt-outline bg-white p-4">
                <?php if ($fotoTemuan !== null): ?>
                    <img src="<?= h($fotoTemuan) ?>" alt="<?= h($barang['judul']) ?>" loading="lazy" class="h-48 w-full rounded-lg object-cover">
                <?php else: ?>
                    <div class="flex h-48 w-full items-center justify-center rounded-lg bg-slate-100 text-sm text-slate-500">Foto belum tersedia</div>
                <?php endif; ?>

                <h3 class="mt-4 font-display text-lg font-semibold"><?= h($barang['judul']) ?></h3>
                <p class="text-sm"><?= h($barang['deskripsi']) ?></p>
                <p class="mt-3 text-sm"><span class="font-semibold">Kategori:</span> <?= h($barang['nama_kategori']) ?></p>
                <p class="mt-1 text-sm"><span class="font-semibold">Lokasi:</span> <?= h($barang['lokasi_kejadian']) ?></p>
                <p class="mt-3 text-xs text-gray-500"><?= h(format_date_time($barang['tanggal_waktu_kejadian'])) ?></p>
                <a href="<?= h(base_url('Detail-Temuan.php?id=' . (int) $barang['id_pengumuman'])) ?>" class="mt-6 inline-block text-sm font-semibold hover:text-tt-orange">Lihat Detail &rarr;</a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Barang kehilangan terbaru -->
    <div class="mt-12 flex items-end justify-between">
        <div>
            <h2 class="font-display text-xl font-semibold">Barang Kehilangan Terbaru</h2>
            <p class="mt-2 text-xs">Daftar laporan kehilangan yang telah disetujui.</p>
        </div>
        <a href="<?= base_url('daftar-barang.php') ?>" class="text-xs font-semibold hover:text-tt-orange">Lihat Semua &gt;&gt;</a>
    </div>

    <div class="mt-4 overflow-x-auto rounded-2xl border border-tt-outline bg-white">
        <table class="w-full min-w-180 text-left text-sm">
            <thead class="bg-tt-tablehead font-display text-base">
                <tr>
                    <th class="px-6 py-5 font-semibold whitespace-nowrap">Nama Barang</th>
                    <th class="px-6 py-5 font-semibold whitespace-nowrap">Kategori</th>
                    <th class="px-6 py-5 font-semibold whitespace-nowrap">Lokasi Kehilangan</th>
                    <th class="px-6 py-5 font-semibold whitespace-nowrap">Tanggal &amp; Waktu</th>
                    <th class="px-6 py-5 font-semibold whitespace-nowrap">Status</th>
                    <th class="px-6 py-5 font-semibold whitespace-nowrap">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($barangHilang === []): ?>
                    <tr class="border-t border-tt-outline">
                        <td colspan="6" class="px-6 py-6 text-center text-sm text-gray-500">
                            Belum ada laporan kehilangan yang tayang.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($barangHilang as $barang): ?>
                    <tr class="border-t border-tt-outline">
                        <td class="px-6 py-4"><?= h($barang['judul']) ?></td>
                        <td class="px-6 py-4">
                            <span class="rounded-full bg-[#EBE5F2] px-2 py-1 text-xs font-semibold text-[#8A38F5]"><?= h($barang['nama_kategori']) ?></span>
                        </td>
                        <td class="px-6 py-4"><?= h($barang['lokasi_kejadian']) ?></td>
                        <td class="px-6 py-4"><?= h(format_date_time($barang['tanggal_waktu_kejadian'])) ?></td>
                        <td class="px-6 py-4">
                            <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-700">Aktif</span>
                        </td>
                        <td class="px-6 py-4">
                            <a href="<?= h(base_url('Detail-Kehilangan.php?id=' . (int) $barang['id_pengumuman'])) ?>" class="font-semibold hover:text-tt-orange">Lihat Detail &rarr;</a>
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
        <p class="mt-2 text-xs">Tiga langkah sederhana dari melapor sampai barangmu kembali.</p>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
        <?php foreach ($langkah as $item): ?>
            <div class="rounded-2xl border border-tt-outline bg-white p-8">
                <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-tt-navy">
                    <img src="<?= base_url('assets/images/icon-centang.svg') ?>" alt="" class="h-5 w-5">
                </div>
                <h3 class="mt-3 font-display text-lg font-semibold"><?= h($item['judul']) ?></h3>
                <p class="mt-2 text-xs"><?= h($item['teks']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Ajakan login -->
    <div class="mt-12 mb-8 flex flex-col gap-6 rounded-2xl border border-tt-outline bg-white p-8 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-xs font-semibold">MULAI SEKARANG</p>
            <h2 class="mt-2 font-display text-3xl font-bold">Barangmu bisa ditemukan kembali.</h2>
            <p class="mt-2 text-xs">Masuk dengan NIM atau username untuk melaporkan barang hilang atau barang yang kamu temukan.</p>
        </div>
        <a href="<?= base_url('login.php') ?>" class="shrink-0 self-start rounded-lg bg-tt-navy px-4 py-3 text-xs font-semibold text-white hover:bg-tt-orange hover:text-tt-navy md:self-auto">Login untuk melapor &rarr;</a>
    </div>

</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
