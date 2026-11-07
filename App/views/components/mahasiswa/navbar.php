<?php
/**
 * App/views/components/mahasiswa/navbar.php
 * Navbar untuk semua halaman mahasiswa.
 *
 * Cara pakai di halaman:
 *   $menuAktif = 'dashboard';   // pilihan: dashboard, daftar-barang, riwayat
 *   require_once APP_ROOT . '/App/views/components/mahasiswa/navbar.php';
 */
$menuAktif = $menuAktif ?? '';
?>
<nav class="flex items-center justify-between border-b border-indigo-200 px-4 py-6 md:px-20">
    <div class="w-16"></div>

    <!-- Menu tengah -->
    <div class="flex flex-wrap justify-center gap-6 text-sm font-semibold md:gap-10">
        <a href="<?= base_url('mahasiswa/dashboard.php') ?>"
           class="<?= $menuAktif === 'dashboard' ? 'text-tt-orange' : 'text-tt-navy hover:text-tt-orange' ?>">
            Dashboard
        </a>
        <a href="<?= base_url('mahasiswa/daftar-barang.php') ?>"
           class="<?= $menuAktif === 'daftar-barang' ? 'text-tt-orange' : 'text-tt-navy hover:text-tt-orange' ?>">
            Daftar Barang
        </a>
        <a href="<?= base_url('mahasiswa/riwayat.php') ?>"
           class="<?= $menuAktif === 'riwayat' ? 'text-tt-orange' : 'text-tt-navy hover:text-tt-orange' ?>">
            Riwayat
        </a>
    </div>

    <!-- Logout -->
    <!-- Logout belum ada prosesnya (fitur login belum dibuat) -->
    <a href="#" class="w-16 text-right text-sm font-semibold text-tt-coral hover:text-tt-orange">
        Logout
    </a>
</nav>
