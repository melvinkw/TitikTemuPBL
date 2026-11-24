<?php
/**
 * App/views/components/verifikasi-klaim.php
 * Layar Verifikasi Klaim, dipakai bersama oleh petugas dan admin.
 *
 * Halaman pemanggil cukup menyiapkan:
 *   $pageTitle, $menuAktif, header + navbar sesuai perannya, lalu
 *   $klaimRuangKerja = 'RUANG KERJA PETUGAS';   // label di atas judul
 *   $klaimHalamanUrl = 'petugas/klaim/verifikasi.php';
 *   $klaimProsesUrl  = 'petugas/klaim/proses.php';
 *
 * Komponen ini yang memuat data dan merender <main>, sehingga daftar klaim,
 * detail bukti, dan form keputusan hanya punya satu sumber markup.
 */

declare(strict_types=1);

require_once APP_ROOT . '/App/queries/petugas_claim_queries.php';
require_once APP_ROOT . '/App/services/supabase_storage.php';

$klaimRuangKerja = $klaimRuangKerja ?? 'RUANG KERJA PETUGAS';
$klaimHalamanUrl = $klaimHalamanUrl ?? 'petugas/klaim/verifikasi.php';
$klaimProsesUrl = $klaimProsesUrl ?? 'petugas/klaim/proses.php';

$pdo = db();
$pesan = flash();

$rawId = $_GET['id'] ?? null;

$idKlaim = is_string($rawId)
    ? filter_var($rawId, FILTER_VALIDATE_INT)
    : false;

if ($idKlaim !== false && $idKlaim !== null && $idKlaim < 1) {
    $idKlaim = false;
}

$error = null;
$claims = [];
$claim = null;
$signedUrl = null;

try {
    $claims = get_petugas_pending_claims($pdo);

    if ($idKlaim !== false && $idKlaim !== null) {
        $claim = get_petugas_claim_detail($pdo, (int) $idKlaim);

        if ($claim === null) {
            http_response_code(404);
            exit('Klaim tidak ditemukan.');
        }

        if (!empty($claim['bukti_path'])) {
            try {
                $signedUrl = supabase_storage_create_signed_url(
                    $claim['bukti_bucket'],
                    $claim['bukti_path'],
                    300
                );
            } catch (Throwable $storageError) {
                error_log(
                    '[TitikTemu] Signed URL bukti klaim gagal: '
                    . $storageError->getMessage()
                );
            }
        }
    }
} catch (Throwable $exception) {
    error_log(
        '[TitikTemu] Gagal memuat verifikasi klaim: '
        . $exception->getMessage()
    );

    $error = 'Data klaim belum dapat dimuat.';
}

$statusLabel = static function (string $status): string {
    return match ($status) {
        'menunggu' => 'Menunggu Verifikasi',
        'siap_diambil' => 'Barang Siap Diambil',
        'diterima' => 'Barang Sudah Diterima',
        'ditolak' => 'Ditolak',
        'dibatalkan' => 'Dibatalkan',
        default => $status,
    };
};
?>

