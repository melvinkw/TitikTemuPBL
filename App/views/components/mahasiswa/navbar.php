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
<nav class="flex items-center justify-between gap-3 border-b border-indigo-200 px-4 py-5 md:px-20 md:py-6">
    <!-- Spacer kiri supaya menu tetap di tengah (hanya di layar besar) -->
    <div class="hidden w-16 md:block"></div>

    <!-- Menu: rata kiri di HP, di tengah di layar besar -->
    <div class="flex items-center gap-3 text-xs font-semibold whitespace-nowrap min-[360px]:gap-4 min-[360px]:text-sm sm:gap-6 md:gap-10">
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

    <form method="post" action="<?= h(base_url('logout.php')) ?>" class="flex">
        <?= csrf_field() ?>

        <button type="submit" class="cursor-pointer text-xs font-semibold whitespace-nowrap min-[360px]:text-sm text-tt-coral hover:text-tt-orange md:w-16 md:text-right">
            Logout
        </button>
    </form>
</nav>