SELECT tablename FROM pg_tables WHERE schemaname='public' ORDER BY tablename;
SELECT table_name FROM information_schema.views WHERE table_schema='public' AND table_name='v_kandidat_kecocokan';
SELECT 'kategori' AS tabel,COUNT(*) AS jumlah FROM kategori
UNION ALL SELECT 'lokasi',COUNT(*) FROM lokasi
UNION ALL SELECT 'pos_penyerahan',COUNT(*) FROM pos_penyerahan
UNION ALL SELECT 'pengguna',COUNT(*) FROM pengguna
UNION ALL SELECT 'pengumuman',COUNT(*) FROM pengumuman
UNION ALL SELECT 'foto_pengumuman',COUNT(*) FROM foto_pengumuman
UNION ALL SELECT 'klaim',COUNT(*) FROM klaim
UNION ALL SELECT 'kecocokan',COUNT(*) FROM kecocokan
UNION ALL SELECT 'riwayat_status_pengumuman',COUNT(*) FROM riwayat_status_pengumuman
UNION ALL SELECT 'notifikasi_email',COUNT(*) FROM notifikasi_email
ORDER BY tabel;
SELECT * FROM v_kandidat_kecocokan ORDER BY lokasi_sama DESC, selisih_hari LIMIT 20;
SELECT event_object_table,trigger_name FROM information_schema.triggers WHERE trigger_schema='public' ORDER BY event_object_table,trigger_name;

SELECT table_name, column_name, data_type, is_nullable
FROM information_schema.columns
WHERE table_schema='public'
  AND ((table_name='pengumuman' AND column_name IN ('email_kontak','lokasi_kejadian','tanggal_waktu_kejadian','status'))
    OR (table_name='pengguna' AND column_name IN ('nim','username','password_hash','role','last_login'))
    OR (table_name='notifikasi_email' AND column_name IN ('status_kirim','jumlah_percobaan','error_terakhir')))
ORDER BY table_name, ordinal_position;

SELECT conrelid::regclass AS tabel, conname AS nama_constraint, pg_get_constraintdef(oid) AS definisi
FROM pg_constraint
WHERE connamespace='public'::regnamespace
  AND conname IN ('chk_pengumuman_status_jenis','chk_pos_hanya_untuk_temuan','chk_email_kontak_format','uq_klaim_disetujui_per_barang')
ORDER BY conrelid::regclass::text, conname;
