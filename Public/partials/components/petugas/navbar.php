<?php
/**
 * Lokasi lama navbar petugas. Komponennya sudah dipindah ke
 * App/views/components/petugas/navbar.php (sama seperti mahasiswa & admin).
 * File ini hanya meneruskan supaya halaman petugas yang lama tetap jalan.
 * Setelah semua halaman petugas memakai lokasi baru, file ini boleh dihapus.
 */
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

require APP_ROOT . '/App/views/components/petugas/navbar.php';
