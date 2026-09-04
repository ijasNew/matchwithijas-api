-- Match With Ijas (MWI) - Phase 2 Database Schema
-- Target: MySQL 8.0+
-- Note: Do NOT store plaintext passwords or OTPs.

CREATE DATABASE IF NOT EXISTS mwi
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE mwi;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id VARCHAR(20) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NULL,
    role ENUM('user','admin') NOT NULL DEFAULT 'user',
    account_status ENUM('active','inactive','blocked','pending') NOT NULL DEFAULT 'pending',
    otp_verified TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login_at DATETIME NULL,
    INDEX idx_users_status (account_status),
    INDEX idx_users_role (role)
) ENGINE=InnoDB;

CREATE TABLE profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,

    profile_for VARCHAR(50) NOT NULL,
    gender VARCHAR(20) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    marital_status VARCHAR(50) NOT NULL,
    has_kids VARCHAR(20) NULL,
    number_of_kids SMALLINT UNSIGNED NULL,
    kids_living_status VARCHAR(50) NULL,
    date_of_birth DATE NOT NULL,
    height DECIMAL(5,2) NOT NULL,

    district VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL DEFAULT 'Kerala',
    pincode VARCHAR(10) NOT NULL,
    house_name VARCHAR(150) NOT NULL,
    place VARCHAR(150) NOT NULL,
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    location_source ENUM('current','map','manual') NULL,

    religion VARCHAR(50) NOT NULL,
    sect VARCHAR(100) NULL,
    muslim_group VARCHAR(100) NULL,
    salafi_group VARCHAR(100) NULL,
    caste VARCHAR(100) NULL,
    sub_caste VARCHAR(100) NULL,
    nakshatra VARCHAR(100) NULL,
    rashi VARCHAR(100) NULL,
    dosham VARCHAR(100) NULL,
    denomination VARCHAR(100) NULL,
    christian_sub_group VARCHAR(100) NULL,
    parish_name VARCHAR(150) NULL,

    highest_education VARCHAR(150) NOT NULL,
    specialization VARCHAR(150) NULL,
    job_title VARCHAR(150) NOT NULL,
    job_sector VARCHAR(100) NOT NULL,

    weight DECIMAL(5,2) NULL,
    body_type VARCHAR(50) NULL,
    complexion VARCHAR(50) NULL,
    physical_status VARCHAR(100) NULL,

    secondary_mobile VARCHAR(20) NULL,
    whatsapp_country_code VARCHAR(10) NULL,
    whatsapp_number VARCHAR(20) NULL,
    email VARCHAR(255) NULL,

    college_university VARCHAR(200) NULL,
    annual_income VARCHAR(100) NULL,
    work_location VARCHAR(200) NULL,
    work_location_type VARCHAR(50) NULL,
    work_state VARCHAR(100) NULL,
    work_district VARCHAR(100) NULL,
    work_country VARCHAR(100) NULL,
    work_city VARCHAR(100) NULL,
    company_name VARCHAR(200) NULL,

    father_name VARCHAR(150) NULL,
    father_occupation VARCHAR(150) NULL,
    father_status VARCHAR(50) NULL,
    mother_name VARCHAR(150) NULL,
    mother_occupation VARCHAR(150) NULL,
    mother_status VARCHAR(50) NULL,
    brothers SMALLINT UNSIGNED NULL,
    sisters SMALLINT UNSIGNED NULL,
    married_brothers SMALLINT UNSIGNED NULL,
    married_sisters SMALLINT UNSIGNED NULL,
    family_status VARCHAR(100) NULL,
    home_type VARCHAR(100) NULL,

    expectations TEXT NULL,

    registration_completed TINYINT(1) NOT NULL DEFAULT 0,
    profile_status ENUM('draft','new','pending_verification','verified','rejected','blocked') NOT NULL DEFAULT 'draft',
    completion_percentage TINYINT UNSIGNED NOT NULL DEFAULT 0,
    home_verified TINYINT(1) NOT NULL DEFAULT 0,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_profiles_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_profiles_status (profile_status),
    INDEX idx_profiles_gender (gender),
    INDEX idx_profiles_district (district),
    INDEX idx_profiles_religion (religion),
    INDEX idx_profiles_dob (date_of_birth),
    INDEX idx_profiles_marital (marital_status)
) ENGINE=InnoDB;

