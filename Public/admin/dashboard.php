<?php
/**
 * Public/admin/dashboard.php
 * Halaman Dashboard admin (Figma: Admin-Dashboard).
 * Statistik dan isi tabel diambil dari database lewat
 * App/queries/admin_dashboard_queries.php (memakai ulang query dashboard staff
 * agar angkanya sama dengan halaman petugas).
 */

declare(strict_types=1);

require_once __DIR__ . '/../../App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/queries/admin_dashboard_queries.php';

$currentUser = auth_require_role(['admin']);

$pageTitle = 'Dashboard Admin';
$menuAktif = 'dashboard';
$judulHalaman = 'Dashboard Admin';
$deskripsiHalaman = 'Pantau laporan yang menunggu pemeriksaan, kandidat kecocokan, dan aktivitas mahasiswa dari satu halaman.';

$dataDashboard = [
    'stats' => [
        'pending_reports' => 0,
        'pending_claims' => 0,
        'active_students' => 0,
        'email_pending' => 0,
        'email_failed' => 0,
        'match_candidates' => 0,
    ],
    'reports' => [],
    'recent_students' => [],
];

$errorDashboard = null;

try {
    $dataDashboard = get_admin_dashboard_data();
} catch (Throwable $error) {
    error_log(
        '[TitikTemu] Gagal memuat dashboard admin: '
        . $error->getMessage()
    );

    $errorDashboard = 'Ringkasan dashboard belum dapat dimuat.';
}

$statistik = [
    [
        'angka' => $dataDashboard['stats']['pending_reports'],
        'label' => 'Laporan Menunggu Pemeriksaan',
    ],
    [
        'angka' => $dataDashboard['stats']['pending_claims'],
        'label' => 'Klaim Menunggu Verifikasi',
    ],
    [
        'angka' => $dataDashboard['stats']['active_students'],
        'label' => 'Mahasiswa Aktif',
    ],
    [
        'angka' => $dataDashboard['stats']['email_pending']
            + $dataDashboard['stats']['email_failed'],
        'label' => 'Email Menunggu / Gagal',
    ],
];

$laporanPerluDiperiksa = $dataDashboard['reports'];
$loginTerbaru = $dataDashboard['recent_students'];

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/admin/navbar.php';
?>

<main class="flex w-full flex-col gap-6.5 px-4 pt-2.5 pb-10 md:px-20">

    <!-- Sapaan -->
    <div class="flex flex-col gap-2 p-4">
        <p class="font-display text-[28px] font-bold">Halo, <?= h($currentUser['nama'] ?? '') ?>! 👋</p>
        <p class="text-xs">Selamat datang kembali. Berikut ringkasan aktivitas TitikTemu hari ini.</p>
    </div>

    <?php if ($errorDashboard !== null): ?>
        <div role="alert" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <?= h($errorDashboard) ?>
        </div>
    <?php endif; ?>

    <?php require APP_ROOT . '/App/views/components/judul-halaman.php'; ?>

    <?php require APP_ROOT . '/App/views/components/kartu-statistik.php'; ?>

    <!-- Laporan yang perlu diperiksa -->
    <section class="mt-2.5 flex flex-col gap-2.5">
        <div class="flex items-end justify-between gap-2.5">
            <h2 class="p-4 font-display text-xl font-semibold">Laporan yang Perlu Diperiksa</h2>
            <a href="<?= h(base_url('admin/laporan/daftar.php')) ?>" class="px-2.75 py-3 text-sm font-semibold whitespace-nowrap hover:text-tt-orange">Lihat Semua &gt;&gt;</a>
        </div>

        <div class="tt-table-wrap">
            <table class="tt-table min-w-225">
                <thead>
                    <tr>
                        <th>Jenis</th>
                        <th>Barang</th>
                        <th>Pelapor</th>
                        <th>Lokasi</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($laporanPerluDiperiksa === []): ?>
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada laporan yang menunggu pemeriksaan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($laporanPerluDiperiksa as $laporan): ?>
                            <tr>
                                <td><?= h($laporan['jenis']) ?></td>
                                <td><?= h($laporan['judul']) ?></td>
                                <td><?= h($laporan['pelapor']) ?></td>
                                <td><?= h($laporan['lokasi']) ?></td>
                                <td class="whitespace-nowrap"><?= h(format_date_time($laporan['created_at'])) ?></td>
                                <td>
                                    <a href="<?= h(base_url('admin/laporan/detail.php?id=' . (int) $laporan['id_pengumuman'])) ?>" class="tt-btn">Periksa</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Login mahasiswa terbaru -->
    <section class="mt-2.5 flex flex-col gap-2.5">
        <div class="flex items-end justify-between gap-2.5">
            <div class="flex max-w-232.5 flex-col gap-2 p-4">
                <h2 class="font-display text-xl font-semibold">Login Mahasiswa Terbaru</h2>
                <p class="text-xs">Mahasiswa yang terakhir masuk ke TitikTemu menggunakan akun SIAKAD.</p>
            </div>
            <!-- Halaman daftar semua mahasiswa belum ada -->
            <a href="#" class="px-2.75 py-3 text-sm font-semibold whitespace-nowrap hover:text-tt-orange">Lihat Semua &gt;&gt;</a>
        </div>

        <div class="tt-table-wrap">
            <table class="tt-table min-w-225">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NIM</th>
                        <th>Prodi</th>
                        <th>Login Terakhir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($loginTerbaru === []): ?>
                        <tr>
                            <td colspan="4" class="text-center">Belum ada mahasiswa yang login.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($loginTerbaru as $mahasiswa): ?>
                            <tr>
                                <td><?= h($mahasiswa['nama']) ?></td>
                                <td><?= h($mahasiswa['nim']) ?></td>
                                <td><?= h($mahasiswa['prodi']) ?></td>
                                <td class="whitespace-nowrap"><?= h(format_date_time($mahasiswa['last_login'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
