<?php

declare(strict_types=1);

/**
 * Daftar Barang Mahasiswa
 * Seluruh data diambil dari PostgreSQL.
 */

require_once __DIR__ . '/../../App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/queries/master_queries.php';
require_once APP_ROOT . '/App/queries/mahasiswa_queries.php';

$currentUser = auth_require_role(['mahasiswa']);

$idPengguna = (int) $currentUser['id_pengguna'];

$pageTitle = 'Daftar Barang';
$menuAktif = 'daftar-barang';

// Membaca filter GET dengan aman.
$namaFilter = is_string($_GET['nama'] ?? null)
    ? trim($_GET['nama'])
    : '';

$jenisFilter = is_string($_GET['jenis'] ?? null)
    ? trim($_GET['jenis'])
    : '';

$lokasiFilter = is_string($_GET['lokasi'] ?? null)
    ? trim($_GET['lokasi'])
    : '';

$kategoriFilter = is_string($_GET['kategori'] ?? null)
    ? trim($_GET['kategori'])
    : '';

// Mengumpulkan seluruh filter.
$filters = [
    'nama' => $namaFilter,
    'jenis' => $jenisFilter,
    'lokasi' => $lokasiFilter,
    'kategori' => $kategoriFilter,
];

// Dropdown kategori menggunakan master database.
$kategoriAktif = get_kategori_aktif();

// Mengambil daftar laporan yang sudah disetujui.
$daftarBarang = search_mahasiswa_public_reports(
    $filters,
    $idPengguna,
    50
);

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/mahasiswa/navbar.php';
?>

