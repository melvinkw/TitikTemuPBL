<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/queries/klaim_queries.php';

$currentUser = auth_require_role(['mahasiswa']);

$idKlaim = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$idKlaim || $idKlaim < 1) {
    http_response_code(404);
    exit('Klaim tidak ditemukan.');
}

$klaim = get_mahasiswa_claim_confirmation_detail(
    db(),
    (int) $idKlaim,
    (int) $currentUser['id_pengguna']
);

if ($klaim === null) {
    http_response_code(404);
    exit('Klaim tidak ditemukan.');
}

$pesan = flash();
$pageTitle = 'Konfirmasi Penerimaan Barang';
$menuAktif = 'riwayat';

$statusLabel = static fn (string $status): string => match ($status) {
    'menunggu' => 'Menunggu Verifikasi',
    'siap_diambil' => 'Barang Siap Diambil',
    'diterima' => 'Barang Sudah Diterima',
    'ditolak' => 'Ditolak',
    'dibatalkan' => 'Dibatalkan',
    default => $status,
};

$sudahDiterima = $klaim['status_verifikasi'] === 'diterima';
$siapDiambil = $klaim['status_verifikasi'] === 'siap_diambil';

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/mahasiswa/navbar.php';
?>

<main class="w-full px-4 py-8 md:px-20">
    <a href="<?= h(base_url('mahasiswa/riwayat.php')) ?>"
       class="text-sm font-semibold hover:text-tt-orange">&lt;&lt; Kembali ke riwayat</a>

    <?php if ($pesan !== null): ?>
        <div class="mt-5 rounded-lg border px-4 py-3 text-sm <?= ($pesan['type'] ?? '') === 'success'
            ? 'border-green-200 bg-green-50 text-green-800'
            : 'border-red-200 bg-red-50 text-red-800' ?>"
             role="status">
            <?= h($pesan['message'] ?? '') ?>
        </div>
    <?php endif; ?>

    <div class="mt-8 grid grid-cols-1 items-start gap-8 lg:grid-cols-2">
        <section class="rounded-xl border border-indigo-200 bg-white p-8">
            <span class="rounded-full bg-purple-100 px-2 py-1 text-xs font-semibold text-purple-600">
                <?= h($klaim['nama_kategori']) ?>
            </span>
            <h1 class="mt-3 font-display text-2xl font-bold"><?= h($klaim['judul']) ?></h1>

            <dl class="mt-6 space-y-3 text-sm">
                <div>
                    <dt class="text-xs text-gray-500">Lokasi kejadian</dt>
                    <dd><?= h($klaim['lokasi_kejadian']) ?></dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Waktu kejadian</dt>
                    <dd><?= h(format_date_time((string) $klaim['tanggal_waktu_kejadian'])) ?></dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Klaim disetujui</dt>
                    <dd><?= h(format_date_time($klaim['tanggal_verifikasi'])) ?></dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-500">Ambil barang di</dt>
                    <dd class="font-semibold"><?= h($klaim['nama_pos'] ?? '-') ?></dd>
                    <dd class="text-gray-600"><?= h($klaim['nama_lokasi'] ?? '-') ?></dd>
                    <?php if (!empty($klaim['detail_pos'])): ?>
                        <dd class="text-gray-600"><?= h($klaim['detail_pos']) ?></dd>
                    <?php endif; ?>
                </div>
            </dl>

            <h2 class="mt-6 font-display text-xl font-bold">Deskripsi barang</h2>
            <p class="mt-2 whitespace-pre-line text-sm leading-6"><?= h($klaim['deskripsi']) ?></p>
        </section>

        <section class="rounded-xl border border-indigo-200 bg-white p-8">
            <?php if ($sudahDiterima): ?>
                <h2 class="font-display text-2xl font-bold">Barang sudah diterima</h2>
                <p class="mt-3 text-sm">
                    Dikonfirmasi pada <?= h(format_date_time($klaim['tanggal_diterima'])) ?>.
                </p>
                <div class="mt-5 rounded-lg bg-slate-50 p-4 text-sm">
                    <p class="font-semibold">
                        Rating: <?= h($klaim['rating_feedback'] ?? '-') ?>/5
                    </p>
                    <p class="mt-2 whitespace-pre-line text-gray-600">
                        <?= h($klaim['komentar_feedback'] ?: 'Tanpa komentar.') ?>
                    </p>
                </div>
            <?php elseif (!$siapDiambil): ?>
                <h2 class="font-display text-2xl font-bold">Belum siap dikonfirmasi</h2>
                <p class="mt-3 text-sm">
                    Status klaim saat ini: <strong><?= h($statusLabel((string) $klaim['status_verifikasi'])) ?></strong>.
                    Konfirmasi penerimaan hanya dapat dilakukan setelah petugas menyetujui klaim
                    dan barang siap diambil.
                </p>
            <?php else: ?>
                <h2 class="font-display text-2xl font-bold">Konfirmasi Barang Diterima</h2>
                <p class="mt-3 text-sm">
                    Lakukan konfirmasi setelah kamu benar-benar mengambil barang di
                    <strong><?= h($klaim['nama_pos'] ?? 'pos penyerahan') ?></strong>.
                    Status klaim akan menjadi <strong>Barang Sudah Diterima</strong>.
                </p>

                <form action="<?= h(base_url('mahasiswa/klaim/konfirmasi-proses.php')) ?>"
                      method="POST"
                      class="mt-6 space-y-5">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_klaim" value="<?= (int) $klaim['id_klaim'] ?>">

                    <fieldset>
                        <legend class="font-display text-base font-semibold">
                            Rating pelayanan *
                        </legend>
                        <div class="mt-3 flex flex-wrap gap-3">
                            <?php for ($rating = 1; $rating <= 5; $rating++): ?>
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-tt-outline px-3 py-2 text-sm">
                                    <input type="radio"
                                           name="rating"
                                           value="<?= $rating ?>"
                                           required>
                                    <span><?= $rating ?></span>
                                </label>
                            <?php endfor; ?>
                        </div>
                        <p class="mt-2 text-xs text-gray-600">1 = sangat buruk, 5 = sangat baik.</p>
                    </fieldset>

                    <div>
                        <label for="komentar" class="block font-display text-base font-semibold">
                            Feedback
                        </label>
                        <textarea id="komentar"
                                  name="komentar"
                                  rows="4"
                                  maxlength="1000"
                                  class="mt-2 w-full rounded-[10px] border border-tt-outline p-3 text-sm"
                                  placeholder="Ceritakan pengalamanmu menggunakan layanan TitikTemu (opsional)."></textarea>
                    </div>

                    <label class="flex items-start gap-3 text-sm">
                        <input type="checkbox" required class="mt-1">
                        <span>Saya sudah mengambil barang ini dan data di atas sudah benar.</span>
                    </label>

                    <button type="submit"
                            class="w-full rounded-lg bg-tt-navy py-3 text-sm font-semibold text-white hover:bg-tt-orange hover:text-tt-navy"
                            onclick="return confirm('Konfirmasi bahwa barang sudah kamu terima?')">
                        Konfirmasi Barang Sudah Diterima
                    </button>
                </form>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
