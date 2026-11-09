<?php
/**
 * Public/admin/pengguna/akun-staff.php
 * Halaman Akun Staff — kelola akun admin & petugas (Figma: Admin-Akun Staff).
 */
require_once __DIR__ . '/../../../App/config/bootstrap.php';

$pageTitle = 'Akun Staff';
$menuAktif = 'akun-staff';

// Pilihan role — sesuai role di database (admin & petugas)
$pilihanRole = [
    'admin'   => 'Administrator',
    'petugas' => 'Petugas Keamanan',
];

// Data dummy — nanti diganti dari tabel pengguna
$daftarAkun = [
    ['nama' => 'Jane Doe', 'email' => 'janedoe@gmail.com', 'username' => 'Jane Doe', 'role' => 'Administrator',    'aktif' => true,  'login' => '29-10-2026 15:49'],
    ['nama' => 'John Doe', 'email' => 'johndoe@gmail.com', 'username' => 'John Doe', 'role' => 'Petugas Keamanan', 'aktif' => false, 'login' => '29-10-2026 15:49'],
];

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/admin/navbar.php';
?>

<main class="flex w-full flex-col gap-6.5 px-4 pt-2.5 pb-10 md:px-20">

    <!-- Judul halaman -->
    <div class="flex flex-col gap-2 p-4">
        <p class="text-sm font-semibold">RUANG KERJA ADMINISTRATOR</p>
        <h1 class="font-display text-[32px] font-extrabold">Akun Admin &amp; Petugas</h1>
        <p class="text-xs">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
    </div>

    <!-- Form tambah akun -->
    <form method="post" action="#" class="flex flex-col gap-4 rounded-2xl border border-tt-outline bg-white p-4.5">
        <h2 class="py-4 font-display text-[28px] font-bold">Tambah Akun Staff</h2>

        <div class="flex flex-col gap-4 md:flex-row">
            <label class="flex flex-1 flex-col gap-2">
                <span class="font-display text-base font-semibold">Role*</span>
                <select name="role" required
                        class="h-12 w-full appearance-none rounded-[10px] border border-tt-outline bg-white p-3 text-sm outline-none focus:border-tt-navy">
                    <option value=""></option>
                    <?php foreach ($pilihanRole as $nilai => $label): ?>
                        <option value="<?= h($nilai) ?>"><?= h($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="flex flex-1 flex-col gap-2">
                <span class="font-display text-base font-semibold">Username*</span>
                <input type="text" name="username" required
                       class="h-12 w-full rounded-[10px] border border-tt-outline p-3 text-sm outline-none focus:border-tt-navy">
            </label>
        </div>

        <div class="flex flex-col gap-4 md:flex-row">
            <label class="flex flex-1 flex-col gap-2">
                <span class="font-display text-base font-semibold">Nama Lengkap*</span>
                <input type="text" name="nama" required
                       class="h-12 w-full rounded-[10px] border border-tt-outline p-3 text-sm outline-none focus:border-tt-navy">
            </label>

            <label class="flex flex-1 flex-col gap-2">
                <span class="font-display text-base font-semibold">Email (optional)</span>
                <input type="email" name="email"
                       class="h-12 w-full rounded-[10px] border border-tt-outline p-3 text-sm outline-none focus:border-tt-navy">
            </label>
        </div>

        <label class="flex flex-col gap-2">
            <span class="font-display text-base font-semibold">Password*</span>
            <input type="password" name="password" required autocomplete="new-password"
                   class="h-12 w-full rounded-[10px] border border-tt-outline p-3 text-sm outline-none focus:border-tt-navy">
        </label>

        <div class="p-2.5">
            <button type="submit" class="h-10.75 cursor-pointer rounded-[10px] bg-tt-navy px-2.75 text-sm font-semibold text-white">Simpan</button>
        </div>
    </form>

    <!-- Daftar akun -->
    <section class="mt-2.5 flex flex-col gap-2.5">
        <h2 class="p-4 font-display text-xl font-semibold">Daftar Akun Staff</h2>

        <div class="overflow-x-auto rounded-[15px] border border-tt-outline bg-white">
            <table class="w-full min-w-250 text-left text-sm">
                <thead class="bg-tt-tablehead font-display text-xl">
                    <tr>
                        <th class="w-1/6 px-7.5 py-8 font-semibold whitespace-nowrap">Nama / Email</th>
                        <th class="w-1/6 px-7.5 py-8 font-semibold whitespace-nowrap">Username</th>
                        <th class="w-1/6 px-7.5 py-8 font-semibold whitespace-nowrap">Role</th>
                        <th class="w-1/6 px-7.5 py-8 font-semibold whitespace-nowrap">Status</th>
                        <th class="w-1/6 px-7.5 py-8 font-semibold whitespace-nowrap">Login Terakhir</th>
                        <th class="w-1/6 px-7.5 py-8 font-semibold whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftarAkun as $akun): ?>
                        <tr class="border-b border-tt-outline last:border-b-0">
                            <td class="px-7.5 py-4">
                                <p class="font-semibold"><?= h($akun['nama']) ?></p>
                                <p><?= h($akun['email']) ?></p>
                            </td>
                            <td class="px-7.5 py-4"><?= h($akun['username']) ?></td>
                            <td class="px-7.5 py-4"><?= h($akun['role']) ?></td>
                            <td class="px-7.5 py-4">
                                <?php if ($akun['aktif']): ?>
                                    <span class="inline-flex rounded-full bg-[#e6f7ed] px-1.5 py-2.5 text-xs font-semibold text-[#17633f]">Aktif</span>
                                <?php else: ?>
                                    <span class="inline-flex rounded-full bg-[#f1f1f1] px-1.5 py-2.5 text-xs font-semibold text-[#757575]">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-7.5 py-4 whitespace-nowrap"><?= h($akun['login']) ?></td>
                            <td class="px-7.5 py-4">
                                <!-- Tombol aksi belum berfungsi -->
                                <div class="flex gap-2.5">
                                    <button type="button" class="h-10.75 cursor-pointer rounded-[10px] bg-tt-navy px-2.75 font-semibold text-white">Edit</button>
                                    <button type="button" class="h-10.75 cursor-pointer rounded-[10px] border border-tt-navy px-2.75 font-semibold">
                                        <?= $akun['aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?>
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
