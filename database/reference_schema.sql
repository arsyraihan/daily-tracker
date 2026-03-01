SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS log_tugas;
DROP TABLE IF EXISTS tugas;
DROP TABLE IF EXISTS sesi_tugas;
DROP TABLE IF EXISTS absensi;
DROP TABLE IF EXISTS sesi_absensi;

DROP TABLE IF EXISTS model_has_permissions;
DROP TABLE IF EXISTS model_has_roles;
DROP TABLE IF EXISTS role_has_permissions;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS roles;

DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS jabatan;
DROP TABLE IF EXISTS divisi;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- 1. DIVISI
-- =====================================================
CREATE TABLE divisi (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    deskripsi TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- =====================================================
-- 2. JABATAN
-- =====================================================
CREATE TABLE jabatan (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    level INT DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- =====================================================
-- 3. USERS
-- =====================================================
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,

    kode_karyawan VARCHAR(50) UNIQUE NULL,
    divisi_id BIGINT UNSIGNED NULL,
    jabatan_id BIGINT UNSIGNED NULL,
    atasan_id BIGINT UNSIGNED NULL,

    status ENUM('aktif','nonaktif') DEFAULT 'aktif',

    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (divisi_id) REFERENCES divisi(id) ON DELETE SET NULL,
    FOREIGN KEY (jabatan_id) REFERENCES jabatan(id) ON DELETE SET NULL,
    FOREIGN KEY (atasan_id) REFERENCES users(id) ON DELETE SET NULL
);

-- =====================================================
-- SPATIE RBAC (TETAP)
-- =====================================================

CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE TABLE role_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (permission_id, role_id),

    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

CREATE TABLE model_has_roles (
    role_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,

    PRIMARY KEY (role_id, model_id, model_type),

    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

CREATE TABLE model_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,

    PRIMARY KEY (permission_id, model_id, model_type),

    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

-- =====================================================
-- 4. SESI ABSENSI
-- =====================================================
CREATE TABLE sesi_absensi (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    tanggal DATE NOT NULL,
    divisi_id BIGINT UNSIGNED NOT NULL,
    dibuat_oleh BIGINT UNSIGNED NOT NULL,

    status ENUM('dibuka','ditutup') DEFAULT 'dibuka',

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    UNIQUE KEY unique_sesi (tanggal, divisi_id),

    FOREIGN KEY (divisi_id) REFERENCES divisi(id) ON DELETE CASCADE,
    FOREIGN KEY (dibuat_oleh) REFERENCES users(id) ON DELETE CASCADE
);

-- =====================================================
-- 5. ABSENSI
-- =====================================================
CREATE TABLE absensi (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    sesi_absensi_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,

    jam_masuk TIME NULL,
    jam_keluar TIME NULL,

    status ENUM('hadir','terlambat','sakit','izin','alpa') DEFAULT 'hadir',

    status_persetujuan ENUM('menunggu','disetujui','ditolak') DEFAULT 'menunggu',
    disetujui_oleh BIGINT UNSIGNED NULL,

    catatan TEXT NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    UNIQUE KEY unique_user_sesi (sesi_absensi_id, user_id),

    FOREIGN KEY (sesi_absensi_id) REFERENCES sesi_absensi(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (disetujui_oleh) REFERENCES users(id) ON DELETE SET NULL
);

-- =====================================================
-- 6. SESI TUGAS
-- =====================================================
CREATE TABLE sesi_tugas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    tanggal DATE NOT NULL,
    divisi_id BIGINT UNSIGNED NOT NULL,
    dibuat_oleh BIGINT UNSIGNED NOT NULL,

    status ENUM('dibuka','ditutup') DEFAULT 'dibuka',

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    UNIQUE KEY unique_sesi_tugas (tanggal, divisi_id),

    FOREIGN KEY (divisi_id) REFERENCES divisi(id) ON DELETE CASCADE,
    FOREIGN KEY (dibuat_oleh) REFERENCES users(id) ON DELETE CASCADE
);

-- =====================================================
-- 7. TUGAS
-- =====================================================
CREATE TABLE tugas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    sesi_tugas_id BIGINT UNSIGNED NOT NULL,
    ditugaskan_ke BIGINT UNSIGNED NOT NULL,
    ditugaskan_oleh BIGINT UNSIGNED NOT NULL,

    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT NULL,

    prioritas ENUM('rendah','sedang','tinggi') DEFAULT 'sedang',

    status ENUM('menunggu','dikerjakan','selesai','ditolak') DEFAULT 'menunggu',
    status_persetujuan ENUM('menunggu','disetujui','ditolak') DEFAULT 'menunggu',

    progress INT DEFAULT 0,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (sesi_tugas_id) REFERENCES sesi_tugas(id) ON DELETE CASCADE,
    FOREIGN KEY (ditugaskan_ke) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (ditugaskan_oleh) REFERENCES users(id) ON DELETE CASCADE
);

-- =====================================================
-- 8. LOG TUGAS
-- =====================================================
CREATE TABLE log_tugas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    tugas_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,

    deskripsi TEXT NULL,
    progress INT NOT NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (tugas_id) REFERENCES tugas(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);