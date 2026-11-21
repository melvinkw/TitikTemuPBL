<?php
/**
 * App/views/components/admin/badge-aktif.php
 * Label status Aktif (hijau) / Nonaktif (abu-abu) di tabel Data Master dan Akun Staff.
 *
 * Cara pakai di dalam perulangan tabel:
 *   $aktif = $baris['aktif'];   // true atau false
 *   require APP_ROOT . '/App/views/components/admin/badge-aktif.php';
 */
$aktif = (bool) ($aktif ?? false);
?>
<?php if ($aktif): ?>
    <span class="tt-badge bg-[#E6F7ED] text-[#17633F]">Aktif</span>
<?php else: ?>
    <span class="tt-badge bg-[#F1F1F1] text-[#757575]">Nonaktif</span>
<?php endif; ?>