<main class="w-full px-4 py-8 md:px-20">

    <!-- Judul halaman -->
    <p class="text-xs font-semibold">
        LOST &amp; FOUND POLINEMA
    </p>

    <h1 class="mt-2 font-display text-2xl font-bold">
        Daftar Barang
    </h1>

    <p class="mt-2 text-xs">
        Cari barang kehilangan dan temuan yang telah diverifikasi.
    </p>

    <!-- Filter pencarian -->
    <form
        action="<?= h(base_url('mahasiswa/daftar-barang.php')) ?>"
        method="GET"
        class="mt-8 rounded-xl border border-indigo-200 bg-white p-4">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">

            <!-- Nama barang -->
            <div>
                <label
                    for="filter-nama"
                    class="block font-display text-base font-semibold">
                    Nama Barang
                </label>

                <input
                    type="text"
                    id="filter-nama"
                    name="nama"
                    value="<?= h($namaFilter) ?>"
                    maxlength="150"
                    placeholder="Contoh: Tumbler, Earbuds"
                    class="mt-2 h-12 w-full rounded-[10px] border border-tt-outline px-3 text-sm placeholder:text-tt-outline">
            </div>

            <!-- Jenis laporan -->
            <div>
                <label
                    for="filter-jenis"
                    class="block font-display text-base font-semibold">
                    Jenis
                </label>

                <select
                    id="filter-jenis"
                    name="jenis"
                    class="mt-2 h-12 w-full rounded-[10px] border border-tt-outline bg-white px-3 text-sm">
                    <option value="">Semua Jenis</option>

                    <option
                        value="temuan"
                        <?= $jenisFilter === 'temuan' ? 'selected' : '' ?>>
                        Barang Temuan
                    </option>

                    <option
                        value="hilang"
                        <?= $jenisFilter === 'hilang' ? 'selected' : '' ?>>
                        Barang Kehilangan
                    </option>
                </select>
            </div>

            <!-- Lokasi -->
            <div>
                <label
                    for="filter-lokasi"
                    class="block font-display text-base font-semibold">
                    Lokasi Kejadian
                </label>

                <input
                    type="text"
                    id="filter-lokasi"
                    name="lokasi"
                    value="<?= h($lokasiFilter) ?>"
                    maxlength="200"
                    placeholder="Contoh: Lobby Gedung Sipil"
                    class="mt-2 h-12 w-full rounded-[10px] border border-tt-outline px-3 text-sm placeholder:text-tt-outline">
            </div>

            <!-- Kategori -->
            <div>
                <label
                    for="filter-kategori"
                    class="block font-display text-base font-semibold">
                    Kategori Barang
                </label>

                <select
                    id="filter-kategori"
                    name="kategori"
                    class="mt-2 h-12 w-full rounded-[10px] border border-tt-outline bg-white px-3 text-sm">
                    <option value="">Semua Kategori</option>

                    <?php foreach ($kategoriAktif as $kategori): ?>

                        <option
                            value="<?= h($kategori['nama_kategori']) ?>"
                            <?= $kategoriFilter === $kategori['nama_kategori'] ? 'selected' : '' ?>>
                            <?= h($kategori['nama_kategori']) ?>
                        </option>

                    <?php endforeach; ?>

                </select>
            </div>

        </div>

        <!-- Tombol filter -->
        <div class="mt-6 flex gap-2">

            <button
                type="submit"
                class="rounded-lg bg-tt-navy px-3 py-3 text-sm font-semibold text-white hover:bg-tt-orange hover:text-tt-navy">
                Cari Barang
            </button>

            <a
                href="<?= h(base_url('mahasiswa/daftar-barang.php')) ?>"
                class="rounded-lg border border-tt-navy px-3 py-3 text-sm font-semibold hover:bg-indigo-100">
                Reset
            </a>

        </div>

    </form>

    <!-- Ringkasan hasil -->
    <div class="mt-8 flex items-center justify-between">
        <h2 class="font-display text-lg font-semibold">
            Hasil Pencarian
        </h2>

        <p class="text-sm text-gray-500">
            <?= count($daftarBarang) ?> barang ditemukan
        </p>
    </div>

    <!-- Kartu barang -->
    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:px-6">

        <?php if ($daftarBarang === []): ?>

            <div class="col-span-full rounded-xl border border-indigo-200 bg-white p-10 text-center">

                <h3 class="font-display text-lg font-semibold">
                    Barang Tidak Ditemukan
                </h3>

                <p class="mt-2 text-sm text-gray-500">
                    Belum ada barang yang sesuai dengan pencarianmu.
                </p>

                <a
                    href="<?= h(base_url('mahasiswa/daftar-barang.php')) ?>"
                    class="mt-4 inline-block text-sm font-semibold text-tt-orange">
                    Hapus Filter
                </a>

            </div>

        <?php else: ?>

            <?php foreach ($daftarBarang as $barang): ?>

                <?php
                $idBarang = (int) $barang['id_pengumuman'];

                if ($barang['milik_saya']) {
                    $detailUrl = base_url(
                        'mahasiswa/laporan/detail.php?id=' . $idBarang
                    );
                } elseif ($barang['jenis'] === 'temuan') {
                    $detailUrl = base_url(
                        'mahasiswa/klaim/detail.php?id=' . $idBarang
                    );
                } else {
                    $detailUrl = base_url(
                        'Detail-Kehilangan.php?id=' . $idBarang
                    );
                }
                ?>

                <article class="rounded-xl border border-indigo-200 bg-white p-4">

                    <!-- Foto barang -->
                    <div class="relative">

                        <div class="flex h-48 w-full items-center justify-center rounded-lg bg-slate-100 text-sm text-slate-500">
                            Foto belum dapat ditampilkan
                        </div>

                        <?php if ($barang['jenis'] === 'temuan'): ?>

                            <span class="absolute left-2 top-2 rounded-full bg-sky-100 px-2 py-1 text-xs text-sky-600">
                                Barang Temuan
                            </span>

                        <?php else: ?>

                            <span class="absolute left-2 top-2 rounded-full bg-orange-100 px-2 py-1 text-xs text-tt-orange">
                                Kehilangan Barang
                            </span>

                        <?php endif; ?>

                    </div>

                    <!-- Informasi barang -->
                    <h3 class="mt-4 font-display text-lg font-semibold">
                        <?= h($barang['judul']) ?>
                    </h3>

                    <p class="mt-1 text-sm text-gray-600">
                        <?= h($barang['deskripsi']) ?>
                    </p>

                    <div class="mt-3 space-y-2 text-sm">

                        <p>
                            <span class="font-semibold">Kategori:</span>
                            <?= h($barang['nama_kategori']) ?>
                        </p>

                        <p>
                            <span class="font-semibold">Lokasi:</span>
                            <?= h($barang['lokasi_kejadian']) ?>
                        </p>

                        <p class="text-xs text-gray-500">
                            <?= h(format_date_time($barang['tanggal_waktu_kejadian'])) ?>
                        </p>

                    </div>

                    <!-- Detail -->
                    <a
                        href="<?= h($detailUrl) ?>"
                        class="mt-6 inline-block text-sm font-semibold hover:text-tt-orange">
                        Lihat Detail &rarr;
                    </a>

                </article>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>