<?php
/**
 * Public/admin/kategori/pos-penyerahan.php
 * Data Master — tab Pos Penyerahan (Figma: Data Master-pos penyerahan).
 * Form belum menyimpan dan isi tabel masih data contoh.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';

$currentUser = auth_require_role(['admin']);

$pageTitle = 'Data Master — Pos Penyerahan';
$menuAktif = 'data-master';
$tabAktif = 'pos';
$judulHalaman = 'Data Master';
$deskripsiHalaman = 'Kelola kategori barang, lokasi kejadian, dan pos penyerahan yang dipakai di formulir laporan.';

// Pilihan dropdown — contoh, nanti diambil dari database
$pilihanJenis = ['Pos Keamanan', 'Ruang Admin Prodi'];
$pilihanArea = ['Lobi atau Lift', 'Parkiran', 'Gedung A', 'Gedung B'];

// Data contoh — nanti diganti dari tabel pos_penyerahan
$daftarPos = [
    ['nama' => 'Pos Dalam', 'jenis' => 'Pos Keamanan', 'area' => 'Lobi atau Lift', 'aktif' => true],
    ['nama' => 'Pos Luar',  'jenis' => 'Pos Keamanan', 'area' => 'Parkiran',       'aktif' => false],
];

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/admin/navbar.php';
?>

<main class="flex w-full flex-col gap-6.5 px-4 pt-2.5 pb-10 md:px-20">

    <div class="flex flex-col gap-4">
        <?php require APP_ROOT . '/App/views/components/judul-halaman.php'; ?>
        <?php require APP_ROOT . '/App/views/components/admin/tab-data-master.php'; ?>
    </div>

    <!-- Form tambah pos penyerahan -->
    <form method="post" action="#" class="tt-card flex flex-col gap-4 p-4.5">
        <?= csrf_field() ?>
        <h2 class="py-4 font-display text-[28px] font-bold">Tambah Pos Penyerahan</h2>

        <div class="flex flex-col gap-4 md:flex-row">
            <label class="flex-1">
                <span class="tt-label">Nama Pos Penyerahan*</span>
                <input type="text" name="nama_pos" required class="tt-input h-12">
            </label>

            <label class="flex-1">
                <span class="tt-label">Jenis*</span>
                <span class="relative block">
                    <select name="jenis" required class="tt-input h-12 appearance-none pr-11">
                        <option value=""></option>
                        <?php foreach ($pilihanJenis as $jenis): ?>
                            <option><?= h($jenis) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <img src="<?= h(base_url('assets/images/icon-chevron-down.svg')) ?>" alt=""
                         class="pointer-events-none absolute top-3 right-3 h-6 w-6">
                </span>
            </label>
        </div>

        <label>
            <span class="tt-label">Area Lokasi*</span>
            <span class="relative block">
                <select name="area" required class="tt-input h-12 appearance-none pr-11">
                    <option value=""></option>
                    <?php foreach ($pilihanArea as $area): ?>
                        <option><?= h($area) ?></option>
                    <?php endforeach; ?>
                </select>
                <img src="<?= h(base_url('assets/images/icon-chevron-down.svg')) ?>" alt=""
                     class="pointer-events-none absolute top-3 right-3 h-6 w-6">
            </span>
        </label>

        <div class="p-2.5">
            <button type="submit" class="tt-btn">Simpan</button>
        </div>
    </form>

    <!-- Daftar pos penyerahan -->
    <section class="mt-2.5 flex flex-col gap-2.5">
        <h2 class="p-4 font-display text-xl font-semibold">Pos Penyerahan</h2>

        <div class="tt-table-wrap">
            <table class="tt-table min-w-225">
                <thead>
                    <tr>
                        <th class="w-1/5">Nama</th>
                        <th class="w-1/5">Jenis</th>
                        <th class="w-1/5">Area</th>
                        <th class="w-1/5">Status</th>
                        <th class="w-1/5">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftarPos as $pos): ?>
                        <tr>
                            <td><?= h($pos['nama']) ?></td>
                            <td><?= h($pos['jenis']) ?></td>
                            <td><?= h($pos['area']) ?></td>
                            <td>
                                <?php $aktif = $pos['aktif']; require APP_ROOT . '/App/views/components/admin/badge-aktif.php'; ?>
                            </td>
                            <td>
                                <!-- Tombol aksi belum berfungsi -->
                                <div class="flex gap-2.5">
                                    <button type="button" class="tt-btn">Edit</button>
                                    <button type="button" class="tt-btn-outline"><?= $pos['aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?></button>
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
