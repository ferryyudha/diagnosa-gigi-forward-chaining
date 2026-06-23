# DIAGNOSA PENYAKIT GIGI METODE FORWARD CHAINING

Aplikasi Sistem Pakar berbasis web yang dirancang untuk membantu melakukan diagnosis dini penyakit gigi dan mulut pada **Praktik Mandiri Drg. Hj. Rini Sutarti** menggunakan metode inferensi **Forward Chaining** (penalaran maju).

---

## 🚀 Fitur Utama

- **Konsultasi Mandiri (Diagnosis)**: Pasien dapat memilih keluhan gejala yang dirasakan untuk dianalisis oleh sistem secara *real-time*.
- **Mesin Inferensi Forward Chaining**: Menelusuri fakta gejala yang diinputkan pengguna untuk dicocokkan dengan 40 aturan (*rules*) basis pengetahuan penyakit gigi.
- **Visualisasi Analisis Dinamis**: Menampilkan persentase tingkat kecocokan untuk semua kemungkinan penyakit menggunakan grafik lingkaran (circle SVG) serta rincian gejala yang cocok (terpenuhi) dan gejala yang belum ada (tidak terpenuhi) melalui panel akordion.
- **Rujukan Ramah Cetak (Print-Friendly)**: Hasil diagnosis memiliki format khusus cetak yang rapi untuk diunduh sebagai berkas rujukan awal saat berobat secara fisik.
- **Panel Admin Lengkap**:
  - Dashboard statistik data.
  - Grafik bar penyakit paling sering terdiagnosis menggunakan **Chart.js**.
  - Manajemen basis pengetahuan (*Knowledge Base*): kelola penyakit, gejala, dan aturan relasi.
  - Pengelolaan data pengguna (Admin & Dokter Gigi).
  - Manajemen histori/riwayat pemeriksaan pasien.

---

## 🛠️ Teknologi yang Digunakan

- **Core**: PHP (Native)
- **Database**: MySQL (MariaDB)
- **Styling**: Vanilla CSS (Premium Dark Theme & Responsive Mobile Layout)
- **Interactive Logic**: Vanilla JavaScript
- **Libraries**: Chart.js (Grafik Dashboard), Phosphor Icons

---

## 📦 Panduan Instalasi & Penggunaan

### 1. Prasyarat
Pastikan Anda sudah menginstal web server lokal seperti **XAMPP** (Apache & MySQL) dan **Git**.

### 2. Kloning Repositori
Kloning repositori ini ke dalam direktori server lokal Anda (misal `htdocs` di XAMPP):
```bash
git clone https://github.com/ferryyudha/diagnosa-gigi-forward-chaining.git
```

### 3. Setup Basis Data
1. Aktifkan modul **Apache** dan **MySQL** di XAMPP Control Panel.
2. Buka **phpMyAdmin** (`http://localhost/phpmyadmin`).
3. Buat database baru bernama `db_gigi`.
4. Impor file berkas basis data `database.sql` yang ada di root proyek ke dalam database tersebut.

### 4. Konfigurasi Lingkungan (.env)
1. Salin berkas `.env.example` dan ubah namanya menjadi `.env`.
2. Sesuaikan konfigurasi database Anda di dalam berkas `.env` tersebut:
   ```env
   DB_HOST=localhost
   DB_PORT=3306
   DB_NAME=db_gigi
   DB_USER=root
   DB_PASS=
   ```

### 5. Jalankan Aplikasi
Buka peramban (browser) dan akses URL:
```
http://localhost/diagnosa-gigi-forward-chaining/
```

---

## 👤 Akun Akses Admin Default
- **Username**: `admin`
- **Password**: `password`
