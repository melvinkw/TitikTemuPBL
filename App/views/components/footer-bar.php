<?php
/**
 * App/views/components/footer-bar.php
 * Isi footer (Figma: komponen "Nav/Footer"). Dipakai oleh footer.php dan footer petugas.
 * Halaman cukup memanggil App/views/components/footer.php.
 */
?>
<footer class="mt-auto flex flex-col items-center justify-between gap-2 border-t-[0.5px] border-tt-outline px-4 py-6 text-center text-xs text-tt-outline md:flex-row md:px-11 md:text-left">
    <p>
        <span class="font-extrabold">TitikTemu.</span>
        Politeknik Negeri Malang
    </p>
    <p>Platform informasi barang hilang dan barang temuan.</p>
    <p>&copy; <?= date('Y') ?> TitikTemu</p>
</footer>
