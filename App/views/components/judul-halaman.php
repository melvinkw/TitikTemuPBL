<?php
/**
 * App/views/components/judul-halaman.php
 * Judul halaman ruang kerja: label kecil, judul besar, dan deskripsi singkat.
 * Dipakai semua halaman admin dan halaman rekapitulasi petugas.
 *
 * Cara pakai di halaman:
 *   $judulHalaman     = 'Data Master';
 *   $deskripsiHalaman = 'Kelola kategori barang ...';
 *   $labelRuangKerja  = 'RUANG KERJA PETUGAS';   // opsional, bawaannya RUANG KERJA ADMINISTRATOR
 *   require APP_ROOT . '/App/views/components/judul-halaman.php';
 */
$judulHalaman = $judulHalaman ?? '';
$deskripsiHalaman = $deskripsiHalaman ?? '';
$labelRuangKerja = $labelRuangKerja ?? 'RUANG KERJA ADMINISTRATOR';
?>
<div class="flex flex-col gap-2 p-4">
    <p class="text-sm font-semibold"><?= h($labelRuangKerja) ?></p>
    <h1 class="font-display text-[32px] font-extrabold"><?= h($judulHalaman) ?></h1>
    <?php if ($deskripsiHalaman !== ''): ?>
        <p class="text-xs"><?= h($deskripsiHalaman) ?></p>
    <?php endif; ?>
</div>
