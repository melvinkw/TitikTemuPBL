<?php
/**
 * Public/admin/kategori/lokasi.php
 * Data Master — tab Lokasi (Figma: Data Master-lokasi).
 *
 * Form tambah/edit dan tombol aktif/nonaktif mengirim POST ke halaman ini
 * sendiri lalu redirect. Logika penulisan ada di App/actions/master_actions.php.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/queries/master_queries.php';
require_once APP_ROOT . '/App/actions/master_actions.php';

$currentUser = auth_require_role(['admin']);

$halamanIni = 'admin/kategori/lokasi.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post();
    verify_csrf();

    $aksi = is_string($_POST['aksi'] ?? null) ? $_POST['aksi'] : '';

    try {
        switch ($aksi) {
            case 'tambah':
                create_lokasi(
                    db(),
                    is_string($_POST['nama_lokasi'] ?? null) ? $_POST['nama_lokasi'] : '',
                    is_string($_POST['deskripsi'] ?? null) ? $_POST['deskripsi'] : null
                );

                flash([
                    'type' => 'success',
                    'message' => 'Lokasi berhasil ditambahkan.',
                ]);
                break;

            case 'ubah':
                update_lokasi(
                    db(),
                    (int) ($_POST['id_lokasi'] ?? 0),
                    is_string($_POST['nama_lokasi'] ?? null) ? $_POST['nama_lokasi'] : '',
                    is_string($_POST['deskripsi'] ?? null) ? $_POST['deskripsi'] : null
                );

                flash([
                    'type' => 'success',
                    'message' => 'Lokasi berhasil diperbarui.',
                ]);
                break;

            case 'toggle':
                $idLokasi = (int) ($_POST['id_lokasi'] ?? 0);
                $aktifBaru = ($_POST['is_active'] ?? '') === '1';

                set_lokasi_active(db(), $idLokasi, $aktifBaru);

                flash([
                    'type' => 'success',
                    'message' => $aktifBaru
                        ? 'Lokasi diaktifkan.'
                        : 'Lokasi dinonaktifkan.',
                ]);
                break;

            default:
                throw new InvalidArgumentException(
                    'Aksi lokasi tidak dikenal.'
                );
        }
    } catch (InvalidArgumentException | RuntimeException $error) {
        flash([
            'type' => 'error',
            'message' => $error->getMessage(),
        ]);
    } catch (Throwable $error) {
        error_log(
            '[TitikTemu] Data master lokasi gagal: '
            . get_class($error)
            . ' - '
            . $error->getMessage()
        );

        flash([
            'type' => 'error',
            'message' => 'Lokasi belum dapat disimpan. Silakan coba lagi.',
        ]);
    }

    redirect('/' . $halamanIni);
}

$pesan = flash();

$daftarLokasi = [];

try {
    $daftarLokasi = get_all_lokasi(db());
} catch (Throwable $error) {
    error_log(
        '[TitikTemu] Gagal memuat lokasi: ' . $error->getMessage()
    );

    $pesan = [
        'type' => 'error',
        'message' => 'Daftar lokasi belum dapat dimuat.',
    ];
}

$editId = filter_var($_GET['edit'] ?? null, FILTER_VALIDATE_INT);
$lokasiEdit = null;

if ($editId !== false && $editId !== null && $editId > 0) {
    foreach ($daftarLokasi as $lokasi) {
        if ((int) $lokasi['id_lokasi'] === (int) $editId) {
            $lokasiEdit = $lokasi;
            break;
        }
    }
}

$pageTitle = 'Data Master — Lokasi';
$menuAktif = 'data-master';
$tabAktif = 'lokasi';
$judulHalaman = 'Data Master';
$deskripsiHalaman = 'Kelola kategori barang, lokasi kejadian, dan pos penyerahan yang dipakai di formulir laporan.';

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/admin/navbar.php';
?>

<main class="flex w-full flex-col gap-6.5 px-4 pt-2.5 pb-10 md:px-20">

    <div class="flex flex-col gap-4">
        <?php require APP_ROOT . '/App/views/components/judul-halaman.php'; ?>
        <?php require APP_ROOT . '/App/views/components/admin/tab-data-master.php'; ?>
    </div>

    <?php require APP_ROOT . '/App/views/components/alert.php'; ?>

    <!-- Form tambah/edit lokasi -->
    <form method="post" action="<?= h(base_url($halamanIni)) ?>" class="tt-card flex flex-col gap-4 p-4.5">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="<?= $lokasiEdit === null ? 'tambah' : 'ubah' ?>">
        <?php if ($lokasiEdit !== null): ?>
            <input type="hidden" name="id_lokasi" value="<?= (int) $lokasiEdit['id_lokasi'] ?>">
        <?php endif; ?>

        <h2 class="py-4 font-display text-[28px] font-bold">
            <?= $lokasiEdit === null
                ? 'Tambah Lokasi (untuk titik penyerahan)'
                : 'Edit Lokasi (untuk titik penyerahan)' ?>
        </h2>

        <label>
            <span class="tt-label">Nama Lokasi*</span>
            <input type="text" name="nama_lokasi" required maxlength="100" class="tt-input h-12"
                   value="<?= h($lokasiEdit['nama_lokasi'] ?? '') ?>">
        </label>

        <label>
            <span class="tt-label">Deskripsi (opsional)</span>
            <textarea name="deskripsi" rows="3" maxlength="2000"
                      class="tt-input h-22.25 resize-none"><?= h($lokasiEdit['deskripsi'] ?? '') ?></textarea>
        </label>

        <div class="flex gap-2.5 p-2.5">
            <button type="submit" class="tt-btn">Simpan</button>
            <?php if ($lokasiEdit !== null): ?>
                <a href="<?= h(base_url($halamanIni)) ?>" class="tt-btn-outline">Batal</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Daftar lokasi -->
    <section class="mt-2.5 flex flex-col gap-2.5">
        <h2 class="p-4 font-display text-xl font-semibold">Lokasi (untuk titik penyerahan)</h2>

        <div class="tt-table-wrap">
            <table class="tt-table min-w-200">
                <thead>
                    <tr>
                        <th class="w-1/4">Nama</th>
                        <th class="w-1/4">Deskripsi</th>
                        <th class="w-1/4">Status</th>
                        <th class="w-1/4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($daftarLokasi === []): ?>
                        <tr>
                            <td colspan="4" class="text-center">Belum ada lokasi.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftarLokasi as $lokasi): ?>
                            <?php $aktif = (bool) $lokasi['is_active']; ?>
                            <tr>
                                <td><?= h($lokasi['nama_lokasi']) ?></td>
                                <td><?= h($lokasi['deskripsi'] ?? '') ?></td>
                                <td>
                                    <?php require APP_ROOT . '/App/views/components/admin/badge-aktif.php'; ?>
                                </td>
                                <td>
                                    <div class="flex gap-2.5">
                                        <a href="<?= h(base_url($halamanIni . '?edit=' . (int) $lokasi['id_lokasi'])) ?>"
                                           class="tt-btn">Edit</a>

                                        <form method="post" action="<?= h(base_url($halamanIni)) ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="aksi" value="toggle">
                                            <input type="hidden" name="id_lokasi" value="<?= (int) $lokasi['id_lokasi'] ?>">
                                            <input type="hidden" name="is_active" value="<?= $aktif ? '0' : '1' ?>">
                                            <button type="submit" class="tt-btn-outline">
                                                <?= $aktif ? 'Nonaktifkan' : 'Aktifkan' ?>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
