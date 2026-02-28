-- ======================================================
-- DATABASE: SISTEM MANAJEMEN ABSENSI & TASK PERUSAHAAN
-- ======================================================

-- =========================
-- 1. TABLE USERS
-- =========================
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,

    kode_karyawan VARCHAR(50) UNIQUE,
    jabatan VARCHAR(100),
    divisi VARCHAR(100),
    tanggal_masuk DATE,
    
    -- [BARU] Menentukan siapa atasan langsung dari karyawan ini
    supervisor_id BIGINT UNSIGNED NULL,

    status ENUM('aktif','nonaktif') DEFAULT 'aktif',

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    CONSTRAINT fk_user_supervisor
        FOREIGN KEY (supervisor_id) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================
-- 2. TABLE ABSENSI
-- =========================
CREATE TABLE absensi (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    tanggal DATE NOT NULL,

    jam_masuk DATETIME NULL,
    jam_pulang DATETIME NULL,

    total_jam DECIMAL(5,2) NULL,
    menit_terlambat INT DEFAULT 0,

    status ENUM('hadir','izin','sakit','cuti','absen') DEFAULT 'hadir',
    status_approval ENUM('menunggu','disetujui','ditolak') DEFAULT 'menunggu',

    disetujui_oleh BIGINT UNSIGNED NULL,
    waktu_persetujuan DATETIME NULL,
    alasan_penolakan TEXT NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    -- [BARU] Karyawan tidak boleh punya 2 record absensi di hari yang sama
    UNIQUE KEY unique_absensi_harian (user_id, tanggal),

    CONSTRAINT fk_absensi_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_absensi_disetujui
        FOREIGN KEY (disetujui_oleh) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================
-- 3. TABLE AKTIVITAS HARIAN (Laporan Mandiri)
-- =========================
CREATE TABLE aktivitas_harian (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    absensi_id BIGINT UNSIGNED NULL,

    tanggal DATE NOT NULL,
    deskripsi TEXT NOT NULL,
    
    -- [BARU] File bukti pekerjaan rutin
    file_lampiran VARCHAR(255) NULL, 

    jam_mulai TIME NULL,
    jam_selesai TIME NULL,
    durasi_menit INT NULL,

    status ENUM('draft','dilaporkan','disetujui','ditolak') DEFAULT 'draft',

    disetujui_oleh BIGINT UNSIGNED NULL,
    waktu_persetujuan DATETIME NULL,
    alasan_penolakan TEXT NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    CONSTRAINT fk_aktivitas_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_aktivitas_absensi
        FOREIGN KEY (absensi_id) REFERENCES absensi(id)
        ON DELETE SET NULL,
    CONSTRAINT fk_aktivitas_disetujui
        FOREIGN KEY (disetujui_oleh) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================
-- 4. TABLE TUGAS (Delegasi Atasan)
-- =========================
CREATE TABLE tugas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT,

    dibuat_oleh BIGINT UNSIGNED NOT NULL,

    prioritas ENUM('rendah','sedang','tinggi') DEFAULT 'sedang',
    tenggat_waktu DATETIME NULL, -- Diubah ke DATETIME agar bisa set jam deadline

    status ENUM('terbuka','dikerjakan','selesai','ditutup') DEFAULT 'terbuka',

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    CONSTRAINT fk_tugas_dibuat_oleh
        FOREIGN KEY (dibuat_oleh) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================
-- 5. TABLE TUGAS_USER (PIVOT - Progres Karyawan)
-- =========================
CREATE TABLE tugas_user (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tugas_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,

    progres INT DEFAULT 0,
    -- [UPDATE] Menambahkan status 'revisi'
    status ENUM('ditugaskan','dikerjakan','dikirim','revisi','disetujui') DEFAULT 'ditugaskan',
    
    -- [BARU] Karyawan bisa melampirkan file/link saat submit tugas
    bukti_penyelesaian VARCHAR(255) NULL, 

    waktu_dikirim DATETIME NULL,
    waktu_disetujui DATETIME NULL,
    
    disetujui_oleh BIGINT UNSIGNED NULL,
    catatan_penolakan TEXT NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    UNIQUE KEY unique_tugas_user (tugas_id, user_id),

    CONSTRAINT fk_tugasuser_tugas
        FOREIGN KEY (tugas_id) REFERENCES tugas(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_tugasuser_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_tugasuser_disetujui
        FOREIGN KEY (disetujui_oleh) REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================
-- 6. TABLE LOG ABSENSI (Detail Titik Koordinat / Foto Selfie)
-- =========================
CREATE TABLE log_absensi (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    absensi_id BIGINT UNSIGNED NOT NULL,
    
    tipe ENUM('masuk','pulang','istirahat_mulai','istirahat_selesai') NOT NULL,
    waktu DATETIME NOT NULL,
    
    latitude VARCHAR(50) NULL,  -- [BARU] Dipisah agar mudah dihitung jaraknya
    longitude VARCHAR(50) NULL, -- [BARU] 
    lokasi VARCHAR(255) NULL,   -- Alamat tex
    
    foto VARCHAR(255) NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    CONSTRAINT fk_log_absensi
        FOREIGN KEY (absensi_id) REFERENCES absensi(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;
