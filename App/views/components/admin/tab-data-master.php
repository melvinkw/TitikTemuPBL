<?php
/**
 * App/views/components/admin/tab-data-master.php
 * Tab di halaman Data Master admin: Kategori, Lokasi, Pos Penyerahan.
 *
 * Cara pakai di halaman:
 *   $tabAktif = 'kategori';   // pilihan: kategori, lokasi, pos
 *   require APP_ROOT . '/App/views/components/admin/tab-data-master.php';
 */
$tabAktif = $tabAktif ?? '';

// kunci => [label, alamat]
$tabDataMaster = [
    'kategori' => ['Kategori',       'admin/kategori/kategori.php'],
    'lokasi'   => ['Lokasi',         'admin/kategori/lokasi.php'],
    'pos'      => ['Pos Penyerahan', 'admin/kategori/pos-penyerahan.php'],
];
?>
<div class="flex flex-wrap items-center gap-3.25">
    <?php foreach ($tabDataMaster as $kunci => $tab): ?>
        <?php if ($tabAktif === $kunci): ?>
            <span class="rounded-full border border-tt-orange bg-tt-orange px-4.5 py-2.5 text-xs font-extrabold" aria-current="page">
                <?= h($tab[0]) ?>
            </span>
        <?php else: ?>
            <a href="<?= h(base_url($tab[1])) ?>"
               class="rounded-full border border-tt-outline bg-white px-4.5 py-2.5 text-xs font-semibold hover:border-tt-orange hover:text-tt-orange">
                <?= h($tab[0]) ?>
            </a>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
