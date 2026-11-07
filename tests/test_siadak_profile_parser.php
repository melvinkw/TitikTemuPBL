<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/App/services/siakad_service.php';

$passed = 0;
$failed = 0;

function assert_case(string $label, bool $condition): void
{
    global $passed, $failed;

    if ($condition) {
        $passed++;
        echo "[LULUS] {$label}\n";
    } else {
        $failed++;
        echo "[GAGAL] {$label}\n";
    }
}

$nim = '1234567890';
$html = <<<'HTML'
<script>
    $(".page-content .page-breadcrumb > li").next().next().find("a").text("1234567890 / BUDI SANTOSO");
</script>
HTML;

$profile = siakad_extract_profile_from_html($html, $nim);
assert_case(
    'Parser mengambil NIM dan nama dari pola JavaScript lama SIAKAD',
    $profile === ['nim' => $nim, 'nama' => 'BUDI SANTOSO']
);

assert_case(
    'Parser menolak NIM yang berbeda dari identitas login',
    siakad_extract_profile_from_html($html, '1234567891') === null
);

assert_case(
    'Parser menolak HTML tanpa identitas profil',
    siakad_extract_profile_from_html('<html><body>Beranda</body></html>', $nim) === null
);

assert_case(
    'Parser menolak format NIM yang tidak valid',
    siakad_extract_profile_from_html($html, '12345') === null
);

assert_case(
    'Parser mengubah HTML entity pada nama',
    siakad_extract_profile_from_html(
        '<script>$("x").text("1234567890 / BUDI &amp; SANTOSO");</script>',
        $nim
    ) === ['nim' => $nim, 'nama' => 'BUDI & SANTOSO']
);

assert_case(
    'Parser menerima tanda kutip tunggal dan spasi multiline',
    siakad_extract_profile_from_html(
        "<script>$('.breadcrumb').text(\n '1234567890 / BUDI SANTOSO'\n);</script>",
        $nim
    ) === ['nim' => $nim, 'nama' => 'BUDI SANTOSO']
);

assert_case(
    'Parser dapat mendeteksi identitas lain tanpa menganggapnya cocok',
    siakad_extract_profile_from_html(
        '<script>$("x").text("1234567891 / NAMA LAIN");</script>',
        $nim
    ) === null
    && count(siakad_extract_profile_candidates_from_html(
        '<script>$("x").text("1234567891 / NAMA LAIN");</script>'
    )) === 1
);

assert_case(
    'Parser memilih NIM yang cocok bila HTML memuat lebih dari satu kandidat',
    siakad_extract_profile_from_html(
        '<script>$("x").text("1234567891 / LAIN"); $("y").text("1234567890 / BUDI");</script>',
        $nim
    ) === ['nim' => $nim, 'nama' => 'BUDI']
);

$authenticatedPageWithPasswordFields = <<<'HTML'
<form action="https://siakad.polinema.ac.id/beranda" id="form-action-wrapper">
    <input type="password" name="passwordLama">
    <input type="password" name="passwordBaru">
    <input type="password" name="konfirmasiPassword">
</form>
<script>
    $(".page-content .page-breadcrumb > li").next().next().find("a").text("1234567890 / BUDI SANTOSO");
</script>
HTML;

assert_case(
    'Halaman profil dengan form ganti password tidak dianggap halaman login',
    siakad_html_looks_like_login_page($authenticatedPageWithPasswordFields) === false
);

assert_case(
    'Form login dengan username dan password terdeteksi',
    siakad_html_looks_like_login_page(
        '<form action="/login"><input name="username"><input type="password" name="password"></form>'
    ) === true
);

assert_case(
    'Halaman profil percobaan tetap dapat diparsing meski ada field password',
    siakad_extract_profile_from_html($authenticatedPageWithPasswordFields, $nim)
        === ['nim' => $nim, 'nama' => 'BUDI SANTOSO']
);

assert_case(
    'Parser tetap menemukan identitas bila ada byte non-UTF8 di bagian lain halaman',
    siakad_extract_profile_from_html(
        "<div>\xFF</div><script>$(\"x\").text(\"1234567890 / BUDI SANTOSO\");</script>",
        $nim
    ) === ['nim' => $nim, 'nama' => 'BUDI SANTOSO']
);


$actualProfileMarkup = <<<'HTML'
<div class="top-menu">
    <span class="username username-hide-on-mobile">
        &nbsp;&nbsp;1234567890 / BUDI SANTOSO&nbsp;&nbsp;
    </span>
</div>
<h3 class="page-title">
    1234567890 / BUDI SANTOSO
</h3>
<ul class="page-breadcrumb breadcrumb">
    <li><a>Siakad</a></li>
    <li><a>Beranda</a></li>
    <li><a>Welcome</a></li>
</ul>
HTML;

assert_case(
    'Parser membaca identitas langsung dari span username SIAKAD',
    siakad_extract_profile_from_html($actualProfileMarkup, $nim)
        === ['nim' => $nim, 'nama' => 'BUDI SANTOSO']
);

assert_case(
    'Parser membaca identitas langsung dari h3 page-title SIAKAD',
    siakad_extract_profile_from_html(
        '<h3 class="page-title">1234567890 / BUDI SANTOSO</h3>',
        $nim
    ) === ['nim' => $nim, 'nama' => 'BUDI SANTOSO']
);

assert_case(
    'Parser membaca identitas span dengan class tambahan dan urutan class berbeda',
    siakad_extract_profile_from_html(
        '<span id="user" class="username-hide-on-mobile active username">1234567890 / BUDI</span>',
        $nim
    ) === ['nim' => $nim, 'nama' => 'BUDI']
);

assert_case(
    'Parser menormalkan spasi non-breaking dari entity &nbsp;',
    siakad_extract_profile_from_html(
        '<span class="username username-hide-on-mobile">&nbsp;1234567890 / BUDI&nbsp;</span>',
        $nim
    ) === ['nim' => $nim, 'nama' => 'BUDI']
);

assert_case(
    'Parser tidak mengambil kata Siakad Beranda Welcome dari breadcrumb sebagai identitas',
    siakad_extract_profile_from_html(
        '<ul class="page-breadcrumb breadcrumb"><li>Siakad</li><li>Beranda</li><li>Welcome</li></ul>',
        $nim
    ) === null
);

assert_case(
    'Parser menghindari kandidat duplikat dari navbar dan page title',
    count(siakad_extract_profile_candidates_from_html($actualProfileMarkup)) === 1
);

assert_case(
    'Parser tetap menolak jika NIM di profil tidak sama dengan NIM login',
    siakad_extract_profile_from_html($actualProfileMarkup, '1234567891') === null
);

echo "\nLulus: {$passed}\nGagal: {$failed}\n";
exit($failed === 0 ? 0 : 1);
