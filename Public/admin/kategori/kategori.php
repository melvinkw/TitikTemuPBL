<?php
/**
 * Public/admin/kategori/kategori.php
 * Data Master — tab Kategori (Figma: Data Master-kategori).
 *
 * Form tambah/edit dan tombol aktif/nonaktif mengirim POST ke halaman ini
 * sendiri (pola sama dengan Public/login.php), lalu redirect agar refresh
 * tidak mengirim ulang data. Logika penulisan ada di
 * App/actions/master_actions.php.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/queries/master_queries.php';
require_once APP_ROOT . '/App/actions/master_actions.php';

$currentUser = auth_require_role(['admin']);

$halamanIni = 'admin/kategori/kategori.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post();
    verify_csrf();

    $aksi = is_string($_POST['aksi'] ?? null) ? $_POST['aksi'] : '';

    try {
        switch ($aksi) {
            case 'tambah':
                create_kategori(
                    db(),
                    is_string($_POST['nama_kategori'] ?? null) ? $_POST['nama_kategori'] : '',
                    is_string($_POST['deskripsi'] ?? null) ? $_POST['deskripsi'] : null
                );

                flash([
                    'type' => 'success',
                    'message' => 'Kategori berhasil ditambahkan.',
                ]);
                break;

            case 'ubah':
                update_kategori(
                    db(),
                    (int) ($_POST['id_kategori'] ?? 0),
                    is_string($_POST['nama_kategori'] ?? null) ? $_POST['nama_kategori'] : '',
                    is_string($_POST['deskripsi'] ?? null) ? $_POST['deskripsi'] : null
                );

                flash([
                    'type' => 'success',
                    'message' => 'Kategori berhasil diperbarui.',
                ]);
                break;

            case 'toggle':
                $idKategori = (int) ($_POST['id_kategori'] ?? 0);
                $aktifBaru = ($_POST['is_active'] ?? '') === '1';

                set_kategori_active(db(), $idKategori, $aktifBaru);

                flash([
                    'type' => 'success',
                    'message' => $aktifBaru
                        ? 'Kategori diaktifkan.'
                        : 'Kategori dinonaktifkan.',
                ]);
                break;

            default:
                throw new InvalidArgumentException(
                    'Aksi kategori tidak dikenal.'
                );
        }
    } catch (InvalidArgumentException | RuntimeException $error) {
        flash([
            'type' => 'error',
            'message' => $error->getMessage(),
        ]);
    } catch (Throwable $error) {
        error_log(
            '[TitikTemu] Data master kategori gagal: '
            . get_class($error)
            . ' - '
            . $error->getMessage()
        );

        flash([
            'type' => 'error',
            'message' => 'Kategori belum dapat disimpan. Silakan coba lagi.',
        ]);
    }

    redirect('/' . $halamanIni);
}

$pesan = flash();

$daftarKategori = [];

try {
    $daftarKategori = get_all_kategori(db());
} catch (Throwable $error) {
    error_log(
        '[TitikTemu] Gagal memuat kategori: ' . $error->getMessage()
    );

    $pesan = [
        'type' => 'error',
        'message' => 'Daftar kategori belum dapat dimuat.',
    ];
}

// Mode edit: ?edit=<id_kategori> mengisi form dengan data yang dipilih.
$editId = filter_var($_GET['edit'] ?? null, FILTER_VALIDATE_INT);
$kategoriEdit = null;

if ($editId !== false && $editId !== null && $editId > 0) {
    foreach ($daftarKategori as $kategori) {
        if ((int) $kategori['id_kategori'] === (int) $editId) {
            $kategoriEdit = $kategori;
            break;
        }
    }
}

$pageTitle = 'Data Master — Kategori';
$menuAktif = 'data-master';
$tabAktif = 'kategori';
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

    <!-- Form tambah/edit kategori -->
    <form method="post" action="<?= h(base_url($halamanIni)) ?>" class="tt-card flex flex-col gap-4 p-4.5">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="<?= $kategoriEdit === null ? 'tambah' : 'ubah' ?>">
        <?php if ($kategoriEdit !== null): ?>
            <input type="hidden" name="id_kategori" value="<?= (int) $kategoriEdit['id_kategori'] ?>">
        <?php endif; ?>

        <h2 class="py-4 font-display text-[28px] font-bold">
            <?= $kategoriEdit === null ? 'Tambah Kategori Barang' : 'Edit Kategori Barang' ?>
        </h2>

        <label>
            <span class="tt-label">Nama Kategori*</span>
            <input type="text" name="nama_kategori" required maxlength="50" class="tt-input h-12"
                   value="<?= h($kategoriEdit['nama_kategori'] ?? '') ?>">
        </label>

        <label>
            <span class="tt-label">Deskripsi (opsional)</span>
            <textarea name="deskripsi" rows="3" maxlength="2000"
                      class="tt-input h-22.25 resize-none"><?= h($kategoriEdit['deskripsi'] ?? '') ?></textarea>
        </label>

        <div class="flex gap-2.5 p-2.5">
            <button type="submit" class="tt-btn">Simpan</button>
            <?php if ($kategoriEdit !== null): ?>
                <a href="<?= h(base_url($halamanIni)) ?>" class="tt-btn-outline">Batal</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Daftar kategori -->
    <section class="mt-2.5 flex flex-col gap-2.5">
        <h2 class="p-4 font-display text-xl font-semibold">Kategori Barang</h2>

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
                    <?php if ($daftarKategori === []): ?>
                        <tr>
                            <td colspan="4" class="text-center">Belum ada kategori.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftarKategori as $kategori): ?>
                            <?php $aktif = (bool) $kategori['is_active']; ?>
                            <tr>
                                <td><?= h($kategori['nama_kategori']) ?></td>
                                <td><?= h($kategori['deskripsi'] ?? '') ?></td>
                                <td>
                                    <?php require APP_ROOT . '/App/views/components/admin/badge-aktif.php'; ?>
                                </td>
                                <td>
                                    <div class="flex gap-2.5">
                                        <a href="<?= h(base_url($halamanIni . '?edit=' . (int) $kategori['id_kategori'])) ?>"
                                           class="tt-btn">Edit</a>

                                        <form method="post" action="<?= h(base_url($halamanIni)) ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="aksi" value="toggle">
                                            <input type="hidden" name="id_kategori" value="<?= (int) $kategori['id_kategori'] ?>">
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