CREATE TABLE profile_preferences (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    age_min TINYINT UNSIGNED NULL,
    age_max TINYINT UNSIGNED NULL,
    height_min DECIMAL(5,2) NULL,
    height_max DECIMAL(5,2) NULL,
    preferred_religion VARCHAR(50) NULL,
    acceptance_of_kids VARCHAR(50) NULL,
    horoscope_required VARCHAR(50) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_preferences_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE preference_values (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    preference_type VARCHAR(50) NOT NULL,
    value VARCHAR(200) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_preference_values_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uq_preference_value (user_id, preference_type, value),
    INDEX idx_preference_lookup (preference_type, value),
    INDEX idx_preference_user_type (user_id, preference_type)
) ENGINE=InnoDB;

-- preference_type values used by the current frontend:
-- marital_status, sect, sunni_group, salafi_group, caste, sub_caste,
-- education, education_specific, career_sector, location, family_status,
-- physical_status, income, location_radius, complexion, star

CREATE TABLE profile_photos (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    display_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('active','pending','rejected','deleted') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_profile_photos_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_photos_user (user_id),
    INDEX idx_photos_primary (user_id, is_primary)
) ENGINE=InnoDB;

CREATE TABLE plans (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    duration_days INT UNSIGNED NULL,
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_plans_active (is_active)
) ENGINE=InnoDB;

CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    plan_id BIGINT UNSIGNED NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NULL,
    transaction_id VARCHAR(150) NULL UNIQUE,
    payment_status ENUM('pending','success','failed','refunded') NOT NULL DEFAULT 'pending',
    paid_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_payments_plan
        FOREIGN KEY (plan_id) REFERENCES plans(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_payments_user_status (user_id, payment_status),
    INDEX idx_payments_status (payment_status)
) ENGINE=InnoDB;

CREATE TABLE verification_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    payment_id BIGINT UNSIGNED NULL,
    status ENUM('pending','in_progress','verified','rejected','cancelled') NOT NULL DEFAULT 'pending',
    requested_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    started_at DATETIME NULL,
    completed_at DATETIME NULL,
    verified_by BIGINT UNSIGNED NULL,
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    location_accuracy DECIMAL(10,2) NULL,
    location_name VARCHAR(255) NULL,
    location_place VARCHAR(150) NULL,
    location_district VARCHAR(100) NULL,
    location_state VARCHAR(100) NULL,
    verification_notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_verification_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_verification_payment
        FOREIGN KEY (payment_id) REFERENCES payments(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_verification_admin
        FOREIGN KEY (verified_by) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_verification_status (status),
    INDEX idx_verification_user (user_id)
) ENGINE=InnoDB;

CREATE TABLE interests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sender_user_id BIGINT UNSIGNED NOT NULL,
    receiver_user_id BIGINT UNSIGNED NOT NULL,
    status ENUM('pending','accepted','declined','cancelled') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    responded_at DATETIME NULL,
    CONSTRAINT fk_interest_sender
        FOREIGN KEY (sender_user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_interest_receiver
        FOREIGN KEY (receiver_user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_interest_not_self CHECK (sender_user_id <> receiver_user_id),
    UNIQUE KEY uq_interest_pair (sender_user_id, receiver_user_id),
    INDEX idx_interest_received (receiver_user_id, status),
    INDEX idx_interest_sent (sender_user_id, status)
) ENGINE=InnoDB;

CREATE TABLE otp_verifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL,
    purpose ENUM('registration','login','forgot_password','change_phone') NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    verified_at DATETIME NULL,
    attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_otp_phone_purpose (phone, purpose),
    INDEX idx_otp_expiry (expires_at)
) ENGINE=InnoDB;

CREATE TABLE auth_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    revoked_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_used_at DATETIME NULL,
    CONSTRAINT fk_auth_sessions_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_sessions_user (user_id),
    INDEX idx_sessions_expiry (expires_at)
) ENGINE=InnoDB;

CREATE TABLE admin_users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    admin_role ENUM('super_admin','admin','staff') NOT NULL DEFAULT 'staff',
    status ENUM('active','inactive','blocked') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_admin_users_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Optional initial plan records. Prices should be confirmed before production use.
-- INSERT INTO plans (name, price, duration_days, description) VALUES
-- ('Basic', 0.00, 365, 'Verified Matrimony Access');

