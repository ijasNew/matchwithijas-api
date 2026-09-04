-- Match With Ijas (MWI) - Migration: Shortlists
-- Run this after mwi_phase2_schema.sql
-- The `interests` table already exists in mwi_phase2_schema.sql, no changes needed there.

USE mwi;

CREATE TABLE IF NOT EXISTS shortlists (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    shortlisted_user_id BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_shortlist_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT fk_shortlist_target
        FOREIGN KEY (shortlisted_user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT chk_shortlist_not_self CHECK (user_id <> shortlisted_user_id),

    UNIQUE KEY uq_shortlist_pair (user_id, shortlisted_user_id),
    INDEX idx_shortlist_user (user_id)
) ENGINE=InnoDB;
