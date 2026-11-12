# SpaceHub

Aplikasi web untuk booking ruang meeting dan coworking. Dibangun dengan Laravel 13, MariaDB, dan Blade + Tailwind CSS v4.

## Fitur

- Autentikasi (login, register, logout) dengan role **admin** dan **user**
- Katalog ruang publik: pencarian, filter tipe, pagination, dan halaman detail
- Booking ruang dengan validasi jam dan **cek bentrok jadwal**
- Halaman "Booking Saya": lihat riwayat dan batalkan booking
- Panel admin:
  - Dashboard ringkasan (total ruang, pengguna, booking hari ini, booking pending)
  - CRUD ruang (upload foto, pilih fasilitas, soft delete)
  - CRUD fasilitas
  - Kelola booking (setujui, tolak, hapus, filter status)
- Test otomatis untuk aturan booking dan hak akses

## Teknologi

- PHP 8.3+ dan Laravel 13
- MariaDB
- Blade, Tailwind CSS v4, Vite
sed -i 's/^APP_NAME=.*/APP_NAME=SpaceHub/' .env.example
sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=mariadb/' .env.example
sed -i 's/^# DB_HOST=.*/DB_HOST=127.0.0.1/; s/^# DB_PORT=.*/DB_PORT=3306/; s/^# DB_DATABASE=.*/DB_DATABASE=spacehub/; s/^# DB_USERNAME=.*/DB_USERNAME=root/; s/^# DB_PASSWORD=.*/DB_PASSWORD=/' .env.example
grep -q '^APP_TIMEZONE' .env.example || echo 'APP_TIMEZONE=Asia/Jakarta' >> .env.example

cat > README.md <<'EOF'
# SpaceHub

Aplikasi web untuk booking ruang meeting dan coworking. Dibangun dengan Laravel 13, MariaDB, dan Blade + Tailwind CSS v4.

## Fitur

- Autentikasi (login, register, logout) dengan role **admin** dan **user**
- Katalog ruang publik: pencarian, filter tipe, pagination, dan halaman detail
- Booking ruang dengan validasi jam dan **cek bentrok jadwal**
- Halaman "Booking Saya": lihat riwayat dan batalkan booking
- Panel admin:
  - Dashboard ringkasan (total ruang, pengguna, booking hari ini, booking pending)
  - CRUD ruang (upload foto, pilih fasilitas, soft delete)
  - CRUD fasilitas
  - Kelola booking (setujui, tolak, hapus, filter status)
- Test otomatis untuk aturan booking dan hak akses

## Teknologi

- PHP 8.3+ dan Laravel 13
- MariaDB
- Blade, Tailwind CSS v4, Vite

## Struktur Database

| Tabel | Keterangan |
| --- | --- |
| `users` | Pengguna, kolom `role` (`admin` / `user`) |
| `spaces` | Data ruang (tipe, kapasitas, lokasi, harga per jam, foto, soft delete) |
| `facilities` | Daftar fasilitas |
| `facility_space` | Pivot many-to-many ruang dan fasilitas |
| `bookings` | Booking (pengguna, ruang, tanggal, jam mulai/selesai, status) |

## Instalasi

Contoh untuk Fedora. Sesuaikan nama paket dengan distro kamu.

```bash
sudo dnf install mariadb-server php-cli php-mysqlnd php-mbstring php-xml php-pdo composer nodejs npm
sudo systemctl enable --now mariadb

git clone <url-repo-ini> spacehub && cd spacehub
composer install
cp .env.example .env
php artisan key:generate
```

Buat database dan user, lalu sesuaikan `DB_USERNAME` dan `DB_PASSWORD` di `.env`:

```sql
CREATE DATABASE spacehub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'spacehub'@'localhost' IDENTIFIED BY 'ganti-password';
GRANT ALL PRIVILEGES ON spacehub.* TO 'spacehub'@'localhost';
```

Lanjutkan:

```bash
php artisan migrate --seed
php artisan storage:link
npm install
npm run dev      # terminal 1
php artisan serve  # terminal 2
```

Buka `http://localhost:8000`.

## Akun Demo (dari seeder)

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@spacehub.test` | `password` |
| User | `user@spacehub.test` | `password` |

## Menjalankan Test

```bash
php artisan test
```

Test memakai SQLite in-memory, jadi tidak menyentuh database MariaDB.

## Rencana Pengembangan

- Edit booking oleh user selama masih pending
- Restore ruang yang sudah dihapus
- Notifikasi email saat status booking berubah

## Lisensi

[MIT](https://opensource.org/licenses/MIT)

## Struktur Database

| Tabel | Keterangan |
| --- | --- |
| `users` | Pengguna, kolom `role` (`admin` / `user`) |
| `spaces` | Data ruang (tipe, kapasitas, lokasi, harga per jam, foto, soft delete) |
| `facilities` | Daftar fasilitas |
| `facility_space` | Pivot many-to-many ruang dan fasilitas |
| `bookings` | Booking (pengguna, ruang, tanggal, jam mulai/selesai, status) |

## Instalasi

Contoh untuk Fedora. Sesuaikan nama paket dengan distro kamu.

```bash
sudo dnf install mariadb-server php-cli php-mysqlnd php-mbstring php-xml php-pdo composer nodejs npm
sudo systemctl enable --now mariadb

git clone <url-repo-ini> spacehub && cd spacehub
composer install
cp .env.example .env
php artisan key:generate
```

Buat database dan user, lalu sesuaikan `DB_USERNAME` dan `DB_PASSWORD` di `.env`:

```sql
CREATE DATABASE spacehub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'spacehub'@'localhost' IDENTIFIED BY 'ganti-password';
GRANT ALL PRIVILEGES ON spacehub.* TO 'spacehub'@'localhost';
```

Lanjutkan:

```bash
php artisan migrate --seed
php artisan storage:link
npm install
npm run dev      # terminal 1
php artisan serve  # terminal 2
```

Buka `http://localhost:8000`.

## Akun Demo (dari seeder)

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@spacehub.test` | `password` |
| User | `user@spacehub.test` | `password` |

## Menjalankan Test

```bash
php artisan test
```

Test memakai SQLite in-memory, jadi tidak menyentuh database MariaDB.

## Rencana Pengembangan

- Edit booking oleh user selama masih pending
- Restore ruang yang sudah dihapus
- Notifikasi email saat status booking berubah

## Lisensi

[MIT](https://opensource.org/licenses/MIT)
