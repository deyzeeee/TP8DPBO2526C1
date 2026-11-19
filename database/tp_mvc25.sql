-- =============================================================================
-- Database: tp_mvc25
-- Tema    : Manajemen Departemen, Dosen Ilkom UPI, dan Beban SKS Mata Kuliah
-- =============================================================================

CREATE DATABASE IF NOT EXISTS tp_mvc25;
USE tp_mvc25;

-- Hapus tabel mulai dari tabel anak dulu, baru tabel induk
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS lecturers;
DROP TABLE IF EXISTS departments;

-- -----------------------------------------------------------------------------
-- 1. Tabel Induk: departments (Program Studi / Departemen)
-- -----------------------------------------------------------------------------
CREATE TABLE departments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dept_code VARCHAR(20) NOT NULL UNIQUE,
  dept_name VARCHAR(100) NOT NULL,
  faculty VARCHAR(100) NOT NULL,
  head_room VARCHAR(60) NOT NULL,
  accreditation ENUM('Unggul', 'Baik Sekali', 'Baik') NOT NULL DEFAULT 'Unggul'
);

-- -----------------------------------------------------------------------------
-- 2. Tabel Entitas Utama: lecturers (Dosen)
-- -----------------------------------------------------------------------------
CREATE TABLE lecturers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  department_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  nidn VARCHAR(20) NOT NULL UNIQUE,
  email VARCHAR(120) NOT NULL UNIQUE,
  phone VARCHAR(25) NOT NULL,
  join_date DATE NOT NULL,
  academic_rank ENUM('Asisten Ahli', 'Lektor', 'Lektor Kepala', 'Guru Besar') NOT NULL DEFAULT 'Asisten Ahli',
  max_credits INT NOT NULL DEFAULT 12,
  assigned_credits INT NOT NULL DEFAULT 0,
  CONSTRAINT fk_lecturers_department
    FOREIGN KEY (department_id) REFERENCES departments(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
);

-- -----------------------------------------------------------------------------
-- 3. Tabel Relasi: courses (Mata Kuliah & Penugasan Dosen Pengampu)
-- -----------------------------------------------------------------------------
CREATE TABLE courses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  department_id INT NOT NULL,
  lecturer_id INT NOT NULL,
  course_code VARCHAR(20) NOT NULL UNIQUE,
  course_name VARCHAR(120) NOT NULL,
  credits INT NOT NULL,
  semester INT NOT NULL,
  class_room VARCHAR(60) NOT NULL,
  status ENUM('Aktif', 'Nonaktif') NOT NULL DEFAULT 'Aktif',
  CONSTRAINT fk_courses_department
    FOREIGN KEY (department_id) REFERENCES departments(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT fk_courses_lecturer
    FOREIGN KEY (lecturer_id) REFERENCES lecturers(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
);

-- -----------------------------------------------------------------------------
-- Data Awal (Nilai assigned_credits dosen sesuai total SKS mata kuliah Aktif)
-- -----------------------------------------------------------------------------
INSERT INTO departments (id, dept_code, dept_name, faculty, head_room, accreditation) VALUES
(1, 'IF-S1', 'Ilmu Komputer', 'FPMIPA UPI', 'Gedung JICA FPMIPA A Lt. 3', 'Unggul'),
(2, 'PIK-S1', 'Pendidikan Ilmu Komputer', 'FPMIPA UPI', 'Gedung JICA FPMIPA A Lt. 3', 'Unggul'),
(3, 'RPL-S1', 'Rekayasa Perangkat Lunak', 'Kampus UPI Cibiru', 'Gedung Utama Cibiru Lt. 2', 'Baik Sekali'),
(4, 'MTK-S1', 'Matematika', 'FPMIPA UPI', 'Gedung JICA FPMIPA A Lt. 2', 'Unggul');

INSERT INTO lecturers (id, department_id, name, nidn, email, phone, join_date, academic_rank, max_credits, assigned_credits) VALUES
(1, 1, 'Rosa Ariani Sukamto, S.T., M.T.', '0014098101', 'rosa.ariani@upi.edu', '081220114001', '2008-01-01', 'Lektor', 12, 7),
(2, 1, 'Dr. Yudi Wibisono, S.T., M.T.', '0007057502', 'yudi@upi.edu', '081220114002', '2005-03-01', 'Lektor Kepala', 12, 6),
(3, 1, 'Dr. Rani Megasari, S.T., M.T.', '0022108403', 'megasari@upi.edu', '081220114003', '2010-12-01', 'Lektor Kepala', 12, 3),
(4, 1, 'Prof. Dr. Lala Septem Riza, M.T.', '0015097804', 'lala.s.riza@upi.edu', '081220114004', '2006-04-01', 'Guru Besar', 14, 3),
(5, 2, 'Dr. Asep Wahyudin, S.Kom., M.T.', '0019067405', 'away@upi.edu', '081220114005', '2004-08-01', 'Lektor Kepala', 12, 3),
(6, 2, 'Faiz Muhammad Ghazali, M.Kom.', '0411039206', 'paesGhazali@upi.edu', '081234567890', '2021-09-01', 'Asisten Ahli', 10, 3),
(7, 3, 'Abdillah Ridho Alriza, M.T.', '0425079307', 'abdul666@upi.edu', '082345678901', '2022-02-01', 'Asisten Ahli', 10, 0),
(8, 2, 'Hafiz Rifai, M.Kom.', '0418119408', 'hafizrifai@upi.edu', '083456789012', '2023-01-15', 'Asisten Ahli', 10, 0);

INSERT INTO courses (id, department_id, lecturer_id, course_code, course_name, credits, semester, class_room, status) VALUES
(1, 1, 1, 'IK250', 'Desain dan Pemrograman Berorientasi Objek', 4, 3, 'Lab Pemrograman Kemakom', 'Aktif'),
(2, 1, 1, 'IK120', 'Struktur Data', 3, 2, 'Ruang Kuliah S-302 JICA', 'Aktif'),
(3, 1, 2, 'IK310', 'Pemrograman Bergerak', 3, 5, 'Lab Riset AI & Mobile', 'Aktif'),
(4, 1, 2, 'IK290', 'Kecerdasan Buatan', 3, 4, 'Ruang Kuliah S-304 JICA', 'Aktif'),
(5, 1, 3, 'IK210', 'Sistem Basis Data', 3, 3, 'Lab Basis Data JICA', 'Aktif'),
(6, 1, 4, 'IK420', 'Big Data Analysis', 3, 6, 'Lab Komputasi Lanjut', 'Aktif'),
(7, 2, 5, 'PK230', 'Rekayasa Perangkat Lunak', 3, 4, 'Ruang Kuliah C-205', 'Aktif'),
(8, 2, 6, 'PK215', 'Jaringan Komputer', 3, 3, 'Lab Jaringan Komputer', 'Aktif'),
(9, 3, 7, 'RL302', 'Sistem Operasi Terdistribusi', 3, 5, 'Ruang R-201 Cibiru', 'Nonaktif');
