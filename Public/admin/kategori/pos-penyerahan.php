<?php
/**
 * Public/admin/kategori/pos-penyerahan.php
 * Data Master — tab Pos Penyerahan (Figma: Data Master-pos penyerahan).
 */
require_once __DIR__ . '/../../../App/config/bootstrap.php';

$pageTitle = 'Data Master — Pos Penyerahan';
$menuAktif = 'data-master';
$tabAktif = 'pos';

// Tab Data Master: kunci => [label, alamat]
$tabDataMaster = [
    'kategori' => ['Kategori',       'admin/kategori/kategori.php'],
    'lokasi'   => ['Lokasi',         'admin/kategori/lokasi.php'],
    'pos'      => ['Pos Penyerahan', 'admin/kategori/pos-penyerahan.php'],
];

// Pilihan dropdown — contoh, nanti diambil dari database
$pilihanJenis = ['Pos Keamanan', 'Ruang Admin Prodi'];
$pilihanArea = ['Lobi atau Lift', 'Parkiran', 'Gedung A', 'Gedung B'];

// Data dummy — nanti diganti dari tabel pos_penyerahan
$daftarPos = [
    ['nama' => 'Pos Dalam', 'jenis' => 'Pos Keamanan', 'area' => 'Lobi atau Lift', 'aktif' => true],
    ['nama' => 'Pos Luar',  'jenis' => 'Pos Keamanan', 'area' => 'Parkiran',       'aktif' => false],
];

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/admin/navbar.php';
?>

<main class="flex w-full flex-col gap-6.5 px-4 pt-2.5 pb-10 md:px-20">

    <div class="flex flex-col gap-4">
        <!-- Judul halaman -->
        <div class="flex flex-col gap-2 p-4">
            <p class="text-sm font-semibold">RUANG KERJA ADMINISTRATOR</p>
            <h1 class="font-display text-[32px] font-extrabold">Data Master</h1>
            <p class="text-xs">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
        </div>

        <!-- Tab Data Master -->
        <div class="flex flex-wrap items-center gap-3.25">
            <?php foreach ($tabDataMaster as $kunci => $tab): ?>
                <?php if ($tabAktif === $kunci): ?>
                    <span class="rounded-full border border-tt-orange bg-tt-orange px-4.5 py-2.5 text-xs font-extrabold"><?= h($tab[0]) ?></span>
                <?php else: ?>
                    <a href="<?= h(base_url($tab[1])) ?>" class="rounded-full border border-tt-outline bg-white px-4.5 py-2.5 text-xs font-semibold"><?= h($tab[0]) ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Form tambah pos penyerahan -->
    <form method="post" action="#" class="flex flex-col gap-4 rounded-2xl border border-tt-outline bg-white p-4.5">
        <h2 class="py-4 font-display text-[28px] font-bold">Tambah Pos Penyerahan</h2>

        <div class="flex flex-col gap-4 md:flex-row">
            <label class="flex flex-1 flex-col gap-2">
                <span class="font-display text-base font-semibold">Nama Pos Penyerahan*</span>
                <input type="text" name="nama_pos" required
                       class="h-12 w-full rounded-[10px] border border-tt-outline p-3 text-sm outline-none focus:border-tt-navy">
            </label>

            <label class="flex flex-1 flex-col gap-2">
                <span class="font-display text-base font-semibold">Jenis*</span>
                <span class="relative">
                    <select name="jenis" required
                            class="h-12 w-full appearance-none rounded-[10px] border border-tt-outline bg-white p-3 pr-11 text-sm outline-none focus:border-tt-navy">
                        <option value=""></option>
                        <?php foreach ($pilihanJenis as $jenis): ?>
                            <option><?= h($jenis) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <img src="<?= h(base_url('assets/images/icon-chevron-down.svg')) ?>" alt="" width="24" height="24"
                         class="pointer-events-none absolute top-3 right-3">
                </span>
            </label>
        </div>

        <label class="flex flex-col gap-2">
            <span class="font-display text-base font-semibold">Area Lokasi*</span>
            <span class="relative">
                <select name="area" required
                        class="h-12 w-full appearance-none rounded-[10px] border border-tt-outline bg-white p-3 pr-11 text-sm outline-none focus:border-tt-navy">
                    <option value=""></option>
                    <?php foreach ($pilihanArea as $area): ?>
                        <option><?= h($area) ?></option>
                    <?php endforeach; ?>
                </select>
                <img src="<?= h(base_url('assets/images/icon-chevron-down.svg')) ?>" alt="" width="24" height="24"
                     class="pointer-events-none absolute top-3 right-3">
            </span>
        </label>

        <div class="p-2.5">
            <button type="submit" class="h-10.75 cursor-pointer rounded-[10px] bg-tt-navy px-2.75 text-sm font-semibold text-white">Simpan</button>
        </div>
    </form>

    <!-- Daftar pos penyerahan -->
    <section class="mt-2.5 flex flex-col gap-2.5">
        <h2 class="p-4 font-display text-xl font-semibold">Pos Penyerahan</h2>

        <div class="overflow-x-auto rounded-[15px] border border-tt-outline bg-white">
            <table class="w-full min-w-225 text-left text-sm">
                <thead class="bg-tt-tablehead font-display text-xl">
                    <tr>
                        <th class="w-1/5 px-7.5 py-8 font-semibold whitespace-nowrap">Nama</th>
                        <th class="w-1/5 px-7.5 py-8 font-semibold whitespace-nowrap">Jenis</th>
                        <th class="w-1/5 px-7.5 py-8 font-semibold whitespace-nowrap">Area</th>
                        <th class="w-1/5 px-7.5 py-8 font-semibold whitespace-nowrap">Status</th>
                        <th class="w-1/5 px-7.5 py-8 font-semibold whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftarPos as $pos): ?>
                        <tr class="border-b border-tt-outline last:border-b-0">
                            <td class="px-7.5 py-4"><?= h($pos['nama']) ?></td>
                            <td class="px-7.5 py-4"><?= h($pos['jenis']) ?></td>
                            <td class="px-7.5 py-4"><?= h($pos['area']) ?></td>
                            <td class="px-7.5 py-4">
                                <?php if ($pos['aktif']): ?>
                                    <span class="inline-flex rounded-full bg-[#e6f7ed] px-1.5 py-2.5 text-xs font-semibold text-[#17633f]">Aktif</span>
                                <?php else: ?>
                                    <span class="inline-flex rounded-full bg-[#f1f1f1] px-1.5 py-2.5 text-xs font-semibold text-[#757575]">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-7.5 py-4">
                                <!-- Tombol aksi belum berfungsi -->
                                <div class="flex gap-2.5">
                                    <button type="button" class="h-10.75 cursor-pointer rounded-[10px] bg-tt-navy px-2.75 font-semibold text-white">Edit</button>
                                    <button type="button" class="h-10.75 cursor-pointer rounded-[10px] border border-tt-navy px-2.75 font-semibold">
                                        <?= $pos['aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
