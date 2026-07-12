# SPK Rekomendasi Kost (SPKKOST)

Decision Support System (Sistem Pendukung Keputusan) untuk rekomendasi pemilihan tempat kost terbaik berbasis web, dibangun menggunakan framework **CodeIgniter 4**. Aplikasi ini dirancang untuk membantu calon penghuni kost dalam menentukan pilihan optimal berdasarkan kriteria-kriteria yang telah ditentukan (seperti harga, jarak, fasilitas, dan keamanan).[cite: 2]

---

## Fitur Utama

- **Sistem Pendukung Keputusan:** Implementasi metode SPK (seperti SAW / AHP / TOPSIS) untuk perhitungan rekomendasi yang objektif.[cite: 2]
- **Manajemen Data Kost:** Pengelolaan data kos-kosan, fasilitas, harga, dan lokasi secara dinamis.[cite: 2]
- **Manajemen Kriteria & Bobot:** Fleksibilitas dalam mengubah kriteria penilaian dan bobot preferensi.[cite: 2]
- **Dashboard Interaktif:** Halaman ringkasan informasi yang bersih dan responsif untuk admin maupun pengguna.[cite: 2]

---

## Arsitektur & Keamanan Informasi

Aplikasi ini dibangun dengan memperhatikan standar penulisan kode yang aman (*Secure Coding Principles*) dan praktik terbaik Git:

- **Environment Isolation:** Kredensial sensitif basis data diisolasi sepenuhnya di dalam berkas `.env` lokal dan tidak dilacak oleh Git untuk mencegah kebocoran data (*anti-information disclosure*).[cite: 2]
- **SQL Injection Prevention:** Menggunakan *Query Builder* dan *Data Binding* bawaan CodeIgniter 4 untuk menangani komunikasi basis data dengan aman.[cite: 2]
- **Cross-Site Scripting (XSS) Protection:** Implementasi *auto-escaping* pada *views* untuk mencegah injeksi skrip berbahaya.[cite: 2]

---

## Prasyarat Sistem

Sebelum menjalankan proyek ini di lingkungan lokal antum, pastikan perangkat antum sudah memenuhi spesifikasi berikut:

- **PHP:** Versi 8.1 atau yang lebih baru (dengan ekstensi `intl`, `mbstring`, `sqlsrv` atau `mysqli` aktif)[cite: 2]
- **Database:** MySQL / MariaDB[cite: 2]
- **Dependency Manager:** Composer[cite: 2]
- **Local Server:** Laragon / XAMPP[cite: 2]

---

## Langkah Instalasi Lokal

Ikuti langkah-langkah berikut untuk melakukan *deployment* proyek di *workspace* lokal antum:

### 1. Kloning Repositori

Klon repositori ini ke direktori server lokal antum:

```bash
git clone [https://github.com/username-antum/SPKKOST.git](https://github.com/username-antum/SPKKOST.git)
cd SPKKOST
```[cite: 2]

### 2. Instalasi Dependensi Pihak Ketiga
Unduh seluruh pustaka (*libraries*) yang dibutuhkan menggunakan Composer:
```bash
composer install
```[cite: 2]

### 3. Konfigurasi Environment (.env)
Salin berkas template konfigurasi bawaan menjadi berkas `.env` aktif:
```bash
cp env .env
```[cite: 2]

Buka berkas `.env` tersebut menggunakan teks editor antum, kemudian sesuaikan pengaturan basis data lokal antum:
```env
database.default.hostname = localhost
database.default.database = nama_database_antum
database.default.username = root
database.default.password = password_db_antum
database.default.DBDriver = MySQLi
database.default.port     = 3306
```[cite: 2]

### 4. Impor Basis Data
1. Buat basis data baru di phpMyAdmin atau DBMS andalan antum dengan nama `nama_database_antum`.[cite: 2]
2. Impor berkas skema basis data (`.sql`) yang telah disediakan (pastikan berkas `.sql` eksternal ini disimpan dengan aman di luar repositori publik).[cite: 2]

### 5. Jalankan Aplikasi
Antum dapat menjalankan server pengembangan bawaan CodeIgniter 4 dengan perintah:
```bash
php spark serve
```[cite: 2]

Buka peramban (*browser*) antum dan akses alamat: `http://localhost:8080`[cite: 2]

---

## 📄 Lisensi

Proyek ini didistribusikan di bawah **MIT License**. Antum bebas memodifikasi, mendistribusikan, dan mengembangkan ulang kode aplikasi ini untuk keperluan akademik maupun komersial dengan tetap mencantumkan hak cipta asli.[cite: 2]
