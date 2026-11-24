<?php
/**
 * App/views/components/admin/navbar.php
 * Navbar untuk semua halaman admin (Figma: komponen "Nav" di halaman Admin).
 *
 * Cara pakai di halaman:
 *   $menuAktif = 'dashboard';   // pilihan: dashboard, data-master, akun-staff, kecocokan, rekapitulasi
 *   require_once APP_ROOT . '/App/views/components/admin/navbar.php';
 */
$daftarMenu = [
    'dashboard'         => ['Dashboard',         'admin/dashboard.php'],
    'laporan-masuk'     => ['Laporan Masuk',     'admin/laporan/daftar.php'],
    'verifikasi-klaim'  => ['Verifikasi Klaim',  'admin/klaim/verifikasi.php'],
    'data-master'       => ['Data Master',       'admin/kategori/kategori.php'],
    'akun-staff'        => ['Akun Staff',        'admin/pengguna/akun-staff.php'],
    'kecocokan'         => ['Kecocokan',         'admin/kecocokan/kecocokan.php'],
    'rekapitulasi'      => ['Rekapitulasi',      'admin/rekap/rekapitulasi.php'],
];

require APP_ROOT . '/App/views/components/navbar.php';
