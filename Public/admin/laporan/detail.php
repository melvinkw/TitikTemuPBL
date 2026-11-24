<?php
/**
 * Public/admin/laporan/detail.php
 * Halaman Detail Laporan untuk admin (Figma: user-Admin / Detail Laporan).
 * Data diambil dari database: info barang, foto Supabase Storage, status
 * laporan, dan riwayat status. Form pemeriksaan dikirim ke
 * Public/admin/laporan/verifikasi.php.
 */

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/queries/petugas_laporan_queries.php';
require_once APP_ROOT . '/App/services/supabase_storage.php';

$currentUser = auth_require_role(['admin']);

$pageTitle = 'Detail Laporan';
$menuAktif = 'laporan-masuk';
$judulHalaman = 'Detail Laporan';
$deskripsiHalaman = 'Periksa data dan foto barang, lalu tetapkan apakah laporan ini disetujui atau ditolak.';

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

$photos = [];
$riwayatStatus = [];

try {
    $laporan = get_petugas_report_detail(db(), (int) $idPengumuman);
} catch (Throwable $error) {
    error_log(
        '[TitikTemu] Gagal mengambil detail laporan admin: '
            . $error->getMessage()
    );

    http_response_code(500);
    exit('Terjadi kesalahan saat memuat detail laporan.');
}

if ($laporan === null) {
    http_response_code(404);
    exit('Laporan tidak ditemukan.');
}

$photos = is_array($laporan['photos'] ?? null) ? $laporan['photos'] : [];
$riwayatStatus = is_array($laporan['history'] ?? null) ? $laporan['history'] : [];

// Signed URL foto hanya untuk file yang tersimpan di Storage.
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
            '[TitikTemu] Gagal membuat signed URL foto laporan admin: '
                . $photoError->getMessage()
        );
    }
}
unset($photo);

$photos = array_values(array_filter(
    $photos,
    static fn (array $photo): bool => !empty($photo['signed_url'])
));

$statusLabels = [
    'menunggu' => 'Menunggu Pemeriksaan',
    'aktif' => 'Aktif',
    'tersedia' => 'Barang Tersedia',
    'diproses' => 'Siap Diambil',
    'selesai' => 'Selesai',
    'ditolak' => 'Ditolak',
    'ditutup' => 'Ditutup',
];

$statusColors = [
    'menunggu' => 'bg-[#FFF7DD] text-[#7A5605]',
    'aktif' => 'bg-[#E6F7ED] text-[#17633F]',
    'tersedia' => 'bg-[#E7F0FF] text-[#1D4ED8]',
    'diproses' => 'bg-[#E0F2FE] text-[#0369A1]',
    'selesai' => 'bg-[#E6F7ED] text-[#17633F]',
    'ditolak' => 'bg-[#FDE8E8] text-[#B42318]',
    'ditutup' => 'bg-[#F1F1F1] text-[#757575]',
];

$status = (string) ($laporan['status'] ?? '');

$statusLabel = $statusLabels[$status] ?? ucfirst($status ?: 'Tidak diketahui');
$statusColor = $statusColors[$status] ?? 'bg-[#F1F1F1] text-[#757575]';

// Keputusan memakai nilai yang sama dengan action verifikasi (setujui/tolak);
// labelnya mengikuti status hasil yang sah di database:
// laporan kehilangan → aktif, laporan temuan → tersedia, keduanya bisa ditolak.
$pilihanKeputusan = $laporan['jenis'] === 'hilang'
    ? ['setujui' => 'Aktif', 'tolak' => 'Ditolak']
    : ['setujui' => 'Barang Tersedia', 'tolak' => 'Ditolak'];

$pesan = flash();

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/admin/navbar.php';
?>

