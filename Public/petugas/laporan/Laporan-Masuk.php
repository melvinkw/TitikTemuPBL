<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/config/database.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/queries/petugas_laporan_queries.php';
require_once APP_ROOT . '/App/services/supabase_storage_signed_url.php';

$currentUser = auth_require_role(['petugas']);
$pageTitle = 'Laporan Masuk';
$menuAktif = 'laporan-masuk';

$reports = [];
$reportError = null;

try {

    $reports = get_petugas_pending_reports(db());

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
                '[TitikTemu] Gagal membuat signed URL foto: '
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
    <div class="mt-5 flex items-center gap-2">

        <span class="text-sm text-slate-600">
            Total laporan menunggu:
        </span>

        <span id="total-laporan" class="rounded-full bg-[#FFF7DD] px-3 py-1 text-sm font-semibold text-[#7A5605]">
            <?= count($reports) ?>
        </span>

    </div>

    <!-- Filter -->
    <form id="filter-form" class="mt-5 rounded-2xl border border-tt-outline bg-white p-4">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">

            <div>

                <label for="filter-nama" class="block font-display text-base font-semibold">
                    Nama Barang
                </label>

                <input type="text" id="filter-nama" placeholder="Contoh: Tumbler" class="mt-2 h-12 w-full rounded-[10px] border border-tt-outline px-3 text-sm placeholder:text-tt-outline">

            </div>

            <div>

                <label for="filter-jenis" class="block font-display text-base font-semibold">
                    Jenis
                </label>

                <input type="text" id="filter-jenis" placeholder="Contoh: Barang Temuan" class="mt-2 h-12 w-full rounded-[10px] border border-tt-outline px-3 text-sm placeholder:text-tt-outline">

            </div>

            <div>

                <label for="filter-lokasi" class="block font-display text-base font-semibold">
                    Lokasi Kejadian
                </label>

                <input type="text" id="filter-lokasi" placeholder="Contoh: Lobby Gedung Sipil" class="mt-2 h-12 w-full rounded-[10px] border border-tt-outline px-3 text-sm placeholder:text-tt-outline">

            </div>

            <div>

                <label for="filter-kategori" class="block font-display text-base font-semibold">
                    Kategori barang
                </label>

                <input type="text" id="filter-kategori" placeholder="Contoh: Elektronik" class="mt-2 h-12 w-full rounded-[10px] border border-tt-outline px-3 text-sm placeholder:text-tt-outline">

            </div>

        </div>

        <div class="mt-6 flex gap-2">

            <button type="submit" class="rounded-lg bg-tt-navy px-3 py-3 text-sm font-semibold text-white hover:bg-tt-orange hover:text-tt-navy">
                Cari Laporan
            </button>

            <button type="reset" class="rounded-lg border border-tt-navy px-3 py-3 text-sm font-semibold hover:bg-tt-primary">
                Reset
            </button>

        </div>

    </form>

    <?php if ($reportError !== null): ?>

        <div role="alert" class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">

            <?= h($reportError) ?>

        </div>

    <?php endif; ?>

    <!-- Card list -->
    <div id="daftar-laporan" class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:px-6">

        <?php if ($reports === [] && $reportError === null): ?>

            <div class="col-span-full rounded-2xl border border-tt-outline bg-white p-10 text-center">

                <h3 class="mb-2 font-display text-xl font-bold text-tt-navy">
                    Belum Ada Laporan Masuk
                </h3>

                <p class="text-sm text-slate-500">
                    Saat ini tidak ada laporan yang menunggu pemeriksaan.
                </p>

            </div>

        <?php else: ?>


            <?php foreach ($reports as $report): ?>

                <?php
                $jenis = (string) ($report['jenis'] ?? '');

                if ($jenis === 'temuan') {
                    $jenisLabel = 'Barang Temuan';
                    $badgeClass = 'bg-sky-100 text-sky-600';
                } else {
                    $jenisLabel = 'Kehilangan Barang';
                    $badgeClass = 'bg-orange-100 text-orange-600';
                }

                $fotoUrl = $report['foto_url'] ?? null;

                $detailUrl = base_url(
                    'petugas/laporan/Detail-Laporan.php?id='
                        . (int) $report['id_pengumuman']
                );
                ?>

                <!-- Card -->
                <article data-report-card data-name="<?= h($report['judul'] ?? '') ?>" data-type="<?= h($jenisLabel) ?>" data-location="<?= h($report['lokasi_kejadian'] ?? '') ?>" data-category="<?= h($report['nama_kategori'] ?? '') ?>" class="flex h-full min-h-[390px] min-w-0 flex-col overflow-hidden rounded-2xl border border-tt-outline bg-white p-4">

                    <!-- Isi -->
                    <div class="flex flex-1 flex-col">

                        <!-- Foto: hapus h-full agar tidak memaksa tinggi berlebih -->
                        <div class="relative mb-4 aspect-[4/3] w-full shrink-0 overflow-hidden rounded-xl bg-slate-100">

                            <span class="absolute left-2 top-2 z-10 rounded-full px-2 py-1 text-xs font-semibold <?= h($badgeClass) ?>">
                                <?= h($jenisLabel) ?>
                            </span>

                            <?php if (!empty($fotoUrl)): ?>

                                <img
                                    src="<?= h($fotoUrl) ?>"
                                    alt="<?= h($report['judul'] ?? 'Foto barang') ?>"
                                    class="absolute inset-0 block h-full w-full object-cover"
                                    loading="lazy">

                            <?php else: ?>

                                <div class="absolute inset-0 flex items-center justify-center text-sm text-slate-400">
                                    Foto tidak tersedia
                                </div>

                            <?php endif; ?>

                        </div>

                        <!-- Desc barang -->
                        <div class="min-w-0">

                            <h2 class="break-words font-display text-lg font-bold leading-6 text-tt-navy">
                                <?= h($report['judul'] ?? '-') ?>
                            </h2>

                            <p class="mt-1 line-clamp-2 min-h-[40px] break-all text-sm leading-5 text-tt-navy">
                                <?= h($report['deskripsi'] ?? '-') ?>
                            </p>

                            <p class="mt-3 break-words text-sm leading-5 text-tt-navy">
                                <?= h($report['lokasi_kejadian'] ?? '-') ?>
                            </p>

                            <p class="mt-2 text-sm leading-5 text-tt-navy">
                                <?= h(format_date_time($report['tanggal_waktu_kejadian'] ?? null)) ?>
                            </p>

                        </div>

                    </div>

                    <a href="<?= h($detailUrl) ?>" class="mt-auto shrink-0 pt-6 text-sm font-semibold text-tt-navy transition hover:text-tt-orange">

                        Lihat Detail &rarr;

                    </a>

                </article>

            <?php endforeach; ?>


        <?php endif; ?>

    </div>

    <!-- Filter not found -->
    <div id="filter-empty" class="mt-6 hidden rounded-2xl border border-tt-outline bg-white p-10 text-center">

        <h3 class="font-display text-xl font-bold text-tt-navy">
            Laporan Tidak Ditemukan
        </h3>

        <p class="mt-2 text-sm text-slate-500">
            Tidak ada laporan yang sesuai dengan filter pencarian.
        </p>

    </div>

</main>

<script>
    document.addEventListener('DOMContentLoaded', () => {

        const form = document.getElementById('filter-form');

        const inputNama = document.getElementById('filter-nama');
        const inputJenis = document.getElementById('filter-jenis');
        const inputLokasi = document.getElementById('filter-lokasi');
        const inputKategori = document.getElementById('filter-kategori');

        const cards = document.querySelectorAll('[data-report-card]');

        const emptyMessage = document.getElementById('filter-empty');
        const totalLaporan = document.getElementById('total-laporan');

        function filterReports() {

            const nama = inputNama.value.toLowerCase().trim();
            const jenis = inputJenis.value.toLowerCase().trim();
            const lokasi = inputLokasi.value.toLowerCase().trim();
            const kategori = inputKategori.value.toLowerCase().trim();

            let visibleCount = 0;

            cards.forEach((card) => {

                const dataNama = (card.dataset.name || '').toLowerCase();
                const dataJenis = (card.dataset.type || '').toLowerCase();
                const dataLokasi = (card.dataset.location || '').toLowerCase();
                const dataKategori = (card.dataset.category || '').toLowerCase();

                const match =
                    dataNama.includes(nama) &&
                    dataJenis.includes(jenis) &&
                    dataLokasi.includes(lokasi) &&
                    dataKategori.includes(kategori);

                card.classList.toggle('hidden', !match);

                if (match) {
                    visibleCount++;
                }

            });

            totalLaporan.textContent = visibleCount;

            emptyMessage.classList.toggle(
                'hidden',
                visibleCount !== 0 || cards.length === 0
            );
        }

        form.addEventListener('submit', (event) => {

            event.preventDefault();
            filterReports();

        });

        form.addEventListener('reset', () => {

            requestAnimationFrame(filterReports);

        });

    });
</script>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>