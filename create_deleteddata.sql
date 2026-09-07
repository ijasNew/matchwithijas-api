-- ============================================================
-- MATCH WITH IJAS
-- Deleted profile archive
-- Run this migration once before using admin profile deletion.
-- ============================================================

CREATE TABLE IF NOT EXISTS `deleteddata` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `original_user_id` bigint(20) UNSIGNED NOT NULL,
  `member_id` varchar(20) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `place` varchar(150) DEFAULT NULL,
  `delete_reason` varchar(1000) NOT NULL,
  `deleted_by_admin_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `account_data` LONGTEXT,
  `profile_data` LONGTEXT,
  `preferences_data` LONGTEXT,
  `preference_values_data` LONGTEXT,
  `related_data` LONGTEXT,
  `deleted_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_deleteddata_member_id` (`member_id`),
  KEY `idx_deleteddata_phone` (`phone`),
  KEY `idx_deleteddata_original_user` (`original_user_id`),
  KEY `idx_deleteddata_deleted_at` (`deleted_at`),
  KEY `idx_deleteddata_admin` (`deleted_by_admin_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
