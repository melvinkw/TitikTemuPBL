<?php

declare(strict_types=1);

/**
 * Public/daftar-barang.php
 * Daftar barang untuk pengunjung (belum login).
 * Seluruh data diambil dari PostgreSQL.
 */

require_once __DIR__ . '/../App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/queries/public_queries.php';
require_once APP_ROOT . '/App/services/supabase_storage.php';

// Pengguna yang sudah login diarahkan ke daftar barang versi mahasiswa.
if (auth_current_user() !== null) {
    redirect('/mahasiswa/daftar-barang.php');
}

$pageTitle = 'Daftar Barang';
$menuAktif = 'daftar-barang';

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

$filters = [
    'nama' => $namaFilter,
    'jenis' => $jenisFilter,
    'lokasi' => $lokasiFilter,
    'kategori' => $kategoriFilter,
];

$daftarBarang = [];
$daftarError = null;

try {
    $daftarBarang = search_public_reports($filters, 50);
} catch (Throwable $error) {
    error_log(
        '[TitikTemu] Gagal memuat daftar barang publik: '
        . $error->getMessage()
    );

    $daftarError = 'Daftar barang belum dapat dimuat. Silakan coba lagi.';
}

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/public/navbar.php';

?>

<main class="w-full px-4 py-8 md:px-20">

    <p class="text-xs font-semibold">LOST &amp; FOUND POLINEMA</p>

    <h1 class="mt-2 font-display text-2xl font-bold">
        Daftar Barang
    </h1>

    <p class="mt-2 text-xs">
        Cari barang kehilangan dan temuan yang telah diverifikasi petugas.
    </p>

    <!-- Search Filter -->
    <form action="<?= h(base_url('daftar-barang.php')) ?>" method="GET" class="mt-8 rounded-xl border border-indigo-200 bg-white p-4">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">

            <div>
                <label for="filter-nama" class="block font-display text-base font-semibold">
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

            <div>
                <label for="filter-jenis" class="block font-display text-base font-semibold">
                    Jenis
                </label>

                <select
                    id="filter-jenis"
                    name="jenis"
                    class="mt-2 h-12 w-full rounded-[10px] border border-tt-outline bg-white px-3 text-sm">
                    <option value="">Semua Jenis</option>
                    <option value="temuan" <?= $jenisFilter === 'temuan' ? 'selected' : '' ?>>
                        Barang Temuan
                    </option>
                    <option value="hilang" <?= $jenisFilter === 'hilang' ? 'selected' : '' ?>>
                        Barang Kehilangan
                    </option>
                </select>
            </div>

            <div>
                <label for="filter-lokasi" class="block font-display text-base font-semibold">
                    Lokasi Kejadian
                </label>

                <input
                    type="text"
                    id="filter-lokasi"
                    name="lokasi"
                    value="<?= h($lokasiFilter) ?>"
                    maxlength="200"
                    placeholder="Contoh: Lobby Gedung Sipil lt 1"
                    class="mt-2 h-12 w-full rounded-[10px] border border-tt-outline px-3 text-sm placeholder:text-tt-outline">
            </div>

            <div>
                <label for="filter-kategori" class="block font-display text-base font-semibold">
                    Kategori Barang
                </label>

                <input
                    type="text"
                    id="filter-kategori"
                    name="kategori"
                    value="<?= h($kategoriFilter) ?>"
                    maxlength="100"
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
                href="<?= h(base_url('daftar-barang.php')) ?>"
                class="rounded-lg border border-tt-navy px-3 py-3 text-sm font-semibold hover:bg-indigo-100">
                Reset
            </a>

        </div>

    </form>

    <div class="mt-8 flex items-center justify-between">
        <h2 class="font-display text-lg font-semibold">Hasil Pencarian</h2>

        <p class="text-sm text-gray-500">
            <?= count($daftarBarang) ?> barang ditemukan
        </p>
    </div>

    <?php if ($daftarError !== null): ?>

        <div role="alert" class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <?= h($daftarError) ?>
        </div>

    <?php endif; ?>

    <!-- Card -->
    <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-4 lg:px-6">

        <?php if ($daftarBarang === []): ?>

            <div class="col-span-full rounded-xl border border-indigo-200 bg-white p-10 text-center">

                <h3 class="font-display text-lg font-semibold">
                    Barang Tidak Ditemukan
                </h3>

                <p class="mt-2 text-sm text-gray-500">
                    Belum ada barang yang sesuai dengan pencarianmu.
                </p>

                <a
                    href="<?= h(base_url('daftar-barang.php')) ?>"
                    class="mt-4 inline-block text-sm font-semibold text-tt-orange">
                    Hapus Filter
                </a>

            </div>

        <?php else: ?>

            <?php foreach ($daftarBarang as $barang): ?>

                <?php
                $idBarang = (int) $barang['id_pengumuman'];
                $fotoUrl = null;

                if (!empty($barang['foto_bucket']) && !empty($barang['foto_path'])) {
                    try {
                        $fotoUrl = supabase_storage_create_signed_url(
                            $barang['foto_bucket'],
                            $barang['foto_path']
                        );
                    } catch (Throwable $error) {
                        error_log(
                            '[TitikTemu] Gagal menampilkan foto daftar barang publik: '
                            . $error->getMessage()
                        );
                    }
                }

                $detailPage = $barang['jenis'] === 'temuan'
                    ? 'Detail-Temuan.php'
                    : 'Detail-Kehilangan.php';
                ?>

                <article class="rounded-xl border border-indigo-200 bg-white p-4">

                    <!-- Foto + Label Jenis Barang -->
                    <div class="relative">

                        <?php if ($fotoUrl !== null): ?>

                            <img
                                src="<?= h($fotoUrl) ?>"
                                alt="<?= h($barang['judul']) ?>"
                                loading="lazy"
                                class="h-48 w-full rounded-lg object-cover">

                        <?php else: ?>

                            <div class="flex h-48 w-full items-center justify-center rounded-lg bg-slate-100 text-sm text-slate-500">
                                Foto belum tersedia
                            </div>

                        <?php endif; ?>

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
                        <?= h($barang['judul']) ?>
                    </h3>

                    <p class="text-sm">
                        <?= h($barang['deskripsi']) ?>
                    </p>

                    <p class="mt-3 text-sm">
                        <span class="font-semibold">Kategori:</span>
                        <?= h($barang['nama_kategori']) ?>
                    </p>

                    <p class="mt-1 text-sm">
                        <span class="font-semibold">Lokasi:</span>
                        <?= h($barang['lokasi_kejadian']) ?>
                    </p>

                    <p class="mt-3 text-xs text-gray-500">
                        <?= h(format_date_time($barang['tanggal_waktu_kejadian'])) ?>
                    </p>

                    <a
                        href="<?= h(base_url($detailPage . '?id=' . $idBarang)) ?>"
                        class="mt-6 inline-block text-sm font-semibold hover:text-tt-orange">
                        Lihat Detail &rarr;
                    </a>

                </article>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
