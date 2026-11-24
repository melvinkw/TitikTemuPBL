<?php
/**
 * Public/admin/kategori/pos-penyerahan.php
 * Data Master — tab Pos Penyerahan (Figma: Data Master-pos penyerahan).
 *
 * Form tambah/edit dan tombol aktif/nonaktif mengirim POST ke halaman ini
 * sendiri lalu redirect. Logika penulisan ada di App/actions/master_actions.php.
 *
 * Catatan: kolom "Area" di Figma adalah relasi ke tabel lokasi (id_lokasi),
 * sedangkan kolom opsional pos_penyerahan.detail_lokasi tidak punya input di
 * desain. Nilai detail_lokasi yang sudah ada dipertahankan saat edit.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/queries/master_queries.php';
require_once APP_ROOT . '/App/actions/master_actions.php';

$currentUser = auth_require_role(['admin']);

$halamanIni = 'admin/kategori/pos-penyerahan.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post();
    verify_csrf();

    $aksi = is_string($_POST['aksi'] ?? null) ? $_POST['aksi'] : '';

    try {
        switch ($aksi) {
            case 'tambah':
                create_pos(
                    db(),
                    is_string($_POST['nama_pos'] ?? null) ? $_POST['nama_pos'] : '',
                    is_string($_POST['jenis'] ?? null) ? $_POST['jenis'] : '',
                    (int) ($_POST['id_lokasi'] ?? 0),
                    null
                );

                flash([
                    'type' => 'success',
                    'message' => 'Pos penyerahan berhasil ditambahkan.',
                ]);
                break;

            case 'ubah':
                $idPos = (int) ($_POST['id_pos'] ?? 0);
                $posLama = find_pos(db(), $idPos);

                update_pos(
                    db(),
                    $idPos,
                    is_string($_POST['nama_pos'] ?? null) ? $_POST['nama_pos'] : '',
                    is_string($_POST['jenis'] ?? null) ? $_POST['jenis'] : '',
                    (int) ($_POST['id_lokasi'] ?? 0),
                    $posLama['detail_lokasi'] ?? null
                );

                flash([
                    'type' => 'success',
                    'message' => 'Pos penyerahan berhasil diperbarui.',
                ]);
                break;

            case 'toggle':
                $idPos = (int) ($_POST['id_pos'] ?? 0);
                $aktifBaru = ($_POST['is_active'] ?? '') === '1';

                set_pos_active(db(), $idPos, $aktifBaru);

                flash([
                    'type' => 'success',
                    'message' => $aktifBaru
                        ? 'Pos penyerahan diaktifkan.'
                        : 'Pos penyerahan dinonaktifkan.',
                ]);
                break;

            default:
                throw new InvalidArgumentException(
                    'Aksi pos penyerahan tidak dikenal.'
                );
        }
    } catch (InvalidArgumentException | RuntimeException $error) {
        flash([
            'type' => 'error',
            'message' => $error->getMessage(),
        ]);
    } catch (Throwable $error) {
        error_log(
            '[TitikTemu] Data master pos penyerahan gagal: '
            . get_class($error)
            . ' - '
            . $error->getMessage()
        );

        flash([
            'type' => 'error',
            'message' => 'Pos penyerahan belum dapat disimpan. Silakan coba lagi.',
        ]);
    }

    redirect('/' . $halamanIni);
}

$pesan = flash();

$daftarPos = [];
$daftarLokasi = [];

try {
    $daftarPos = get_all_pos(db());
    $daftarLokasi = get_all_lokasi(db());
} catch (Throwable $error) {
    error_log(
        '[TitikTemu] Gagal memuat pos penyerahan: ' . $error->getMessage()
    );

    $pesan = [
        'type' => 'error',
        'message' => 'Daftar pos penyerahan belum dapat dimuat.',
    ];
}

$pilihanJenis = get_jenis_pos_options();

$editId = filter_var($_GET['edit'] ?? null, FILTER_VALIDATE_INT);
$posEdit = null;

if ($editId !== false && $editId !== null && $editId > 0) {
    foreach ($daftarPos as $pos) {
        if ((int) $pos['id_pos'] === (int) $editId) {
            $posEdit = $pos;
            break;
        }
    }
}

$pageTitle = 'Data Master — Pos Penyerahan';
$menuAktif = 'data-master';
$tabAktif = 'pos';
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

    <!-- Form tambah/edit pos penyerahan -->
    <form method="post" action="<?= h(base_url($halamanIni)) ?>" class="tt-card flex flex-col gap-4 p-4.5">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="<?= $posEdit === null ? 'tambah' : 'ubah' ?>">
        <?php if ($posEdit !== null): ?>
            <input type="hidden" name="id_pos" value="<?= (int) $posEdit['id_pos'] ?>">
        <?php endif; ?>

        <h2 class="py-4 font-display text-[28px] font-bold">
            <?= $posEdit === null ? 'Tambah Pos Penyerahan' : 'Edit Pos Penyerahan' ?>
        </h2>

        <div class="flex flex-col gap-4 md:flex-row">
            <label class="flex-1">
                <span class="tt-label">Nama Pos Penyerahan*</span>
                <input type="text" name="nama_pos" required maxlength="100" class="tt-input h-12"
                       value="<?= h($posEdit['nama_pos'] ?? '') ?>">
            </label>

            <label class="flex-1">
                <span class="tt-label">Jenis*</span>
                <span class="relative block">
                    <select name="jenis" required class="tt-input h-12 appearance-none pr-11">
                        <option value=""></option>
                        <?php foreach ($pilihanJenis as $nilaiJenis => $labelJenis): ?>
                            <option value="<?= h($nilaiJenis) ?>"
                                <?= ($posEdit['jenis_pos'] ?? '') === $nilaiJenis ? 'selected' : '' ?>>
                                <?= h($labelJenis) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <img src="<?= h(base_url('assets/images/icon-chevron-down.svg')) ?>" alt=""
                         class="pointer-events-none absolute top-3 right-3 h-6 w-6">
                </span>
            </label>
        </div>

        <label>
            <span class="tt-label">Area Lokasi*</span>
            <span class="relative block">
                <select name="id_lokasi" required class="tt-input h-12 appearance-none pr-11">
                    <option value=""></option>
                    <?php foreach ($daftarLokasi as $lokasi): ?>
                        <option value="<?= (int) $lokasi['id_lokasi'] ?>"
                            <?= (int) ($posEdit['id_lokasi'] ?? 0) === (int) $lokasi['id_lokasi'] ? 'selected' : '' ?>>
                            <?= h($lokasi['nama_lokasi']) ?><?= $lokasi['is_active'] ? '' : ' (nonaktif)' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <img src="<?= h(base_url('assets/images/icon-chevron-down.svg')) ?>" alt=""
                     class="pointer-events-none absolute top-3 right-3 h-6 w-6">
            </span>
        </label>

        <div class="flex gap-2.5 p-2.5">
            <button type="submit" class="tt-btn">Simpan</button>
            <?php if ($posEdit !== null): ?>
                <a href="<?= h(base_url($halamanIni)) ?>" class="tt-btn-outline">Batal</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Daftar pos penyerahan -->
    <section class="mt-2.5 flex flex-col gap-2.5">
        <h2 class="p-4 font-display text-xl font-semibold">Pos Penyerahan</h2>

        <div class="tt-table-wrap">
            <table class="tt-table min-w-225">
                <thead>
                    <tr>
                        <th class="w-1/5">Nama</th>
                        <th class="w-1/5">Jenis</th>
                        <th class="w-1/5">Area</th>
                        <th class="w-1/5">Status</th>
                        <th class="w-1/5">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($daftarPos === []): ?>
                        <tr>
                            <td colspan="5" class="text-center">Belum ada pos penyerahan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftarPos as $pos): ?>
                            <?php $aktif = (bool) $pos['is_active']; ?>
                            <tr>
                                <td><?= h($pos['nama_pos']) ?></td>
                                <td><?= h($pilihanJenis[$pos['jenis_pos']] ?? $pos['jenis_pos']) ?></td>
                                <td><?= h($pos['nama_lokasi']) ?></td>
                                <td>
                                    <?php require APP_ROOT . '/App/views/components/admin/badge-aktif.php'; ?>
                                </td>
                                <td>
                                    <div class="flex gap-2.5">
                                        <a href="<?= h(base_url($halamanIni . '?edit=' . (int) $pos['id_pos'])) ?>"
                                           class="tt-btn">Edit</a>

                                        <form method="post" action="<?= h(base_url($halamanIni)) ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="aksi" value="toggle">
                                            <input type="hidden" name="id_pos" value="<?= (int) $pos['id_pos'] ?>">
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
