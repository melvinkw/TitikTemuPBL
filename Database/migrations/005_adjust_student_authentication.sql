-- TitikTemu migration 005
-- Mahasiswa diverifikasi melalui SIAKAD, sedangkan petugas/admin
-- menggunakan password lokal yang disimpan dalam bentuk hash.
-- Jalankan satu kali setelah migration 001-004 berhasil.

BEGIN;

ALTER TABLE pengguna
    ALTER COLUMN password_hash DROP NOT NULL;

-- Hapus hash lama pada akun mahasiswa karena password mereka tidak
-- boleh disimpan di database TitikTemu.
UPDATE pengguna
SET password_hash = NULL
WHERE role = 'mahasiswa';

ALTER TABLE pengguna
    ADD CONSTRAINT chk_pengguna_password_by_role
    CHECK (
        (role = 'mahasiswa' AND password_hash IS NULL)
        OR
        (role IN ('petugas', 'admin') AND password_hash IS NOT NULL AND password_hash <> '')
    );

COMMIT;