<main class="mx-auto w-full max-w-[1440px] px-5 py-8 sm:px-8 lg:px-12">

    <section class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-wide">
            <?= h($klaimRuangKerja) ?>
        </p>

        <h1 class="mt-2 font-display text-3xl font-bold">
            Verifikasi Klaim
        </h1>

        <p class="mt-2 text-sm text-slate-600">
            Periksa bukti kepemilikan sebelum menyetujui atau menolak klaim.
            Klaim yang disetujui menjadi <strong>Barang Siap Diambil</strong>;
            mahasiswa mengonfirmasi sendiri setelah mengambil barang.
        </p>
    </section>

    <?php if ($pesan !== null): ?>
        <div
            role="status"
            class="mb-6 rounded-xl border px-4 py-3 text-sm
            <?= ($pesan['type'] ?? '') === 'success'
                ? 'border-green-200 bg-green-50 text-green-800'
                : 'border-red-200 bg-red-50 text-red-800' ?>"
        >
            <?= h($pesan['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== null): ?>
        <div role="alert" class="mb-6 rounded-xl bg-red-50 p-4 text-red-700">
            <?= h($error) ?>
        </div>
    <?php else: ?>

        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">

            <!-- DAFTAR KLAIM -->
            <section class="lg:col-span-4">
                <div class="overflow-hidden rounded-2xl border border-tt-outline bg-white">

                    <div class="border-b border-tt-outline p-5">
                        <h2 class="font-display text-xl font-bold">
                            Klaim Menunggu
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            <?= count($claims) ?> klaim belum diperiksa
                        </p>
                    </div>

                    <div class="divide-y divide-slate-100">

                        <?php if ($claims === []): ?>

                            <p class="p-6 text-center text-sm text-slate-500">
                                Tidak ada klaim yang menunggu verifikasi.
                            </p>

                        <?php else: ?>

                            <?php foreach ($claims as $item): ?>

                                <a
                                    href="<?= h(base_url(
                                        $klaimHalamanUrl . '?id='
                                        . (int) $item['id_klaim']
                                    )) ?>"
                                    class="block p-5 transition hover:bg-slate-50
                                    <?= $claim !== null
                                        && (int) $claim['id_klaim'] === (int) $item['id_klaim']
                                        ? 'bg-indigo-50'
                                        : '' ?>"
                                >
                                    <div class="flex items-start justify-between gap-3">

                                        <div class="min-w-0">
                                            <h3 class="font-semibold">
                                                <?= h($item['judul']) ?>
                                            </h3>

                                            <p class="mt-1 text-xs text-slate-500">
                                                <?= h($item['nama_mahasiswa']) ?>
                                                · <?= h($item['nim']) ?>
                                            </p>

                                            <p class="mt-2 text-xs text-slate-500">
                                                <?= h(format_date_time(
                                                    $item['tanggal_pengajuan']
                                                )) ?>
                                            </p>
                                        </div>

                                        <span class="shrink-0 rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">
                                            Menunggu
                                        </span>

                                    </div>
                                </a>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </div>
                </div>
            </section>

            <!-- DETAIL KLAIM -->
            <section class="lg:col-span-8">

                <?php if ($claim === null): ?>

                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
                        <h2 class="font-display text-xl font-bold">
                            Pilih klaim untuk diperiksa
                        </h2>

                        <p class="mt-2 text-sm text-slate-500">
                            Detail mahasiswa dan bukti kepemilikan akan tampil di sini.
                        </p>
                    </div>

                <?php else: ?>

                    <div class="space-y-6">

                        <article class="rounded-2xl border border-tt-outline bg-white p-6">

                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <h2 class="font-display text-2xl font-bold">
                                    <?= h($claim['judul']) ?>
                                </h2>

                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">
                                    <?= h($statusLabel((string) $claim['status_verifikasi'])) ?>
                                </span>
                            </div>

                            <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">

                                <div>
                                    <p class="text-xs text-slate-500">Mahasiswa</p>
                                    <p class="mt-1 font-semibold">
                                        <?= h($claim['nama_mahasiswa']) ?>
                                    </p>
                                    <p class="text-sm text-slate-500">
                                        <?= h($claim['nim']) ?>
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-500">Kategori</p>
                                    <p class="mt-1 font-semibold">
                                        <?= h($claim['nama_kategori']) ?>
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-500">Lokasi kejadian</p>
                                    <p class="mt-1 font-semibold">
                                        <?= h($claim['lokasi_kejadian']) ?>
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-slate-500">Waktu pengajuan</p>
                                    <p class="mt-1 font-semibold">
                                        <?= h(format_date_time(
                                            $claim['tanggal_pengajuan']
                                        )) ?>
                                    </p>
                                </div>

                            </div>

                            <div class="mt-6 border-t border-slate-100 pt-5">
                                <h3 class="font-semibold">Deskripsi barang</h3>
                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">
                                    <?= h($claim['deskripsi_barang']) ?>
                                </p>
                            </div>

                        </article>

                        <article class="rounded-2xl border border-tt-outline bg-white p-6">

                            <h3 class="font-display text-xl font-bold">
                                Bukti Kepemilikan
                            </h3>

                            <div class="mt-4 rounded-xl bg-slate-50 p-5">
                                <p class="whitespace-pre-line text-sm leading-6">
                                    <?= h($claim['deskripsi_bukti']) ?>
                                </p>
                            </div>

                            <div class="mt-5">

                                <?php if ($signedUrl === null): ?>

                                    <p class="rounded-lg bg-amber-50 p-4 text-sm text-amber-800">
                                        Bukti file tidak dapat dimuat. Periksa konfigurasi Supabase Storage.
                                    </p>

                                <?php elseif (
                                    in_array(
                                        strtolower(pathinfo($claim['bukti_path'], PATHINFO_EXTENSION)),
                                        ['jpg', 'jpeg', 'png', 'webp'],
                                        true
                                    )
                                ): ?>

                                    <img
                                        src="<?= h($signedUrl) ?>"
                                        alt="Bukti kepemilikan"
                                        class="max-h-[420px] w-full rounded-xl bg-slate-100 object-contain"
                                    >

                                <?php else: ?>

                                    <a
                                        href="<?= h($signedUrl) ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex rounded-lg bg-tt-navy px-5 py-3 text-sm font-semibold text-white"
                                    >
                                        Lihat dokumen bukti
                                    </a>

                                <?php endif; ?>

                            </div>

                        </article>

                        <?php if ($claim['status_verifikasi'] === 'menunggu'): ?>

                            <article class="rounded-2xl border border-tt-outline bg-white p-6">

                                <h3 class="font-display text-xl font-bold">
                                    Keputusan Verifikasi
                                </h3>

                                <form
                                    action="<?= h(base_url($klaimProsesUrl)) ?>"
                                    method="POST"
                                    class="mt-5 space-y-5"
                                >
                                    <?= csrf_field() ?>

                                    <input
                                        type="hidden"
                                        name="id_klaim"
                                        value="<?= (int) $claim['id_klaim'] ?>"
                                    >

                                    <div>
                                        <label for="catatan" class="block text-sm font-semibold">
                                            Catatan verifikasi
                                        </label>

                                        <textarea
                                            id="catatan"
                                            name="catatan"
                                            rows="4"
                                            maxlength="2000"
                                            class="mt-2 w-full rounded-xl border border-slate-300 p-3 text-sm"
                                            placeholder="Tuliskan alasan atau catatan pemeriksaan..."
                                        ></textarea>

                                        <p class="mt-1 text-xs text-slate-500">
                                            Wajib diisi jika klaim ditolak.
                                        </p>
                                    </div>

                                    <div class="flex flex-col gap-3 sm:flex-row">

                                        <button
                                            type="submit"
                                            name="keputusan"
                                            value="setujui"
                                            class="flex-1 rounded-xl bg-green-700 px-5 py-3 text-sm font-semibold text-white hover:bg-green-800"
                                        >
                                            Setujui Klaim
                                        </button>

                                        <button
                                            type="submit"
                                            name="keputusan"
                                            value="tolak"
                                            onclick="return confirm('Yakin ingin menolak klaim ini?')"
                                            class="flex-1 rounded-xl bg-red-600 px-5 py-3 text-sm font-semibold text-white hover:bg-red-700"
                                        >
                                            Tolak Klaim
                                        </button>

                                    </div>

                                </form>

                            </article>

                        <?php else: ?>

                            <article class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                <p class="font-semibold">
                                    Klaim sudah diverifikasi.
                                </p>

                                <p class="mt-2 text-sm text-slate-600">
                                    Status:
                                    <?= h($statusLabel((string) $claim['status_verifikasi'])) ?>
                                </p>

                                <?php if ($claim['status_verifikasi'] === 'siap_diambil'): ?>
                                    <p class="mt-1 text-sm text-slate-600">
                                        Mahasiswa perlu mengambil barang di pos penyerahan
                                        lalu mengonfirmasi penerimaan lewat web.
                                    </p>
                                <?php endif; ?>

                                <p class="mt-1 text-sm text-slate-600">
                                    Catatan:
                                    <?= h($claim['catatan_verifikasi'] ?? '-') ?>
                                </p>
                            </article>

                        <?php endif; ?>

                    </div>

                <?php endif; ?>

            </section>

        </div>

    <?php endif; ?>

</main>
