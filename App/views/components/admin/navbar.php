<?php
/**
 * App/views/components/admin/navbar.php
 * Navbar untuk semua halaman admin (Figma: komponen "Nav" di halaman Admin).
 *
 * Cara pakai di halaman:
 *   $menuAktif = 'dashboard';   // pilihan: dashboard, data-master, akun-staff, kecocokan, rekapitulasi
 *   require_once APP_ROOT . '/App/views/components/admin/navbar.php';
 */
$menuAktif = $menuAktif ?? '';

// Daftar menu admin: kunci => [label, alamat]
$menuAdmin = [
    'dashboard'    => ['Dashboard',    'admin/dashboard.php'],
    'data-master'  => ['Data Master',  'admin/kategori/kategori.php'],
    'akun-staff'   => ['Akun Staff',   'admin/pengguna/akun-staff.php'],
    'kecocokan'    => ['Kecocokan',    'admin/kecocokan/kecocokan.php'],
    'rekapitulasi' => ['Rekapitulasi', 'admin/rekap/rekapitulasi.php'],
];
?>
<nav class="flex flex-wrap items-center justify-between gap-2 border-b-[0.5px] border-tt-outline px-4 py-3 lg:min-h-19 lg:flex-nowrap lg:px-11">
    <!-- Spacer kiri supaya menu tetap di tengah (sama lebar dengan tombol Logout) -->
    <div class="hidden w-18 lg:block"></div>

    <!-- Menu tengah -->
    <div class="order-3 flex w-full flex-wrap items-center justify-center gap-x-5.5 gap-y-1 lg:order-none lg:w-auto lg:flex-1">
        <?php foreach ($menuAdmin as $kunci => $menu): ?>
            <?php if ($menuAktif === $kunci): ?>
                <span class="p-2.5 text-sm font-extrabold whitespace-nowrap text-tt-orange"><?= h($menu[0]) ?></span>
            <?php else: ?>
                <a href="<?= h(base_url($menu[1])) ?>"
                   class="p-2.5 text-sm font-semibold whitespace-nowrap text-tt-navy hover:text-tt-orange">
                    <?= h($menu[0]) ?>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <!-- Logout (dikirim lewat POST + token CSRF, sama seperti halaman petugas) -->
    <form method="post" action="<?= h(base_url('logout.php')) ?>" class="ml-auto w-18 text-right lg:ml-0">
        <?= csrf_field() ?>
        <button type="submit" class="cursor-pointer p-2.5 text-sm font-semibold text-tt-coral hover:text-tt-orange">
            Logout
        </button>
    </form>
</nav>
