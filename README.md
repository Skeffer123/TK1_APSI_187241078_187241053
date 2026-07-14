# Sistem Informasi Perpustakaan UNAIR

Aplikasi web Sistem Informasi Peminjaman Buku Perpustakaan Universitas Airlangga (UNAIR) dengan arsitektur REST API (Laravel 11 + Sanctum) dan Single Page Application (Vue 3 + Pinia + Vue Router + Tailwind CSS).

---

## Tech Stack
*   **Backend**: Laravel 11 (REST API, Sanctum, Barryvdh Laravel DomPDF)
*   **Frontend**: Vue 3 (Composition API) + Vite + Pinia (State) + Vue Router + Axios + Tailwind CSS (v3)
*   **Database**: MySQL (Community Server 8.x/Laragon)

---

## Langkah Setup & Instalasi

### 1. Database Setup
Buat database baru di MySQL server Anda dengan nama `perpustakaan_unair`:
```sql
CREATE DATABASE perpustakaan_unair;
```

### 2. Backend Setup (Laravel 11)
1.  Buka terminal dan masuk ke folder `backend`:
    ```bash
    cd backend
    ```
2.  Duplikat file `.env.example` menjadi `.env` (Jika belum terkonfigurasi, edit koneksi DB ke MySQL):
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=perpustakaan_unair
    DB_USERNAME=root
    DB_PASSWORD=
    ```
3.  Jalankan instalasi dependensi (opsional jika sudah diinstall otomatis):
    ```bash
    composer install
    ```
4.  Jalankan migrasi database beserta data seeder dummy:
    ```bash
    php artisan migrate --seed
    ```
5.  Nyalakan server development Laravel:
    ```bash
    php artisan serve
    ```
    *Server API secara default akan berjalan di http://127.0.0.1:8000*

### 3. Frontend Setup (Vue 3)
1.  Buka terminal baru dan masuk ke folder `frontend`:
    ```bash
    cd frontend
    ```
2.  Install dependensi npm:
    ```bash
    npm install
    ```
3.  Jalankan server development Vite:
    ```bash
    npm run dev
    ```
    *Aplikasi frontend secara default akan berjalan di http://localhost:5173 (atau port alternatif)*

---

## Kredensial Uji Coba (Dummy Data)

Berikut kredensial yang dapat digunakan untuk login ke sistem setelah database di-seed:

### 1. Akun Admin
*   **Username / Email**: `admin` atau `admin@unair.ac.id`
*   **Password**: `admin123`

### 2. Akun Anggota (Mahasiswa)
*   **Username / Email / NIM**: `1020113300000001` atau `ahmad.fauzi@fst.unair.ac.id`
*   **Password**: `anggota123`
*   **Status**: `Aktif`

---

## Aturan Bisnis & Fungsionalitas
1.  **Satu login endpoint** (`/api/login`) mendeteksi secara otomatis apakah login key yang digunakan adalah milik Admin (username/email) atau Anggota (NIM/email).
2.  **Validasi transaksi peminjaman** (status keanggotaan aktif dan kecukupan stok buku) diimplementasikan secara aman menggunakan database transaction (`DB::transaction`) dengan row locking (`lockForUpdate`).
3.  **Denda keterlambatan** dihitung otomatis saat pengembalian: `jumlah_hari * Rp2.000` (konfigurasi denda dan tenggat waktu peminjaman 7 hari dapat diubah di file `.env`).
4.  **Ekspor Laporan PDF** resmi untuk data Buku, Anggota, Peminjaman, dan Denda terintegrasi penuh menggunakan library DomPDF.
