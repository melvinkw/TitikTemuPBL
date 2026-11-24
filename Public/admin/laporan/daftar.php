<?php
/**
 * Public/admin/laporan/daftar.php
 * Daftar laporan yang menunggu pemeriksaan untuk admin.
 * Memakai query dan aturan filter yang sama dengan halaman petugas
 * (App/queries/petugas_laporan_queries.php) supaya hasilnya identik.
 */

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/queries/master_queries.php';
require_once APP_ROOT . '/App/queries/petugas_laporan_queries.php';
require_once APP_ROOT . '/App/services/supabase_storage.php';

$currentUser = auth_require_role(['admin']);

$pageTitle = 'Laporan Masuk';
$menuAktif = 'laporan-masuk';
$judulHalaman = 'Laporan Masuk';
$deskripsiHalaman = 'Periksa laporan yang menunggu keputusan. Gunakan filter untuk menemukan laporan tertentu.';

// Filter dari URL; nilai yang tidak dikenal diabaikan.
$filterJudul = is_string($_GET['judul'] ?? null) ? trim($_GET['judul']) : '';
$filterJenis = is_string($_GET['jenis'] ?? null) ? trim($_GET['jenis']) : '';
$filterLokasi = is_string($_GET['lokasi'] ?? null) ? trim($_GET['lokasi']) : '';
$filterKategori = (int) ($_GET['kategori'] ?? 0);

if (!in_array($filterJenis, ['temuan', 'hilang'], true)) {
    $filterJenis = '';
}

$adaFilter = $filterJudul !== ''
    || $filterJenis !== ''
    || $filterLokasi !== ''
    || $filterKategori > 0;

$kategoriList = [];
$reports = [];
$totalMenunggu = 0;
$reportError = null;

try {
    $pdo = db();

    $kategoriList = get_kategori_aktif();
    $totalMenunggu = count_petugas_pending_reports($pdo);

    $reports = get_petugas_pending_reports($pdo, [
        'judul' => $filterJudul,
        'jenis' => $filterJenis,
        'lokasi' => $filterLokasi,
        'id_kategori' => $filterKategori,
    ]);

    // Signed URL foto dibuat per laporan; kegagalan satu foto tidak
    // menggagalkan seluruh halaman.
    foreach ($reports as &$report) {
        $report['foto_url'] = null;

        $bucket = (string) ($report['foto_bucket'] ?? '');
        $path = (string) ($report['foto_path'] ?? '');

        if ($bucket === '' || $path === '') {
            continue;
        }

        try {
            $report['foto_url'] = supabase_storage_create_signed_url(
                $bucket,
                $path,
                300
            );
        } catch (Throwable $photoError) {
            error_log(
                '[TitikTemu] Gagal membuat signed URL foto laporan masuk admin: '
                    . $photoError->getMessage()
            );
        }
    }
    unset($report);
} catch (Throwable $error) {
    error_log(
        '[TitikTemu] Gagal mengambil laporan masuk admin: '
            . $error->getMessage()
    );

    $reportError = 'Gagal memuat daftar laporan. Silakan coba kembali.';
}

// Label jenis dipakai di dropdown filter dan badge kartu.
$labelJenis = [
    'temuan' => 'Barang Temuan',
    'hilang' => 'Kehilangan Barang',
];

$badgeJenis = [
    'temuan' => 'bg-sky-100 text-sky-600',
    'hilang' => 'bg-orange-100 text-tt-orange',
];

$pesan = flash();

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/admin/navbar.php';
?>

