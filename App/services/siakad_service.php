<?php

declare(strict_types=1);

/**
 * Normalisasi teks HTML sebelum dipakai untuk membaca identitas.
 * Tidak memakai modifier /u agar byte UTF-8 rusak tidak menggagalkan parser.
 */
function siakad_normalize_profile_text(string $value): string
{
    $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $value = str_replace(["\xC2\xA0", "\xA0"], ' ', $value);

    if (function_exists('iconv')) {
        $clean = @iconv('UTF-8', 'UTF-8//IGNORE', $value);
        if ($clean !== false) {
            $value = $clean;
        }
    }

    $value = preg_replace('/[\x00-\x1F\x7F]+/', ' ', $value) ?? '';
    $value = preg_replace('/\s+/', ' ', trim($value)) ?? '';

    return $value;
}

/**
 * Mengambil teks dari elemen breadcrumb nyata pada HTML.
 * DOMDocument dipakai bila ekstensi DOM aktif. Fallback regex khusus
 * page-breadcrumb dipakai untuk lingkungan PHP yang tidak mengaktifkan DOM.
 */
/**
 * Mengambil teks dari elemen HTML berdasarkan tag dan class yang diwajibkan.
 * Dipakai untuk membaca identitas profil, bukan breadcrumb navigasi.
 */
function siakad_extract_text_from_class_element(
    string $html,
    string $tag,
    array $requiredClasses
): array {
    $texts = [];
    $tagPattern = preg_quote($tag, '~');

    $matched = preg_match_all(
        '~<' . $tagPattern . '\b([^>]*)>(.*?)</' . $tagPattern . '\s*>~is',
        $html,
        $elements,
        PREG_SET_ORDER
    );

    if ($matched === false || $matched === 0) {
        return [];
    }

    foreach ($elements as $element) {
        $attributes = $element[1] ?? '';
        $innerHtml = $element[2] ?? '';

        if (
            preg_match("~\\bclass\\s*=\\s*([\"'])(.*?)\\1~is", $attributes, $classMatch) !== 1
        ) {
            continue;
        }

        $classes = preg_split('/\\s+/', strtolower(trim($classMatch[2]))) ?: [];
        $hasAllClasses = true;

        foreach ($requiredClasses as $requiredClass) {
            if (!in_array(strtolower($requiredClass), $classes, true)) {
                $hasAllClasses = false;
                break;
            }
        }

        if (!$hasAllClasses) {
            continue;
        }

        $text = siakad_normalize_profile_text(strip_tags($innerHtml));
        if ($text !== '') {
            $texts[] = $text;
        }
    }

    return $texts;
}

/**
 * Identitas halaman /beranda muncul di navbar dan judul halaman:
 * span.username.username-hide-on-mobile serta h3.page-title.
 */
function siakad_extract_identity_texts_from_html(string $html): array
{
    $texts = array_merge(
        siakad_extract_text_from_class_element(
            $html,
            'span',
            ['username', 'username-hide-on-mobile']
        ),
        siakad_extract_text_from_class_element(
            $html,
            'h3',
            ['page-title']
        )
    );

    return array_values(array_unique($texts));
}

