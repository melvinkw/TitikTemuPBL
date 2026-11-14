<?php
/**
 * Public/admin/laporan/detail.php
 * Halaman Detail Laporan untuk admin (Figma: user-Admin / Detail Laporan).
 * Berisi info barang, foto, form pemeriksaan (Tindakan), status laporan, dan riwayat status.
 * Masih tampilan statis — form pemeriksaan belum tersambung ke backend dan data masih contoh.
 *
 * Teks yang di Figma salah salin sudah dibetulkan:
 *   judul halaman "Daftar Barang" → "Detail Laporan".
 */
require_once __DIR__ . '/../../../App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';

$currentUser = auth_require_role(['admin']);

$pageTitle = 'Detail Laporan';
$menuAktif = '';

// Data dummy — nanti diganti dari database
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

    <!-- Judul halaman -->
    <div class="flex flex-col gap-2 p-4">
        <p class="text-sm font-semibold">RUANG KERJA ADMINISTRATOR</p>
        <h1 class="font-display text-[32px] font-extrabold">Detail Laporan</h1>
        <p class="text-xs">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
    </div>

    <div class="grid grid-cols-1 items-start gap-9 lg:grid-cols-2">

        <!-- Kiri: info barang, foto, dan tindakan -->
        <div class="flex flex-col gap-4 rounded-2xl border border-tt-outline bg-white p-4.5">
            <div class="flex flex-col gap-2 p-4">
                <span class="w-fit rounded-full bg-[#EBE5F2] px-1.5 py-2.5 text-xs font-semibold text-[#8A38F5]"><?= h($laporan['kategori']) ?></span>
                <h2 class="font-display text-[28px] font-bold"><?= h($laporan['nama']) ?></h2>
            </div>
            <p class="px-2.75 text-sm"><?= h($laporan['lokasi']) ?></p>
            <p class="px-2.75 text-sm"><?= h($laporan['waktu']) ?></p>
            <p class="px-2.75 text-sm"><?= h($laporan['pelapor']) ?></p>

            <!-- Foto barang -->
            <div class="flex flex-col gap-2 p-4">
                <h2 class="font-display text-[28px] font-bold">Foto Barang</h2>
                <div class="grid h-60 grid-cols-2 grid-rows-2 gap-2">
                    <img src="<?= base_url('assets/images/foto-barang.png') ?>" alt="Foto 1" class="row-span-2 h-full w-full rounded-2xl object-cover">
                    <img src="<?= base_url('assets/images/foto-barang.png') ?>" alt="Foto 2" class="h-full w-full rounded-2xl object-cover">
                    <img src="<?= base_url('assets/images/foto-barang.png') ?>" alt="Foto 3" class="h-full w-full rounded-2xl object-cover">
                </div>
            </div>

            <!-- Tindakan: form pemeriksaan -->
            <div class="flex flex-col gap-4">
                <h2 class="p-4 font-display text-[28px] font-bold">Tindakan</h2>

                <!-- action="#" karena backend pemeriksaan belum dibuat -->
                <form method="post" action="#" class="flex flex-col gap-4 rounded-2xl border border-tt-outline bg-white p-4.5">
                    <?= csrf_field() ?>

                    <div class="flex flex-col gap-2 p-4">
                        <h3 class="font-display text-xl font-semibold">Pemeriksaan Petugas / Admin</h3>
                        <p class="text-xs">Lorem ipsum dolor sit amet, consectetur adipiscing elit</p>
                    </div>

                    <label class="flex flex-col gap-2">
                        <span class="font-display text-base font-semibold">Keputusan*</span>
                        <span class="relative">
                            <select name="keputusan" required
                                    class="h-12 w-full appearance-none rounded-[10px] border border-tt-outline bg-white p-3 pr-11 text-sm outline-none focus:border-tt-navy">
                                <option value="" selected disabled></option>
                                <?php foreach ($pilihanKeputusan as $nilai => $label): ?>
                                    <option value="<?= h($nilai) ?>"><?= h($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <img src="<?= base_url('assets/images/icon-chevron-down.svg') ?>" alt="" class="pointer-events-none absolute right-3 top-3 h-6 w-6">
                        </span>
                    </label>

                    <label class="flex flex-col gap-2">
                        <span class="font-display text-base font-semibold">Catatan (optional)</span>
                        <textarea name="catatan" rows="3"
                                  class="h-22 w-full resize-none rounded-[10px] border border-tt-outline p-3 text-sm outline-none focus:border-tt-navy"></textarea>
                    </label>

                    <div class="p-2.5">
                        <button type="submit" class="h-10.75 w-full cursor-pointer rounded-[10px] bg-tt-navy px-2.75 text-sm font-semibold text-white hover:bg-tt-orange hover:text-tt-navy">
                            Simpan Keputusan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Kanan: status laporan -->
        <div class="rounded-2xl border border-tt-outline bg-white p-4.5">
            <div class="flex flex-col gap-2 p-4">
                <h2 class="font-display text-[28px] font-bold">Status Laporan</h2>
                <span class="w-fit rounded-full bg-[#FFF7DD] px-1.5 py-2.5 text-xs font-semibold text-[#7A5605]"><?= h($laporan['status']) ?></span>
                <p class="text-xs">Laporan dibuat pada <?= h($laporan['dibuat']) ?>.</p>
            </div>
        </div>
    </div>

    <!-- Riwayat status -->
    <div class="flex flex-col gap-4">
        <h2 class="p-4 font-display text-xl font-semibold">Riwayat Status Laporan</h2>

        <div class="overflow-x-auto rounded-[15px] border border-tt-outline bg-white">
            <table class="w-full text-left text-sm">
                <thead class="bg-tt-tablehead font-display text-xl">
                    <tr>
                        <th class="px-7.5 py-8 font-semibold">Waktu</th>
                        <th class="px-7.5 py-8 font-semibold">Perubahan</th>
                        <th class="px-7.5 py-8 font-semibold">Diubah Oleh</th>
                        <th class="px-7.5 py-8 font-semibold">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($riwayatStatus as $riwayat): ?>
                        <tr class="border-b border-tt-outline last:border-b-0">
                            <td class="px-4 py-4"><?= h($riwayat['waktu']) ?></td>
                            <td class="px-4 py-4">
                                <?= h($riwayat['perubahan']) ?> &rarr;
                                <span class="ml-2 rounded-full bg-[#FFF7DD] px-1.5 py-2.5 text-xs font-semibold whitespace-nowrap text-[#7A5605]"><?= h($riwayat['status']) ?></span>
                            </td>
                            <td class="px-4 py-4"><?= h($riwayat['oleh']) ?></td>
                            <td class="px-4 py-4"><?= h($riwayat['catatan']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
