<?php
/**
 * Public/petugas/rekapitulasi.php
 * Halaman Rekapitulasi Barang untuk petugas (Figma: user-Petugas, frame "Data Master"
 * yang isinya rekapitulasi). Isinya memakai komponen bersama
 * App/views/components/rekapitulasi.php (sama dengan rekap admin).
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';

$currentUser = auth_require_role(['petugas']);

$pageTitle = 'Rekapitulasi Barang';
$menuAktif = 'rekapitulasi';
$labelRuangKerja = 'RUANG KERJA PETUGAS';

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/petugas/navbar.php';
?>

<main class="flex w-full flex-col gap-6.5 px-4 pt-2.5 pb-10 md:px-20">
    <?php require APP_ROOT . '/App/views/components/rekapitulasi.php'; ?>
</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
