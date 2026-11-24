<?php

declare(strict_types=1);

/**
 * Public/partials/detail-laporan-publik.php
 * Halaman detail laporan untuk pengunjung (belum login).
 *
 * Dipakai oleh Detail-Temuan.php dan Detail-Kehilangan.php.
 * Halaman pemanggil wajib mengisi $jenisHalaman dengan 'temuan' atau 'hilang'.
 */

require_once dirname(__DIR__, 2) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/queries/public_queries.php';
require_once APP_ROOT . '/App/services/supabase_storage.php';

$jenisHalaman = $jenisHalaman ?? 'temuan';

if (!in_array($jenisHalaman, ['temuan', 'hilang'], true)) {
    $jenisHalaman = 'temuan';
}

// Halaman ini publik, tetapi pengguna yang sudah login tetap dilayani
// agar tautan dari halaman mahasiswa tidak berakhir di halaman login.
$penggunaAktif = auth_current_user();

$pageTitle = $jenisHalaman === 'temuan'
    ? 'Detail Barang Temuan'
    : 'Detail Barang Hilang';

$menuAktif = 'daftar-barang';

$rawId = $_GET['id'] ?? null;

$idPengumuman = is_string($rawId)
    ? filter_var($rawId, FILTER_VALIDATE_INT)
    : false;

if ($idPengumuman === false || $idPengumuman === null || $idPengumuman < 1) {
    http_response_code(400);
    $laporan = null;
    $pesanError = 'ID laporan tidak valid.';
} else {
    $laporan = null;
    $pesanError = null;

    try {
        $laporan = get_public_report_detail(db(), (int) $idPengumuman);

        if ($laporan === null || $laporan['jenis'] !== $jenisHalaman) {
            http_response_code(404);
            $laporan = null;
            $pesanError = 'Laporan tidak ditemukan atau sudah tidak tayang.';
        }
    } catch (Throwable $error) {
        error_log(
            '[TitikTemu] Gagal memuat detail laporan publik: '
            . $error->getMessage()
        );

        http_response_code(500);
        $pesanError = 'Detail laporan belum dapat dimuat. Silakan coba lagi.';
    }
}

$fotoUrls = [];

if ($laporan !== null) {
    try {
        foreach (get_public_report_photos(db(), (int) $idPengumuman) as $foto) {
            if (empty($foto['bucket_name']) || empty($foto['file_path'])) {
                continue;
            }

            $fotoUrls[] = supabase_storage_create_signed_url(
                $foto['bucket_name'],
                $foto['file_path']
            );
        }
    } catch (Throwable $error) {
        error_log(
            '[TitikTemu] Gagal memuat foto laporan publik: '
            . $error->getMessage()
        );
    }
}

$statusLabel = [
    'aktif' => 'Aktif',
    'tersedia' => 'Tersedia untuk Diklaim',
    'diproses' => 'Siap Diambil',
];

$statusBadge = [
    'aktif' => 'bg-[#E6F7ED] text-[#17633F]',
    'tersedia' => 'bg-[#E6F7ED] text-[#17633F]',
    'diproses' => 'bg-[#EAF1FF] text-[#1D4ED8]',
];

require_once APP_ROOT . '/App/views/components/header.php';

if ($penggunaAktif !== null && in_array($penggunaAktif['role'] ?? '', ['mahasiswa', 'petugas', 'admin'], true)) {
    require_once APP_ROOT . '/App/views/components/' . $penggunaAktif['role'] . '/navbar.php';
} else {
    require_once APP_ROOT . '/App/views/components/public/navbar.php';
}
?>

