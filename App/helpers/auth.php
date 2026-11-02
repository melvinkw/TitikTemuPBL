<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/queries/auth_queries.php';

function auth_login(array $user): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        throw new RuntimeException('Session belum dimulai.');
    }

    session_regenerate_id(true);

    $_SESSION['auth_user'] = [
        'id_pengguna' => (int) $user['id_pengguna'],
        'nim' => $user['nim'] ?? null,
        'username' => $user['username'] ?? null,
        'nama' => (string) $user['nama'],
        'role' => (string) $user['role'],
    ];
}

/** Mengambil user dari session tanpa melakukan query database. */
function auth_current_user(): ?array
{
    $user = $_SESSION['auth_user'] ?? null;

    return is_array($user) ? $user : null;
}

/** Menghapus session autentikasi dan cookie session dari browser. */
function auth_logout(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'] ?: '/',
            'domain' => $params['domain'],
            'secure' => (bool) $params['secure'],
            'httponly' => (bool) $params['httponly'],
            'samesite' => $params['samesite'] ?: 'Lax',
        ]);
    }

    session_destroy();
}

function auth_require_login(): array
{
    $sessionUser = auth_current_user();
    $userId = (int) ($sessionUser['id_pengguna'] ?? 0);

    if ($userId < 1) {
        redirect('/login.php');
    }

    $freshUser = find_user_for_auth_session($userId);

    if (
        $freshUser === null
        || !$freshUser['is_active']
        || $freshUser['role'] !== ($sessionUser['role'] ?? null)
    ) {
        auth_logout();
        redirect('/login.php');
    }

    $_SESSION['auth_user'] = [
        'id_pengguna' => $freshUser['id_pengguna'],
        'nim' => $freshUser['nim'],
        'username' => $freshUser['username'],
        'nama' => $freshUser['nama'],
        'role' => $freshUser['role'],
    ];

    return $_SESSION['auth_user'];
}

function auth_require_role(array $allowedRoles): array
{
    $user = auth_require_login();

    if (!in_array($user['role'], $allowedRoles, true)) {
        http_response_code(403);
        exit('Anda tidak memiliki akses ke halaman ini.');
    }

    return $user;
}
