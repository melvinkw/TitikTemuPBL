<?php
/**
 * Public/Detail-Kehilangan.php
 * Detail barang hilang untuk pengunjung yang belum login (Figma: Public-Detail Kehilangan).
 * Isi halaman ada di komponen App/views/components/public/detail-barang.php.
 */
require_once __DIR__ . '/../App/config/bootstrap.php';

$pageTitle = 'Detail Barang Hilang';
$menuAktif = 'daftar-barang';

// Data contoh — nanti diganti dari database
$barang = [
    'kategori' => 'Elektronik',
    'nama'     => 'TWS Huawei',
    'lokasi'   => 'LPR 8',
    'waktu'    => '24 Oktober 2026 12:45',
    'pelapor'  => 'John Doe',
    'status'   => 'Aktif',
    'dibuat'   => '29-10-2026 13:57',
    'foto'     => ['assets/images/foto-barang.png', 'assets/images/foto-barang.png', 'assets/images/foto-barang.png'],
];

// Barang hilang tidak diklaim, jadi tanpa kotak ajakan login (sesuai Figma)
$tampilkanAjakanLogin = false;

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/public/navbar.php';
require APP_ROOT . '/App/views/components/public/detail-barang.php';
require_once APP_ROOT . '/App/views/components/footer.php';
