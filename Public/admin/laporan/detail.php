<?php
/**
 * Public/admin/laporan/detail.php
 * Halaman Detail Laporan untuk admin (Figma: user-Admin / Detail Laporan).
 * Berisi info barang, foto, form pemeriksaan (Tindakan), status laporan, dan riwayat status.
 * Form pemeriksaan belum tersambung ke backend dan data masih contoh.
 *
 * Teks yang di Figma salah salin sudah dibetulkan:
 *   judul halaman "Daftar Barang" → "Detail Laporan".
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';

$currentUser = auth_require_role(['admin']);

$pageTitle = 'Detail Laporan';
$menuAktif = '';
$judulHalaman = 'Detail Laporan';
$deskripsiHalaman = 'Periksa data dan foto barang, lalu tetapkan apakah laporan ini disetujui atau ditolak.';

// Data contoh — nanti diganti dari database
$laporan = [
    'jenis'    => 'temuan',   // hilang atau temuan
    'kategori' => 'Elektronik',
    'nama'     => 'TWS Huawei',
    'lokasi'   => 'LPR 8',
    'waktu'    => '24 Oktober 2026 12:45',
    'pelapor'  => 'John Doe',
    'status'   => 'Menunggu Pemeriksaan',
    'dibuat'   => '29-10-2026 13:57',
];

// Pilihan keputusan pemeriksaan (sesuai status di database):
// laporan kehilangan → aktif / ditolak, laporan temuan → tersedia / ditolak
$pilihanKeputusan = $laporan['jenis'] === 'hilang'
    ? ['aktif' => 'Aktif', 'ditolak' => 'Ditolak']
    : ['tersedia' => 'Barang Tersedia', 'ditolak' => 'Ditolak'];

$riwayatStatus = [
    ['waktu' => '27-10-2026 13:01', 'perubahan' => 'Status Awal', 'status' => 'Menunggu Pemeriksaan', 'oleh' => 'Admin 1', 'catatan' => 'Status ditetapkan melalui pemeriksaan petugas.'],
];

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/admin/navbar.php';
?>

<main class="flex w-full flex-col gap-6.5 px-4 pt-2.5 pb-10 md:px-20">

    <?php require APP_ROOT . '/App/views/components/judul-halaman.php'; ?>

    <div class="grid grid-cols-1 items-start gap-9 lg:grid-cols-2">

        <!-- Kiri: info barang, foto, dan tindakan -->
        <div class="tt-card flex flex-col gap-4 p-4.5">
            <div class="flex flex-col gap-2 p-4">
                <span class="tt-badge bg-[#EBE5F2] text-[#8A38F5]"><?= h($laporan['kategori']) ?></span>
                <h2 class="font-display text-[28px] font-bold"><?= h($laporan['nama']) ?></h2>
            </div>
            <p class="px-2.75 text-sm"><?= h($laporan['lokasi']) ?></p>
            <p class="px-2.75 text-sm"><?= h($laporan['waktu']) ?></p>
            <p class="px-2.75 text-sm"><?= h($laporan['pelapor']) ?></p>

            <!-- Foto barang -->
            <div class="flex flex-col gap-2 p-4">
                <h2 class="font-display text-[28px] font-bold">Foto Barang</h2>
                <div class="grid h-60 grid-cols-2 grid-rows-2 gap-2">
                    <img src="<?= h(base_url('assets/images/foto-barang.png')) ?>" alt="Foto 1" class="row-span-2 h-full w-full rounded-2xl object-cover">
                    <img src="<?= h(base_url('assets/images/foto-barang.png')) ?>" alt="Foto 2" class="h-full w-full rounded-2xl object-cover">
                    <img src="<?= h(base_url('assets/images/foto-barang.png')) ?>" alt="Foto 3" class="h-full w-full rounded-2xl object-cover">
                </div>
            </div>

            <!-- Tindakan: form pemeriksaan -->
            <div class="flex flex-col gap-4">
                <h2 class="p-4 font-display text-[28px] font-bold">Tindakan</h2>

                <!-- action="#" karena backend pemeriksaan belum dibuat -->
                <form method="post" action="#" class="tt-card flex flex-col gap-4 p-4.5">
                    <?= csrf_field() ?>

                    <div class="flex flex-col gap-2 p-4">
                        <h3 class="font-display text-xl font-semibold">Pemeriksaan Petugas / Admin</h3>
                        <p class="text-xs">Pilih keputusan dan tambahkan catatan bila perlu. Keputusan akan tercatat di riwayat status laporan.</p>
                    </div>

                    <label>
                        <span class="tt-label">Keputusan*</span>
                        <span class="relative block">
                            <select name="keputusan" required class="tt-input h-12 appearance-none pr-11">
                                <option value="" selected disabled></option>
                                <?php foreach ($pilihanKeputusan as $nilai => $label): ?>
                                    <option value="<?= h($nilai) ?>"><?= h($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <img src="<?= h(base_url('assets/images/icon-chevron-down.svg')) ?>" alt=""
                                 class="pointer-events-none absolute top-3 right-3 h-6 w-6">
                        </span>
                    </label>

                    <label>
                        <span class="tt-label">Catatan (opsional)</span>
                        <textarea name="catatan" rows="3" class="tt-input h-22 resize-none"></textarea>
                    </label>

                    <div class="p-2.5">
                        <button type="submit" class="tt-btn w-full">Simpan Keputusan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Kanan: status laporan -->
        <div class="tt-card p-4.5">
            <div class="flex flex-col gap-2 p-4">
                <h2 class="font-display text-[28px] font-bold">Status Laporan</h2>
                <span class="tt-badge bg-[#FFF7DD] text-[#7A5605]"><?= h($laporan['status']) ?></span>
                <p class="text-xs">Laporan dibuat pada <?= h($laporan['dibuat']) ?>.</p>
            </div>
        </div>
    </div>

    <!-- Riwayat status -->
    <section class="mt-2.5 flex flex-col gap-2.5">
        <h2 class="p-4 font-display text-xl font-semibold">Riwayat Status Laporan</h2>

        <div class="tt-table-wrap">
            <table class="tt-table min-w-200">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Perubahan</th>
                        <th>Diubah Oleh</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($riwayatStatus as $riwayat): ?>
                        <tr>
                            <td class="whitespace-nowrap"><?= h($riwayat['waktu']) ?></td>
                            <td>
                                <span class="inline-flex flex-wrap items-center gap-2">
                                    <?= h($riwayat['perubahan']) ?> &rarr;
                                    <span class="tt-badge bg-[#FFF7DD] text-[#7A5605]"><?= h($riwayat['status']) ?></span>
                                </span>
                            </td>
                            <td><?= h($riwayat['oleh']) ?></td>
                            <td><?= h($riwayat['catatan']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
