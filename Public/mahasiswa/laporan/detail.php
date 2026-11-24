<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/queries/mahasiswa_queries.php';
require_once APP_ROOT . '/App/services/supabase_storage.php';

$currentUser = auth_require_role(['mahasiswa']);

$idPengumuman = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$idPengumuman || $idPengumuman < 1) {
    http_response_code(404);
    exit('Laporan tidak ditemukan.');
}

$pdo = db();
$idPengguna = (int) $currentUser['id_pengguna'];

$barang = get_mahasiswa_own_report_detail(
    $pdo,
    (int) $idPengumuman,
    $idPengguna
);

if ($barang === null) {
    http_response_code(404);
    exit('Laporan tidak ditemukan.');
}

$fotoBarang = get_mahasiswa_own_report_photos(
    $pdo,
    (int) $idPengumuman,
    $idPengguna
);

foreach ($fotoBarang as &$foto) {
    $foto['url'] = null;

    try {
        $foto['url'] = supabase_storage_create_signed_url(
            $foto['bucket_name'],
            $foto['file_path']
        );
    } catch (Throwable $error) {
        error_log(
            '[TitikTemu] Gagal membuat URL foto laporan pribadi: '
            . $error->getMessage()
        );
    }
}
unset($foto);

$riwayatStatus = get_mahasiswa_own_report_history(
    $pdo,
    (int) $idPengumuman,
    $idPengguna
);

$statusLabels = [
    'menunggu' => 'Menunggu Pemeriksaan',
    'aktif' => 'Aktif',
    'tersedia' => 'Barang Tersedia',
    'diproses' => 'Siap Diambil',
    'selesai' => 'Selesai',
    'ditolak' => 'Ditolak',
    'ditutup' => 'Ditutup',
];

$formatStatus = static fn (?string $status): string
    => $statusLabels[$status ?? ''] ?? ucfirst((string) $status);

$pageTitle = 'Detail Barang';
$menuAktif = '';

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/mahasiswa/navbar.php';
?>

<main class="w-full px-4 py-6 md:px-20">

    <!-- Kembali ke halaman sebelumnya -->
    <a href="javascript:history.back()" class="text-sm font-semibold hover:text-tt-orange">&lt;&lt; Kembali ke daftar</a>

    <div class="mt-8 grid grid-cols-1 items-start gap-8 lg:grid-cols-2">

    <!-- Informasi barang -->
    <div class="rounded-xl border border-tt-outline bg-white p-8">

        <span class="rounded-full bg-purple-100 px-2 py-1 text-xs font-semibold text-purple-600">
            <?= h($barang['nama_kategori']) ?>
        </span>

        <h1 class="mt-3 font-display text-2xl font-bold">
            <?= h($barang['judul']) ?>
        </h1>

        <p class="mt-6 text-sm">
            Lokasi: <?= h($barang['lokasi_kejadian']) ?>
        </p>

        <?php if (!empty($barang['detail_lokasi'])): ?>
            <p class="mt-2 text-sm">
                Detail lokasi: <?= h($barang['detail_lokasi']) ?>
            </p>
        <?php endif; ?>

        <p class="mt-3 text-sm">
            Waktu kejadian:
            <?= h(format_date_time($barang['tanggal_waktu_kejadian'])) ?>
        </p>

        <p class="mt-3 text-sm">
            Pelapor: <?= h($barang['nama_pelapor']) ?>
        </p>

        <h2 class="mt-6 font-display text-xl font-bold">
            Deskripsi Barang
        </h2>

        <p class="mt-2 whitespace-pre-line text-sm leading-6">
            <?= h($barang['deskripsi']) ?>
        </p>

        
<h2 class="mt-6 font-display text-2xl font-bold">
    Foto Barang
</h2>

<div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">

    <?php if ($fotoBarang === []): ?>

        <div class="col-span-full flex h-48 items-center justify-center rounded-xl bg-slate-100 text-sm text-slate-500">
            Belum ada foto untuk laporan ini.
        </div>

    <?php else: ?>

        <?php foreach ($fotoBarang as $foto): ?>

            <?php if ($foto['url'] !== null): ?>

                <img
                    src="<?= h($foto['url']) ?>"
                    alt="<?= h($foto['nama_file'] ?? $barang['judul']) ?>"
                    loading="lazy"
                    class="h-48 w-full rounded-xl object-cover"
                >

            <?php endif; ?>

        <?php endforeach; ?>

    <?php endif; ?>

</div>

    </div>

    <!-- Informasi status -->
    <div class="rounded-xl border border-tt-outline bg-white p-8">

        <h2 class="font-display text-2xl font-bold">
            Status Laporan
        </h2>

        <span class="mt-3 inline-block rounded-full bg-tt-primary px-3 py-1 text-xs font-semibold text-tt-navy">
            <?= h($formatStatus($barang['status'])) ?>
        </span>

        <p class="mt-4 text-sm">
            Laporan dibuat pada
            <?= h(format_date_time($barang['created_at'])) ?>.
        </p>

        <?php if (!empty($barang['catatan_verifikasi'])): ?>

            <div class="mt-6 rounded-lg border border-tt-outline bg-tt-primary p-4">

                <h3 class="font-semibold text-sm">
                    Catatan Verifikasi
                </h3>

                <p class="mt-2 whitespace-pre-line text-sm">
                    <?= h($barang['catatan_verifikasi']) ?>
                </p>

            </div>

        <?php endif; ?>

    </div>

</div>

    <!-- Riwayat status -->
    <h2 class="mt-12 font-display text-lg font-semibold">
    Riwayat Status Laporan
</h2>

<div class="mt-6 overflow-x-auto rounded-xl border border-tt-outline bg-white">

    <table class="w-full min-w-160 text-left text-sm">

        <thead class="bg-tt-tablehead font-display text-base">
            <tr>
                <th class="px-6 py-5 font-semibold whitespace-nowrap">
                    Waktu
                </th>

                <th class="px-6 py-5 font-semibold whitespace-nowrap">
                    Perubahan Status
                </th>

                <th class="px-6 py-5 font-semibold whitespace-nowrap">
                    Diubah Oleh
                </th>

                <th class="px-6 py-5 font-semibold whitespace-nowrap">
                    Catatan
                </th>
            </tr>
        </thead>

        <tbody>

            <?php if ($riwayatStatus === []): ?>

                <tr>
                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                        Belum ada riwayat perubahan status.
                    </td>
                </tr>

            <?php else: ?>

                <?php foreach ($riwayatStatus as $riwayat): ?>

                    <tr class="border-t border-tt-outline">

                        <td class="px-6 py-4 whitespace-nowrap">
                            <?= h(format_date_time($riwayat['created_at'])) ?>
                        </td>

                        <td class="px-6 py-4">

                            <?= h($formatStatus($riwayat['status_sebelumnya'])) ?>

                            <span class="mx-1">→</span>

                            <strong>
                                <?= h($formatStatus($riwayat['status_baru'])) ?>
                            </strong>

                        </td>

                        <td class="px-6 py-4">
                            <?= h($riwayat['nama_pengubah'] ?? 'Sistem') ?>
                        </td>

                        <td class="px-6 py-4">
                            <?= h($riwayat['catatan'] ?? '-') ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php endif; ?>

        </tbody>

    </table>

</div>

</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