function siakad_extract_breadcrumb_texts(string $html): array
{
    $texts = [];

    if (class_exists('DOMDocument') && class_exists('DOMXPath')) {
        $previousSetting = libxml_use_internal_errors(true);

        try {
            $document = new DOMDocument('1.0', 'UTF-8');
            $loaded = $document->loadHTML(
                '<?xml encoding="UTF-8">' . $html,
                LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING
            );

            if ($loaded) {
                $xpath = new DOMXPath($document);
                $nodes = $xpath->query(
                    '//*[contains(concat(" ", normalize-space(@class), " "), " page-breadcrumb ")]'
                    . ' | //*[@aria-label and contains(translate(@aria-label,'
                    . ' "ABCDEFGHIJKLMNOPQRSTUVWXYZ", "abcdefghijklmnopqrstuvwxyz"), "breadcrumb")]'
                );

                if ($nodes !== false) {
                    foreach ($nodes as $node) {
                        $text = siakad_normalize_profile_text($node->textContent ?? '');
                        if ($text !== '') {
                            $texts[] = $text;
                        }
                    }
                }
            }
        } catch (Throwable $error) {
            // Parsing DOM adalah bantuan tambahan. Fallback regex tetap berjalan.
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousSetting);
        }
    }

    // Fallback untuk shared hosting / PHP yang tidak memiliki ekstensi DOM.
    // Prioritaskan <li> di dalam elemen yang class-nya memuat page-breadcrumb.
    if (preg_match_all(
        '~<(ul|ol|nav|div)\\b(?=[^>]*\\bclass\\s*=\\s*(?:\\x22[^\\x22]*\\bpage-breadcrumb\\b[^\\x22]*\\x22|\\x27[^\\x27]*\\bpage-breadcrumb\\b[^\\x27]*\\x27))[^>]*>(.*?)</\\1>~is',
        $html,
        $containers,
        PREG_SET_ORDER
    )) {
        foreach ($containers as $container) {
            if (preg_match_all('~<li\\b[^>]*>(.*?)</li>~is', $container[2], $items)) {
                foreach ($items[1] as $itemHtml) {
                    $text = siakad_normalize_profile_text(strip_tags($itemHtml));
                    if ($text !== '') {
                        $texts[] = $text;
                    }
                }
            }

            // Beberapa tema menggunakan <a> langsung tanpa daftar <li>.
            if (empty($items[1]) && preg_match_all('~<a\\b[^>]*>(.*?)</a>~is', $container[2], $links)) {
                foreach ($links[1] as $linkHtml) {
                    $text = siakad_normalize_profile_text(strip_tags($linkHtml));
                    if ($text !== '') {
                        $texts[] = $text;
                    }
                }
            }
        }
    }

    return array_values(array_unique($texts));
}

/**
 * Mendeteksi apakah HTML benar-benar memiliki container breadcrumb.
 * Jangan memakai div_script_ajax_footer sebagai bukti: elemen tersebut
 * hanya penampung script dan bisa ada walaupun breadcrumb tidak ada.
 */
function siakad_has_breadcrumb_marker(string $html): bool
{
    return preg_match(
        '~\\bclass\\s*=\\s*(?:\\x22[^\\x22]*\\bpage-breadcrumb\\b[^\\x22]*\\x22|\\x27[^\\x27]*\\bpage-breadcrumb\\b[^\\x27]*\\x27)'
        . '|\\baria-label\\s*=\\s*(?:\\x22[^\\x22]*breadcrumb[^\\x22]*\\x22|\\x27[^\\x27]*breadcrumb[^\\x27]*\\x27)~i',
        $html
    ) === 1;
}

/**
 * Mengambil kandidat NIM dan nama dari elemen profil, script lama,
 * atau teks breadcrumb bila format lama memang menaruh identitas di sana.
 */
