-- TitikTemu migration 006: alur akhir klaim tanpa serah terima petugas.
--
-- Alur baru:
--   menunggu -> siap_diambil -> diterima
--
-- 1. Petugas menyetujui klaim; status klaim menjadi 'siap_diambil' dan
--    status barang temuan menjadi 'diproses' (siap diambil di pos).
-- 2. Mahasiswa mengambil barang, lalu mengonfirmasi lewat web. Status klaim
--    menjadi 'diterima', feedback wajib diisi, dan status barang menjadi
--    'selesai'.
--
-- Tabel serah_terima tidak dipakai lagi. Data penyerahan lama dipindahkan ke
-- klaim sebelum tabel dihapus agar riwayat tidak hilang.

BEGIN;

ALTER TABLE klaim
    ADD COLUMN tanggal_diterima TIMESTAMPTZ,
    ADD COLUMN rating_feedback SMALLINT,
    ADD COLUMN komentar_feedback TEXT;

COMMENT ON COLUMN klaim.tanggal_diterima IS
    'Waktu mahasiswa mengonfirmasi bahwa barang sudah diterima.';
COMMENT ON COLUMN klaim.rating_feedback IS
    'Nilai kepuasan mahasiswa 1-5 saat mengonfirmasi penerimaan.';
COMMENT ON COLUMN klaim.komentar_feedback IS
    'Komentar opsional mahasiswa saat mengonfirmasi penerimaan.';

-- Constraint lama dibuang lebih dahulu: nilainya belum mengenal status baru,
-- sehingga baik UPDATE data lama maupun constraint baru akan ditolak.
ALTER TABLE klaim
    DROP CONSTRAINT klaim_status_verifikasi_check;

-- Penyerahan lama dianggap sudah dikonfirmasi oleh mahasiswa.
UPDATE klaim AS c
SET status_verifikasi = 'diterima',
    tanggal_diterima = st.tanggal_serah_terima
FROM serah_terima AS st
WHERE st.id_klaim = c.id_klaim;

-- Klaim yang sudah disetujui tetapi belum diserahkan menjadi siap diambil.
UPDATE klaim
SET status_verifikasi = 'siap_diambil'
WHERE status_verifikasi = 'disetujui';

ALTER TABLE klaim
    ADD CONSTRAINT klaim_status_verifikasi_check
    CHECK (
        status_verifikasi IN (
            'menunggu',
            'siap_diambil',
            'diterima',
            'ditolak',
            'dibatalkan'
        )
    );

ALTER TABLE klaim
    ADD CONSTRAINT chk_klaim_rating_feedback
    CHECK (rating_feedback IS NULL OR rating_feedback BETWEEN 1 AND 5);

ALTER TABLE klaim
    ADD CONSTRAINT chk_klaim_diterima
    CHECK (
        status_verifikasi <> 'diterima'
        OR tanggal_diterima IS NOT NULL
    );

ALTER TABLE klaim
    ADD CONSTRAINT chk_klaim_feedback_hanya_diterima
    CHECK (
        rating_feedback IS NULL
        OR status_verifikasi = 'diterima'
    );

-- Klaim aktif: satu mahasiswa hanya boleh punya satu klaim aktif per barang.
DROP INDEX uq_klaim_aktif_pengguna_barang;

CREATE UNIQUE INDEX uq_klaim_aktif_pengguna_barang
    ON klaim(id_pengumuman, id_pengguna)
    WHERE status_verifikasi IN ('menunggu', 'siap_diambil');

-- Satu barang hanya boleh memiliki satu klaim yang diterima/disiapkan.
DROP INDEX uq_klaim_disetujui_per_barang;

CREATE UNIQUE INDEX uq_klaim_aktif_per_barang
    ON klaim(id_pengumuman)
    WHERE status_verifikasi IN ('siap_diambil', 'diterima');

-- Validasi transisi status penerimaan oleh mahasiswa.
CREATE OR REPLACE FUNCTION fn_validasi_penerimaan_klaim()
RETURNS TRIGGER AS $$
BEGIN
    IF OLD.status_verifikasi = 'diterima'
       AND NEW.status_verifikasi <> 'diterima' THEN
        RAISE EXCEPTION
            'Klaim yang sudah diterima tidak dapat diubah statusnya.';
    END IF;

    IF NEW.status_verifikasi = 'diterima'
       AND OLD.status_verifikasi IS DISTINCT FROM 'diterima' THEN
        IF OLD.status_verifikasi IS DISTINCT FROM 'siap_diambil' THEN
            RAISE EXCEPTION
                'Klaim hanya dapat diterima dari status siap_diambil.';
        END IF;

        IF NEW.tanggal_diterima IS NULL THEN
            RAISE EXCEPTION
                'Tanggal penerimaan wajib diisi.';
        END IF;

        IF NEW.rating_feedback IS NULL THEN
            RAISE EXCEPTION
                'Feedback wajib diisi saat mengonfirmasi penerimaan.';
        END IF;
    END IF;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_validasi_penerimaan_klaim
BEFORE UPDATE OF status_verifikasi ON klaim
FOR EACH ROW
EXECUTE FUNCTION fn_validasi_penerimaan_klaim();

-- Tabel serah terima dihapus karena alur berakhir di klaim.
DROP TRIGGER trg_validasi_serah_terima_insert ON serah_terima;
DROP TABLE serah_terima;
DROP FUNCTION fn_validasi_serah_terima();

COMMIT;
