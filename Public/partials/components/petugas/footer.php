<?php
/**
 * Lokasi lama footer petugas. Isinya sekarang memakai footer bersama
 * App/views/components/footer-bar.php supaya tampilannya sama di semua role.
 * Setelah halaman petugas memakai App/views/components/footer.php, file ini boleh dihapus.
 */
if (!defined('APP_ROOT')) {
    http_response_code(404);
    exit;
}

require APP_ROOT . '/App/views/components/footer-bar.php';
