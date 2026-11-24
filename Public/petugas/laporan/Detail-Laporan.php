<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/config/database.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/queries/petugas_laporan_queries.php';
require_once APP_ROOT . '/App/services/supabase_storage.php';

$currentUser = auth_require_role(['petugas']);
$pageTitle = 'Detail Laporan';
$menuAktif = 'laporan-masuk';

$rawId = $_GET['id'] ?? '';

$idPengumuman = is_string($rawId)
    ? filter_var($rawId, FILTER_VALIDATE_INT)
    : false;

if (
    $idPengumuman === false
    || $idPengumuman === null
    || $idPengumuman < 1
) {
    http_response_code(400);
    exit('ID laporan tidak valid.');
}

$report = null;
$photos = [];
$history = [];
$reportError = null;

try {

    $report = get_petugas_report_detail(
        db(),
        $idPengumuman
    );

    if ($report === null) {
        http_response_code(404);
        exit('Laporan tidak ditemukan.');
    }

    // Ambil foto laporan.
    $photos = is_array($report['photos'] ?? null)
        ? $report['photos']
        : [];

    // Ambil riwayat status.
    $history = is_array($report['history'] ?? null)
        ? $report['history']
        : [];

    // Buat signed URL untuk foto dari Supabase Storage.
    foreach ($photos as &$photo) {

        $photo['signed_url'] = null;

        $bucket = (string) ($photo['bucket_name'] ?? '');
        $path = (string) ($photo['file_path'] ?? '');

        if ($bucket === '' || $path === '') {
            continue;
        }

        try {

            $photo['signed_url'] = supabase_storage_create_signed_url(
                $bucket,
                $path,
                300
            );

        } catch (Throwable $photoError) {

            error_log(
                '[TitikTemu] Gagal memuat foto: '
                . $photoError->getMessage()
            );
        }
    }

    unset($photo);

    // Tampilkan hanya foto yang berhasil mendapatkan URL.
    $photos = array_values(array_filter(
        $photos,
        static fn (array $photo): bool => !empty($photo['signed_url'])
    ));

} catch (Throwable $error) {

    error_log(
        '[TitikTemu] Gagal mengambil detail laporan: '
        . $error->getMessage()
    );

    http_response_code(500);
    exit('Terjadi kesalahan saat memuat detail laporan.');
}

// Format status
$statusLabels = [
    'menunggu' => 'Menunggu Pemeriksaan',
    'aktif' => 'Aktif',
    'tersedia' => 'Tersedia',
    'diproses' => 'Siap Diambil',
    'selesai' => 'Selesai',
    'ditolak' => 'Ditolak',
    'ditutup' => 'Ditutup',
];

$statusColors = [
    'menunggu' => 'bg-amber-100 text-amber-800',
    'aktif' => 'bg-green-100 text-green-700',
    'tersedia' => 'bg-blue-100 text-blue-700',
    'diproses' => 'bg-sky-100 text-sky-700',
    'selesai' => 'bg-green-100 text-green-700',
    'ditolak' => 'bg-red-100 text-red-700',
    'ditutup' => 'bg-slate-100 text-slate-700',
];

$status = (string) ($report['status'] ?? '');

$statusLabel = $statusLabels[$status]
    ?? ucfirst($status ?: 'Tidak diketahui');

$statusColor = $statusColors[$status]
    ?? 'bg-slate-100 text-slate-700';

$pesan = flash();

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/petugas/navbar.php';
?>

