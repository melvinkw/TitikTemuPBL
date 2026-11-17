<?php
/**
 * App/views/components/public/detail-barang.php
 * Isi halaman detail barang untuk pengunjung (Figma: Public-Detail Temuan & Public-Detail Kehilangan).
 * Dipakai oleh Public/Detail-Temuan.php dan Public/Detail-Kehilangan.php supaya kodenya tidak dobel.
 *
 * Variabel yang disiapkan halaman:
 *   $barang = [
 *       'kategori' => 'Elektronik', 'nama' => 'TWS Huawei', 'lokasi' => 'LPR 8',
 *       'waktu' => '24 Oktober 2026 12:45', 'pelapor' => 'John Doe',
 *       'status' => 'Aktif', 'dibuat' => '29-10-2026 13:57',
 *       'foto' => ['assets/images/foto-barang.png', ...],   // maksimal 3
 *   ];
 *   $tampilkanAjakanLogin = true;   // kotak "Login terlebih dahulu" (hanya barang temuan)
 */
$tampilkanAjakanLogin = $tampilkanAjakanLogin ?? false;
$foto = array_slice($barang['foto'] ?? [], 0, 3);
?>
<main class="w-full px-4 py-8 md:px-20">
    <a href="<?= h(base_url('daftar-barang.php')) ?>" class="text-sm font-semibold hover:text-tt-orange">&lt;&lt; Kembali ke daftar</a>

    <div class="mt-6 grid grid-cols-1 items-start gap-9 lg:grid-cols-2">

        <!-- Kiri: info barang dan foto -->
        <section class="tt-card flex flex-col gap-4 p-4.5">
            <div class="flex flex-col gap-2 p-4">
                <span class="tt-badge bg-[#EBE5F2] text-[#8A38F5]"><?= h($barang['kategori']) ?></span>
                <h1 class="font-display text-[28px] font-bold"><?= h($barang['nama']) ?></h1>
            </div>
            <p class="px-2.75 text-sm"><?= h($barang['lokasi']) ?></p>
            <p class="px-2.75 text-sm"><?= h($barang['waktu']) ?></p>
            <p class="px-2.75 text-sm"><?= h($barang['pelapor']) ?></p>

            <div class="flex flex-col gap-2 p-4">
                <h2 class="font-display text-[28px] font-bold">Foto Barang</h2>
                <?php if ($foto === []): ?>
                    <p class="text-sm text-tt-outline">Belum ada foto barang.</p>
                <?php else: ?>
                    <div class="grid h-60 grid-cols-2 grid-rows-2 gap-2">
                        <?php foreach ($foto as $urutan => $file): ?>
                            <img src="<?= h(base_url($file)) ?>"
                                 alt="Foto <?= h($barang['nama']) ?> <?= $urutan + 1 ?>"
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
                <span class="tt-badge bg-[#E6F7ED] text-[#17633F]"><?= h($barang['status']) ?></span>
                <p class="text-xs">Laporan dibuat pada <?= h($barang['dibuat']) ?>.</p>
            </div>

            <?php if ($tampilkanAjakanLogin): ?>
                <!-- Pengunjung harus login dulu untuk mengajukan klaim -->
                <a href="<?= h(base_url('login.php')) ?>"
                   class="flex w-full max-w-106 items-center border-l-[6px] border-[#E48F31] bg-[#FFF4EE] p-4 text-xs text-[#E48F31] hover:underline">
                    Login terlebih dahulu sebelum melanjutkan
                </a>
            <?php endif; ?>
        </section>
    </div>
</main>
