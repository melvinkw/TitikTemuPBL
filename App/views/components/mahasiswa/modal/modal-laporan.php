<?php
/**
 * App/views/components/mahasiswa/modal/modal-laporan.php
 * Modal form "Laporan Kehilangan / Penemuan Barang".
 *
 * Cara pakai di halaman:
 *   $jenisLaporan = 'hilang';   // pilihan: hilang atau temuan
 *   $linkTutup    = base_url('mahasiswa/dashboard.php');   // tujuan tombol Batal
 *   require_once APP_ROOT . '/App/views/components/mahasiswa/modal/modal-laporan.php';
 *
 * Kalau jenisnya "temuan", ada tambahan kolom "Titik penyerahan barang temuan".
 * Tombol belum berfungsi (belum ada proses ke database).
 */
$jenisLaporan = $jenisLaporan ?? 'hilang';
$linkTutup    = $linkTutup ?? '#';
?>
<!-- Latar gelap di belakang modal -->
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/40 p-4">

    <!-- Kotak modal -->
    <form action="#" method="POST" class="my-8 w-full max-w-3xl rounded-xl bg-white p-6">
        <h2 class="font-display text-xl font-semibold">Laporan Kehilangan / Penemuan Barang</h2>
        <p class="mt-2 text-xs">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>

        <!-- Jenis laporan -->
        <label class="mt-6 block font-display text-sm font-semibold">Jenis Laporan *</label>
        <select name="jenis" class="mt-2 w-full rounded-lg border border-indigo-200 bg-white px-3 py-3 text-sm">
            <option value="hilang" <?= $jenisLaporan === 'hilang' ? 'selected' : '' ?>>Barang Hilang</option>
            <option value="temuan" <?= $jenisLaporan === 'temuan' ? 'selected' : '' ?>>Barang Temuan</option>
        </select>

        <!-- Kategori -->
        <label class="mt-4 block font-display text-sm font-semibold">Kategori barang*</label>
        <select name="kategori" class="mt-2 w-full rounded-lg border border-indigo-200 bg-white px-3 py-3 text-sm">
            <option value="">Pilih Kategori</option>
            <option value="elektronik">Elektronik</option>
            <option value="aksesoris">Aksesoris</option>
            <option value="identitas">Identitas</option>
        </select>

        <!-- Nama barang & email -->
        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="block font-display text-sm font-semibold">Nama Barang*</label>
                <input type="text" name="nama_barang" placeholder="Contoh: Earbuds, Tumbler, Charger Laptop"
                       class="mt-2 w-full rounded-lg border border-indigo-200 px-3 py-3 text-sm placeholder:text-indigo-200">
            </div>
            <div>
                <label class="block font-display text-sm font-semibold">Email*</label>
                <input type="email" name="email"
                       class="mt-2 w-full rounded-lg border border-indigo-200 px-3 py-3 text-sm">
            </div>
        </div>

        <!-- Deskripsi -->
        <label class="mt-4 block font-display text-sm font-semibold">Deskripsi dan ciri khusus*</label>
        <textarea name="deskripsi" rows="3" placeholder="Tuliskan detail/ciri-ciri umum barang"
                  class="mt-2 w-full rounded-lg border border-indigo-200 px-3 py-3 text-sm placeholder:text-indigo-200"></textarea>

        <!-- Tanggal & lokasi -->
        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="block font-display text-sm font-semibold">Tanggal dan waktu kejadian*</label>
                <input type="datetime-local" name="waktu"
                       class="mt-2 w-full rounded-lg border border-indigo-200 px-3 py-3 text-sm">
            </div>
            <div>
                <label class="block font-display text-sm font-semibold">Lokasi Kejadian*</label>
                <input type="text" name="lokasi" placeholder="Contoh: Lobby Gedung Sipil lt 1"
                       class="mt-2 w-full rounded-lg border border-indigo-200 px-3 py-3 text-sm placeholder:text-indigo-200">
            </div>
        </div>

        <!-- Foto (+ titik penyerahan khusus barang temuan) -->
        <?php if ($jenisLaporan === 'temuan'): ?>
            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="block font-display text-sm font-semibold">Foto barang (max 3)*</label>
                    <input type="file" name="foto[]" multiple
                           class="mt-2 w-full rounded-lg border border-indigo-200 px-3 py-2 text-sm file:mr-3 file:rounded file:border-0 file:bg-indigo-50 file:px-3 file:py-1">
                </div>
                <div>
                    <label class="block font-display text-sm font-semibold">Titik penyerahan barang temuan*</label>
                    <select name="pos" class="mt-2 w-full rounded-lg border border-indigo-200 bg-white px-3 py-3 text-sm">
                        <option value="pos-1">Pos 1</option>
                        <option value="pos-2">Pos 2</option>
                    </select>
                </div>
            </div>
        <?php else: ?>
            <label class="mt-4 block font-display text-sm font-semibold">Foto barang (opsional)</label>
            <input type="file" name="foto[]" multiple
                   class="mt-2 w-full rounded-lg border border-indigo-200 px-3 py-2 text-sm file:mr-3 file:rounded file:border-0 file:bg-indigo-50 file:px-3 file:py-1">
        <?php endif; ?>
        <p class="mt-2 text-xs">JPG, PNG, WebP, PDF maksimal 5 MB.</p>

        <!-- Tombol -->
        <div class="mt-6 flex gap-2">
            <button type="button" class="rounded-lg bg-tt-navy px-4 py-3 text-sm font-semibold text-white hover:bg-tt-orange hover:text-tt-navy">Kirim Laporan</button>
            <a href="<?= $linkTutup ?>" class="rounded-lg border border-tt-navy px-4 py-3 text-sm font-semibold hover:bg-indigo-100">Batal</a>
        </div>
    </form>
</div>
