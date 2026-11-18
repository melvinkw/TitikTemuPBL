<?php
/**
 * Public/admin/pengguna/akun-staff.php
 * Halaman Akun Staff — kelola akun admin dan petugas (Figma: Admin-Akun Staff).
 * Form belum menyimpan dan isi tabel masih data contoh.
 *
 * Teks yang di Figma salah salin sudah dibetulkan:
 *   judul form "Tambah Kategori Barang" → "Tambah Akun Staff",
 *   judul tabel "Kategori Barang" → "Daftar Akun Staff",
 *   kolom Password dari kotak besar → input password biasa.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';

$currentUser = auth_require_role(['admin']);

$pageTitle = 'Akun Staff';
$menuAktif = 'akun-staff';
$judulHalaman = 'Akun Admin & Petugas';
$deskripsiHalaman = 'Tambah dan kelola akun administrator dan petugas keamanan yang mengelola TitikTemu.';

// Pilihan role — sesuai kolom role di tabel pengguna
$pilihanRole = [
    'admin'   => 'Administrator',
    'petugas' => 'Petugas Keamanan',
];

// Data contoh — nanti diganti dari tabel pengguna
$daftarAkun = [
    ['nama' => 'Jane Doe', 'email' => 'janedoe@gmail.com', 'username' => 'Jane Doe', 'role' => 'Administrator',    'aktif' => true,  'login' => '29-10-2026 15:49'],
    ['nama' => 'John Doe', 'email' => 'johndoe@gmail.com', 'username' => 'John Doe', 'role' => 'Petugas Keamanan', 'aktif' => false, 'login' => '29-10-2026 15:49'],
];

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/admin/navbar.php';
?>

<main class="flex w-full flex-col gap-6.5 px-4 pt-2.5 pb-10 md:px-20">

    <?php require APP_ROOT . '/App/views/components/judul-halaman.php'; ?>

    <!-- Form tambah akun -->
    <form method="post" action="#" class="tt-card flex flex-col gap-4 p-4.5">
        <?= csrf_field() ?>
        <h2 class="py-4 font-display text-[28px] font-bold">Tambah Akun Staff</h2>

        <div class="flex flex-col gap-4 md:flex-row">
            <label class="flex-1">
                <span class="tt-label">Role*</span>
                <span class="relative block">
                    <select name="role" required class="tt-input h-12 appearance-none pr-11">
                        <option value=""></option>
                        <?php foreach ($pilihanRole as $nilai => $label): ?>
                            <option value="<?= h($nilai) ?>"><?= h($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <img src="<?= h(base_url('assets/images/icon-chevron-down.svg')) ?>" alt=""
                         class="pointer-events-none absolute top-3 right-3 h-6 w-6">
                </span>
            </label>

            <label class="flex-1">
                <span class="tt-label">Username*</span>
                <input type="text" name="username" required autocomplete="off" class="tt-input h-12">
            </label>
        </div>

        <div class="flex flex-col gap-4 md:flex-row">
            <label class="flex-1">
                <span class="tt-label">Nama Lengkap*</span>
                <input type="text" name="nama" required class="tt-input h-12">
            </label>

            <label class="flex-1">
                <span class="tt-label">Email (opsional)</span>
                <input type="email" name="email" class="tt-input h-12">
            </label>
        </div>

        <label>
            <span class="tt-label">Password*</span>
            <input type="password" name="password" required autocomplete="new-password" class="tt-input h-12">
        </label>

        <div class="p-2.5">
            <button type="submit" class="tt-btn">Simpan</button>
        </div>
    </form>

    <!-- Daftar akun -->
    <section class="mt-2.5 flex flex-col gap-2.5">
        <h2 class="p-4 font-display text-xl font-semibold">Daftar Akun Staff</h2>

        <div class="tt-table-wrap">
            <table class="tt-table min-w-250">
                <thead>
                    <tr>
                        <th class="w-1/6">Nama / Email</th>
                        <th class="w-1/6">Username</th>
                        <th class="w-1/6">Role</th>
                        <th class="w-1/6">Status</th>
                        <th class="w-1/6">Login Terakhir</th>
                        <th class="w-1/6">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftarAkun as $akun): ?>
                        <tr>
                            <td>
                                <p class="font-semibold"><?= h($akun['nama']) ?></p>
                                <p><?= h($akun['email']) ?></p>
                            </td>
                            <td><?= h($akun['username']) ?></td>
                            <td><?= h($akun['role']) ?></td>
                            <td>
                                <?php $aktif = $akun['aktif']; require APP_ROOT . '/App/views/components/admin/badge-aktif.php'; ?>
                            </td>
                            <td class="whitespace-nowrap"><?= h($akun['login']) ?></td>
                            <td>
                                <!-- Tombol aksi belum berfungsi -->
                                <div class="flex gap-2.5">
                                    <button type="button" class="tt-btn">Edit</button>
                                    <button type="button" class="tt-btn-outline"><?= $akun['aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?></button>
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