<main class="flex w-full flex-col gap-6.5 px-4 pt-2.5 pb-10 md:px-20">

    <a href="<?= h(base_url('admin/laporan/daftar.php')) ?>" class="px-4 text-sm font-semibold hover:text-tt-orange">&lt;&lt; Kembali ke daftar laporan</a>

    <?php require APP_ROOT . '/App/views/components/alert.php'; ?>

    <?php require APP_ROOT . '/App/views/components/judul-halaman.php'; ?>

    <div class="grid grid-cols-1 items-start gap-9 lg:grid-cols-2">

        <!-- Kiri: info barang, foto, dan tindakan -->
        <div class="tt-card flex flex-col gap-4 p-4.5">
            <div class="flex flex-col gap-2 p-4">
                <span class="tt-badge bg-[#EBE5F2] text-[#8A38F5]"><?= h($laporan['nama_kategori'] ?? '-') ?></span>
                <h2 class="font-display text-[28px] font-bold"><?= h($laporan['judul'] ?? '-') ?></h2>
            </div>
            <p class="px-2.75 text-sm"><?= h($laporan['lokasi_kejadian'] ?? '-') ?></p>
            <p class="px-2.75 text-sm"><?= h(format_date_time($laporan['tanggal_waktu_kejadian'] ?? null)) ?></p>
            <p class="px-2.75 text-sm">
                <?= h($laporan['nama_pelapor'] ?? '-') ?>
                <?php if (!empty($laporan['nim_pelapor'])): ?>
                    (<?= h($laporan['nim_pelapor']) ?>)
                <?php endif; ?>
            </p>

            <!-- Foto barang -->
            <div class="flex flex-col gap-2 p-4">
                <h2 class="font-display text-[28px] font-bold">Foto Barang</h2>

                <?php if ($photos === []): ?>
                    <div class="flex h-60 items-center justify-center rounded-2xl bg-slate-100 text-sm text-slate-500">
                        Belum ada foto barang.
                    </div>
                <?php else: ?>
                    <div class="grid h-60 grid-cols-2 grid-rows-2 gap-2">
                        <?php foreach ($photos as $index => $photo): ?>
                            <img
                                src="<?= h($photo['signed_url']) ?>"
                                alt="Foto <?= $index + 1 ?> <?= h($laporan['judul'] ?? 'barang') ?>"
                                loading="lazy"
                                class="<?= $index === 0 ? 'row-span-2' : '' ?> h-full w-full rounded-2xl object-cover">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tindakan: form pemeriksaan -->
            <div class="flex flex-col gap-4">
                <h2 class="p-4 font-display text-[28px] font-bold">Tindakan</h2>

                <div class="tt-card flex flex-col gap-4 p-4.5">
                    <div class="flex flex-col gap-2 p-4">
                        <h3 class="font-display text-xl font-semibold">Pemeriksaan Petugas / Admin</h3>
                        <p class="text-xs">Pilih keputusan dan tambahkan catatan bila perlu. Keputusan akan tercatat di riwayat status laporan.</p>
                    </div>

                    <?php if ($status === 'menunggu'): ?>
                        <form method="post" action="<?= h(base_url('admin/laporan/verifikasi.php')) ?>" class="flex flex-col gap-4">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id_pengumuman" value="<?= (int) $laporan['id_pengumuman'] ?>">

                            <label>
                                <span class="tt-label">Keputusan*</span>
                                <span class="relative block">
                                    <select name="keputusan" required class="tt-input h-12 appearance-none pr-11">
                                        <option value="" selected disabled></option>
                                        <?php foreach ($pilihanKeputusan as $nilai => $label): ?>
                                            <option value="<?= h($nilai) ?>"><?= h($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <img src="<?= h(base_url('assets/images/icon-chevron-down.svg')) ?>" alt=""
                                         class="pointer-events-none absolute top-3 right-3 h-6 w-6">
                                </span>
                            </label>

                            <label>
                                <span class="tt-label">Catatan (opsional)</span>
                                <textarea name="catatan" rows="3" maxlength="2000" class="tt-input h-22 resize-none"></textarea>
                            </label>

                            <p class="px-2.75 text-xs">Catatan wajib diisi apabila laporan ditolak.</p>

                            <div class="p-2.5">
                                <button type="submit" class="tt-btn w-full">Simpan Keputusan</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="rounded-xl bg-tt-primary p-4 text-sm leading-relaxed">
                            Laporan ini sudah diproses dan tidak memerlukan tindakan lanjutan.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Kanan: status laporan -->
        <div class="tt-card p-4.5">
            <div class="flex flex-col gap-2 p-4">
                <h2 class="font-display text-[28px] font-bold">Status Laporan</h2>
                <span class="tt-badge <?= h($statusColor) ?>"><?= h($statusLabel) ?></span>
                <p class="text-xs">Laporan dibuat pada <?= h(format_date_time($laporan['created_at'] ?? null)) ?>.</p>

                <?php if (!empty($laporan['tanggal_verifikasi'])): ?>
                    <p class="text-xs">Diverifikasi pada <?= h(format_date_time($laporan['tanggal_verifikasi'])) ?>.</p>
                <?php endif; ?>

                <?php if (!empty($laporan['catatan_verifikasi'])): ?>
                    <div class="mt-2 rounded-xl bg-tt-primary p-4">
                        <h3 class="text-sm font-semibold">Catatan Verifikasi</h3>
                        <p class="mt-2 whitespace-pre-line text-xs leading-relaxed"><?= h($laporan['catatan_verifikasi']) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Riwayat status -->
    <section class="mt-2.5 flex flex-col gap-2.5">
        <h2 class="p-4 font-display text-xl font-semibold">Riwayat Status Laporan</h2>

        <div class="tt-table-wrap">
            <table class="tt-table min-w-200">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Perubahan</th>
                        <th>Diubah Oleh</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($riwayatStatus === []): ?>
                        <tr>
                            <td colspan="4" class="text-center">Belum ada riwayat perubahan status.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($riwayatStatus as $riwayat): ?>
                        <?php
                        $statusBaru = (string) ($riwayat['status_baru'] ?? '');
                        $statusSebelumnya = $riwayat['status_sebelumnya'] ?? null;

                        $labelSebelumnya = $statusSebelumnya === null
                            ? 'Status Awal'
                            : ($statusLabels[(string) $statusSebelumnya]
                                ?? ucfirst((string) $statusSebelumnya));

                        $labelBaru = $statusLabels[$statusBaru] ?? ucfirst($statusBaru ?: '-');
                        $warnaBaru = $statusColors[$statusBaru] ?? 'bg-[#F1F1F1] text-[#757575]';
                        ?>
                        <tr>
                            <td class="whitespace-nowrap"><?= h(format_date_time($riwayat['created_at'] ?? null)) ?></td>
                            <td>
                                <span class="inline-flex flex-wrap items-center gap-2">
                                    <?= h($labelSebelumnya) ?> &rarr;
                                    <span class="tt-badge <?= h($warnaBaru) ?>"><?= h($labelBaru) ?></span>
                                </span>
                            </td>
                            <td><?= h($riwayat['nama_pengubah'] ?? 'Sistem') ?></td>
                            <td><?= h($riwayat['catatan'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
