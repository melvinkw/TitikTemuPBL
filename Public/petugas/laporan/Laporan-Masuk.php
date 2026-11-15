<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/config/database.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/queries/petugas_laporan_queries.php';
require_once APP_ROOT . '/App/services/supabase_storage_signed_url.php';

$currentUser = auth_require_role(['petugas']);

$reports = [];
$reportError = null;

try {
    // Mengambil seluruh laporan yang menunggu pemeriksaan.
    $reports = get_petugas_pending_reports(db());

    // Membuat signed URL foto laporan.
    foreach ($reports as &$report) {
        $report['foto_url'] = null;

        $bucket = $report['foto_bucket'] ?? '';
        $path = $report['foto_path'] ?? '';

        if ($bucket !== '' && $path !== '') {
            try {
                $report['foto_url'] = supabase_storage_create_signed_url(
                    $bucket,
                    $path,
                    300
                );
            } catch (Throwable $photoError) {
                error_log(
                    '[TitikTemu] Gagal memuat foto laporan: '
                        . $photoError->getMessage()
                );
            }
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

// Format tanggal laporan.
$formatTanggal = static function ($tanggal): string {
    if (empty($tanggal)) {
        return '-';
    }

    try {
        return (new DateTime($tanggal))->format('d F Y H:i');
    } catch (Throwable) {
        return '-';
    }
};

?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TitikTemu - Detail Barang</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=League+Spartan:wght@600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        spartan: ['"League Spartan"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-[#DCE3FD] min-h-screen flex flex-col justify-between font-sans text-slate-800">

    <!-- BAGIAN NAVBAR -->

    <header class="w-full bg-[#DCE3FD] py-4 px-8 flex justify-between items-center border-b border-[#8795C7]">

        <nav class="flex gap-6 text-sm font-semibold text-[#02103D]">

            <a href="<?= h(base_url('petugas/Dashboard-Petugas.php')) ?>">
                Dashboard
            </a>

            <a href="<?= h(base_url('petugas/laporan/Laporan-Masuk.php')) ?>">
                Laporan Masuk
            </a>

            <a href="#">
                Lihat Data Master
            </a>

            <a href="#">
                Rekapitulasi
            </a>

        </nav>

        <form action="<?= h(base_url('logout.php')) ?>" method="POST">
            <?= csrf_field() ?>

            <button
                type="submit"
                class="text-sm font-medium text-[#EC745C]">
                Logout
            </button>
        </form>

    </header>


    <!-- BODY HALAMAN DETAIL LAPORAN -->

    <main class="max-w-[1500px] w-full mx-auto mt-2 mb-2 py-2.5 flex-grow">

        <!-- HEADER HALAMAN -->
        <div class="mt-5 px-5 text-[14px] font-semibold tracking-wider text-[#02103D]">
            <p class="text-[14px] uppercase">Ruang Kerja Petugas</p>

            <p class="mb-2 text-[32px] font-spartan font-bold">
                Laporan Masuk
            </p>

            <p class="mb-6 text-[12px] font-normal">
                Daftar laporan barang hilang dan temuan yang menunggu pemeriksaan petugas.
            </p>

            <div class="flex items-center gap-2 mb-8">
                <span class="text-sm text-slate-600">
                    Total laporan menunggu:
                </span>

                <span class="px-3 py-1 rounded-full bg-[#FFF7DD] text-[#7A5605] text-sm font-semibold">
                    <?= count($reports) ?>
                </span>
            </div>
        </div>

        <!-- PESAN ERROR -->
        <?php if ($reportError !== null): ?>

            <div class="mx-5 mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <?= h($reportError) ?>
            </div>

        <?php endif; ?>

        <!-- GRID DAFTAR LAPORAN -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-12 px-5 lg:px-10">

            <?php if (empty($reports) && $reportError === null): ?>

                <div class="col-span-full bg-white rounded-2xl border border-[#8795C7] p-10 text-center">

                    <h3 class="text-xl font-spartan font-bold text-[#02103D] mb-2">
                        Belum Ada Laporan Masuk
                    </h3>

                    <p class="text-sm text-slate-500">
                        Saat ini tidak ada laporan yang menunggu pemeriksaan.
                    </p>

                </div>

            <?php else: ?>

                <?php foreach ($reports as $report): ?>

                    <?php
                    $jenis = $report['jenis'] ?? '';

                    if ($jenis === 'temuan') {
                        $jenisLabel = 'Barang Temuan';
                        $badgeClass = 'bg-[#E5F3FE] text-[#008BFB]';
                    } else {
                        $jenisLabel = 'Kehilangan Barang';
                        $badgeClass = 'bg-[#FFF4EE] text-[#E48F31]';
                    }

                    $fotoUrl = $report['foto_url'] ?? null;
                    ?>

                    <!-- CARD LAPORAN -->
                    <div class="bg-white rounded-2xl p-5 shadow-sm border border-[#8795C7] flex flex-col justify-between">

                        <div>

                            <!-- FOTO DAN BADGE -->
                            <div class="relative w-full h-[218px] rounded-xl overflow-hidden bg-slate-100 mb-4">

                                <span class="absolute top-3 left-3 px-3 py-2 <?= h($badgeClass) ?> text-[12px] font-semibold rounded-full shadow-sm z-10">
                                    <?= h($jenisLabel) ?>
                                </span>

                                <?php if (!empty($fotoUrl)): ?>

                                    <img
                                        src="<?= h($fotoUrl) ?>"
                                        alt="<?= h($report['judul'] ?? 'Foto barang') ?>"
                                        class="w-full h-full object-cover"
                                        loading="lazy">

                                <?php else: ?>

                                    <div class="w-full h-full flex flex-col items-center justify-center text-slate-400">
                                        <span class="text-sm">Foto tidak tersedia</span>
                                    </div>

                                <?php endif; ?>

                            </div>

                            <!-- INFORMASI BARANG -->
                            <h3 class="text-[18px] font-spartan font-bold text-[#02103D] mb-1">
                                <?= h($report['judul'] ?? '-') ?>
                            </h3>

                            <p class="text-[13px] text-slate-500 mb-3 line-clamp-2">
                                <?= h($report['deskripsi'] ?? '-') ?>
                            </p>

                            <div class="space-y-1 text-[13px] text-[#02103D] mb-8">

                                <p class="font-normal">
                                    <?= h($report['lokasi_kejadian'] ?? '-') ?>
                                </p>

                                <p class="text-slate-500">
                                    <?= h($formatTanggal($report['tanggal_waktu_kejadian'] ?? null)) ?>
                                </p>

                            </div>

                        </div>

                        <!-- TOMBOL DETAIL -->
                        <div>

                            <a
                                href="<?= h(base_url('petugas/laporan/Detail-Laporan.php?id=' . (int) $report['id_pengumuman'])) ?>"
                                class="inline-flex items-center gap-1 text-[13px] font-semibold text-[#02103D] hover:text-[#FA9B2D]">

                                Lihat Detail &rarr;

                            </a>

                        </div>

                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>

    </main>


    <!-- BAGIAN FOOTER -->
    <footer class="w-full bg-[#DCE3FD] py-4 px-8 text-xs text-[#8795C7] flex justify-between items-center border-t border-[#8795C7]">
        <div><strong class="text-[#8795C7]">TitikTemu.</strong> Politeknik Negeri Malang</div>
        <div>Platform informasi barang hilang dan barang temuan.</div>
        <div>&copy; 2026 TitikTemu</div>
    </footer>
</body>

</html>