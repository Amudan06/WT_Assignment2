-- =====================================================================
--  Web Technology (U21CS501) - Assignment II : Database Schema Script
--  One database is shared by the PHP admin panel (Q2) and the
--  Servlet/JDBC student portal (Q3).
--  Run with:   mysql -u root -p < database/schema.sql
-- =====================================================================

CREATE DATABASE IF NOT EXISTS student_portal
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE student_portal;

-- Clean re-runs: drop the child table first because of the foreign key
DROP TABLE IF EXISTS student_files;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS users;

-- ---------------------------------------------------------------------
-- users : accounts allowed to log in to the Servlet portal (Q3)
--         password_hash holds a SHA-256 hex digest (64 chars)
-- ---------------------------------------------------------------------
CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  password_hash CHAR(64)     NOT NULL,
  full_name     VARCHAR(100) NOT NULL,
  created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- students : core student record managed by the PHP CRUD pages (Q2)
-- ---------------------------------------------------------------------
CREATE TABLE students (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100) NOT NULL,
  roll_number   VARCHAR(20)  NOT NULL UNIQUE,
  email         VARCHAR(120) NOT NULL UNIQUE,
  course        VARCHAR(80)  NOT NULL,
  password_hash VARCHAR(255) NULL,           -- bcrypt hash from PHP password_hash()
  created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- student_files : metadata of every uploaded file (path, type, date)
--                 linked to a student; deleting a student removes the rows
-- ---------------------------------------------------------------------
CREATE TABLE student_files (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id    INT UNSIGNED NOT NULL,
  category      ENUM('profile_image','id_proof','certificate') NOT NULL,
  original_name VARCHAR(255) NOT NULL,        -- name on the user computer
  stored_name   VARCHAR(255) NOT NULL UNIQUE, -- unique name on the server (no overwrite)
  file_path     VARCHAR(255) NOT NULL,        -- relative path, e.g. uploads/abc.png
  file_type     VARCHAR(100) NOT NULL,        -- real MIME type detected on the server
  file_size     INT UNSIGNED NOT NULL,        -- size in bytes
  uploaded_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_files_student FOREIGN KEY (student_id)
    REFERENCES students(id) ON DELETE CASCADE,
  INDEX idx_files_student (student_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Sample data
--   portal logins :  admin / admin123    and    faculty / student123
-- ---------------------------------------------------------------------
INSERT INTO users (username, password_hash, full_name) VALUES
  ('admin',   '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9',  'Portal Administrator'),
  ('faculty', '703b0a3d6ad75b649a28adde7d83c6251da457549263bc7ff45ec709b0a8448b', 'Faculty Member');

INSERT INTO students (name, roll_number, email, course) VALUES
  ('Amudan',        '22CSE101',  'amudan@example.com',  'B.E. Computer Science and Engineering'),
  ('Priya Sharma',  '22CSE102',  'priya@example.com',   'B.E. Computer Science and Engineering'),
  ('Karthik Raja',  '22ECE215',  'karthik@example.com', 'B.E. Electronics and Communication'),
  ('Meena Lakshmi', '22AIDS033', 'meena@example.com',   'B.Tech Artificial Intelligence and Data Science');
