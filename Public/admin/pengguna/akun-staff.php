<?php
/**
 * Public/admin/pengguna/akun-staff.php
 * Halaman Akun Staff — kelola akun admin dan petugas (Figma: Admin-Akun Staff).
 *
 * Form tambah/edit dan tombol aktif/nonaktif mengirim POST ke halaman ini
 * sendiri (pola sama dengan Public/admin/kategori/kategori.php), lalu redirect
 * agar refresh tidak mengirim ulang data. Logika penulisan ada di
 * App/actions/staff_actions.php.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../App/config/bootstrap.php';
require_once APP_ROOT . '/App/helpers/auth.php';
require_once APP_ROOT . '/App/queries/staff_queries.php';
require_once APP_ROOT . '/App/actions/staff_actions.php';

$currentUser = auth_require_role(['admin']);
$idAdmin = (int) $currentUser['id_pengguna'];

$halamanIni = 'admin/pengguna/akun-staff.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    require_post();
    verify_csrf();

    $aksi = is_string($_POST['aksi'] ?? null) ? $_POST['aksi'] : '';

    try {
        switch ($aksi) {
            case 'tambah':
                create_staff_account(
                    db(),
                    is_string($_POST['role'] ?? null) ? $_POST['role'] : '',
                    is_string($_POST['username'] ?? null) ? $_POST['username'] : '',
                    is_string($_POST['nama'] ?? null) ? $_POST['nama'] : '',
                    is_string($_POST['email'] ?? null) ? $_POST['email'] : null,
                    is_string($_POST['password'] ?? null) ? $_POST['password'] : ''
                );

                flash([
                    'type' => 'success',
                    'message' => 'Akun staff berhasil ditambahkan.',
                ]);
                break;

            case 'ubah':
                update_staff_account(
                    db(),
                    (int) ($_POST['id_pengguna'] ?? 0),
                    $idAdmin,
                    is_string($_POST['role'] ?? null) ? $_POST['role'] : '',
                    is_string($_POST['username'] ?? null) ? $_POST['username'] : '',
                    is_string($_POST['nama'] ?? null) ? $_POST['nama'] : '',
                    is_string($_POST['email'] ?? null) ? $_POST['email'] : null,
                    is_string($_POST['password'] ?? null) ? $_POST['password'] : ''
                );

                flash([
                    'type' => 'success',
                    'message' => 'Akun staff berhasil diperbarui.',
                ]);
                break;

            case 'toggle':
                $idPengguna = (int) ($_POST['id_pengguna'] ?? 0);
                $aktifBaru = ($_POST['is_active'] ?? '') === '1';

                set_staff_active(db(), $idPengguna, $aktifBaru, $idAdmin);

                flash([
                    'type' => 'success',
                    'message' => $aktifBaru
                        ? 'Akun staff diaktifkan.'
                        : 'Akun staff dinonaktifkan.',
                ]);
                break;

            default:
                throw new InvalidArgumentException(
                    'Aksi akun staff tidak dikenal.'
                );
        }
    } catch (InvalidArgumentException | RuntimeException $error) {
        flash([
            'type' => 'error',
            'message' => $error->getMessage(),
        ]);
    } catch (Throwable $error) {
        error_log(
            '[TitikTemu] Akun staff gagal disimpan: '
            . get_class($error)
            . ' - '
            . $error->getMessage()
        );

        flash([
            'type' => 'error',
            'message' => 'Akun staff belum dapat disimpan. Silakan coba lagi.',
        ]);
    }

    redirect('/' . $halamanIni);
}

$pesan = flash();

// Pilihan role — sesuai kolom role di tabel pengguna
$pilihanRole = [
    'admin'   => 'Administrator',
    'petugas' => 'Petugas Keamanan',
];

$daftarAkun = [];
$muatError = null;

try {
    $daftarAkun = get_all_staff_accounts(db());
} catch (Throwable $error) {
    error_log(
        '[TitikTemu] Gagal memuat akun staff: ' . $error->getMessage()
    );

    $muatError = 'Daftar akun staff belum dapat dimuat.';
}

// Mode edit: ?edit=<id_pengguna> mengisi form dengan data yang dipilih.
$editId = filter_var($_GET['edit'] ?? null, FILTER_VALIDATE_INT);
$akunEdit = null;

if ($editId !== false && $editId !== null && $editId > 0) {
    foreach ($daftarAkun as $akun) {
        if ((int) $akun['id_pengguna'] === (int) $editId) {
            $akunEdit = $akun;
            break;
        }
    }
}

$pageTitle = 'Akun Staff';
$menuAktif = 'akun-staff';
$judulHalaman = 'Akun Admin & Petugas';
$deskripsiHalaman = 'Tambah dan kelola akun administrator dan petugas keamanan yang mengelola TitikTemu.';

require_once APP_ROOT . '/App/views/components/header.php';
require_once APP_ROOT . '/App/views/components/admin/navbar.php';
?>

<main class="flex w-full flex-col gap-6.5 px-4 pt-2.5 pb-10 md:px-20">

    <?php require APP_ROOT . '/App/views/components/alert.php'; ?>

    <div class="flex flex-col gap-4">
        <?php require APP_ROOT . '/App/views/components/judul-halaman.php'; ?>
    </div>

    <!-- Form tambah/edit akun -->
    <form method="post" action="<?= h(base_url($halamanIni)) ?>" class="tt-card flex flex-col gap-4 p-4.5">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="<?= $akunEdit === null ? 'tambah' : 'ubah' ?>">
        <?php if ($akunEdit !== null): ?>
            <input type="hidden" name="id_pengguna" value="<?= (int) $akunEdit['id_pengguna'] ?>">
        <?php endif; ?>

        <h2 class="py-4 font-display text-[28px] font-bold">
            <?= $akunEdit === null ? 'Tambah Akun Staff' : 'Edit Akun Staff' ?>
        </h2>

        <div class="flex flex-col gap-4 md:flex-row">
            <label class="flex-1">
                <span class="tt-label">Role*</span>
                <span class="relative block">
                    <select name="role" required class="tt-input h-12 appearance-none pr-11">
                        <option value=""></option>
                        <?php foreach ($pilihanRole as $nilai => $label): ?>
                            <option
                                value="<?= h($nilai) ?>"
                                <?= ($akunEdit['role'] ?? '') === $nilai ? 'selected' : '' ?>>
                                <?= h($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <img src="<?= h(base_url('assets/images/icon-chevron-down.svg')) ?>" alt=""
                         class="pointer-events-none absolute top-3 right-3 h-6 w-6">
                </span>
            </label>

            <label class="flex-1">
                <span class="tt-label">Username*</span>
                <input
                    type="text"
                    name="username"
                    required
                    maxlength="50"
                    autocomplete="off"
                    class="tt-input h-12"
                    value="<?= h($akunEdit['username'] ?? '') ?>">
            </label>
        </div>

        <div class="flex flex-col gap-4 md:flex-row">
            <label class="flex-1">
                <span class="tt-label">Nama Lengkap*</span>
                <input
                    type="text"
                    name="nama"
                    required
                    maxlength="100"
                    class="tt-input h-12"
                    value="<?= h($akunEdit['nama'] ?? '') ?>">
            </label>

            <label class="flex-1">
                <span class="tt-label">Email (opsional)</span>
                <input
                    type="email"
                    name="email"
                    maxlength="254"
                    class="tt-input h-12"
                    value="<?= h($akunEdit['email'] ?? '') ?>">
            </label>
        </div>

        <label>
            <span class="tt-label">
                Password<?= $akunEdit === null ? '*' : ' (kosongkan kalau tidak diubah)' ?>
            </span>
            <input
                type="password"
                name="password"
                autocomplete="new-password"
                minlength="8"
                maxlength="72"
                <?= $akunEdit === null ? 'required' : '' ?>
                class="tt-input h-12">
        </label>

        <div class="flex gap-2.5 p-2.5">
            <button type="submit" class="tt-btn">Simpan</button>
            <?php if ($akunEdit !== null): ?>
                <a href="<?= h(base_url($halamanIni)) ?>" class="tt-btn-outline">Batal</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Daftar akun -->
    <section class="mt-2.5 flex flex-col gap-2.5">
        <h2 class="p-4 font-display text-xl font-semibold">Daftar Akun Staff</h2>

        <?php if ($muatError !== null): ?>
            <div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <?= h($muatError) ?>
            </div>
        <?php endif; ?>

        <div class="tt-table-wrap">
            <table class="tt-table min-w-250">
                <thead>
                    <tr>
                        <th class="w-1/6">Nama / Email</th>
                        <th class="w-1/6">Username</th>
                        <th class="w-1/6">Role</th>
                        <th class="w-1/6">Status</th>
                        <th class="w-1/6">Login Terakhir</th>
                        <th class="w-1/6">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($muatError === null && $daftarAkun === []): ?>
                        <tr>
                            <td colspan="6" class="text-center">Belum ada akun staff.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($daftarAkun as $akun): ?>
                        <?php
                        $aktif = (bool) $akun['is_active'];
                        $akunSendiri = (int) $akun['id_pengguna'] === $idAdmin;
                        ?>
                        <tr>
                            <td>
                                <p class="font-semibold"><?= h($akun['nama']) ?></p>
                                <p><?= h($akun['email'] ?? '—') ?></p>
                            </td>
                            <td><?= h($akun['username'] ?? '—') ?></td>
                            <td><?= h($pilihanRole[$akun['role']] ?? $akun['role']) ?></td>
                            <td>
                                <?php require APP_ROOT . '/App/views/components/admin/badge-aktif.php'; ?>
                            </td>
                            <td class="whitespace-nowrap"><?= h(format_date_time($akun['last_login'] ?? null)) ?></td>
                            <td>
                                <div class="flex gap-2.5">
                                    <a href="<?= h(base_url($halamanIni . '?edit=' . (int) $akun['id_pengguna'])) ?>"
                                       class="tt-btn">Edit</a>

                                    <?php if ($akunSendiri): ?>
                                        <span class="self-center text-xs text-gray-500">Akun Anda</span>
                                    <?php else: ?>
                                        <form method="post" action="<?= h(base_url($halamanIni)) ?>">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="aksi" value="toggle">
                                            <input type="hidden" name="id_pengguna" value="<?= (int) $akun['id_pengguna'] ?>">
                                            <input type="hidden" name="is_active" value="<?= $aktif ? '0' : '1' ?>">
                                            <button type="submit" class="tt-btn-outline">
                                                <?= $aktif ? 'Nonaktifkan' : 'Aktifkan' ?>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<?php require_once APP_ROOT . '/App/views/components/footer.php'; ?>
