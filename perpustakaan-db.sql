-- Database untuk Sistem Informasi Perpustakaan
CREATE DATABASE IF NOT EXISTS perpustakaan_db;

USE perpustakaan_db;

-- Tabel Admin/Pustakawan
CREATE TABLE admin (
    id_admin INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Anggota
CREATE TABLE anggota (
    id_anggota INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    no_telp VARCHAR(20),
    alamat TEXT,
    tanggal_daftar DATE DEFAULT CURRENT_DATE,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Kategori Buku
CREATE TABLE kategori (
    id_kategori INT PRIMARY KEY AUTO_INCREMENT,
    nama_kategori VARCHAR(50) NOT NULL
);

-- Tabel Buku
CREATE TABLE buku (
    id_buku INT PRIMARY KEY AUTO_INCREMENT,
    judul VARCHAR(200) NOT NULL,
    pengarang VARCHAR(100) NOT NULL,
    penerbit VARCHAR(100),
    tahun_terbit YEAR,
    isbn VARCHAR(20),
    id_kategori INT,
    jumlah_total INT DEFAULT 1,
    jumlah_tersedia INT DEFAULT 1,
    lokasi_rak VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_kategori) REFERENCES kategori (id_kategori)
);

-- Tabel Peminjaman
CREATE TABLE peminjaman (
    id_peminjaman INT PRIMARY KEY AUTO_INCREMENT,
    id_anggota INT NOT NULL,
    id_buku INT NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_kembali DATE NOT NULL,
    tanggal_dikembalikan DATE NULL,
    status ENUM(
        'dipinjam',
        'dikembalikan',
        'terlambat'
    ) DEFAULT 'dipinjam',
    denda DECIMAL(10, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_anggota) REFERENCES anggota (id_anggota),
    FOREIGN KEY (id_buku) REFERENCES buku (id_buku)
);

-- Insert data admin default (password: admin123)
INSERT INTO
    admin (
        username,
        password,
        nama_lengkap,
        email
    )
VALUES (
        'admin',
        '$2y$10$THtJmVqfjp.YAMzDW8qRU.1v7bd73v1ExakqqTbsIS9Z1ae9IBVCm',
        'Administrator',
        'admin@perpustakaan.com'
    );

INSERT INTO
    admin (
        username,
        password,
        nama_lengkap,
        email
    )
VALUES (
        'superadmin',
        '$2y$10$QzEw8K3GQe5P0cZPpYz1QO2zXH7Qb7f8wGQFzP7K8YvQ5FZKjPz1u',
        'Super Administrator',
        'superadmin@perpustakaan.com'
    );

-- Insert kategori default
INSERT INTO
    kategori (nama_kategori)
VALUES ('Fiksi'),
    ('Non-Fiksi'),
    ('Referensi'),
    ('Teknologi'),
    ('Sejarah'),
    ('Sains'),
    ('Agama'),
    ('Biografi');

ALTER TABLE buku ADD COLUMN foto VARCHAR(255) NULL AFTER lokasi_rak;