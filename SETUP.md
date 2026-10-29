# Panduan Menjalankan Proyek

Proyek ini adalah aplikasi **Web Monitoring Jaringan** berbasis Laravel, dengan fitur pemantauan metrik sistem (`/system`) dan lalu lintas jaringan (`/network`), termasuk pengambilan data otomatis dan pengaturan aplikasi.

Panduan ini ditulis untuk orang yang baru pertama kali menjalankan proyek Laravel di komputernya.

## 1. Yang Perlu Dipasang Dulu

| Alat | Kegunaan | Link |
|---|---|---|
| **Laragon** (atau XAMPP) | Menyediakan PHP, Apache, MySQL sekaligus | https://laragon.org |
| **PHP 8.4 atau lebih baru** | Wajib. Proyek ini memakai Laravel 12.60+, yang tidak berjalan di PHP 8.3 ke bawah | dibundel Laragon, atau https://windows.php.net/download |
| **Composer** | Mengelola dependency PHP | https://getcomposer.org |
| **Node.js (versi LTS)** | Untuk `npm`, dipakai membangun CSS/JS (Tailwind, Vite) | https://nodejs.org |
| **Git** | Mengunduh (clone) kode dari GitHub | https://git-scm.com |

Kalau memakai Laragon, Composer dan Git bisa dipasang lewat menu **Quick app** di Laragon, jadi tidak perlu instal terpisah.

Cek dulu versi PHP yang aktif sebelum lanjut:

```bash
php -v
```

Kalau hasilnya di bawah 8.4, ganti dulu versi PHP-nya (di Laragon: **Menu → PHP → Version**) sebelum lanjut ke langkah berikutnya.

## 2. Clone Repo

```bash
git clone https://github.com/niyan22/web-monitoring-jaringan-bmkg.git
cd web-monitoring-jaringan-bmkg
```

Kalau pakai Laragon, taruh hasil clone-nya di dalam `C:\Laragon\laragon\www\`, supaya Laragon otomatis membuatkan alamat lokal untuk proyek ini.

## 3. Install Dependency PHP

```bash
composer install
```

## 4. Siapkan File `.env`

```bash
cp .env.example .env
```

Di Windows PowerShell, kalau `cp` tidak dikenali, pakai:

```powershell
Copy-Item .env.example .env
```

Proyek ini secara default memakai database **SQLite**, jadi tidak perlu membuat database MySQL untuk sekadar mencoba menjalankannya.

## 5. Buat File Database SQLite

File database tidak ikut ter-upload ke GitHub, jadi harus dibuat sendiri:

```bash
touch database/database.sqlite
```

Di Windows PowerShell:

```powershell
New-Item database\database.sqlite -ItemType File
```

## 6. Generate Application Key

```bash
php artisan key:generate
```

## 7. Jalankan Migrasi Database

```bash
php artisan migrate
```

## 8. Install dan Build Aset Front-End

```bash
npm install
npm run build
```

## 9. Jalankan Aplikasinya

Pilih salah satu:

- **Cara cepat, untuk coba-coba:**
  ```bash
  php artisan serve
  ```
  Lalu buka `http://127.0.0.1:8000` di browser.

- **Lewat Laragon** (kalau folder proyek ada di `www/`):
  Buka `http://web-monitoring-jaringan-bmkg.test` di browser. Pastikan **Start All** sudah diklik di Laragon.

## Menjalankan Pengujian

**Tes otomatis (aman untuk data):** memakai database SQLite sementara di memori, jadi `database.sqlite` Anda tidak tersentuh.

```bash
php artisan test
```

**Postman:** aplikasi ini tidak punya API token; autentikasinya memakai sesi login + CSRF. Import dua file di folder `docs/postman/` (koleksi dan environment), isi `email` dan `password` dengan akun uji, lalu jalankan folder **Auth** terlebih dulu (1. Ambil cookie CSRF, lalu 2. Login).

> Request bertanda ⚠ di koleksi Postman menambah, mengubah, atau menghapus data **pada database yang dipakai server**. Backup `database/database.sqlite` dulu, atau jalankan server dengan database salinan.

## Masalah yang Sering Muncul

- **Error `Class "Pdo\Mysql" not found` atau sejenisnya saat `php artisan` dijalankan.**
  Tandanya PHP masih versi lama (di bawah 8.4). Ganti versi PHP dulu, lalu ulangi dari langkah `composer install`.

- **Halaman tampil tapi tanpa tampilan/CSS berantakan.**
  Berarti `npm run build` belum dijalankan atau gagal. Ulangi langkah 8.

- **Error terkait database / `.env`.**
  Pastikan file `.env` sudah ada (langkah 4) dan `database/database.sqlite` sudah dibuat (langkah 5).

- **`composer` atau `npm` tidak dikenali di terminal.**
  Biasanya karena baru saja diinstal dan terminal belum dibuka ulang. Tutup dan buka lagi terminalnya.
