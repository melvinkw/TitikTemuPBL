<?php
/**
 * App/views/components/kartu-statistik.php
 * Deretan kartu angka (4 kolom di layar lebar, 2 di tablet, 1 di HP).
 * Dipakai di dashboard admin dan halaman rekapitulasi.
 *
 * Cara pakai di halaman:
 *   $statistik = [
 *       ['angka' => 128, 'label' => 'Laporan Menunggu Pemeriksaan'],
 *       ...
 *   ];
 *   require APP_ROOT . '/App/views/components/kartu-statistik.php';
 */
$statistik = $statistik ?? [];
?>
<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
    <?php foreach ($statistik as $stat): ?>
        <div class="tt-card flex flex-col gap-2 p-6">
            <p class="font-display text-[28px] font-bold"><?= h($stat['angka']) ?></p>
            <p class="text-base"><?= h($stat['label']) ?></p>
        </div>
    <?php endforeach; ?>
</div>
