<?php
/**
 * Public/admin/kecocokan/kecocokan.php
 * Halaman Kecocokan Laporan (Figma: Admin-Kecocokan).
 * Admin meninjau pasangan laporan kehilangan dan temuan yang mungkin cocok.
 * Tombol belum berfungsi dan data masih contoh.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';

$currentUser = auth_require_role(['admin']);

$pageTitle = 'Kecocokan Laporan';
$menuAktif = 'kecocokan';
$judulHalaman = 'Kecocokan Laporan';
$deskripsiHalaman = 'Tinjau pasangan laporan kehilangan dan temuan yang mungkin cocok, lalu kirim email ke pelapor bila sesuai.';

// Data contoh — satu kandidat pasangan (laporan kehilangan + laporan temuan)
$kandidat = [
    'laporan' => [
        ['nama' => 'Tumbler Hitam', 'deskripsi' => 'Ini tumbler warna hitam pekat', 'lokasi' => 'LPR 8', 'waktu' => '24 Oktober 2026 12:45', 'pelapor' => 'John Doe'],
        ['nama' => 'Tumbler Hitam', 'deskripsi' => 'Ini tumbler warna hitam pekat', 'lokasi' => 'LPR 8', 'waktu' => '24 Oktober 2026 12:45', 'pelapor' => 'John Doe'],
    ],
    'poin' => ['Kategori', 'Lokasi', 'Selisih 0 hari'],
    'skor' => '70 / 100',
];

// Nomor halaman kandidat (contoh): halaman 1 dari 7
$halamanAktif = 1;
$nomorHalaman = ['1', '2', '..', '7'];

$sudahDitinjau = [
    ['kehilangan' => 'TWS Huawei', 'temuan' => 'TWS putih', 'status' => 'Kemungkinan cocok', 'oleh' => 'John Doe', 'waktu' => '29-10-2026 15:49', 'catatan' => '-'],
];

$logEmail = [
    ['dibuat' => '29-10-2026 15:49', 'nama' => 'Jane Doe', 'email' => 'janedoe@gmail.com', 'kehilangan' => 'TWS Huawei', 'temuan' => 'TWS putih', 'status' => 'Terkirim', 'percobaan' => 1, 'error' => '-'],
];

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/admin/navbar.php';
?>

<main class="flex w-full flex-col gap-6.5 px-4 pt-2.5 pb-10 md:px-20">

    <div class="flex flex-col gap-4">
        <?php require APP_ROOT . '/App/views/components/judul-halaman.php'; ?>

        <!-- Kotak informasi -->
        <div class="flex bg-[#FFF4EE]">
            <div class="w-1.5 shrink-0 bg-[#E48F31]"></div>
            <p class="flex-1 p-4 text-xs text-[#E48F31]">
                Email dikirim hanya ketika admin memilih “Tandai Cocok &amp; Kirim Email”. Isi email menyatakan kemungkinan kecocokan, bukan kepastian kepemilikan. SMTP dan Composer/PHPMailer harus dikonfigurasi terlebih dahulu agar email benar-benar terkirim.
            </p>
        </div>
    </div>

    <!-- Kandidat baru -->
    <section class="mt-2.5 flex flex-col gap-2.5">
        <div class="flex flex-wrap items-end justify-between gap-2">
            <h2 class="px-4 pb-2 font-display text-xl font-semibold">Kandidat Baru</h2>

            <!-- Nomor halaman kandidat (belum berfungsi) -->
            <nav class="flex items-center gap-4 rounded-full bg-white p-1.5" aria-label="Halaman kandidat">
                <a href="#" class="rounded-full hover:bg-tt-primary" aria-label="Kandidat sebelumnya">
                    <img src="<?= h(base_url('assets/images/icon-panah-kiri.svg')) ?>" alt="" class="h-8 w-8">
                </a>
                <div class="flex items-center gap-1.5 px-2 py-1">
                    <?php foreach ($nomorHalaman as $nomor): ?>
                        <span class="flex w-6 justify-center rounded-lg py-1.25 text-xs <?= $nomor === (string) $halamanAktif ? 'bg-tt-primary font-semibold' : '' ?>">
                            <?= h($nomor) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
                <a href="#" class="rounded-full hover:bg-tt-primary" aria-label="Kandidat berikutnya">
                    <img src="<?= h(base_url('assets/images/icon-panah-kiri.svg')) ?>" alt="" class="h-8 w-8 rotate-180">
                </a>
            </nav>
        </div>

        <div class="tt-card flex flex-col gap-4 p-4.5 lg:flex-row">
            <!-- Kiri: dua laporan yang dibandingkan -->
            <div class="flex flex-wrap justify-center gap-1.25 lg:justify-start">
                <?php foreach ($kandidat['laporan'] as $laporan): ?>
                    <div class="tt-card flex w-74 max-w-full flex-col gap-4.5 p-4">
                        <img src="<?= h(base_url('assets/images/foto-barang.png')) ?>" alt="<?= h($laporan['nama']) ?>"
                             class="h-49 w-full rounded-2xl object-cover">
                        <div class="flex flex-col gap-1.5 px-2.75">
                            <p class="font-display text-xl font-semibold"><?= h($laporan['nama']) ?></p>
                            <p class="text-sm"><?= h($laporan['deskripsi']) ?></p>
                        </div>
                        <p class="px-2.75 text-sm"><?= h($laporan['lokasi']) ?></p>
                        <p class="px-2.75 text-sm"><?= h($laporan['waktu']) ?></p>
                        <p class="px-2.75 text-sm font-semibold">Pelapor: <?= h($laporan['pelapor']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Kanan: poin kecocokan dan keputusan admin -->
            <form method="post" action="#" class="flex flex-1 flex-col gap-4 p-4">
                <?= csrf_field() ?>

                <div>
                    <p class="tt-label">Poin Kecocokan</p>
                    <div class="flex flex-wrap gap-2.5">
                        <?php foreach ($kandidat['poin'] as $poin): ?>
                            <span class="tt-badge border border-tt-navy px-3"><?= h($poin) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div>
                    <p class="tt-label">Skor Petunjuk</p>
                    <span class="tt-badge border border-tt-navy px-3"><?= h($kandidat['skor']) ?></span>
                </div>

                <label>
                    <span class="tt-label">Catatan Admin (opsional)</span>
                    <textarea name="catatan" rows="4" class="tt-input h-27 resize-none"></textarea>
                </label>

                <div class="flex flex-wrap gap-2.5 p-2.5">
                    <button type="submit" name="keputusan" value="cocok" class="tt-btn">Tandai Cocok &amp; Kirim Email</button>
                    <button type="submit" name="keputusan" value="tidak-cocok" class="tt-btn-outline">Tidak Cocok</button>
                </div>
            </form>
        </div>
    </section>

    <!-- Pasangan yang sudah ditinjau -->
    <section class="mt-2.5 flex flex-col gap-2.5">
        <h2 class="p-4 font-display text-xl font-semibold">Pasangan yang Sudah Ditinjau</h2>

        <div class="tt-table-wrap">
            <table class="tt-table min-w-250">
                <thead>
                    <tr>
                        <th class="w-1/6">Kehilangan</th>
                        <th class="w-1/6">Temuan</th>
                        <th class="w-1/6">Status</th>
                        <th class="w-1/6">Ditinjau Oleh</th>
                        <th class="w-1/6">Waktu</th>
                        <th class="w-1/6">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sudahDitinjau as $pasangan): ?>
                        <tr>
                            <td><?= h($pasangan['kehilangan']) ?></td>
                            <td><?= h($pasangan['temuan']) ?></td>
                            <td><?= h($pasangan['status']) ?></td>
                            <td><?= h($pasangan['oleh']) ?></td>
                            <td class="whitespace-nowrap"><?= h($pasangan['waktu']) ?></td>
                            <td><?= h($pasangan['catatan']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Log email -->
    <section class="mt-2.5 flex flex-col gap-2.5">
        <h2 class="p-4 font-display text-xl font-semibold">Log Email</h2>

        <div class="tt-table-wrap">
            <table class="tt-table min-w-250">
                <thead>
                    <tr>
                        <th class="w-1/6">Dibuat</th>
                        <th class="w-1/6">Penerima</th>
                        <th class="w-1/6">Pasangan Laporan</th>
                        <th class="w-1/6">Status</th>
                        <th class="w-1/6">Percobaan</th>
                        <th class="w-1/6">Error Terakhir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logEmail as $log): ?>
                        <tr>
                            <td class="whitespace-nowrap"><?= h($log['dibuat']) ?></td>
                            <td>
                                <p class="font-semibold"><?= h($log['nama']) ?></p>
                                <p><?= h($log['email']) ?></p>
                            </td>
                            <td>
                                <p class="font-semibold"><?= h($log['kehilangan']) ?></p>
                                <p class="whitespace-nowrap">mungkin sesuai dengan</p>
                                <p class="font-semibold"><?= h($log['temuan']) ?></p>
                            </td>
                            <td>
                                <span class="tt-badge bg-[#E6F7ED] text-[#17633F]"><?= h($log['status']) ?></span>
                            </td>
                            <td><?= h($log['percobaan']) ?></td>
                            <td><?= h($log['error']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
