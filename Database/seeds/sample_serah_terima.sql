-- TitikTemu sample data: alur klaim sampai serah terima.
-- Jalankan HANYA pada database development / testing, bukan production.
-- Prasyarat: schema awal, seed master, dan 003_add_serah_terima.sql sudah diterapkan.
-- Script ini membuat akun, barang temuan, klaim disetujui, lalu catatan penyerahan.
-- Script dapat dijalankan ulang: data demo yang sudah memiliki serah terima tidak dibuat ulang.
--
-- Akun demo menggunakan password awal: Demo123!
-- Ganti atau hapus akun demo sebelum aplikasi dipakai di lingkungan production.

BEGIN;

DO $$
DECLARE
    v_id_kategori INTEGER;
    v_id_lokasi INTEGER;
    v_id_pos INTEGER;
    v_id_pelapor INTEGER;
    v_id_penerima INTEGER;
    v_id_petugas INTEGER;
    v_id_pengumuman INTEGER;
    v_id_klaim INTEGER;

    v_judul_demo CONSTANT VARCHAR(150) := '[DEMO] Laptop ditemukan di area administrasi';
    v_hash_demo CONSTANT VARCHAR(255) := '$2y$12$S0GgKArORF3CF7WHkPOCt..aDhKj0lm76LU/pLU8EECeQ.g07.wQ.';
