<?php
/**
 * Public/admin/kategori/kategori.php
 * Data Master — tab Kategori (Figma: Data Master-kategori).
 * Form belum menyimpan dan isi tabel masih data contoh.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';

$currentUser = auth_require_role(['admin']);

$pageTitle = 'Data Master — Kategori';
$menuAktif = 'data-master';
$tabAktif = 'kategori';
$judulHalaman = 'Data Master';
$deskripsiHalaman = 'Kelola kategori barang, lokasi kejadian, dan pos penyerahan yang dipakai di formulir laporan.';

// Data contoh — nanti diganti dari tabel kategori
$daftarKategori = [
    ['nama' => 'Alat Tulis', 'deskripsi' => 'Pulpen, buku, dan perlengkapan tulis', 'aktif' => true],
    ['nama' => 'Dokumen',    'deskripsi' => 'KTM, kartu, surat, dan dokumen',       'aktif' => false],
];

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/admin/navbar.php';
?>

<main class="flex w-full flex-col gap-6.5 px-4 pt-2.5 pb-10 md:px-20">

    <div class="flex flex-col gap-4">
        <?php require APP_ROOT . '/App/views/components/judul-halaman.php'; ?>
        <?php require APP_ROOT . '/App/views/components/admin/tab-data-master.php'; ?>
    </div>

    <!-- Form tambah kategori -->
    <form method="post" action="#" class="tt-card flex flex-col gap-4 p-4.5">
        <?= csrf_field() ?>
        <h2 class="py-4 font-display text-[28px] font-bold">Tambah Kategori Barang</h2>

        <label>
            <span class="tt-label">Nama Kategori*</span>
            <input type="text" name="nama_kategori" required class="tt-input h-12">
        </label>

        <label>
            <span class="tt-label">Deskripsi (opsional)</span>
            <textarea name="deskripsi" rows="3" class="tt-input h-22.25 resize-none"></textarea>
        </label>

        <div class="p-2.5">
            <button type="submit" class="tt-btn">Simpan</button>
        </div>
    </form>

    <!-- Daftar kategori -->
    <section class="mt-2.5 flex flex-col gap-2.5">
        <h2 class="p-4 font-display text-xl font-semibold">Kategori Barang</h2>

        <div class="tt-table-wrap">
            <table class="tt-table min-w-200">
                <thead>
                    <tr>
                        <th class="w-1/4">Nama</th>
                        <th class="w-1/4">Deskripsi</th>
                        <th class="w-1/4">Status</th>
                        <th class="w-1/4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftarKategori as $kategori): ?>
                        <tr>
                            <td><?= h($kategori['nama']) ?></td>
                            <td><?= h($kategori['deskripsi']) ?></td>
                            <td>
                                <?php $aktif = $kategori['aktif']; require APP_ROOT . '/App/views/components/admin/badge-aktif.php'; ?>
                            </td>
                            <td>
                                <!-- Tombol aksi belum berfungsi -->
                                <div class="flex gap-2.5">
                                    <button type="button" class="tt-btn">Edit</button>
                                    <button type="button" class="tt-btn-outline"><?= $kategori['aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?></button>
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
