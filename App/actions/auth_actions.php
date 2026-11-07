<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/services/siakad_service.php';
require_once dirname(__DIR__) . '/queries/auth_queries.php';

function authenticate_user(string $identifier, string $password): array
{
    $identifier = trim($identifier);

    if ($identifier === '' || $password === '') {
        return [
            'success' => false,
            'message' => 'NIM/username dan password wajib diisi.',
        ];
    }

    // Mahasiswa: NIM hanya berisi 8-20 digit.
    if (preg_match('/^[0-9]+$/D', $identifier) === 1) {
        if (preg_match('/^[0-9]{8,20}$/D', $identifier) !== 1) {
            return [
                'success' => false,
                'message' => 'Format NIM tidak valid.',
            ];
        }

        $siakadResult = siakad_login($identifier, $password);

        if (($siakadResult['success'] ?? false) !== true) {
            return [
                'success' => false,
                'message' => $siakadResult['message'] ?? 'Autentikasi SIAKAD gagal.',
            ];
        }

        $profile = $siakadResult['data'] ?? null;

        if (
            !is_array($profile)
            || ($profile['nim'] ?? null) !== $identifier
            || !is_string($profile['nama'] ?? null)
            || trim($profile['nama']) === ''
        ) {
            return [
                'success' => false,
                'message' => 'Data mahasiswa dari SIAKAD tidak valid.',
            ];
        }

        $user = save_or_update_active_student(
            $identifier,
            trim($profile['nama'])
        );

        if ($user === null) {
            return [
                'success' => false,
                'message' => 'Akun TitikTemu tidak aktif. Hubungi petugas atau administrator.',
            ];
        }

        return [
            'success' => true,
            'user' => $user,
        ];
    }

    // Petugas/admin: username lokal dengan format sesuai constraint database.
    if (preg_match('/^[A-Za-z][A-Za-z0-9._-]{3,49}$/D', $identifier) !== 1) {
        return [
            'success' => false,
            'message' => 'NIM atau username tidak valid.',
        ];
    }

    $user = find_staff_by_username($identifier);

    if (
        $user === null
        || !$user['is_active']
        || !is_string($user['password_hash'] ?? null)
        || $user['password_hash'] === ''
        || !password_verify($password, $user['password_hash'])
    ) {
        return [
            'success' => false,
            'message' => 'Username atau password salah.',
        ];
    }

    update_staff_last_login((int) $user['id_pengguna']);

    unset($user['password_hash']);

    return [
        'success' => true,
        'user' => $user,
    ];
}