BEGIN
    ---------------------------------------------------------------------------
    -- 1. Pastikan kategori dan lokasi master tersedia.
    ---------------------------------------------------------------------------
    INSERT INTO kategori (nama_kategori, deskripsi)
    VALUES ('Elektronik', 'Perangkat elektronik dan aksesori')
    ON CONFLICT (nama_kategori)
    DO UPDATE SET is_active = TRUE, updated_at = NOW();

    SELECT id_kategori
    INTO v_id_kategori
    FROM kategori
    WHERE nama_kategori = 'Elektronik';

    INSERT INTO lokasi (nama_lokasi, deskripsi)
    VALUES ('Ruang administrasi JTI', 'Ruang administrasi jurusan')
    ON CONFLICT (nama_lokasi)
    DO UPDATE SET is_active = TRUE, updated_at = NOW();

    SELECT id_lokasi
    INTO v_id_lokasi
    FROM lokasi
    WHERE nama_lokasi = 'Ruang administrasi JTI';

    ---------------------------------------------------------------------------
    -- 2. Siapkan pos penyerahan demo.
    ---------------------------------------------------------------------------
    INSERT INTO pos_penyerahan (
        nama_pos,
        jenis_pos,
        id_lokasi,
        detail_lokasi
    )
    VALUES (
        'Pos Keamanan Utama Demo',
        'pos_keamanan',
        v_id_lokasi,
        'Contoh pos penyerahan untuk pengujian database'
    )
    ON CONFLICT (nama_pos, id_lokasi)
    DO UPDATE SET is_active = TRUE, updated_at = NOW()
    RETURNING id_pos INTO v_id_pos;

    ---------------------------------------------------------------------------
    -- 3. Buat akun demo.
    -- Password awal ketiga akun: Demo123!
    -- Hash dibuat menggunakan password_hash() PHP.
    ---------------------------------------------------------------------------
    INSERT INTO pengguna (
        nim, username, password_hash, nama, email,
        no_hp, prodi, role, is_active
    )
    VALUES (
        '999999999999991', NULL, v_hash_demo,
        'Demo Pelapor Barang', 'pelapor.demo@example.test',
        '080000000001', 'Sistem Informasi Bisnis', 'mahasiswa', TRUE
    )
    ON CONFLICT DO NOTHING;

    SELECT id_pengguna
    INTO v_id_pelapor
    FROM pengguna
    WHERE nim = '999999999999991'
      AND role = 'mahasiswa';

    IF v_id_pelapor IS NULL THEN
        RAISE EXCEPTION 'Akun demo pelapor tidak dapat disiapkan. Periksa konflik NIM/email.';
    END IF;

    INSERT INTO pengguna (
        nim, username, password_hash, nama, email,
        no_hp, prodi, role, is_active
    )
    VALUES (
        '999999999999992', NULL, v_hash_demo,
        'Demo Penerima Barang', 'penerima.demo@example.test',
        '080000000002', 'Sistem Informasi Bisnis', 'mahasiswa', TRUE
    )
    ON CONFLICT DO NOTHING;

    SELECT id_pengguna
    INTO v_id_penerima
    FROM pengguna
    WHERE nim = '999999999999992'
      AND role = 'mahasiswa';

    IF v_id_penerima IS NULL THEN
        RAISE EXCEPTION 'Akun demo penerima tidak dapat disiapkan. Periksa konflik NIM/email.';
    END IF;

    INSERT INTO pengguna (
        nim, username, password_hash, nama, email,
        no_hp, prodi, role, is_active
    )
    VALUES (
        NULL, 'petugas.demo', v_hash_demo,
        'Demo Petugas', 'petugas.demo@example.test',
        '080000000003', 'Administrasi', 'petugas', TRUE
    )
    ON CONFLICT DO NOTHING;

    SELECT id_pengguna
    INTO v_id_petugas
    FROM pengguna
    WHERE LOWER(username) = LOWER('petugas.demo')
      AND role = 'petugas';

    IF v_id_petugas IS NULL THEN
        RAISE EXCEPTION 'Akun demo petugas tidak dapat disiapkan. Periksa konflik username/email.';
    END IF;

    ---------------------------------------------------------------------------
    -- 4. Cari laporan demo. Jika belum ada, buat satu laporan barang temuan.
    ---------------------------------------------------------------------------
    SELECT id_pengumuman
    INTO v_id_pengumuman
    FROM pengumuman
    WHERE id_pengguna = v_id_pelapor
      AND jenis = 'temuan'
      AND judul = v_judul_demo
    ORDER BY id_pengumuman DESC
    LIMIT 1;

    IF v_id_pengumuman IS NULL THEN
        INSERT INTO pengumuman (
            id_pengguna,
            id_kategori,
            id_lokasi,
            id_pos,
            jenis,
            judul,
            deskripsi,
            email_kontak,
            lokasi_kejadian,
            tanggal_waktu_kejadian,
            detail_lokasi,
            status,
            id_verifikator,
            catatan_verifikasi,
            tanggal_verifikasi
        )
        VALUES (
            v_id_pelapor,
            v_id_kategori,
            v_id_lokasi,
            v_id_pos,
            'temuan',
            v_judul_demo,
            'Data contoh untuk menguji proses klaim dan serah terima. Barang berupa laptop warna hitam.',
            'pelapor.demo@example.test',
            'Ruang administrasi JTI',
            NOW() - INTERVAL '2 days',
            'Dekat meja pelayanan',
            'tersedia',
            v_id_petugas,
            'Laporan demo telah diperiksa dan disetujui.',
            NOW() - INTERVAL '1 day'
        )
        RETURNING id_pengumuman INTO v_id_pengumuman;
    END IF;

    ---------------------------------------------------------------------------
    -- 5. Jika serah terima untuk klaim demo sudah ada, hentikan tanpa duplikasi.
    ---------------------------------------------------------------------------
    SELECT id_klaim
    INTO v_id_klaim
    FROM klaim
    WHERE id_pengumuman = v_id_pengumuman
      AND id_pengguna = v_id_penerima
    ORDER BY id_klaim DESC
    LIMIT 1;

    IF v_id_klaim IS NOT NULL AND EXISTS (
        SELECT 1
        FROM serah_terima
        WHERE id_klaim = v_id_klaim
    ) THEN
        RAISE NOTICE 'Sample serah terima sudah tersedia. Tidak ada data duplikat yang dibuat.';
    ELSE
        -----------------------------------------------------------------------
        -- 6. Pastikan laporan berstatus tersedia sebelum klaim dibuat/disetujui.
        -----------------------------------------------------------------------
        UPDATE pengumuman
        SET id_kategori = v_id_kategori,
            id_lokasi = v_id_lokasi,
            id_pos = v_id_pos,
            status = 'tersedia',
            id_verifikator = v_id_petugas,
            catatan_verifikasi = 'Laporan demo telah diperiksa dan disetujui.',
            tanggal_verifikasi = COALESCE(tanggal_verifikasi, NOW()),
            updated_at = NOW()
        WHERE id_pengumuman = v_id_pengumuman;

        -----------------------------------------------------------------------
        -- 7. Buat atau siapkan klaim yang sudah disetujui.
        -- Trigger fn_validasi_klaim memerlukan laporan temuan berstatus tersedia.
        -----------------------------------------------------------------------
        IF v_id_klaim IS NULL THEN
            INSERT INTO klaim (
                id_pengumuman,
                id_pengguna,
                id_petugas,
                deskripsi_bukti,
                bukti_path,
                status_verifikasi,
                tanggal_pengajuan,
                tanggal_verifikasi,
                catatan_verifikasi
            )
            VALUES (
                v_id_pengumuman,
                v_id_penerima,
                v_id_petugas,
                'Contoh deskripsi bukti kepemilikan untuk pengujian. Penerima dapat menjelaskan ciri khusus laptop.',
                NULL,
                'disetujui',
                NOW() - INTERVAL '12 hours',
                NOW() - INTERVAL '6 hours',
                'Klaim demo disetujui untuk pengujian alur serah terima.'
            )
            RETURNING id_klaim INTO v_id_klaim;
        ELSE
            UPDATE klaim
            SET id_petugas = v_id_petugas,
                status_verifikasi = 'disetujui',
                tanggal_verifikasi = COALESCE(tanggal_verifikasi, NOW()),
                catatan_verifikasi = 'Klaim demo disetujui untuk pengujian alur serah terima.',
                updated_at = NOW()
            WHERE id_klaim = v_id_klaim;
        END IF;

        -----------------------------------------------------------------------
        -- 8. Laporan masuk tahap diproses sebelum penyerahan dicatat.
        -----------------------------------------------------------------------
        UPDATE pengumuman
        SET status = 'diproses', updated_at = NOW()
        WHERE id_pengumuman = v_id_pengumuman;

        -----------------------------------------------------------------------
        -- 9. Catat penyerahan. Trigger akan memastikan klaim, petugas, dan pos valid.
        -----------------------------------------------------------------------
        INSERT INTO serah_terima (
            id_klaim,
            id_petugas,
            id_pos,
            nama_penerima,
            tanggal_serah_terima,
            catatan
        )
        VALUES (
            v_id_klaim,
            v_id_petugas,
            v_id_pos,
            'Nama ini akan diisi otomatis oleh trigger dari data akun penerima.',
            NOW(),
            'Contoh penyerahan barang telah dilakukan di Pos Keamanan Utama Demo.'
        )
        ON CONFLICT (id_klaim) DO NOTHING;

        -----------------------------------------------------------------------
        -- 10. Setelah penyerahan dicatat, tandai laporan sebagai selesai.
        -----------------------------------------------------------------------
        UPDATE pengumuman
        SET status = 'selesai', updated_at = NOW()
        WHERE id_pengumuman = v_id_pengumuman;

        -----------------------------------------------------------------------
        -- 11. Tambahkan riwayat status contoh jika belum ada.
        -----------------------------------------------------------------------
        INSERT INTO riwayat_status_pengumuman (
            id_pengumuman, status_sebelumnya, status_baru, id_pengubah, catatan
        )
        SELECT v_id_pengumuman, 'menunggu', 'tersedia', v_id_petugas,
               'Sample: laporan temuan disetujui.'
        WHERE NOT EXISTS (
            SELECT 1 FROM riwayat_status_pengumuman
            WHERE id_pengumuman = v_id_pengumuman
              AND status_baru = 'tersedia'
              AND catatan = 'Sample: laporan temuan disetujui.'
        );

        INSERT INTO riwayat_status_pengumuman (
            id_pengumuman, status_sebelumnya, status_baru, id_pengubah, catatan
        )
        SELECT v_id_pengumuman, 'tersedia', 'diproses', v_id_petugas,
               'Sample: klaim disetujui dan barang disiapkan untuk diserahkan.'
        WHERE NOT EXISTS (
            SELECT 1 FROM riwayat_status_pengumuman
            WHERE id_pengumuman = v_id_pengumuman
              AND status_baru = 'diproses'
              AND catatan = 'Sample: klaim disetujui dan barang disiapkan untuk diserahkan.'
        );

        INSERT INTO riwayat_status_pengumuman (
            id_pengumuman, status_sebelumnya, status_baru, id_pengubah, catatan
        )
        SELECT v_id_pengumuman, 'diproses', 'selesai', v_id_petugas,
               'Sample: barang telah diserahkan kepada penerima.'
        WHERE NOT EXISTS (
            SELECT 1 FROM riwayat_status_pengumuman
            WHERE id_pengumuman = v_id_pengumuman
              AND status_baru = 'selesai'
              AND catatan = 'Sample: barang telah diserahkan kepada penerima.'
        );
    END IF;

    RAISE NOTICE 'Sample serah terima selesai diproses.';
    RAISE NOTICE 'ID laporan demo: %', v_id_pengumuman;
    RAISE NOTICE 'ID klaim demo: %', v_id_klaim;
END;
$$;

COMMIT;

-- Hasil pemeriksaan sample serah terima.
SELECT
    st.id_serah_terima,
    st.tanggal_serah_terima,
    st.nama_penerima,
    st.catatan,
    k.id_klaim,
    k.status_verifikasi AS status_klaim,
    p.id_pengumuman,
    p.judul AS judul_barang,
    p.status AS status_laporan,
    petugas.nama AS nama_petugas,
    pos.nama_pos
FROM serah_terima st
JOIN klaim k
    ON k.id_klaim = st.id_klaim
JOIN pengumuman p
    ON p.id_pengumuman = k.id_pengumuman
JOIN pengguna petugas
    ON petugas.id_pengguna = st.id_petugas
JOIN pos_penyerahan pos
    ON pos.id_pos = st.id_pos
WHERE p.judul = '[DEMO] Laptop ditemukan di area administrasi'
ORDER BY st.id_serah_terima DESC;

-- Akun demo (password ketiganya: Demo123!).
SELECT id_pengguna, nim, username, nama, email, role
FROM pengguna
WHERE nim IN ('999999999999991', '999999999999992')
   OR username = 'petugas.demo'
ORDER BY role, nama;
