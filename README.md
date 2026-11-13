<div align="center">

# SpaceHub

**Aplikasi web untuk memesan ruang meeting, coworking, private office, dan event hall.**

![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.4%2B-777BB4?logo=php&logoColor=white)
![MariaDB](https://img.shields.io/badge/MariaDB-003545?logo=mariadb&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-v4-06B6D4?logo=tailwindcss&logoColor=white)
![Tests](https://img.shields.io/badge/tests-22%20passed-2ea44f)
![License](https://img.shields.io/badge/license-MIT-blue)

</div>

---

## Ringkasan

SpaceHub memudahkan pengguna menelusuri ruang, mengecek jadwal yang masih kosong, dan mengirim booking. Admin meninjau setiap booking lalu menyetujui atau menolaknya dari panel yang terpisah. Sistem otomatis menolak jadwal yang bentrok.

## Fitur

### Pengguna
- Registrasi, login, dan logout
- Landing page dengan pencarian, jumlah ruang per tipe, dan ruang terbaru
- Katalog ruang: pencarian, filter tipe, pagination, dan halaman detail
- Booking dengan date dan time picker berbahasa Indonesia serta estimasi biaya langsung
- Validasi jam dan **cek bentrok jadwal** (booking berurutan tetap diizinkan)
- **Jam operasional** dan batas durasi (minimal dan maksimal) per ruang, diatur admin
- **Kalender ketersediaan** per hari di halaman detail ruang; klik jam kosong untuk mengisi formulir booking
- Halaman "Booking Saya": riwayat dan pembatalan
- Profil: ubah nama, email, dan password

### Admin
- Tampilan terpisah dengan sidebar gelap di semua halaman
- Dashboard: ruang aktif, permohonan menunggu, booking disetujui bulan ini, dan estimasi pendapatan
- Kelola ruang: upload foto dengan pratinjau, pilih fasilitas, status aktif, soft delete, dan filter
- Kelola fasilitas
- Kelola booking: setujui, tolak, hapus, dan filter status
- Akun admin tidak dapat membuat booking

### Kualitas
- Test otomatis untuk aturan booking, hak akses, profil, dan landing page

## Teknologi

| Bagian   | Teknologi                          |
| -------- | ---------------------------------- |
| Backend  | PHP 8.4+, Laravel 13               |
| Database | MariaDB (SQLite in-memory untuk test) |
| Frontend | Blade, Tailwind CSS v4, Vite       |
| Library  | Flatpickr (date dan time picker)   |

## Struktur Database

| Tabel            | Keterangan                                                             |
| ---------------- | ---------------------------------------------------------------------- |
| `users`          | Pengguna, kolom `role` (`admin` / `user`)                              |
| `spaces`         | Data ruang (tipe, kapasitas, lokasi, harga per jam, foto, soft delete) |
| `facilities`     | Daftar fasilitas                                                       |
| `facility_space` | Pivot many-to-many ruang dan fasilitas                                 |
| `bookings`       | Booking (pengguna, ruang, tanggal, jam mulai/selesai, status)          |

## Instalasi

Contoh untuk Fedora. Sesuaikan nama paket dengan distro kamu.

**1. Pasang kebutuhan dan unduh proyek**

```bash
sudo dnf install mariadb-server php-cli php-mysqlnd php-mbstring php-xml php-pdo composer nodejs npm
sudo systemctl enable --now mariadb

git clone https://github.com/nbyhq/spacehub.git
cd spacehub
composer install
cp .env.example .env
php artisan key:generate
```

**2. Buat database**

```sql
CREATE DATABASE spacehub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'spacehub'@'localhost' IDENTIFIED BY 'ganti-password';
GRANT ALL PRIVILEGES ON spacehub.* TO 'spacehub'@'localhost';
```

Sesuaikan `DB_USERNAME` dan `DB_PASSWORD` di `.env`.

**3. Migrasi dan jalankan**

```bash
php artisan migrate --seed
php artisan storage:link
npm install
npm run dev          # terminal 1
php artisan serve    # terminal 2
```

Buka `http://localhost:8000`.

## Akun Demo

| Role  | Email                 | Password   |
| ----- | --------------------- | ---------- |
| Admin | `admin@spacehub.com` | `password` |
| User  | `user@spacehub.com`  | `password` |

> Ganti atau hapus akun demo sebelum aplikasi dibuka ke publik.

## Menjalankan Test

```bash
php artisan test
```

Test memakai SQLite in-memory, jadi tidak menyentuh database MariaDB.

## Mencoba dari HP

- **Satu jaringan:** jalankan `php artisan serve --host=0.0.0.0`, lalu buka `http://IP-LAPTOP:8000` dari HP.
- **Jaringan berbeda:** gunakan tunnel seperti ngrok (`ngrok http 8000`).
- Jalankan `npm run build` dan hentikan `npm run dev` agar tampilan tidak polos di HP.

## Rencana Pengembangan

- [ ] Edit booking oleh user selama masih pending
- [ ] Restore ruang yang sudah dihapus
- [ ] Notifikasi email saat status booking berubah

## Lisensi

Dirilis di bawah lisensi [MIT](https://opensource.org/licenses/MIT).
