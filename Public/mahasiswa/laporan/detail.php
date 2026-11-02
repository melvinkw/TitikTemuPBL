<?php
/**
 * Public/mahasiswa/laporan/detail.php
 * Detail laporan milik mahasiswa sendiri (Figma: Mahasiswa-Detail Barang).
 * Tampil kalau laporan ini dibuat oleh user yang sedang login.
 * Berisi info barang, foto, status laporan, dan riwayat perubahan status.
 */
require_once __DIR__ . '/../../../App/config/bootstrap.php';

$pageTitle = 'Detail Barang';
$menuAktif = '';

// Data dummy — nanti diganti dari database
$barang = [
    'kategori' => 'Elektronik',
    'nama'     => 'TWS Huawei',
    'lokasi'   => 'LPR 8',
    'waktu'    => '24 Oktober 2026 12:45',
    'pelapor'  => 'John Doe',
    'status'   => 'Aktif',
    'dibuat'   => '29-10-2026 13:57',
];

$riwayatStatus = [
    ['waktu' => '27-10-2026 13:01', 'perubahan' => 'Menunggu pemeriksaan', 'status' => 'Aktif', 'oleh' => 'Admin 1', 'catatan' => 'Status ditetapkan melalui pemeriksaan petugas.'],
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

        <!-- Kanan: status laporan -->
        <div class="rounded-xl border border-indigo-200 bg-white p-8">
            <h2 class="font-display text-2xl font-bold">Status Laporan</h2>
            <span class="mt-3 inline-block rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-700"><?= h($barang['status']) ?></span>
            <p class="mt-3 text-xs">Laporan dibuat pada <?= h($barang['dibuat']) ?>.</p>
        </div>
    </div>

    <!-- Riwayat status -->
    <h2 class="mt-12 font-display text-lg font-semibold">Riwayat Status Laporan</h2>

    <div class="mt-6 overflow-x-auto rounded-xl border border-indigo-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-tt-tablehead font-display text-base">
                <tr>
                    <th class="px-6 py-5 font-semibold">Waktu</th>
                    <th class="px-6 py-5 font-semibold">Perubahan</th>
                    <th class="px-6 py-5 font-semibold">Diubah Oleh</th>
                    <th class="px-6 py-5 font-semibold">Catatan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($riwayatStatus as $riwayat): ?>
                    <tr class="border-t border-indigo-100">
                        <td class="px-6 py-4"><?= h($riwayat['waktu']) ?></td>
                        <td class="px-6 py-4">
                            <?= h($riwayat['perubahan']) ?>
                            <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-700"><?= h($riwayat['status']) ?></span>
                        </td>
                        <td class="px-6 py-4"><?= h($riwayat['oleh']) ?></td>
                        <td class="px-6 py-4"><?= h($riwayat['catatan']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