function siakad_extract_profile_candidates_from_html(string $html): array
{
    $profiles = [];

    // Format JavaScript pada percobaan awal: .text(...) atau .html(...).
    $scriptPattern = <<<'REGEX'
~\.(?:text|html)\(\s*(["'])\s*([0-9]{8,20})\s*/\s*([^"'<>\r\n]{1,100}?)\1\s*\)~i
REGEX;
    $matchCount = preg_match_all(
        $scriptPattern,
        $html,
        $scriptMatches,
        PREG_SET_ORDER
    );

    if ($matchCount !== false && $matchCount > 0) {
        foreach ($scriptMatches as $match) {
            $nim = trim($match[2]);
            $name = siakad_normalize_profile_text($match[3]);

            if (
                preg_match('/^[0-9]{8,20}$/D', $nim) === 1
                && $name !== ''
                && (function_exists('mb_strlen') ? mb_strlen($name, 'UTF-8') : strlen($name)) <= 100
            ) {
                $profiles[$nim] = ['nim' => $nim, 'nama' => $name];
            }
        }
    }

    // HTML terbaru: NIM / nama langsung tercetak pada span navbar dan h3.
    // Jangan cari di breadcrumb untuk struktur ini; breadcrumb hanya navigasi.
    $identityTexts = siakad_extract_identity_texts_from_html($html);

    // Tetap pertahankan kompatibilitas jika struktur lama menaruh identitas
    // langsung di area breadcrumb.
    $identityTexts = array_merge(
        $identityTexts,
        siakad_extract_breadcrumb_texts($html)
    );

    foreach ($identityTexts as $identityText) {
        $identityText = siakad_normalize_profile_text($identityText);

        if (preg_match('/^([0-9]{8,20})\\s*\\/\\s*(.{1,100})$/', $identityText, $match) !== 1) {
            continue;
        }

        $nim = trim($match[1]);
        $name = siakad_normalize_profile_text($match[2]);
        $nameLength = function_exists('mb_strlen')
            ? mb_strlen($name, 'UTF-8')
            : strlen($name);

        if (
            preg_match('/^[0-9]{8,20}$/D', $nim) === 1
            && $name !== ''
            && $nameLength <= 100
        ) {
            // Navbar dan page title menampilkan data sama. Simpan satu kandidat.
            if (!isset($profiles[$nim])) {
                $profiles[$nim] = ['nim' => $nim, 'nama' => $name];
            }
        }
    }

    return array_values($profiles);
}

/**
 * Memilih identitas hanya jika NIM pada halaman sama dengan NIM yang login.
 */
function siakad_extract_profile_from_html(string $html, string $expectedNim): ?array
{
    if (preg_match('/^[0-9]{8,20}$/D', $expectedNim) !== 1) {
        return null;
    }

    foreach (siakad_extract_profile_candidates_from_html($html) as $profile) {
        if (hash_equals($expectedNim, $profile['nim'])) {
            return $profile;
        }
    }

    return null;
}

/**
 * Mendeteksi halaman login berdasarkan struktur form, bukan sekadar
 * keberadaan input password. Halaman /beranda juga dapat memiliki form
 * ganti password dengan field passwordLama/passwordBaru.
 */
function siakad_html_looks_like_login_page(string $html): bool
{
    if (
        preg_match(
            '~<form\b[^>]*\baction\s*=\s*["\'][^"\']*/login(?:[/?#][^"\']*)?["\']~i',
            $html
        ) === 1
    ) {
        return true;
    }

    if (preg_match_all('~<form\b[^>]*>.*?</form>~is', $html, $forms) === false) {
        return false;
    }

    foreach ($forms[0] as $formHtml) {
        $hasLoginIdentifier = preg_match(
            '~<input\b[^>]*\bname\s*=\s*["\'](?:username|nim|nip)["\']~i',
            $formHtml
        ) === 1;

        $hasGenericPassword = preg_match(
            '~<input\b(?=[^>]*\btype\s*=\s*["\']password["\'])(?=[^>]*\bname\s*=\s*["\']password["\'])[^>]*>~i',
            $formHtml
        ) === 1;

        if ($hasLoginIdentifier && $hasGenericPassword) {
            return true;
        }
    }

    return false;
}

/**
 * Memvalidasi mahasiswa ke SIAKAD Polinema.
 * Password hanya dikirim ke SIAKAD. Cookie sesi hanya disimpan sementara
 * dan tidak pernah dimasukkan ke database atau log.
 *
 * Endpoint /login dan /beranda berdasarkan perilaku yang diamati dalam
 * percobaan pengguna, bukan klaim adanya API publik resmi SIAKAD.
 */
function siakad_login(string $nim, string $password): array
{
    if (preg_match('/^[0-9]{8,20}$/D', $nim) !== 1 || $password === '') {
        return [
            'success' => false,
            'message' => 'NIM atau password tidak valid.',
        ];
    }

    if (!function_exists('curl_init')) {
        return [
            'success' => false,
            'message' => 'Komponen cURL PHP belum aktif di server.',
        ];
    }

    $baseUrl = rtrim(
        (string) ($_ENV['SIAKAD_URL'] ?? 'https://siakad.polinema.ac.id'),
        '/'
    );

    $urlParts = parse_url($baseUrl);

    if (
        !is_array($urlParts)
        || strtolower((string) ($urlParts['scheme'] ?? '')) !== 'https'
        || strtolower((string) ($urlParts['host'] ?? '')) !== 'siakad.polinema.ac.id'
        || isset($urlParts['user'])
        || isset($urlParts['pass'])
        || isset($urlParts['query'])
        || isset($urlParts['fragment'])
        || !in_array($urlParts['path'] ?? '', ['', '/'], true)
    ) {
        return [
            'success' => false,
            'message' => 'Alamat server SIAKAD belum dikonfigurasi dengan benar.',
        ];
    }

    $cookieFile = tempnam(sys_get_temp_dir(), 'tt_siakad_');

    if ($cookieFile === false) {
        return [
            'success' => false,
            'message' => 'Sesi sementara tidak dapat dibuat.',
        ];
    }

    @chmod($cookieFile, 0600);
    $curl = false;

    try {
        $curl = curl_init($baseUrl . '/login');

        if ($curl === false) {
            return [
                'success' => false,
                'message' => 'Koneksi ke server SIAKAD tidak dapat dimulai.',
            ];
        }

        $commonOptions = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_COOKIEJAR => $cookieFile,
            CURLOPT_COOKIEFILE => $cookieFile,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
        ];

        curl_setopt_array($curl, $commonOptions + [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'username' => $nim,
                'password' => $password,
            ]),
            CURLOPT_HTTPHEADER => [
                'X-Requested-With: XMLHttpRequest',
                'Accept: application/json, text/javascript, */*; q=0.01',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Origin: ' . $baseUrl,
                'Referer: ' . $baseUrl . '/',
            ],
            CURLOPT_FOLLOWLOCATION => false,
        ]);

        $loginResponse = curl_exec($curl);

        if ($loginResponse === false) {
            error_log('[TitikTemu] SIAKAD login request failed. cURL errno: ' . curl_errno($curl));
            return [
                'success' => false,
                'message' => 'Tidak dapat menghubungi SIAKAD. Coba lagi beberapa saat.',
            ];
        }

        $loginHttpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $loginJson = json_decode($loginResponse, true);

        if (
            $loginHttpCode !== 200
            || !is_array($loginJson)
            || ($loginJson['output'] ?? null) !== 'ok'
        ) {
            return [
                'success' => false,
                'message' => 'NIM atau password SIAKAD salah.',
            ];
        }

        // Pertahankan handle cURL yang sama agar cookie yang diterima pada
        // POST /login tetap tersedia untuk GET /beranda.
        curl_setopt_array($curl, [
            CURLOPT_URL => $baseUrl . '/beranda',
            CURLOPT_POST => false,
            CURLOPT_POSTFIELDS => '',
            CURLOPT_HTTPGET => true,
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Referer: ' . $baseUrl . '/',
            ],
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
        ]);

        $dashboardHtml = curl_exec($curl);

        if ($dashboardHtml === false) {
            error_log('[TitikTemu] SIAKAD profile request failed. cURL errno: ' . curl_errno($curl));
            return [
                'success' => false,
                'message' => 'Profil SIAKAD tidak dapat dibaca. Silakan coba lagi.',
            ];
        }

        $dashboardHttpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($dashboardHttpCode !== 200) {
            return [
                'success' => false,
                'message' => 'Sesi SIAKAD tidak dapat diverifikasi.',
            ];
        }

        $profile = siakad_extract_profile_from_html($dashboardHtml, $nim);

        if ($profile !== null) {
            return [
                'success' => true,
                'data' => $profile,
            ];
        }

        // Diagnostik tidak menganggap setiap input password sebagai halaman
        // login karena halaman profil juga mempunyai form ganti password.
        $effectiveUrl = (string) curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);
        $effectivePath = (string) (parse_url($effectiveUrl, PHP_URL_PATH) ?: '/');
        $contentType = (string) (curl_getinfo($curl, CURLINFO_CONTENT_TYPE) ?: 'unknown');
        $redirectCount = (int) curl_getinfo($curl, CURLINFO_REDIRECT_COUNT);
        $htmlBytes = strlen($dashboardHtml);
        $looksLikeLogin = siakad_html_looks_like_login_page($dashboardHtml);
        $hasPasswordField = preg_match(
            '~<input\b[^>]*\btype\s*=\s*["\']?password~i',
            $dashboardHtml
        ) === 1;
        $hasBreadcrumbMarker = siakad_has_breadcrumb_marker($dashboardHtml);
        $breadcrumbTextCount = count(siakad_extract_breadcrumb_texts($dashboardHtml));
        $identityElementCount = count(siakad_extract_identity_texts_from_html($dashboardHtml));
        $textCallCount = preg_match_all('~\.text\s*\(~i', $dashboardHtml);
        $candidateCount = count(siakad_extract_profile_candidates_from_html($dashboardHtml));

        if ($looksLikeLogin) {
            $diagnosis = 'login_form_detected';
            $message = 'SIAKAD mengembalikan form login saat halaman profil dibuka. Cookie sesi belum terpakai pada request profil.';
        } elseif ($candidateCount > 0) {
            $diagnosis = 'nim_mismatch';
            $message = 'Halaman SIAKAD memuat identitas, tetapi NIM pada profil berbeda dari NIM yang dimasukkan.';
        } elseif ($identityElementCount > 0) {
            $diagnosis = 'identity_format_unrecognized';
            $message = 'Elemen identitas profil ditemukan, tetapi format NIM dan nama belum dikenali parser.';
        } elseif ($hasBreadcrumbMarker || $textCallCount > 0) {
            $diagnosis = 'profile_identity_not_present';
            $message = 'Breadcrumb navigasi ditemukan, tetapi tidak berisi identitas mahasiswa. Identitas profil tidak ditemukan pada elemen yang dikenali.';
        } else {
            $diagnosis = 'profile_identity_not_present';
            $message = 'Halaman /beranda berhasil dibuka, tetapi identitas mahasiswa tidak ditemukan pada HTML yang diterima.';
        }

        $safeContentType = preg_replace('/[^A-Za-z0-9.+\/=; -]/', '', $contentType) ?: 'unknown';
        error_log(sprintf(
            '[TitikTemu] SIAKAD diagnosis=%s http=%d path=%s content_type=%s redirects=%d bytes=%d login_form=%s password_field=%s breadcrumb=%s breadcrumb_texts=%d identity_elements=%d text_calls=%d candidates=%d',
            $diagnosis,
            $dashboardHttpCode,
            substr($effectivePath, 0, 100),
            substr($safeContentType, 0, 80),
            $redirectCount,
            $htmlBytes,
            $looksLikeLogin ? 'yes' : 'no',
            $hasPasswordField ? 'yes' : 'no',
            $hasBreadcrumbMarker ? 'yes' : 'no',
            $breadcrumbTextCount,
            $identityElementCount,
            is_int($textCallCount) ? $textCallCount : 0,
            $candidateCount
        ));

        $message .= sprintf(
            ' [Diagnostik: HTTP %d, path %s, redirect %d, ukuran %d byte, form login %s, field password %s, breadcrumb %s, teks breadcrumb %d, elemen identitas %d, pemanggilan .text() %d, kandidat identitas %d.]',
            $dashboardHttpCode,
            substr($effectivePath, 0, 100),
            $redirectCount,
            $htmlBytes,
            $looksLikeLogin ? 'terdeteksi' : 'tidak terdeteksi',
            $hasPasswordField ? 'ada' : 'tidak ada',
            $hasBreadcrumbMarker ? 'ada' : 'tidak ada',
            $breadcrumbTextCount,
            $identityElementCount,
            is_int($textCallCount) ? $textCallCount : 0,
            $candidateCount
        );

        return [
            'success' => false,
            'message' => $message,
        ];
    } finally {
        if ($curl !== false) {
            curl_close($curl);
        }

        if (is_file($cookieFile)) {
            @unlink($cookieFile);
        }
    }
}
