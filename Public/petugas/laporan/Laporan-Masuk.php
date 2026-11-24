<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/config/database.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/queries/master_queries.php';
require_once APP_ROOT . '/App/queries/petugas_laporan_queries.php';
require_once APP_ROOT . '/App/services/supabase_storage.php';

$currentUser = auth_require_role(['petugas']);
$pageTitle = 'Laporan Masuk';
$menuAktif = 'laporan-masuk';

// Filter dibaca dari URL; nilai yang tidak valid diabaikan.
$filterJudul = trim((string) ($_GET['judul'] ?? ''));
$filterJenis = (string) ($_GET['jenis'] ?? '');
$filterLokasi = trim((string) ($_GET['lokasi'] ?? ''));
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
                '[TitikTemu] Gagal membuat signed URL foto laporan masuk: '
                    . $photoError->getMessage()
            );
        }
    }
    unset($report);
} catch (Throwable $error) {
    error_log(
        '[TitikTemu] Gagal mengambil laporan masuk: '
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
require_once APP_ROOT . '/App/views/components/petugas/navbar.php';
?>

<main class="w-full px-4 py-8 md:px-20">

    <?php require APP_ROOT . '/App/views/components/alert.php'; ?>

    <p class="text-xs font-semibold">
        RUANG KERJA PETUGAS
    </p>

    <h1 class="mt-2 font-display text-2xl font-bold">
        Laporan Masuk
    </h1>

    <p class="mt-2 text-xs">
        Laporan yang masuk akan ditampilkan di halaman ini. Petugas dapat melakukan filter pencarian untuk menemukan laporan tertentu.
    </p>

    <!-- Total laporan -->
    <div class="mt-5 flex flex-wrap items-center gap-2">

        <span class="text-sm text-slate-600">
            Total laporan menunggu:
        </span>

        <span class="rounded-full bg-[#FFF7DD] px-3 py-1 text-sm font-semibold text-[#7A5605]">
            <?= $totalMenunggu ?>
        </span>

        <?php if ($adaFilter): ?>
            <span class="text-sm text-slate-600">
                &middot; Hasil pencarian: <?= count($reports) ?> laporan
            </span>
        <?php endif; ?>

    </div>

    <!-- Filter: dikirim ke server lewat GET -->
    <form
        action="<?= h(base_url('petugas/laporan/Laporan-Masuk.php')) ?>"
        method="get"
        class="mt-5 rounded-2xl border border-tt-outline bg-white p-4">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">

            <div>
                <label for="filter-judul" class="block font-display text-base font-semibold">
                    Nama Barang
                </label>

                <input
                    type="text"
                    id="filter-judul"
                    name="judul"
                    value="<?= h($filterJudul) ?>"
                    maxlength="150"
                    placeholder="Contoh: Tumbler"
                    class="mt-2 h-12 w-full rounded-[10px] border border-tt-outline px-3 text-sm placeholder:text-tt-outline">
            </div>

            <div>
                <label for="filter-jenis" class="block font-display text-base font-semibold">
                    Jenis
                </label>

                <select
                    id="filter-jenis"
                    name="jenis"
                    class="mt-2 h-12 w-full rounded-[10px] border border-tt-outline bg-white px-3 text-sm">
                    <option value="">Semua jenis</option>
                    <option value="temuan" <?= $filterJenis === 'temuan' ? 'selected' : '' ?>>
                        Barang Temuan
                    </option>
                    <option value="hilang" <?= $filterJenis === 'hilang' ? 'selected' : '' ?>>
                        Barang Kehilangan
                    </option>
                </select>
            </div>

            <div>
                <label for="filter-lokasi" class="block font-display text-base font-semibold">
                    Lokasi Kejadian
                </label>

                <input
                    type="text"
                    id="filter-lokasi"
                    name="lokasi"
                    value="<?= h($filterLokasi) ?>"
                    maxlength="200"
                    placeholder="Contoh: Lobby Gedung Sipil"
                    class="mt-2 h-12 w-full rounded-[10px] border border-tt-outline px-3 text-sm placeholder:text-tt-outline">
            </div>

            <div>
                <label for="filter-kategori" class="block font-display text-base font-semibold">
                    Kategori Barang
                </label>

                <select
                    id="filter-kategori"
                    name="kategori"
                    class="mt-2 h-12 w-full rounded-[10px] border border-tt-outline bg-white px-3 text-sm">
                    <option value="">Semua kategori</option>
                    <?php foreach ($kategoriList as $kategori): ?>
                        <option
                            value="<?= (int) $kategori['id_kategori'] ?>"
                            <?= $filterKategori === (int) $kategori['id_kategori'] ? 'selected' : '' ?>>
                            <?= h($kategori['nama_kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>

        <div class="mt-6 flex gap-2">

            <button type="submit" class="rounded-lg bg-tt-navy px-3 py-3 text-sm font-semibold text-white hover:bg-tt-orange hover:text-tt-navy">
                Cari Laporan
            </button>

            <a href="<?= h(base_url('petugas/laporan/Laporan-Masuk.php')) ?>"
               class="rounded-lg border border-tt-navy px-3 py-3 text-sm font-semibold hover:bg-tt-primary">
                Reset
            </a>

        </div>

    </form>

    <?php if ($reportError !== null): ?>

        <div role="alert" class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">

            <?= h($reportError) ?>

        </div>

    <?php endif; ?>

    <!-- Kartu laporan -->
    <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:px-6">

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
                    <a href="<?= h(base_url('petugas/laporan/Laporan-Masuk.php')) ?>"
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
                    'petugas/laporan/Detail-Laporan.php?id='
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
                            <span class="font-semibold">Kategori:</span>
                            <?= h($report['nama_kategori'] ?? '-') ?>
                        </p>

                        <p>
                            <span class="font-semibold">Lokasi:</span>
                            <?= h($report['lokasi_kejadian'] ?? '-') ?>
                        </p>

                        <p class="text-xs text-gray-500">
                            <?= h(format_date_time($report['tanggal_waktu_kejadian'] ?? null)) ?>
                        </p>

                    </div>

                    <a
                        href="<?= h($detailUrl) ?>"
                        class="mt-auto pt-6 text-sm font-semibold hover:text-tt-orange">
                        Lihat Detail &rarr;
                    </a>

                </article>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
