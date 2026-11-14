<?php

declare(strict_types=1);

require_once __DIR__ . '/../../App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/queries/klaim_queries.php';

$currentUser = auth_require_role(['mahasiswa']);
$riwayat = get_mahasiswa_claim_history(db(), (int) $currentUser['id_pengguna']);
$pesan = flash();

$pageTitle = 'Riwayat Laporan';
$menuAktif = 'riwayat';

$statusLabel = static function (string $status): string {
    return match ($status) {
        'menunggu' => 'Menunggu Verifikasi',
        'tersedia' => 'Tersedia',
        'diproses' => 'Diproses',
        'aktif' => 'Aktif',
        'selesai' => 'Selesai',
        'ditolak' => 'Ditolak',
        'ditutup' => 'Ditutup',
        'disetujui' => 'Disetujui',
        'dibatalkan' => 'Dibatalkan',
        default => $status,
    };
};

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/mahasiswa/navbar.php';
?>

<main class="w-full px-8 py-8 md:px-20">
    <p class="text-xs font-semibold">LOST &amp; FOUND POLINEMA</p>
    <h1 class="mt-2 font-display text-2xl font-bold">Riwayat Laporan Saya</h1>
    <p class="mt-2 text-sm">Daftar laporan kehilangan, barang temuan, dan klaim yang pernah kamu ajukan.</p>

    <?php if ($pesan !== null): ?>
        <div class="mt-5 rounded-lg border px-4 py-3 text-sm <?= ($pesan['type'] ?? '') === 'success'
            ? 'border-green-200 bg-green-50 text-green-800'
            : 'border-red-200 bg-red-50 text-red-800' ?>"
             role="status">
            <?= h($pesan['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <?php foreach ([
        'temuan' => ['title' => 'Laporan Temuan Barang', 'description' => 'Daftar barang temuan yang pernah kamu laporkan.'],
        'hilang' => ['title' => 'Laporan Kehilangan Barang', 'description' => 'Daftar barang hilang yang pernah kamu laporkan.'],
    ] as $jenis => $section): ?>
        <h2 class="mt-12 font-display text-lg font-semibold"><?= h($section['title']) ?></h2>
        <p class="mt-2 text-xs"><?= h($section['description']) ?></p>

        <div class="mt-6 overflow-x-auto rounded-xl border border-indigo-200 bg-white">
            <table class="w-full text-left text-sm">
                <thead class="bg-tt-tablehead font-display text-base">
                    <tr>
                        <th class="px-6 py-5 font-semibold">Nama Barang</th>
                        <th class="px-6 py-5 font-semibold">Kategori</th>
                        <th class="px-6 py-5 font-semibold">Lokasi</th>
                        <th class="px-6 py-5 font-semibold">Tanggal &amp; Waktu</th>
                        <th class="px-6 py-5 font-semibold">Status</th>
                        <th class="px-6 py-5 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($riwayat[$jenis] === []): ?>
                        <tr><td colspan="6" class="px-6 py-6 text-center text-gray-500">Belum ada laporan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($riwayat[$jenis] as $laporan): ?>
                            <tr class="border-t border-indigo-100">
                                <td class="px-6 py-4"><?= h($laporan['judul']) ?></td>
                                <td class="px-6 py-4"><?= h($laporan['nama_kategori']) ?></td>
                                <td class="px-6 py-4"><?= h($laporan['lokasi_kejadian']) ?></td>
                                <td class="px-6 py-4"><?= h(format_date_time((string) $laporan['tanggal_waktu_kejadian'])) ?></td>
                                <td class="px-6 py-4"><?= h($statusLabel((string) $laporan['status'])) ?></td>
                                <td class="px-6 py-4">
                                    <a href="<?= h(base_url('mahasiswa/laporan/detail.php?id=' . (int) $laporan['id_pengumuman'])) ?>"
                                       class="font-semibold hover:text-tt-orange">Lihat Detail</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>

    <h2 class="mt-12 font-display text-lg font-semibold">Riwayat Klaim Barang</h2>
    <p class="mt-2 text-xs">Daftar klaim barang yang pernah kamu ajukan.</p>

    <div class="mt-6 overflow-x-auto rounded-xl border border-indigo-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-tt-tablehead font-display text-base">
                <tr>
                    <th class="px-6 py-5 font-semibold">Nama Barang</th>
                    <th class="px-6 py-5 font-semibold">Pemohon</th>
                    <th class="px-6 py-5 font-semibold">Bukti Kepemilikan</th>
                    <th class="px-6 py-5 font-semibold">Tanggal Pengajuan</th>
                    <th class="px-6 py-5 font-semibold">Status</th>
                    <th class="px-6 py-5 font-semibold">Catatan</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($riwayat['klaim'] === []): ?>
                    <tr><td colspan="6" class="px-6 py-6 text-center text-gray-500">Belum ada klaim.</td></tr>
                <?php else: ?>
                    <?php foreach ($riwayat['klaim'] as $klaim): ?>
                        <tr class="border-t border-indigo-100">
                            <td class="px-6 py-4">
                                <p class="font-semibold"><?= h($klaim['judul']) ?></p>
                                <p class="text-xs"><?= h($klaim['lokasi_kejadian']) ?></p>
                            </td>
                            <td class="px-6 py-4"><?= h($klaim['nama_pemohon']) ?></td>
                            <td class="px-6 py-4"><?= h($klaim['deskripsi_bukti']) ?></td>
                            <td class="px-6 py-4"><?= h(format_date_time((string) $klaim['tanggal_pengajuan'])) ?></td>
                            <td class="px-6 py-4"><?= h($statusLabel((string) $klaim['status_verifikasi'])) ?></td>
                            <td class="px-6 py-4"><?= h($klaim['catatan_verifikasi'] ?: 'Belum ada catatan') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