<main class="w-full px-4 py-8 md:px-20">
    <a href="<?= h(base_url('daftar-barang.php')) ?>" class="text-sm font-semibold hover:text-tt-orange">&lt;&lt; Kembali ke daftar</a>

    <?php if ($laporan === null): ?>

        <div class="tt-card mt-6 p-10 text-center">
            <h1 class="font-display text-2xl font-bold">Laporan Tidak Dapat Ditampilkan</h1>
            <p class="mt-3 text-sm"><?= h($pesanError ?? 'Laporan tidak ditemukan.') ?></p>
            <a href="<?= h(base_url('daftar-barang.php')) ?>" class="tt-btn mt-6">Kembali ke Daftar Barang</a>
        </div>

    <?php else: ?>

        <?php
        $status = (string) $laporan['status'];
        $statusText = $statusLabel[$status] ?? ucfirst($status);
        $statusBadgeClass = $statusBadge[$status] ?? 'bg-slate-100 text-slate-700';
        ?>

        <div class="mt-6 grid grid-cols-1 items-start gap-9 lg:grid-cols-2">

            <!-- Kiri: info barang dan foto -->
            <section class="tt-card flex flex-col gap-4 p-4.5">
                <div class="flex flex-col gap-2 p-4">
                    <span class="tt-badge bg-[#EBE5F2] text-[#8A38F5]"><?= h($laporan['nama_kategori']) ?></span>
                    <h1 class="font-display text-[28px] font-bold"><?= h($laporan['judul']) ?></h1>
                </div>

                <p class="px-2.75 text-sm"><?= h($laporan['lokasi_kejadian']) ?></p>
                <p class="px-2.75 text-sm"><?= h(format_date_time($laporan['tanggal_waktu_kejadian'])) ?></p>
                <p class="px-2.75 text-sm"><?= h($laporan['nama_pelapor']) ?></p>

                <?php if (!empty($laporan['nama_pos'])): ?>
                    <p class="px-2.75 text-sm">
                        Pos penyerahan: <?= h($laporan['nama_pos']) ?><?= !empty($laporan['nama_lokasi']) ? ' - ' . h($laporan['nama_lokasi']) : '' ?>
                    </p>
                <?php endif; ?>

                <div class="flex flex-col gap-2 p-4">
                    <h2 class="font-display text-[28px] font-bold">Deskripsi Barang</h2>
                    <p class="whitespace-pre-line text-sm"><?= h($laporan['deskripsi']) ?></p>
                </div>

                <div class="flex flex-col gap-2 p-4">
                    <h2 class="font-display text-[28px] font-bold">Foto Barang</h2>
                    <?php if ($fotoUrls === []): ?>
                        <p class="text-sm text-tt-outline">Belum ada foto barang.</p>
                    <?php else: ?>
                        <div class="grid h-60 grid-cols-2 grid-rows-2 gap-2">
                            <?php foreach (array_slice($fotoUrls, 0, 3) as $urutan => $url): ?>
                                <img src="<?= h($url) ?>"
                                     alt="Foto <?= h($laporan['judul']) ?> <?= $urutan + 1 ?>"
                                     loading="lazy"
                                     class="h-full w-full rounded-2xl object-cover <?= $urutan === 0 ? 'row-span-2' : '' ?>">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Kanan: status laporan -->
            <section class="tt-card flex flex-col gap-4 p-4.5">
                <div class="flex flex-col gap-2 p-4">
                    <h2 class="font-display text-[28px] font-bold">Status Laporan</h2>
                    <span class="tt-badge <?= h($statusBadgeClass) ?>"><?= h($statusText) ?></span>
                    <p class="text-xs">Laporan dibuat pada <?= h(format_date_time($laporan['created_at'])) ?>.</p>
                </div>

                <?php if ($jenisHalaman === 'temuan' && $status === 'tersedia'): ?>
                    <?php if ($penggunaAktif !== null && ($penggunaAktif['role'] ?? '') === 'mahasiswa'): ?>
                        <a href="<?= h(base_url('mahasiswa/klaim/detail.php?id=' . (int) $laporan['id_pengumuman'])) ?>"
                           class="flex w-full max-w-106 items-center justify-center border-l-[6px] border-[#E48F31] bg-[#FFF4EE] p-4 text-xs font-semibold text-[#E48F31] hover:underline">
                            Ajukan Klaim Barang Ini
                        </a>
                    <?php elseif ($penggunaAktif === null): ?>
                        <a href="<?= h(base_url('login.php')) ?>"
                           class="flex w-full max-w-106 items-center border-l-[6px] border-[#E48F31] bg-[#FFF4EE] p-4 text-xs text-[#E48F31] hover:underline">
                            Login terlebih dahulu untuk mengajukan klaim barang ini.
                        </a>
                    <?php endif; ?>
                <?php elseif ($jenisHalaman === 'hilang' && $penggunaAktif === null): ?>
                    <a href="<?= h(base_url('login.php')) ?>"
                       class="flex w-full max-w-106 items-center border-l-[6px] border-[#E48F31] bg-[#FFF4EE] p-4 text-xs text-[#E48F31] hover:underline">
                        Menemukan barang ini? Login lalu laporkan sebagai barang temuan.
                    </a>
                <?php endif; ?>
            </section>
        </div>

    <?php endif; ?>
</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
