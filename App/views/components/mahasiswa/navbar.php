<?php
/**
 * App/views/components/mahasiswa/navbar.php
 * Navbar untuk semua halaman mahasiswa.
 *
 * Cara pakai di halaman:
 *   $menuAktif = 'dashboard';   // pilihan: dashboard, daftar-barang, riwayat
 *   require_once APP_ROOT . '/App/views/components/mahasiswa/navbar.php';
 */
$daftarMenu = [
    'dashboard'     => ['Dashboard',     'mahasiswa/dashboard.php'],
    'daftar-barang' => ['Daftar Barang', 'mahasiswa/daftar-barang.php'],
    'riwayat'       => ['Riwayat',       'mahasiswa/riwayat.php'],
];

require APP_ROOT . '/App/views/components/navbar.php';
