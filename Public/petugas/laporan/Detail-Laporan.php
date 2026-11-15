<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/config/database.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/queries/petugas_laporan_queries.php';
require_once APP_ROOT . '/App/services/supabase_storage_signed_url.php';

$currentUser = auth_require_role(['petugas']);

$rawId = $_GET['id'] ?? '';

$idPengumuman = is_string($rawId)
    ? filter_var($rawId, FILTER_VALIDATE_INT)
    : false;

if ($idPengumuman === false || $idPengumuman === null || $idPengumuman < 1) {
    http_response_code(400);
    exit('ID laporan tidak valid.');
}

try {
    $report = get_petugas_report_detail(db(), $idPengumuman);

    if ($report === null) {
        http_response_code(404);
        exit('Laporan tidak ditemukan.');
    }

    // Ambil foto laporan.
    $photos = $report['photos'] ?? [];

    // Ambil riwayat perubahan status.
    $history = $report['history'] ?? [];

    // Buat signed URL untuk setiap foto.
    foreach ($photos as &$photo) {
        $photo['signed_url'] = null;

        try {
            $bucket = $photo['bucket_name'] ?? 'laporan-pending';
            $path = $photo['file_path'] ?? '';

            if ($path !== '') {
                $photo['signed_url'] = supabase_storage_create_signed_url(
                    $bucket,
                    $path,
                    300
                );
            }
        } catch (Throwable $photoError) {
            error_log(
                '[TitikTemu] Gagal membuat signed URL foto: '
                    . $photoError->getMessage()
            );
        }
    }
    unset($photo);
} catch (Throwable $error) {
    error_log(
        '[TitikTemu] Gagal mengambil detail laporan: '
            . $error->getMessage()
    );

    http_response_code(500);
    exit('Terjadi kesalahan saat memuat detail laporan.');
}

// Format tanggal.
$formatTanggal = static function ($tanggal): string {
    if (empty($tanggal)) {
        return '-';
    }

    try {
        return (new DateTime($tanggal))->format('d-m-Y H:i');
    } catch (Throwable) {
        return '-';
    }
};

// Label status.
$statusLabels = [
    'menunggu' => 'Menunggu Pemeriksaan',
    'aktif' => 'Aktif',
    'tersedia' => 'Tersedia',
    'ditolak' => 'Ditolak',
];

$status = $report['status'] ?? '';
$statusLabel = $statusLabels[$status] ?? ucfirst($status ?: 'Tidak diketahui');

$statusColors = [
    'menunggu' => 'bg-[#FFF7DD] text-[#7A5605]',
    'aktif' => 'bg-green-100 text-green-700',
    'tersedia' => 'bg-blue-100 text-blue-700',
    'ditolak' => 'bg-red-100 text-red-700',
];

