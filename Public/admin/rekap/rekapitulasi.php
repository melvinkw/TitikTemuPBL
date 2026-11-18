<?php
/**
 * Public/admin/rekap/rekapitulasi.php
 * Halaman Rekapitulasi Barang untuk admin (Figma: Admin-Rekapitulasi).
 * Isinya memakai komponen bersama App/views/components/rekapitulasi.php
 * (sama dengan rekap petugas).
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';

$currentUser = auth_require_role(['admin']);

$pageTitle = 'Rekapitulasi Barang';
$menuAktif = 'rekapitulasi';

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/admin/navbar.php';
?>

<main class="flex w-full flex-col gap-6.5 px-4 pt-2.5 pb-10 md:px-20">
    <?php require APP_ROOT . '/App/views/components/rekapitulasi.php'; ?>
</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
