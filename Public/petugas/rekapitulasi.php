<?php
/**
 * Public/petugas/rekapitulasi.php
 * Halaman Rekapitulasi Barang untuk petugas (Figma: user-Petugas, frame "Data Master"
 * yang isinya rekapitulasi). Isinya memakai komponen bersama
 * App/views/components/rekapitulasi.php (sama dengan rekap admin).
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/queries/petugas_rekapitulasi_queries.php';

$currentUser = auth_require_role(['petugas']);

$pageTitle = 'Rekapitulasi Barang';
$menuAktif = 'rekapitulasi';
$labelRuangKerja = 'RUANG KERJA PETUGAS';

// Filter bulan dari URL; jatuh ke bulan berjalan kalau tidak valid.
$bulan = $_GET['bulan'] ?? date('Y-m');

if (
    !is_string($bulan)
    || preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan) !== 1
) {
    $bulan = date('Y-m');
}

// Periode memakai zona waktu Indonesia: awal bulan sampai awal bulan berikutnya.
$timezone = new DateTimeZone('Asia/Jakarta');
$tanggalMulai = new DateTimeImmutable($bulan . '-01 00:00:00', $timezone);
$tanggalAkhir = $tanggalMulai->modify('+1 month');

$rekapError = null;

try {
    $rekap = get_petugas_rekapitulasi(
        db(),
        $tanggalMulai->format('Y-m-d H:i:sP'),
        $tanggalAkhir->format('Y-m-d H:i:sP')
    );

    $statistik = [
        ['angka' => $rekap['stats']['total_laporan'], 'label' => 'Total Laporan Pada Bulan Terpilih'],
        ['angka' => $rekap['stats']['laporan_selesai'], 'label' => 'Laporan Berstatus Selesai'],
        ['angka' => $rekap['stats']['total_kategori'], 'label' => 'Kategori Dengan Laporan'],
        ['angka' => $rekap['stats']['total_lokasi'], 'label' => 'Lokasi Kejadian Unik'],
    ];

    // Terjemahkan nilai database supaya tabel tidak menampilkan kode mentah.
    $labelJenis = [
        'hilang' => 'Kehilangan',
        'temuan' => 'Temuan',
    ];

    $labelStatus = [
        'menunggu' => 'Menunggu Pemeriksaan',
        'aktif' => 'Aktif',
        'tersedia' => 'Barang Tersedia',
        'diproses' => 'Siap Diambil',
        'selesai' => 'Selesai',
        'ditolak' => 'Ditolak',
        'ditutup' => 'Ditutup',
    ];

    $perJenisStatus = array_map(
        static fn (array $baris): array => [
            'jenis' => $labelJenis[$baris['jenis']] ?? $baris['jenis'],
            'status' => $labelStatus[$baris['status']] ?? $baris['status'],
            'jumlah' => $baris['jumlah'],
        ],
        $rekap['per_jenis_status']
    );

    $perKategori = $rekap['per_kategori'];
    $perLokasi = $rekap['per_lokasi'];
} catch (Throwable $error) {
    error_log(
        '[TitikTemu] Gagal memuat rekapitulasi: '
        . $error->getMessage()
    );

    $rekapError = 'Data rekapitulasi gagal dimuat. Silakan coba kembali.';
}

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/petugas/navbar.php';
?>

<main class="flex w-full flex-col gap-6.5 px-4 pt-2.5 pb-10 md:px-20">
    <?php require APP_ROOT . '/App/views/components/rekapitulasi.php'; ?>
</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
