-- =====================================================================
--  د صادره او وارده مکتوبونو د ثبت سیستم (Outgoing / Incoming Letters
--  Register System) - Database Schema
--  Database Directorate - Religious Universities & Specializations
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS mail_register
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE mail_register;

-- ---------------------------------------------------------------------
--  users  (سیستم کاروونکي - Login users)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name     VARCHAR(150) NOT NULL,
    role          ENUM('admin','editor','viewer') NOT NULL DEFAULT 'editor',
    is_active     TINYINT(1)   NOT NULL DEFAULT 1,
    last_login_at DATETIME     NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default administrator account
-- username: admin   /   password: Admin@12345
-- (Change this password immediately after first login!)
INSERT INTO users (username, password_hash, full_name, role)
VALUES (
    'admin',
    '$2b$10$uRYzILVKfmGEilB.itAIs.ekg7vrB.9O.lHiGeJME/Jb8Oij1HpCy',
    'سیسټم مدیر',
    'admin'
) ON DUPLICATE KEY UPDATE username = username;

-- ---------------------------------------------------------------------
--  login_logs  (د ننوتلو ثبت - security / audit trail)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS login_logs (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NULL,
    username   VARCHAR(50)  NOT NULL,
    ip_address VARCHAR(45)  NOT NULL,
    success    TINYINT(1)   NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  outgoing_letters  (صادره مکتوبونه)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS outgoing_letters (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    serial_no          VARCHAR(50)  NOT NULL,          -- مسلسل او مشترک لمبر
    dossier_no         VARCHAR(50)  NULL,              -- دوسیه نمبر
    issue_date         VARCHAR(20)  NULL,              -- نیټه (د صدور)
    letter_date        VARCHAR(20)  NULL,              -- نیټه (د مکتوب)
    sent_to            VARCHAR(255) NULL,              -- مرسل الیه
    reference_no       VARCHAR(100) NULL,               -- مرجع
    subject            TEXT         NULL,               -- د مطلب خلاصه
    records_signature  TINYINT(1)   NOT NULL DEFAULT 0, -- د اوراقو د ضبط شعبه - امضاء
    records_attachment TINYINT(1)   NOT NULL DEFAULT 0, -- ضمیمه
    records_attachment_count INT   NULL,               -- د ضمیمې د پاڼو شمېر (ثبت شعبه)
    records_original   TINYINT(1)   NOT NULL DEFAULT 0, -- اصل
    exec_signature      TINYINT(1)  NOT NULL DEFAULT 0, -- د اجرائیه ادارې شعبه - امضاء
    exec_attachment     TINYINT(1)  NOT NULL DEFAULT 0, -- ضمیمه
    exec_attachment_count INT      NULL,                -- د ضمیمې د پاڼو شمېر (اجرائیه شعبه)
    exec_original       TINYINT(1)  NOT NULL DEFAULT 0, -- اصل
    distribution_notes TEXT         NULL,               -- د توزیع او تسلیم
    remarks            TEXT         NULL,               -- ملاحظات
    created_by         INT UNSIGNED NULL,
    created_at         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_out_serial (serial_no),
    INDEX idx_out_sentto (sent_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
--  incoming_letters  (وارده مکتوبونه)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS incoming_letters (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    serial_no       VARCHAR(50)  NOT NULL,   -- مسلسل او مشترک لمبر
    dossier_no      VARCHAR(50)  NULL,       -- دوسیه نمبر
    incoming_date   VARCHAR(20)  NULL,       -- نیټه (ثبت)
    letter_date     VARCHAR(20)  NULL,       -- نیټه (د مکتوب)
    incoming_no     VARCHAR(100) NULL,       -- د وارده مکتوب لمبر
    sent_from       VARCHAR(255) NULL,       -- مرسله الیه (لیږونکی)
    origin          VARCHAR(255) NULL,       -- مبداء
    subject         TEXT         NULL,       -- د مطلب خلاصه
    doc_count       INT          NULL,       -- عدد
    pages_no        VARCHAR(50)  NULL,       -- د اوراقو لمبر
    has_attachment  TINYINT(1)   NOT NULL DEFAULT 0, -- ضمیمه شتون لري؟
    attachment_count INT         NULL,       -- د ضمیمې د پاڼو شمېر
    action_no       VARCHAR(100) NULL,       -- د اقدام او مراجعت لمبر
    remarks         TEXT         NULL,       -- ملاحظات
    created_by      INT UNSIGNED NULL,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_in_serial (serial_no),
    INDEX idx_in_from (sent_from)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;