<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/petugas/navbar.php';

$pageTitle = 'Lihat Data Master'; 
$currentUser = auth_require_role(['petugas']);

$masterData = [
    'kategori' => [
        ['nama' => 'Alat Tulis', 'deskripsi' => 'Bolpoin warna coklat', 'status' => 'Aktif'],
        ['nama' => 'Alat Tulis', 'deskripsi' => 'Bolpoin warna coklat', 'status' => 'Aktif'],
        ['nama' => 'Alat Tulis', 'deskripsi' => 'Bolpoin warna coklat', 'status' => 'Aktif'],
        ['nama' => 'Alat Tulis', 'deskripsi' => 'Bolpoin warna coklat', 'status' => 'Aktif'],
        ['nama' => 'Alat Tulis', 'deskripsi' => 'Bolpoin warna coklat', 'status' => 'Aktif'],
        ['nama' => 'Dokumen', 'deskripsi' => 'KTM, kartu, surat, dan dokumen', 'status' => 'Aktif'],
        ['nama' => 'Elektronik', 'deskripsi' => 'Barang elektronik milik mahasiswa', 'status' => 'Aktif'],
        ['nama' => 'Pakaian', 'deskripsi' => 'Jaket, kemeja, dan pakaian lainnya', 'status' => 'Aktif'],
        ['nama' => 'Aksesoris', 'deskripsi' => 'Jam tangan, gelang, dan aksesoris', 'status' => 'Aktif'],
        ['nama' => 'Tas', 'deskripsi' => 'Tas dan perlengkapan bawaan', 'status' => 'Aktif'],
        ['nama' => 'Sepatu', 'deskripsi' => 'Sepatu dan alas kaki', 'status' => 'Aktif'],
        ['nama' => 'Kunci', 'deskripsi' => 'Kunci kendaraan atau ruangan', 'status' => 'Aktif'],
        ['nama' => 'Kartu', 'deskripsi' => 'Kartu identitas dan kartu akses', 'status' => 'Aktif'],
        ['nama' => 'Uang', 'deskripsi' => 'Uang tunai yang ditemukan', 'status' => 'Aktif'],
        ['nama' => 'Lainnya', 'deskripsi' => 'Kategori barang lainnya', 'status' => 'Aktif'],
        ['nama' => 'Alat Tulis', 'deskripsi' => 'Pensil dan perlengkapan menulis', 'status' => 'Aktif'],
        ['nama' => 'Elektronik', 'deskripsi' => 'Perangkat elektronik lainnya', 'status' => 'Aktif'],
        ['nama' => 'Dokumen', 'deskripsi' => 'Dokumen akademik', 'status' => 'Aktif'],
        ['nama' => 'Pakaian', 'deskripsi' => 'Atribut kampus', 'status' => 'Aktif'],
        ['nama' => 'Tas', 'deskripsi' => 'Tas kuliah dan ransel', 'status' => 'Aktif'],
        ['nama' => 'Aksesoris', 'deskripsi' => 'Aksesoris pribadi', 'status' => 'Aktif'],
        ['nama' => 'Sepatu', 'deskripsi' => 'Sepatu olahraga', 'status' => 'Aktif'],
        ['nama' => 'Kunci', 'deskripsi' => 'Kunci kendaraan', 'status' => 'Aktif'],
        ['nama' => 'Kartu', 'deskripsi' => 'Kartu mahasiswa', 'status' => 'Aktif'],
        ['nama' => 'Lainnya', 'deskripsi' => 'Barang lainnya', 'status' => 'Aktif'],
        ['nama' => 'Alat Tulis', 'deskripsi' => 'Pulpen dan spidol', 'status' => 'Aktif'],
        ['nama' => 'Elektronik', 'deskripsi' => 'Laptop dan perangkat pendukung', 'status' => 'Aktif'],
        ['nama' => 'Dokumen', 'deskripsi' => 'Dokumen pribadi', 'status' => 'Aktif'],
        ['nama' => 'Pakaian', 'deskripsi' => 'Pakaian mahasiswa', 'status' => 'Aktif'],
        ['nama' => 'Tas', 'deskripsi' => 'Tas pribadi', 'status' => 'Aktif'],
        ['nama' => 'Aksesoris', 'deskripsi' => 'Aksesoris pribadi', 'status' => 'Aktif'],
        ['nama' => 'Sepatu', 'deskripsi' => 'Alas kaki', 'status' => 'Aktif'],
        ['nama' => 'Kunci', 'deskripsi' => 'Kunci ruangan', 'status' => 'Aktif'],
        ['nama' => 'Kartu', 'deskripsi' => 'Kartu akses', 'status' => 'Aktif'],
        ['nama' => 'Lainnya', 'deskripsi' => 'Barang lainnya', 'status' => 'Aktif'],
    ],

    'lokasi' => [
        ['nama' => 'Gedung Informatika', 'deskripsi' => 'Area Gedung Informatika', 'status' => 'Aktif'],
        ['nama' => 'Gedung Sipil', 'deskripsi' => 'Area Gedung Sipil', 'status' => 'Aktif'],
        ['nama' => 'Perpustakaan', 'deskripsi' => 'Area perpustakaan kampus', 'status' => 'Aktif'],
        ['nama' => 'Masjid Kampus', 'deskripsi' => 'Area masjid kampus', 'status' => 'Aktif'],
        ['nama' => 'Kantin', 'deskripsi' => 'Area kantin kampus', 'status' => 'Aktif'],
        ['nama' => 'Lapangan', 'deskripsi' => 'Area lapangan kampus', 'status' => 'Aktif'],
        ['nama' => 'Student Center', 'deskripsi' => 'Area student center', 'status' => 'Aktif'],
        ['nama' => 'Laboratorium', 'deskripsi' => 'Area laboratorium', 'status' => 'Aktif'],
        ['nama' => 'Ruang Kelas', 'deskripsi' => 'Area ruang kelas', 'status' => 'Aktif'],
        ['nama' => 'Lobby', 'deskripsi' => 'Area lobby gedung', 'status' => 'Aktif'],
    ],

    'pos' => [
        ['nama' => 'Pos Keamanan Utama', 'deskripsi' => 'Pos keamanan gerbang utama', 'status' => 'Aktif'],
        ['nama' => 'Pos Gedung Informatika', 'deskripsi' => 'Pos keamanan Gedung Informatika', 'status' => 'Aktif'],
        ['nama' => 'Pos Perpustakaan', 'deskripsi' => 'Pos penerimaan barang perpustakaan', 'status' => 'Aktif'],
        ['nama' => 'Pos Student Center', 'deskripsi' => 'Pos penerimaan Student Center', 'status' => 'Aktif'],
        ['nama' => 'Pos Kampus II', 'deskripsi' => 'Pos penerimaan Kampus II', 'status' => 'Aktif'],
        ['nama' => 'Pos Gedung Sipil', 'deskripsi' => 'Pos penerimaan Gedung Sipil', 'status' => 'Aktif'],
        ['nama' => 'Pos Laboratorium', 'deskripsi' => 'Pos penerimaan laboratorium', 'status' => 'Aktif'],
    ],
];

