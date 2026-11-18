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
<nav class="flex min-h-19 items-center justify-end border-b-[0.5px] border-tt-outline px-4 py-3 md:px-11">
    <div class="flex items-center gap-3 text-xs font-semibold whitespace-nowrap min-[360px]:gap-4 min-[360px]:text-sm sm:gap-6 md:gap-10">
        <a href="<?= h(base_url('index.php')) ?>"
            class="<?= $menuAktif === 'beranda' ? 'font-extrabold text-tt-orange' : 'text-tt-navy hover:text-tt-orange' ?>"
            <?= $menuAktif === 'beranda' ? 'aria-current="page"' : '' ?>>
            Beranda
        </a>
        <a href="<?= h(base_url('daftar-barang.php')) ?>"
            class="<?= $menuAktif === 'daftar-barang' ? 'font-extrabold text-tt-orange' : 'text-tt-navy hover:text-tt-orange' ?>"
            <?= $menuAktif === 'daftar-barang' ? 'aria-current="page"' : '' ?>>
            Daftar Barang
        </a>

        <span class="text-tt-outline">|</span>

        <a href="<?= h(base_url('login.php')) ?>"
            class="rounded-lg bg-tt-navy px-5 py-2 text-xs font-semibold text-white hover:bg-tt-orange hover:text-tt-navy">
            Login
        </a>
    </div>
</nav>
