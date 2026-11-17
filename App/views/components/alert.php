<?php
/**
 * App/views/components/alert.php
 * Kotak pesan hasil aksi (berhasil / gagal), misalnya setelah mengirim laporan atau klaim.
 * Letakkan di bagian atas <main> supaya langsung terlihat.
 *
 * Cara pakai di halaman:
 *   $pesan = flash();   // ambil pesan sekali pakai dari session
 *   require APP_ROOT . '/App/views/components/alert.php';
 */
$pesan = $pesan ?? null;

if ($pesan !== null && ($pesan['message'] ?? '') !== ''):
    $berhasil = ($pesan['type'] ?? '') === 'success';
?>
    <div role="<?= $berhasil ? 'status' : 'alert' ?>"
         class="mb-6 rounded-lg border px-4 py-3 text-sm <?= $berhasil
             ? 'border-green-200 bg-green-50 text-green-800'
             : 'border-red-200 bg-red-50 text-red-800' ?>">
        <?= h($pesan['message']) ?>
    </div>
<?php endif; ?>