<main class="w-full px-4 py-5 md:px-20">

    <a href="javascript:history.back()" class="text-sm font-semibold hover:text-tt-orange">&lt;&lt; Kembali ke daftar</a>

    <?php require APP_ROOT . '/App/views/components/alert.php'; ?>

    <div class="mt-8 mb-10 grid grid-cols-1 items-start gap-6 lg:grid-cols-4">

        <!-- Kolom kiri -->
        <section class="rounded-2xl border border-tt-outline bg-white p-7 lg:col-span-2">

            <!-- Badge kategori -->
            <div class="mb-2">

                <span class="inline-block rounded-full bg-purple-100 px-3 py-2 text-xs font-semibold text-purple-600">
                    <?= h($report['nama_kategori'] ?? '-') ?>
                </span>

            </div>

            <!-- Nama barang -->
            <h2 class="mb-4 font-display text-2xl font-bold text-tt-navy">
                <?= h($report['judul'] ?? '-') ?>
            </h2>

            <!-- Desc barang -->
            <div class="mb-5 space-y-2 text-sm text-tt-navy">

                <p>
                    <?= h($report['lokasi_kejadian'] ?? '-') ?>
                </p>

                <p>
                    <?= h(format_date_time($report['tanggal_waktu_kejadian'] ?? null)) ?>
                </p>

                <p>
                    <?= h($report['nama_pelapor'] ?? '-') ?>
                    <?php if (!empty($report['nim_pelapor'])): ?>
                        (<?= h($report['nim_pelapor']) ?>)
                    <?php endif; ?>
                </p>

            </div>

            <!-- foto -->
            <section>

                <h2 class="mb-2 font-display text-2xl font-bold text-tt-navy">
                    Foto Barang
                </h2>

                <div class="grid grid-cols-2 gap-2">

                    <?php if ($photos !== []): ?>

                        <?php foreach ($photos as $index => $photo): ?>

                            <?php if ($index === 0): ?>

                                <div class="row-span-2 overflow-hidden rounded-xl bg-slate-100" style="min-height: 192px;">

                                    <img
                                        src="<?= h($photo['signed_url']) ?>"
                                        alt="Foto utama <?= h($report['judul'] ?? 'barang') ?>"
                                        class="h-full min-h-[192px] w-full object-cover"
                                        loading="lazy">

                                </div>

                            <?php else: ?>

                                <div class="overflow-hidden rounded-xl bg-slate-100" style="height: 92px;">

                                    <img
                                        src="<?= h($photo['signed_url']) ?>"
                                        alt="Foto <?= $index + 1 ?> <?= h($report['judul'] ?? 'barang') ?>"
                                        class="h-full w-full object-cover"
                                        loading="lazy">

                                </div>

                            <?php endif; ?>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <div class="col-span-full flex min-h-[192px] items-center justify-center rounded-xl bg-slate-100 text-sm text-slate-500">
                            Belum ada foto barang.
                        </div>

                    <?php endif; ?>

                </div>

            </section>

            <!-- Tindakan -->
            <section class="mt-8">

                <h2 class="mb-4 font-display text-2xl font-bold text-tt-navy">
                    Tindakan
                </h2>

                <div class="rounded-2xl border border-tt-outline p-5">

                    <h3 class="font-display text-base font-bold text-tt-navy">
                        Pemeriksaan Petugas / Admin
                    </h3>

                    <p class="mb-6 mt-3 text-xs text-tt-navy">
                        Periksa informasi dan foto barang sebelum menentukan keputusan.
                    </p>

                    <?php if ($status === 'menunggu'): ?>

                        <!-- Form -->
                        <form
                            action="<?= h(base_url('petugas/laporan/verifikasi.php')) ?>"
                            method="POST">

                            <?= csrf_field() ?>

                            <input type="hidden" name="id_pengumuman" value="<?= (int) $report['id_pengumuman'] ?>">

                            <!-- Keputusan -->
                            <div class="mb-3">

                                <label for="keputusan" class="mb-2 block text-sm font-semibold">

                                    Keputusan<span class="text-red-500">*</span>

                                </label>

                                <select name="keputusan" id="keputusan" required class="h-12 w-full rounded-[10px] border border-tt-outline bg-white px-3 text-sm">

                                    <option value="">Pilih keputusan</option>

                                    <option value="setujui">
                                        Setujui laporan
                                    </option>

                                    <option value="tolak">
                                        Tolak laporan
                                    </option>

                                </select>

                            </div>

                            <!-- Catatan -->
                            <div class="mb-5">

                                <label for="catatan" class="mb-2 block text-sm font-semibold">

                                    Catatan (optional)

                                </label>

                                <textarea name="catatan" id="catatan" rows="4" maxlength="2000" placeholder="Masukkan catatan pemeriksaan..." class="w-full rounded-[10px] border border-tt-outline p-3 text-sm outline-none focus:border-tt-navy"></textarea>

                                <p class="mt-1 text-xs text-slate-500">
                                    Catatan wajib diisi apabila laporan ditolak.
                                </p>

                            </div>

                            <button type="submit" class="w-full rounded-lg bg-tt-navy px-4 py-3 text-sm font-semibold text-white hover:bg-blue-900">

                                Simpan Keputusan

                            </button>

                        </form>

                    <?php else: ?>

                        <!-- Jika laporan sudah diproses -->
                        <div class="rounded-xl bg-tt-primary p-4 text-sm leading-relaxed text-tt-navy">
                            Laporan ini sudah diproses dan tidak memerlukan tindakan lanjutan.
                        </div>

                    <?php endif; ?>

                </div>

            </section>

        </section>

        <!-- Kolom Kanan -->
        <aside class="h-fit self-start rounded-2xl border border-tt-outline bg-white p-7 lg:col-span-2">

            <h2 class="mb-2 font-display text-2xl font-bold text-tt-navy">
                Status Laporan
            </h2>

            <div class="mb-3">

                <span class="inline-block rounded-full px-3 py-2 text-xs font-semibold <?= h($statusColor) ?>">
                    <?= h($statusLabel) ?>
                </span>

            </div>

            <p class="text-xs text-tt-navy">
                Laporan dibuat pada
                <?= h(format_date_time($report['created_at'] ?? null)) ?>.
            </p>

            <?php if (!empty($report['tanggal_verifikasi'])): ?>

                <p class="mt-2 text-xs text-tt-navy">
                    Diverifikasi pada
                    <?= h(format_date_time($report['tanggal_verifikasi'])) ?>.
                </p>

            <?php endif; ?>

            <?php if (!empty($report['catatan_verifikasi'])): ?>

                <div class="mt-4 rounded-xl bg-tt-primary p-4">

                    <h3 class="text-sm font-semibold">
                        Catatan Verifikasi
                    </h3>

                    <p class="mt-2 whitespace-pre-line text-xs leading-relaxed">
                        <?= h($report['catatan_verifikasi']) ?>
                    </p>

                </div>

            <?php endif; ?>

        </aside>

    </div>

    <!-- Table Riwayat -->
    <section class="mb-2">

        <h2 class="mb-6 px-1 font-display text-lg font-semibold text-tt-navy">
            Riwayat Status Laporan
        </h2>

        <div class="overflow-x-auto rounded-xl border border-tt-outline bg-white">

            <table class="w-full min-w-[720px] text-left">

                <thead>

                    <tr class="bg-tt-tablehead font-display text-base font-semibold text-tt-navy">

                        <th class="px-6 py-5">
                            Waktu
                        </th>

                        <th class="px-6 py-5">
                            Perubahan
                        </th>

                        <th class="px-6 py-5">
                            Diubah Oleh
                        </th>

                        <th class="px-6 py-5">
                            Catatan
                        </th>

                    </tr>

                </thead>

                <tbody class="text-xs text-tt-navy">

                    <?php if ($history === []): ?>

                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-tt-muted">
                                Belum ada riwayat perubahan status.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($history as $item): ?>

                            <?php
                            $previousStatus = $item['status_sebelumnya'] ?? null;
                            $newStatus = (string) ($item['status_baru'] ?? '');

                            $previousLabel = $previousStatus === null
                                ? 'Status Awal'
                                : ($statusLabels[$previousStatus] ?? ucfirst((string) $previousStatus));

                            $newLabel = $statusLabels[$newStatus]
                                ?? ucfirst($newStatus ?: '-');

                            $newColor = $statusColors[$newStatus]
                                ?? 'bg-slate-100 text-slate-700';
                            ?>

                            <tr class="border-t border-tt-outline">

                                <td class="whitespace-nowrap px-4 py-3.5">
                                    <?= h(format_date_time($item['created_at'] ?? null)) ?>
                                </td>

                                <td class="px-4 py-3.5">

                                    <div class="flex flex-wrap items-center gap-2">

                                        <span>
                                            <?= h($previousLabel) ?>
                                        </span>

                                        <span aria-hidden="true">
                                            &rarr;
                                        </span>

                                        <span class="inline-block rounded-full px-3 py-2 text-xs font-semibold <?= h($newColor) ?>">
                                            <?= h($newLabel) ?>
                                        </span>

                                    </div>

                                </td>

                                <td class="px-4 py-3.5">
                                    <?= h($item['nama_pengubah'] ?? 'Sistem') ?>
                                </td>

                                <td class="px-4 py-3.5">
                                    <?= h($item['catatan'] ?? '-') ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>