$tabData = [
    'kategori' => 'Kategori',
    'lokasi' => 'Lokasi Master',
    'pos' => 'Pos penyerahan',
];

?>

<body class="min-h-screen bg-tt-primary font-body text-tt-navy antialiased">

    <main class="mx-auto flex w-full max-w-[1440px] flex-col gap-8 px-5 py-7 sm:px-8 lg:px-20 lg:py-9">

        <section class="space-y-5">

            <div class="space-y-2 px-4">

                <p class="text-sm font-semibold tracking-wide">
                    RUANG KERJA PETUGAS
                </p>

                <h1 class="font-display text-[32px] font-extrabold leading-tight">
                    Lihat Data Master
                </h1>

                <p class="text-xs leading-relaxed text-tt-navy/80">
                    Kategori, lokasi, dan pos penyerahan yang dipakai sebagai pilihan saat mahasiswa membuat laporan.
                </p>

            </div>

            <!-- nav tab -->
            <div class="flex flex-wrap gap-2 px-4">

                <?php foreach ($tabData as $key => $label): ?>

                    <button
                        type="button"
                        data-tab="<?= h($key) ?>"
                        class="master-tab rounded-full border px-4 py-2.5 text-sm font-semibold transition <?= $key === 'kategori'
                            ? 'border-tt-orange bg-tt-orange text-tt-navy'
                            : 'border-tt-outline bg-white text-tt-navy hover:bg-white/70' ?>">

                        <?= h($label) ?>

                    </button>

                <?php endforeach; ?>

            </div>

            <!-- informasi -->
            <div class="mx-4 rounded-xl border-l-4 border-amber-300 bg-amber-50 px-4 py-3 text-xs leading-relaxed text-tt-navy">

                Petugas hanya untuk melihat data master. Perubahan data hanya dapat dilakukan admin.

            </div>

        </section>

        <section class="space-y-3">

            <div class="flex flex-wrap items-center justify-between gap-4 px-4">

                <h2
                    id="table-title"
                    class="font-display text-xl font-semibold">

                    Kategori Barang

                </h2>

                <!-- nav pagination -->
                <div
                    id="pagination"
                    class="flex items-center gap-1 rounded-full bg-white px-3 py-2 shadow-sm">

                    <button
                        type="button"
                        id="prev-page"
                        class="flex h-8 w-8 items-center justify-center rounded-full text-2xl font-semibold leading-none transition hover:bg-tt-tablehead disabled:cursor-not-allowed disabled:opacity-40"
                        aria-label="Halaman sebelumnya">

                        ‹

                    </button>

                    <div
                        id="page-numbers"
                        class="flex items-center gap-1">

                    </div>

                    <button
                        type="button"
                        id="next-page"
                        class="flex h-8 w-8 items-center justify-center rounded-full text-2xl font-semibold leading-none transition hover:bg-tt-tablehead disabled:cursor-not-allowed disabled:opacity-40"
                        aria-label="Halaman berikutnya">

                        ›

                    </button>

                </div>

            </div>

            <!-- tabel  -->
            <div class="overflow-hidden rounded-2xl border border-tt-outline bg-white">

                <div class="overflow-x-auto">

                    <table class="w-full min-w-[850px] border-collapse text-left">

                        <thead class="bg-tt-tablehead">

                            <tr class="font-display text-lg font-semibold">

                                <th class="px-5 py-5">
                                    Nama
                                </th>

                                <th class="px-5 py-5">
                                    Deskripsi
                                </th>

                                <th class="px-5 py-5">
                                    Status
                                </th>

                            </tr>

                        </thead>

                        <!-- isi tabel (js) -->
                        <tbody
                            id="master-table-body"
                            class="divide-y divide-tt-outline text-sm">

                        </tbody>

                    </table>

                </div>

            </div>

        </section>

    </main>

     <?php require APP_ROOT . '/App/views/components/footer-bar.php'; ?>

    <script>
        window.masterData = <?= json_encode(
            $masterData,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ) ?>;

        window.masterTabs = <?= json_encode(
            $tabData,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ) ?>;
    </script>

    <script src="<?= h(base_url('assets/js/data-master.js')) ?>"></script>

</body>

</html>