<main class="flex w-full flex-col gap-6.5 px-4 pt-2.5 pb-10 md:px-20">

    <?php require APP_ROOT . '/App/views/components/alert.php'; ?>

    <div class="flex flex-col gap-4">
        <?php require APP_ROOT . '/App/views/components/judul-halaman.php'; ?>
    </div>

    <!-- Filter pencarian -->
    <form method="get" class="tt-card flex flex-col gap-4 p-4.5">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <label>
                <span class="tt-label">Nama Barang</span>
                <input
                    type="text"
                    name="judul"
                    value="<?= h($filterJudul) ?>"
                    maxlength="150"
                    placeholder="Contoh: Tumbler"
                    class="tt-input h-12">
            </label>

            <label>
                <span class="tt-label">Jenis</span>
                <span class="relative block">
                    <select name="jenis" class="tt-input h-12 appearance-none pr-11">
                        <option value="">Semua jenis</option>
                        <option value="temuan" <?= $filterJenis === 'temuan' ? 'selected' : '' ?>>Barang Temuan</option>
                        <option value="hilang" <?= $filterJenis === 'hilang' ? 'selected' : '' ?>>Barang Kehilangan</option>
                    </select>
                    <img src="<?= h(base_url('assets/images/icon-chevron-down.svg')) ?>" alt=""
                         class="pointer-events-none absolute top-3 right-3 h-6 w-6">
                </span>
            </label>

            <label>
                <span class="tt-label">Lokasi Kejadian</span>
                <input
                    type="text"
                    name="lokasi"
                    value="<?= h($filterLokasi) ?>"
                    maxlength="200"
                    placeholder="Contoh: Lobby Gedung Sipil"
                    class="tt-input h-12">
            </label>

            <label>
                <span class="tt-label">Kategori Barang</span>
                <span class="relative block">
                    <select name="kategori" class="tt-input h-12 appearance-none pr-11">
                        <option value="">Semua kategori</option>
                        <?php foreach ($kategoriList as $kategori): ?>
                            <option
                                value="<?= (int) $kategori['id_kategori'] ?>"
                                <?= $filterKategori === (int) $kategori['id_kategori'] ? 'selected' : '' ?>>
                                <?= h($kategori['nama_kategori']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <img src="<?= h(base_url('assets/images/icon-chevron-down.svg')) ?>" alt=""
                         class="pointer-events-none absolute top-3 right-3 h-6 w-6">
                </span>
            </label>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <button type="submit" class="tt-btn">Cari Laporan</button>
            <a href="<?= h(base_url('admin/laporan/daftar.php')) ?>" class="tt-btn-outline">Reset</a>

            <p class="ml-auto text-sm">
                Menunggu pemeriksaan: <strong><?= $totalMenunggu ?></strong>
                <?php if ($adaFilter): ?>
                    &middot; Hasil filter: <strong><?= count($reports) ?></strong>
                <?php endif; ?>
            </p>
        </div>
    </form>

    <?php if ($reportError !== null): ?>
        <div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <?= h($reportError) ?>
        </div>
    <?php endif; ?>

    <!-- Kartu laporan -->
    <section class="flex flex-col gap-4">
        <h2 class="font-display text-xl font-semibold">Laporan Menunggu Pemeriksaan</h2>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

            <?php if ($reportError === null && $reports === []): ?>
                <div class="col-span-full rounded-2xl border border-tt-outline bg-white p-10 text-center">
                    <h3 class="font-display text-lg font-semibold">
                        <?= $adaFilter ? 'Laporan Tidak Ditemukan' : 'Belum Ada Laporan Masuk' ?>
                    </h3>
                    <p class="mt-2 text-sm text-gray-500">
                        <?= $adaFilter
                            ? 'Tidak ada laporan yang sesuai dengan filter pencarian.'
                            : 'Saat ini tidak ada laporan yang menunggu pemeriksaan.' ?>
                    </p>
                    <?php if ($adaFilter): ?>
                        <a href="<?= h(base_url('admin/laporan/daftar.php')) ?>"
                           class="mt-4 inline-block text-sm font-semibold text-tt-orange">
                            Hapus Filter
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($reports as $report): ?>
                    <?php
                    $jenis = (string) ($report['jenis'] ?? '');
                    $jenisLabel = $labelJenis[$jenis] ?? ucfirst($jenis);
                    $badgeClass = $badgeJenis[$jenis] ?? 'bg-slate-100 text-slate-600';
                    $fotoUrl = $report['foto_url'] ?? null;

                    $detailUrl = base_url(
                        'admin/laporan/detail.php?id='
                            . (int) $report['id_pengumuman']
                    );
                    ?>

                    <article class="flex h-full flex-col rounded-2xl border border-tt-outline bg-white p-4">

                        <!-- Foto barang + badge jenis -->
                        <div class="relative">
                            <?php if ($fotoUrl !== null): ?>
                                <img
                                    src="<?= h($fotoUrl) ?>"
                                    alt="<?= h($report['judul'] ?? 'Foto barang') ?>"
                                    loading="lazy"
                                    class="h-48 w-full rounded-lg object-cover">
                            <?php else: ?>
                                <div class="flex h-48 w-full items-center justify-center rounded-lg bg-slate-100 text-sm text-slate-500">
                                    Foto belum tersedia
                                </div>
                            <?php endif; ?>

                            <span class="absolute left-2 top-2 rounded-full px-2 py-1 text-xs font-semibold <?= h($badgeClass) ?>">
                                <?= h($jenisLabel) ?>
                            </span>
                        </div>

                        <!-- Informasi barang -->
                        <h3 class="mt-4 font-display text-lg font-semibold">
                            <?= h($report['judul'] ?? '-') ?>
                        </h3>

                        <p class="mt-1 line-clamp-2 text-sm text-gray-600">
                            <?= h($report['deskripsi'] ?? '-') ?>
                        </p>

                        <div class="mt-3 space-y-2 text-sm">
                            <p>
                                <span class="font-semibold">Jenis:</span>
                                <?= h($jenisLabel) ?>
                            </p>
                            <p>
                                <span class="font-semibold">Kategori:</span>
                                <?= h($report['nama_kategori'] ?? '-') ?>
                            </p>
                            <p>
                                <span class="font-semibold">Lokasi:</span>
                                <?= h($report['lokasi_kejadian'] ?? '-') ?>
                            </p>
                            <p>
                                <span class="font-semibold">Pelapor:</span>
                                <?= h($report['nama_pelapor'] ?? '-') ?>
                            </p>
                            <p class="text-xs text-gray-500">
                                <?= h(format_date_time($report['tanggal_waktu_kejadian'] ?? null)) ?>
                            </p>
                        </div>

                        <a
                            href="<?= h($detailUrl) ?>"
                            class="mt-auto pt-6 text-sm font-semibold hover:text-tt-orange">
                            Periksa Laporan &rarr;
                        </a>

                    </article>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </section>

</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
