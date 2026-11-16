<?php

require_once __DIR__ . '/../App/config/bootstrap.php';

$pageTitle = 'Daftar Barang';
$menuAktif = 'daftar-barang';

$daftarBarang = [
    [
        'jenis' => 'temuan',
        'nama' => 'Tumbler Hitam',
        'deskripsi' => 'Ini tumbler warna hitam pekat',
        'lokasi' => 'LPR 8',
        'waktu' => '24 Oktober 2026 12:45',
        'milik_saya' => true
    ],
    [
        'jenis' => 'temuan',
        'nama' => 'Tumbler Hitam',
        'deskripsi' => 'Ini tumbler warna hitam pekat',
        'lokasi' => 'LPR 8',
        'waktu' => '24 Oktober 2026 12:45',
        'milik_saya' => false
    ],
    [
        'jenis' => 'hilang',
        'nama' => 'Tumbler Hitam',
        'deskripsi' => 'Ini tumbler warna hitam pekat',
        'lokasi' => 'LPR 8',
        'waktu' => '24 Oktober 2026 12:45',
        'milik_saya' => false
    ],
    [
        'jenis' => 'temuan',
        'nama' => 'Tumbler Hitam',
        'deskripsi' => 'Ini tumbler warna hitam pekat',
        'lokasi' => 'LPR 8',
        'waktu' => '24 Oktober 2026 12:45',
        'milik_saya' => false
    ],
    [
        'jenis' => 'temuan',
        'nama' => 'Tumbler Hitam',
        'deskripsi' => 'Ini tumbler warna hitam pekat',
        'lokasi' => 'LPR 8',
        'waktu' => '24 Oktober 2026 12:45',
        'milik_saya' => false
    ],
    [
        'jenis' => 'hilang',
        'nama' => 'Tumbler Hitam',
        'deskripsi' => 'Ini tumbler warna hitam pekat',
        'lokasi' => 'LPR 8',
        'waktu' => '24 Oktober 2026 12:45',
        'milik_saya' => false
    ],
    [
        'jenis' => 'hilang',
        'nama' => 'Tumbler Hitam',
        'deskripsi' => 'Ini tumbler warna hitam pekat',
        'lokasi' => 'LPR 8',
        'waktu' => '24 Oktober 2026 12:45',
        'milik_saya' => false
    ],
    [
        'jenis' => 'temuan',
        'nama' => 'Tumbler Hitam',
        'deskripsi' => 'Ini tumbler warna hitam pekat',
        'lokasi' => 'LPR 8',
        'waktu' => '24 Oktober 2026 12:45',
        'milik_saya' => false
    ],
];

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/public/navbar.php';

?>

<main class="w-full px-4 py-8 md:px-20">

    <p class="text-xs font-semibold">LOST &amp; FOUND POLINEMA</p>

    <h1 class="mt-2 font-display text-2xl font-bold">
        Daftar Barang
    </h1>

    <p class="mt-2 text-xs">
        Lorem ipsum dolor sit amet, consectetur adipiscing elit,
        sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.
    </p>

    <!-- Search Filter -->
    <form action="" method="GET" class="mt-8 rounded-xl border border-indigo-200 bg-white p-4">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">

            <div>
                <label class="block font-display text-base font-semibold">
                    Nama Barang
                </label>

                <input
                    type="text"
                    name="nama"
                    placeholder="Contoh: Tumbler, Earbuds"
                    class="mt-2 h-12 w-full rounded-[10px] border border-tt-outline px-3 text-sm placeholder:text-tt-outline">
            </div>

            <div>
                <label class="block font-display text-base font-semibold">
                    Jenis
                </label>

                <input
                    type="text"
                    name="jenis"
                    placeholder="Contoh: Barang Temuan"
                    class="mt-2 h-12 w-full rounded-[10px] border border-tt-outline px-3 text-sm placeholder:text-tt-outline">
            </div>

            <div>
                <label class="block font-display text-base font-semibold">
                    Lokasi Kejadian
                </label>

                <input
                    type="text"
                    name="lokasi"
                    placeholder="Contoh: Lobby Gedung Sipil lt 1"
                    class="mt-2 h-12 w-full rounded-[10px] border border-tt-outline px-3 text-sm placeholder:text-tt-outline">
            </div>

            <div>
                <label class="block font-display text-base font-semibold">
                    Kategori Barang
                </label>

                <input
                    type="text"
                    name="kategori"
                    placeholder="Contoh: Elektronik"
                    class="mt-2 h-12 w-full rounded-[10px] border border-tt-outline px-3 text-sm placeholder:text-tt-outline">
            </div>

        </div>

        <div class="mt-6 flex gap-2">

            <button
                type="submit"
                class="rounded-lg bg-tt-navy px-3 py-3 text-sm font-semibold text-white hover:bg-tt-orange hover:text-tt-navy">
                Cari Barang
            </button>

            <a
                href="<?= base_url('daftar-barang.php') ?>"
                class="rounded-lg border border-tt-navy px-3 py-3 text-sm font-semibold hover:bg-indigo-100">
                Reset
            </a>

        </div>

    </form>

    <!-- Card -->
    <div class="mt-8 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4 lg:px-6">

        <?php foreach ($daftarBarang as $barang): ?>

            <div class="rounded-xl border border-indigo-200 bg-white p-4">

                <!-- Foto + Label Jenis Barang -->
                <div class="relative">

                    <img
                        src="<?= base_url('assets/images/foto-barang.png') ?>"
                        alt="<?= h($barang['nama']) ?>"
                        class="h-48 w-full rounded-lg object-cover">

                    <?php if ($barang['jenis'] === 'temuan'): ?>

                        <span class="absolute left-2 top-2 rounded-full bg-sky-100 px-2 py-1 text-xs text-sky-500">
                            Barang Temuan
                        </span>

                    <?php else: ?>

                        <span class="absolute left-2 top-2 rounded-full bg-orange-100 px-2 py-1 text-xs text-tt-orange">
                            Kehilangan Barang
                        </span>

                    <?php endif; ?>

                </div>

                <h3 class="mt-4 font-display text-lg font-semibold">
                    <?= h($barang['nama']) ?>
                </h3>

                <p class="text-sm">
                    <?= h($barang['deskripsi']) ?>
                </p>

                <p class="mt-3 text-sm">
                    <?= h($barang['lokasi']) ?>
                </p>

                <p class="mt-3 text-sm">
                    <?= h($barang['waktu']) ?>
                </p>

                <!-- Detail -->
                <?php if ($barang['milik_saya']): ?>

                    <a
                        href="<?= base_url('Detail-Temuan.php') ?>"
                        class="mt-6 inline-block text-sm font-semibold hover:text-tt-orange">
                        Lihat Detail &rarr;
                    </a>

                <?php else: ?>

                    <a
                        href="<?= base_url('Detail-Kehilangan.php') ?>"
                        class="mt-6 inline-block text-sm font-semibold hover:text-tt-orange">
                        Lihat Detail &rarr;
                    </a>

                <?php endif; ?>

            </div>

        <?php endforeach; ?>

    </div>

</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>