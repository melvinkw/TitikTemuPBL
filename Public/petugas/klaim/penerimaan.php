<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/queries/petugas_penerimaan_queries.php';

$currentUser = auth_require_role(['petugas']);

$pdo = db();

$pesan = flash();

$error = null;
$queue = [];
$received = [];

try {
    $queue = get_petugas_pickup_queue($pdo);
    $received = get_petugas_received_claims($pdo, 20);
} catch (Throwable $exception) {
    error_log(
        '[TitikTemu] Gagal memuat penerimaan barang: '
        . $exception->getMessage()
    );

    $error = 'Data penerimaan barang belum dapat dimuat.';
}

$formatTanggal = static function ($tanggal): string {
    return format_date_time($tanggal === null ? null : (string) $tanggal);
};

$pageTitle = 'Penerimaan Barang';
$menuAktif = 'penerimaan-barang';

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/petugas/navbar.php';
?>

<main class="mx-auto w-full max-w-[1440px] px-5 py-8 sm:px-8 lg:px-12">

    <section class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-wide">
            Ruang Kerja Petugas
        </p>

        <h1 class="mt-2 font-display text-3xl font-bold">
            Penerimaan Barang
        </h1>

        <p class="mt-2 text-sm text-slate-600">
            Klaim yang sudah disetujui berstatus <strong>Barang Siap Diambil</strong>.
            Mahasiswa yang mengambil barang akan mengonfirmasi sendiri lewat web,
            lengkap dengan feedback.
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

        <section class="rounded-2xl border border-tt-outline bg-white">

            <div class="border-b border-tt-outline p-5">
                <h2 class="font-display text-xl font-bold">
                    Menunggu Diambil
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    <?= count($queue) ?> barang siap diambil pemiliknya
                </p>
            </div>

            <div class="overflow-x-auto">

                <table class="w-full text-left text-sm">

                    <thead class="border-b border-tt-outline bg-tt-tablehead">
                        <tr>
                            <th class="px-5 py-3 font-semibold whitespace-nowrap">Barang</th>
                            <th class="px-5 py-3 font-semibold whitespace-nowrap">Penerima</th>
                            <th class="px-5 py-3 font-semibold whitespace-nowrap">Pos</th>
                            <th class="px-5 py-3 font-semibold whitespace-nowrap">Disetujui</th>
                            <th class="px-5 py-3 font-semibold whitespace-nowrap">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        <?php if ($queue === []): ?>

                            <tr>
                                <td colspan="5" class="px-5 py-6 text-center text-slate-500">
                                    Belum ada barang yang menunggu diambil.
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($queue as $row): ?>

                                <tr>
                                    <td class="px-5 py-3">
                                        <p class="font-semibold"><?= h($row['judul']) ?></p>
                                        <p class="text-xs text-slate-500">
                                            <?= h($row['nama_kategori']) ?>
                                            · <?= h($row['lokasi_kejadian']) ?>
                                        </p>
                                    </td>
                                    <td class="px-5 py-3">
                                        <?= h($row['nama_mahasiswa']) ?>
                                        <p class="text-xs text-slate-500">
                                            <?= h($row['nim']) ?>
                                        </p>
                                    </td>
                                    <td class="px-5 py-3">
                                        <?= h($row['nama_pos'] ?? '-') ?>
                                        <p class="text-xs text-slate-500">
                                            <?= h($row['nama_lokasi'] ?? '-') ?>
                                        </p>
                                    </td>
                                    <td class="px-5 py-3 whitespace-nowrap">
                                        <?= h($formatTanggal($row['tanggal_verifikasi'])) ?>
                                    </td>
                                    <td class="px-5 py-3 whitespace-nowrap">
                                        <span class="rounded-full bg-sky-100 px-2 py-1 text-xs font-semibold text-sky-700">
                                            Siap Diambil
                                        </span>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

        <section class="mt-10">

            <h2 class="mb-4 font-display text-xl font-bold">
                Sudah Diterima Mahasiswa
            </h2>

            <div class="overflow-x-auto rounded-2xl border border-tt-outline bg-white">

                <table class="w-full text-left text-sm">

                    <thead class="border-b border-tt-outline bg-tt-tablehead">
                        <tr>
                            <th class="px-5 py-3 font-semibold whitespace-nowrap">Barang</th>
                            <th class="px-5 py-3 font-semibold whitespace-nowrap">Penerima</th>
                            <th class="px-5 py-3 font-semibold whitespace-nowrap">Dikonfirmasi</th>
                            <th class="px-5 py-3 font-semibold whitespace-nowrap">Rating</th>
                            <th class="px-5 py-3 font-semibold whitespace-nowrap">Feedback</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        <?php if ($received === []): ?>

                            <tr>
                                <td colspan="5" class="px-5 py-6 text-center text-slate-500">
                                    Belum ada konfirmasi penerimaan dari mahasiswa.
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($received as $row): ?>

                                <tr>
                                    <td class="px-5 py-3"><?= h($row['judul']) ?></td>
                                    <td class="px-5 py-3">
                                        <?= h($row['nama_mahasiswa']) ?>
                                        <p class="text-xs text-slate-500">
                                            <?= h($row['nim']) ?>
                                        </p>
                                    </td>
                                    <td class="px-5 py-3 whitespace-nowrap">
                                        <?= h($formatTanggal($row['tanggal_diterima'])) ?>
                                    </td>
                                    <td class="px-5 py-3 whitespace-nowrap">
                                        <?= h($row['rating_feedback'] ?? '-') ?>/5
                                    </td>
                                    <td class="px-5 py-3 text-slate-600">
                                        <?= h($row['komentar_feedback'] ?: '-') ?>
                                    </td>
                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    <?php endif; ?>

</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
