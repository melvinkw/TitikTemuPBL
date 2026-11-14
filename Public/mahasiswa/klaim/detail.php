<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/queries/klaim_queries.php';

$currentUser = auth_require_role(['mahasiswa']);

$idPengumuman = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$idPengumuman || $idPengumuman < 1) {
    http_response_code(404);
    exit('Laporan tidak ditemukan.');
}

$pdo = db();
$barang = get_mahasiswa_claimable_report(
    $pdo,
    (int) $idPengumuman,
    (int) $currentUser['id_pengguna']
);

if ($barang === null) {
    http_response_code(404);
    exit('Barang tidak tersedia atau tidak dapat diklaim.');
}

$klaimAktif = find_mahasiswa_active_claim(
    $pdo,
    (int) $idPengumuman,
    (int) $currentUser['id_pengguna']
);

$pesan = flash();
$pageTitle = 'Detail Klaim';
$menuAktif = '';

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/mahasiswa/navbar.php';
?>

<main class="w-full px-8 py-6 md:px-20">
    <a href="<?= h(base_url('mahasiswa/daftar-barang.php')) ?>"
       class="text-sm font-semibold hover:text-tt-orange">&lt;&lt; Kembali ke daftar</a>

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
                <?= h($barang['nama_kategori']) ?>
            </span>
            <h1 class="mt-3 font-display text-2xl font-bold"><?= h($barang['judul']) ?></h1>
            <p class="mt-6 text-sm"><?= h($barang['lokasi_kejadian']) ?></p>
            <p class="mt-3 text-sm">
                <?= h(format_date_time((string) $barang['tanggal_waktu_kejadian'])) ?>
            </p>
            <p class="mt-3 text-sm">Pelapor: <?= h($barang['nama_pelapor']) ?></p>

            <h2 class="mt-6 font-display text-xl font-bold">Deskripsi barang</h2>
            <p class="mt-2 whitespace-pre-line text-sm leading-6"><?= h($barang['deskripsi']) ?></p>
        </section>

        <section class="rounded-xl border border-indigo-200 bg-white p-8">
            <h2 class="font-display text-2xl font-bold">Status Laporan</h2>
            <span class="mt-3 inline-block rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-700">
                Tersedia untuk diklaim
            </span>
            <p class="mt-3 text-xs">
                Laporan dibuat pada <?= h(format_date_time((string) $barang['created_at'])) ?>.
            </p>

            <h2 class="mt-10 font-display text-2xl font-bold">Merasa ini barangmu?</h2>
            <p class="mt-3 text-sm">
                Jelaskan ciri khusus yang dapat membantu petugas memverifikasi kepemilikan.
                Jangan hanya menuliskan informasi yang sudah terlihat pada halaman ini.
            </p>

            <?php if ($klaimAktif !== null): ?>
                <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    Kamu sudah memiliki klaim dengan status
                    <strong><?= h($klaimAktif['status_verifikasi']) ?></strong>
                    untuk barang ini. Pengajuan baru tidak dapat dibuat selama klaim tersebut aktif.
                </div>
            <?php else: ?>
                <form action="<?= h(base_url('mahasiswa/klaim/simpan.php')) ?>"
                      method="POST"
                      enctype="multipart/form-data"
                      class="mt-6">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id_pengumuman" value="<?= (int) $barang['id_pengumuman'] ?>">

                    <label for="deskripsi_bukti" class="block font-display text-base font-semibold">
                        Bukti / ciri kepemilikan *
                    </label>
                    <textarea id="deskripsi_bukti"
                              name="deskripsi_bukti"
                              rows="5"
                              maxlength="3000"
                              required
                              class="mt-2 w-full rounded-[10px] border border-tt-outline p-3 text-sm"
                              placeholder="Contoh: ciri khusus, isi, tanda, atau detail yang hanya diketahui pemilik."></textarea>

                    <label for="bukti" class="mt-5 block font-display text-base font-semibold">
                        Foto bukti kepemilikan *
                    </label>
                    <input id="bukti"
                           type="file"
                           name="bukti"
                           accept=".jpg,.jpeg,.png,.webp,.pdf,image/jpeg,image/png,image/webp,application/pdf"
                           required
                           class="mt-2 block w-full rounded-[10px] border border-tt-outline p-3 text-sm">
                    <p class="mt-2 text-xs text-gray-600">JPG, PNG, WebP, atau PDF. Maksimal 5 MB. Satu file sesuai schema database saat ini.</p>

                    <button type="submit"
                            class="mt-6 w-full rounded-lg bg-tt-navy py-3 text-sm font-semibold text-white hover:bg-tt-orange hover:text-tt-navy">
                        Ajukan Klaim
                    </button>
                </form>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
