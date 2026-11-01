ALTER TABLE foto_pengumuman
ADD COLUMN bucket_name VARCHAR(100)
NOT NULL DEFAULT 'laporan-pending';

ALTER TABLE klaim
ADD COLUMN bukti_bucket VARCHAR(100)
NOT NULL DEFAULT 'klaim-private';