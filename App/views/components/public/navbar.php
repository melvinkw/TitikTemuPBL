<?php

/**
 * App/views/components/public/navbar.php
 * Navbar untuk halaman public (pengunjung yang belum login).
 *
 * Cara pakai di halaman:
 *   $menuAktif = 'beranda';   // pilihan: beranda, daftar-barang
 *   require_once APP_ROOT . '/App/views/components/public/navbar.php';
 */
$menuAktif = $menuAktif ?? '';
?>
<nav class="flex items-center justify-end border-b border-indigo-200 px-4 py-6 md:px-20">
    <div class="flex items-center gap-6 text-sm font-semibold md:gap-10">
        <a href="<?= base_url('index.php') ?>"
            class="<?= $menuAktif === 'beranda' ? 'font-extrabold text-tt-orange' : 'text-tt-navy hover:text-tt-orange' ?>">
            Beranda
        </a>
        <a href="<?= base_url('daftar-barang.php') ?>"
            class="<?= $menuAktif === 'daftar-barang' ? 'font-extrabold text-tt-orange' : 'text-tt-navy hover:text-tt-orange' ?>">
            Daftar Barang
        </a>

        <span class="text-tt-outline">|</span>

        <a href="<?= base_url('login.php') ?>"
            class="rounded-lg bg-tt-navy px-5 py-2 text-xs font-semibold text-white hover:bg-tt-orange hover:text-tt-navy">
            Login
        </a>
    </div>
</nav>
