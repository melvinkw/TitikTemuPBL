<?php
/**
 * Public/mahasiswa/klaim/detail.php
 * Detail barang temuan + form klaim (Figma: Mahasiswa-Detail Klaim).
 * Tampil kalau laporan ini dibuat oleh user lain (riwayat status tidak ditampilkan).
 * Mahasiswa bisa mengajukan klaim kalau merasa barang ini miliknya.
 */
require_once __DIR__ . '/../../../App/config/bootstrap.php';

$pageTitle = 'Detail Klaim';
$menuAktif = '';

// Data dummy — nanti diganti dari database
$barang = [
    'kategori' => 'Elektronik',
    'nama'     => 'TWS Huawei',
    'lokasi'   => 'LPR 8',
    'waktu'    => '24 Oktober 2026 12:45',
    'pelapor'  => 'John Doe',
    'status'   => 'Barang Tersedia',
    'dibuat'   => '29-10-2026 13:57',
];

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/mahasiswa/navbar.php';
?>

<main class="w-full px-4 py-6 md:px-20">

    <!-- Kembali ke halaman sebelumnya -->
    <a href="javascript:history.back()" class="text-sm font-semibold hover:text-tt-orange">&lt;&lt; Kembali ke daftar</a>

    <div class="mt-8 grid grid-cols-1 items-start gap-8 lg:grid-cols-2">

        <!-- Kiri: info barang -->
        <div class="rounded-xl border border-indigo-200 bg-white p-8">
            <span class="rounded-full bg-purple-100 px-2 py-1 text-xs font-semibold text-purple-600"><?= h($barang['kategori']) ?></span>
            <h1 class="mt-3 font-display text-2xl font-bold"><?= h($barang['nama']) ?></h1>

            <p class="mt-6 text-sm"><?= h($barang['lokasi']) ?></p>
            <p class="mt-3 text-sm"><?= h($barang['waktu']) ?></p>
            <p class="mt-3 text-sm"><?= h($barang['pelapor']) ?></p>

            <h2 class="mt-6 font-display text-2xl font-bold">Foto Barang</h2>
            <div class="mt-2 grid grid-cols-2 gap-2">
                <img src="<?= base_url('assets/images/foto-barang.png') ?>" alt="Foto 1" class="row-span-2 h-full w-full rounded-xl object-cover">
                <img src="<?= base_url('assets/images/foto-barang.png') ?>" alt="Foto 2" class="h-28 w-full rounded-xl object-cover">
                <img src="<?= base_url('assets/images/foto-barang.png') ?>" alt="Foto 3" class="h-28 w-full rounded-xl object-cover">
            </div>
        </div>

        <!-- Kanan: status + form klaim -->
        <div class="rounded-xl border border-indigo-200 bg-white p-8">
            <h2 class="font-display text-2xl font-bold">Status Laporan</h2>
            <span class="mt-3 inline-block rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-700"><?= h($barang['status']) ?></span>
            <p class="mt-3 text-xs">Laporan dibuat pada <?= h($barang['dibuat']) ?>.</p>

            <h2 class="mt-12 font-display text-2xl font-bold">Merasa ini barangmu?</h2>
            <p class="mt-3 text-xs">Ajukan klaim dengan menyebutkan ciri atau bukti yang tidak ditampilkan secara publik.</p>

            <form action="#" method="POST" class="mt-6">
                <label class="block font-display text-sm font-semibold">Bukti / ciri kepemilikan*</label>
                <textarea name="bukti" rows="3" placeholder="Tuliskan detail/ciri-ciri umum barang"
                          class="mt-2 w-full rounded-lg border border-indigo-200 px-3 py-3 text-sm placeholder:text-indigo-200"></textarea>

                <label class="mt-4 block font-display text-sm font-semibold">Foto barang (max 3)*</label>
                <input type="file" name="foto[]" multiple
                       class="mt-2 w-full rounded-lg border border-indigo-200 px-3 py-2 text-sm file:mr-3 file:rounded file:border-0 file:bg-indigo-50 file:px-3 file:py-1">
                <p class="mt-3 text-xs">JPG, PNG, WebP, PDF maksimal 5 MB.</p>

                <!-- Sementara langsung ke Riwayat (sesuai prototype), nanti diganti submit form -->
                <a href="<?= base_url('mahasiswa/riwayat.php') ?>" class="mt-4 block w-full rounded-lg bg-tt-navy py-3 text-center text-sm font-semibold text-white hover:bg-tt-orange hover:text-tt-navy">Ajukan Klaim</a>
            </form>
        </div>
    </div>

</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