$statusColor = $statusColors[$status] ?? 'bg-slate-100 text-slate-700';

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
        <div class="text-sm font-semibold text-[#02103D]"></div>
        <nav class="flex gap-6 text-sm font-semibold text-[#02103D]">
            <a href="#">Dashboard</a>
            <a href="Laporan-Masuk.php">Laporan Masuk</a>
            <a href="#">Lihat Data Master</a>
            <a href="#">Rekapitulasi</a>
        </nav>
        <span class="text-sm font-medium text-[#EC745C] cursor-pointer">Logout</span>
    </header>

    <!-- BODY HALAMAN DETAIL LAPORAN -->
    <main class="max-w-[1500px] w-full mx-auto mt-2 mb-2 py-2.5 flex-grow">

        <div class="mt-5 px-5 text-[14px] font-Inter font-[600] tracking-wider text-[#02103D]">
            <p class="text-[14px] font-Inter font-[600] uppercase">Ruang Kerja Petugas</p>
            <p class="mb-2 text-[32px] font-spartan font-[800]">Daftar Barang</p>
            <p class="mb-10 text-[12px] font-Inter font-[400]">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
        </div>

        <!-- Grid Layout Utama (Kiri: Detail & Foto, Kanan: Status Laporan) -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-12">

            <!-- KOLOM KIRI: Informasi Barang & Foto Barang -->
            <div class="lg:col-span-2 bg-white rounded-2xl p-8 shadow-sm border border-slate-100">

                <!-- Badge Kategori -->
                <div class="mb-2">
                    <span class="inline-block px-3 py-2 bg-[#EBE5F2] text-[#8A38F5] text-[12px] font-semibold rounded-full">
                        <?= h($report['nama_kategori'] ?? '-') ?>
                    </span>
                </div>

                <!-- Nama Barang -->
                <h1 class="text-[28px] font-spartan font-bold text-[#02103D] mb-4">
                    <?= h($report['judul'] ?? '-') ?>
                </h1>

                <!-- Detail -->
                <div class="space-y-1.5 text-[14px] text-[#02103D] mb-4">
                    <p>
                        Lokasi: <?= h($report['lokasi_kejadian'] ?? '-') ?>
                    </p>

                    <p>
                        Tanggal kejadian:
                        <?= h($formatTanggal($report['tanggal_waktu_kejadian'] ?? null)) ?>
                    </p>

                    <p>
                        Pelapor: <?= h($report['nama'] ?? '-') ?>
                    </p>

                    <p>
                        Jenis laporan:
                        <?= h(ucfirst($report['jenis'] ?? '-')) ?>
                    </p>

                    <p>
                        Deskripsi:
                        <?= nl2br(h($report['deskripsi'] ?? '-')) ?>
                    </p>

                    <?php if (!empty($report['detail_lokasi'])): ?>
                        <p>
                            Detail lokasi: <?= h($report['detail_lokasi']) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Bagian Foto Barang -->
                <div>
                    <h2 class="text-[28px] font-spartan font-[700] text-[#02103D] mb-4">Foto Barang</h2>

                    <!-- Grid Foto / Image Gallery -->

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        <?php if (!empty($photos)): ?>

                            <?php foreach ($photos as $index => $photo): ?>

                                <?php if (!empty($photo['signed_url'])): ?>

                                    <div class="rounded-xl overflow-hidden bg-slate-100 min-h-[200px] <?= $index === 0 ? 'sm:row-span-2 sm:min-h-[320px]' : '' ?>">
                                        <img
                                            src="<?= h($photo['signed_url']) ?>"
                                            alt="Foto laporan <?= $index + 1 ?>"
                                            class="w-full h-full object-cover"
                                            loading="lazy">
                                    </div>

                                <?php endif; ?>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <div class="col-span-full rounded-xl bg-slate-100 min-h-[200px] flex items-center justify-center text-slate-500">
                                Belum ada foto barang.
                            </div>

                        <?php endif; ?>

                    </div>


                    <!-- BAGIAN TINDAKAN / PEMERIKSAAN ADMIN -->

                    <div class="mt-8">

                        <h2 class="text-[28px] font-spartan font-bold text-[#02103D] mb-4">
                            Tindakan
                        </h2>

                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">

                            <?php if ($status === 'menunggu'): ?>

                                <h3 class="text-[18px] font-spartan font-bold text-[#02103D] mb-1">
                                    Pemeriksaan Petugas
                                </h3>

                                <p class="text-[13px] text-slate-500 mb-6">
                                    Periksa informasi dan foto sebelum menentukan keputusan.
                                </p>

                                <form
                                    action="<?= h(base_url('petugas/laporan/verifikasi.php')) ?>"
                                    method="POST"
                                    class="space-y-5">

                                    <?= csrf_field() ?>

                                    <input
                                        type="hidden"
                                        name="id_pengumuman"
                                        value="<?= h($report['id_pengumuman']) ?>">

                                    <div>
                                        <label for="keputusan" class="block text-sm font-semibold mb-2">
                                            Keputusan <span class="text-red-500">*</span>
                                        </label>

                                        <select
                                            name="keputusan"
                                            id="keputusan"
                                            required
                                            class="w-full rounded-xl border border-slate-300 px-4 py-3">

                                            <option value="">Pilih keputusan</option>
                                            <option value="setujui">Setujui laporan</option>
                                            <option value="tolak">Tolak laporan</option>

                                        </select>
                                    </div>

                                    <div>
                                        <label for="catatan" class="block text-sm font-semibold mb-2">
                                            Catatan pemeriksaan
                                        </label>

                                        <textarea
                                            name="catatan"
                                            id="catatan"
                                            rows="4"
                                            maxlength="2000"
                                            placeholder="Tuliskan catatan pemeriksaan..."
                                            class="w-full rounded-xl border border-slate-300 px-4 py-3"></textarea>

                                        <p class="text-xs text-slate-500 mt-1">
                                            Catatan wajib diisi jika laporan ditolak.
                                        </p>
                                    </div>

                                    <button
                                        type="submit"
                                        class="w-full py-3 px-6 bg-[#02103D] text-white rounded-xl font-semibold hover:bg-blue-900">
                                        Simpan Keputusan
                                    </button>

                                </form>

                            <?php else: ?>

                                <div class="rounded-xl bg-slate-50 p-4">
                                    <p class="font-semibold text-slate-700">
                                        Laporan sudah diverifikasi.
                                    </p>

                                    <p class="text-sm text-slate-500 mt-1">
                                        Keputusan tidak dapat diubah melalui halaman ini.
                                    </p>
                                </div>

                            <?php endif; ?>

                        </div>
                    </div>


                </div>

                <!-- Status Laporan -->

                <div class="lg:col-span-2">

                    <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100">

                        <h2 class="text-[28px] font-spartan font-bold text-[#02103D] mb-4">
                            Status Laporan
                        </h2>

                        <div class="flex items-center gap-2 mb-3">
                            <span class="inline-block px-3 py-2 <?= h($statusColor) ?> text-[12px] font-semibold rounded-full">
                                <?= h($statusLabel) ?>
                            </span>
                        </div>

                        <p class="text-[12px] text-[#02103D]">
                            Laporan dibuat pada
                            <?= h($formatTanggal($report['created_at'] ?? null)) ?>.
                        </p>

                        <?php if (!empty($report['tanggal_verifikasi'])): ?>

                            <p class="text-[12px] text-[#02103D] mt-2">
                                Diverifikasi pada
                                <?= h($formatTanggal($report['tanggal_verifikasi'])) ?>.
                            </p>

                        <?php endif; ?>

                        <?php if (!empty($report['catatan_verifikasi'])): ?>

                            <div class="mt-5 p-4 rounded-xl bg-slate-50">
                                <p class="text-sm font-semibold mb-2">
                                    Catatan verifikasi
                                </p>

                                <p class="text-sm text-slate-600">
                                    <?= nl2br(h($report['catatan_verifikasi'])) ?>
                                </p>
                            </div>

                        <?php endif; ?>

                    </div>

                </div>


            </div>

            <!-- Riwayat Status Laporan -->
            <h2 class="text-[20px] font-spartan font-[600] text-[#02103D] mb-8 px-1">Riwayat Status Laporan</h2>

            <!-- Tabel Riwayat -->
            <div class="overflow-x-auto border border-[#8795C7] bg-[#FFFFFF] rounded-xl">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-[#8795C7] bg-[#E9EDFF] text-[18px] font-spartan font-[600] text-[#02103D]">
                            <th class="py-3 px-10">Waktu</th>
                            <th class="py-3 px-20">Perubahan</th>
                            <th class="py-3 px-30">Diubah Oleh</th>
                            <th class="py-3 px-40">Catatan</th>
                        </tr>
                    </thead>

                    <tbody class="text-[14px] font-Inter font-normal text-[#02103D] divide-y divide-slate-100">

                        <?php if (!empty($history)): ?>

                            <?php foreach ($history as $item): ?>

                                <tr>
                                    <td class="py-3.5 px-10 whitespace-nowrap">
                                        <?= h($formatTanggal($item['created_at'] ?? null)) ?>
                                    </td>

                                    <td class="py-3.5 px-10">
                                        <?= h($item['status_sebelumnya'] ?? 'Status Awal') ?>
                                        →
                                        <span class="font-semibold">
                                            <?= h($item['status_baru'] ?? '-') ?>
                                        </span>
                                    </td>

                                    <td class="py-3.5 px-10">
                                        <?= h($item['nama_pengubah'] ?? 'Sistem') ?>
                                    </td>

                                    <td class="py-3.5 px-10">
                                        <?= h($item['catatan'] ?? '-') ?>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="4" class="py-6 text-center text-slate-500">
                                    Belum ada riwayat perubahan status.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>
            </div>
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