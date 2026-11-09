<?php
/**
 * Public/admin/kecocokan/kecocokan.php
 * Halaman Kecocokan Laporan (Figma: Admin-Kecocokan).
 */
require_once __DIR__ . '/../../../App/config/bootstrap.php';

$pageTitle = 'Kecocokan Laporan';
$menuAktif = 'kecocokan';

// Data dummy — satu kandidat pasangan (laporan kehilangan + laporan temuan)
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
        <!-- Judul halaman -->
        <div class="flex flex-col gap-2 p-4">
            <p class="text-sm font-semibold">RUANG KERJA ADMINISTRATOR</p>
            <h1 class="font-display text-[32px] font-extrabold">Kecocokan Laporan</h1>
            <p class="text-xs">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
        </div>

        <!-- Kotak informasi -->
        <div class="flex bg-[#fff4ee]">
            <div class="w-1.5 shrink-0 bg-[#e48f31]"></div>
            <p class="flex-1 p-4 text-xs text-[#e48f31]">
                Email dikirim hanya ketika admin memilih “Tandai cocok &amp; kirim email”. Isi email menyatakan kemungkinan kecocokan, bukan kepastian kepemilikan. SMTP dan Composer/PHPMailer harus dikonfigurasi terlebih dahulu agar email benar-benar terkirim.
            </p>
        </div>
    </div>

    <!-- Kandidat baru -->
    <section class="mt-2.5 flex flex-col gap-2.5">
        <div class="flex flex-wrap items-end justify-between gap-2">
            <h2 class="px-4 pb-2 font-display text-xl font-semibold">Kandidat Baru</h2>

            <!-- Nomor halaman kandidat (belum berfungsi) -->
            <div class="flex items-center gap-4 rounded-full bg-white p-1.5">
                <a href="#" aria-label="Kandidat sebelumnya">
                    <img src="<?= h(base_url('assets/images/icon-panah-kiri.svg')) ?>" alt="" width="32" height="32">
                </a>
                <div class="flex items-center gap-1.5 px-2 py-1">
                    <?php foreach ($nomorHalaman as $nomor): ?>
                        <span class="flex w-6 justify-center rounded-lg py-1.25 text-xs <?= $nomor === (string) $halamanAktif ? 'bg-tt-primary font-semibold' : '' ?>">
                            <?= h($nomor) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
                <a href="#" aria-label="Kandidat berikutnya">
                    <img src="<?= h(base_url('assets/images/icon-panah-kiri.svg')) ?>" alt="" width="32" height="32" class="rotate-180">
                </a>
            </div>
        </div>

        <div class="flex flex-col gap-4 rounded-2xl border border-tt-outline bg-white p-4.5 lg:flex-row">
            <!-- Kiri: dua laporan yang dibandingkan -->
            <div class="flex flex-wrap justify-center gap-1.25 lg:justify-start">
                <?php foreach ($kandidat['laporan'] as $laporan): ?>
                    <div class="flex w-74 max-w-full flex-col gap-4.5 rounded-2xl border border-tt-outline bg-white p-4">
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

            <!-- Kanan: poin kecocokan & keputusan admin -->
            <form method="post" action="#" class="flex flex-1 flex-col gap-4 p-4">
                <div class="flex flex-col gap-2">
                    <p class="font-display text-base font-semibold">Poin Kecocokan</p>
                    <div class="flex flex-wrap gap-2.5">
                        <?php foreach ($kandidat['poin'] as $poin): ?>
                            <span class="rounded-full border border-tt-navy px-3 py-2.5 text-xs font-semibold"><?= h($poin) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex flex-col items-start gap-2">
                    <p class="font-display text-base font-semibold">Skor Petunjuk</p>
                    <span class="rounded-full border border-tt-navy px-3 py-2.5 text-xs font-semibold"><?= h($kandidat['skor']) ?></span>
                </div>

                <label class="flex flex-col gap-2">
                    <span class="font-display text-base font-semibold">Catatan Admin (optional)</span>
                    <textarea name="catatan" rows="4"
                              class="h-27 w-full resize-none rounded-[10px] border border-tt-outline p-3 text-sm outline-none focus:border-tt-navy"></textarea>
                </label>

                <div class="flex flex-wrap gap-2.5 p-2.5">
                    <button type="submit" name="keputusan" value="cocok"
                            class="h-10.75 cursor-pointer rounded-[10px] bg-tt-navy px-2.75 text-sm font-semibold text-white">Tandai Cocok &amp; Kirim Email</button>
                    <button type="submit" name="keputusan" value="tidak-cocok"
                            class="h-10.75 cursor-pointer rounded-[10px] border border-tt-navy px-2.75 text-sm font-semibold">Tidak Cocok</button>
                </div>
            </form>
        </div>
    </section>

    <!-- Pasangan yang sudah ditinjau -->
    <section class="mt-2.5 flex flex-col gap-2.5">
        <h2 class="p-4 font-display text-xl font-semibold">Pasangan yang Sudah Ditinjau</h2>

        <div class="overflow-x-auto rounded-[15px] border border-tt-outline bg-white">
            <table class="w-full min-w-250 text-left text-sm">
                <thead class="bg-tt-tablehead font-display text-xl">
                    <tr>
                        <th class="w-1/6 px-7.5 py-8 font-semibold whitespace-nowrap">Kehilangan</th>
                        <th class="w-1/6 px-7.5 py-8 font-semibold whitespace-nowrap">Temuan</th>
                        <th class="w-1/6 px-7.5 py-8 font-semibold whitespace-nowrap">Status</th>
                        <th class="w-1/6 px-7.5 py-8 font-semibold whitespace-nowrap">Ditinjau Oleh</th>
                        <th class="w-1/6 px-7.5 py-8 font-semibold whitespace-nowrap">Waktu</th>
                        <th class="w-1/6 px-7.5 py-8 font-semibold whitespace-nowrap">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sudahDitinjau as $pasangan): ?>
                        <tr class="border-b border-tt-outline last:border-b-0">
                            <td class="h-18 px-7.5"><?= h($pasangan['kehilangan']) ?></td>
                            <td class="h-18 px-7.5"><?= h($pasangan['temuan']) ?></td>
                            <td class="h-18 px-7.5"><?= h($pasangan['status']) ?></td>
                            <td class="h-18 px-7.5"><?= h($pasangan['oleh']) ?></td>
                            <td class="h-18 px-7.5 whitespace-nowrap"><?= h($pasangan['waktu']) ?></td>
                            <td class="h-18 px-7.5"><?= h($pasangan['catatan']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Log email -->
    <section class="mt-2.5 flex flex-col gap-2.5">
        <h2 class="p-4 font-display text-xl font-semibold">Log Email</h2>

        <div class="overflow-x-auto rounded-[15px] border border-tt-outline bg-white">
            <table class="w-full min-w-250 text-left text-sm">
                <thead class="bg-tt-tablehead font-display text-xl">
                    <tr>
                        <th class="w-1/6 px-7.5 py-8 font-semibold whitespace-nowrap">Dibuat</th>
                        <th class="w-1/6 px-7.5 py-8 font-semibold whitespace-nowrap">Penerima</th>
                        <th class="w-1/6 px-7.5 py-8 font-semibold whitespace-nowrap">Pasangan Laporan</th>
                        <th class="w-1/6 px-7.5 py-8 font-semibold whitespace-nowrap">Status</th>
                        <th class="w-1/6 px-7.5 py-8 font-semibold whitespace-nowrap">Percobaan</th>
                        <th class="w-1/6 px-7.5 py-8 font-semibold whitespace-nowrap">Error Terakhir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logEmail as $log): ?>
                        <tr class="border-b border-tt-outline last:border-b-0">
                            <td class="px-7.5 py-4 whitespace-nowrap"><?= h($log['dibuat']) ?></td>
                            <td class="px-7.5 py-4">
                                <p class="font-semibold"><?= h($log['nama']) ?></p>
                                <p><?= h($log['email']) ?></p>
                            </td>
                            <td class="px-7.5 py-4">
                                <p class="font-semibold"><?= h($log['kehilangan']) ?></p>
                                <p class="whitespace-nowrap">mungkin sesuai dengan</p>
                                <p class="font-semibold"><?= h($log['temuan']) ?></p>
                            </td>
                            <td class="px-7.5 py-4">
                                <span class="inline-flex rounded-full bg-[#e6f7ed] px-1.5 py-2.5 text-xs font-semibold text-[#17633f]"><?= h($log['status']) ?></span>
                            </td>
                            <td class="px-7.5 py-4"><?= h($log['percobaan']) ?></td>
                            <td class="px-7.5 py-4"><?= h($log['error']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
