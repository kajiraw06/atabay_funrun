-- ============================================================
--  Fun Run Online Registration — Database Setup
--  Run this ONCE in phpMyAdmin or MySQL CLI if the
--  auto-create in config.php does not work.
-- ============================================================

CREATE DATABASE IF NOT EXISTS fun_run_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE fun_run_db;

CREATE TABLE IF NOT EXISTS registrations (
    id                       INT AUTO_INCREMENT PRIMARY KEY,
    reference_number         VARCHAR(20)  UNIQUE NOT NULL,
    first_name               VARCHAR(100) NOT NULL,
    last_name                VARCHAR(100) NOT NULL,
    email                    VARCHAR(150) NOT NULL,
    phone                    VARCHAR(20)  NOT NULL,
    address                  TEXT         NOT NULL,
    birthdate                DATE         NOT NULL,
    gender                   ENUM('Male','Female','Other') NOT NULL,
    emergency_contact_name   VARCHAR(150) NOT NULL,
    emergency_contact_number VARCHAR(20)  NOT NULL,
    category                 VARCHAR(10)  NOT NULL,
    shirt_size               ENUM('XS','S','M','L','XL','XXL') NOT NULL,
    payment_method           ENUM('gcash','paymaya','cash') NOT NULL,
    payment_ref              VARCHAR(40)  DEFAULT NULL,
    payment_proof            VARCHAR(255) DEFAULT NULL,
    payment_status           ENUM('pending','verified','rejected') DEFAULT 'pending',
    registration_status      ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
    notes                    TEXT         DEFAULT NULL,
    created_at               TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at               TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
