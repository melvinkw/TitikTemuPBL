<?php
/**
 * App/views/components/petugas/navbar.php
 * Navbar untuk semua halaman petugas.
 *
 * Cara pakai di halaman:
 *   $menuAktif = 'dashboard';   // pilihan: dashboard, laporan-masuk, data-master, rekapitulasi
 *   require_once APP_ROOT . '/App/views/components/petugas/navbar.php';
 *
 * Kalau $menuAktif belum diisi, menu aktif ditebak dari nama file halaman.
 */
$daftarMenu = [
    'dashboard'     => ['Dashboard',         'petugas/dashboard.php'],
    'laporan-masuk' => ['Laporan Masuk',     'petugas/laporan/Laporan-Masuk.php'],
    'data-master'   => ['Lihat Data Master', 'petugas/data-master.php'],
    'verifikasi-klaim'  => ['Verifikasi Klaim',  'petugas/klaim/verifikasi.php'],
    'penerimaan-barang' => ['Penerimaan Barang', 'petugas/klaim/penerimaan.php'],
    'rekapitulasi'  => ['Rekapitulasi',      'petugas/rekapitulasi.php'],
];

if (!isset($menuAktif)) {
    $menuAktif = match (basename($_SERVER['PHP_SELF'] ?? '')) {
        'dashboard.php' => 'dashboard',
        'Laporan-Masuk.php', 'Detail-Laporan.php' => 'laporan-masuk',
        'data-master.php' => 'data-master',
        'verifikasi.php' => 'verifikasi-klaim',
        'penerimaan.php' => 'penerimaan-barang',
        'rekapitulasi.php' => 'rekapitulasi',
        default => '',
    };
}

require APP_ROOT . '/App/views/components/navbar.php';
