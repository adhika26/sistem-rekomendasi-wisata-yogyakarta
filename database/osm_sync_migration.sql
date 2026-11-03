-- Migrasi kolom metadata sinkronisasi OpenStreetMap
-- Jalankan sekali pada database db_rekomendasi_wisata.
-- Kolom ini tidak menghapus atau mengubah data yang sudah ada.

-- Backup tabel sebelum sinkronisasi pertama (opsional, aman dijalankan berulang)
CREATE TABLE IF NOT EXISTS wisata_backup_before_osm_sync AS SELECT * FROM wisata;

ALTER TABLE `wisata`
    ADD COLUMN IF NOT EXISTS `osm_type` VARCHAR(10) NULL DEFAULT NULL AFTER `id`,
    ADD COLUMN IF NOT EXISTS `osm_id` BIGINT NULL DEFAULT NULL AFTER `osm_type`,
    ADD COLUMN IF NOT EXISTS `source` VARCHAR(20) NOT NULL DEFAULT 'manual' AFTER `clean_description`,
    ADD COLUMN IF NOT EXISTS `last_synced_at` DATETIME NULL DEFAULT NULL AFTER `source`;

ALTER TABLE `wisata`
    ADD UNIQUE KEY IF NOT EXISTS `uniq_osm` (`osm_type`, `osm_id`);
