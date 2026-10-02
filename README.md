# Lentera

Proyek ini dibangun menggunakan **VILT Stack** (Vue.js, Inertia.js, Laravel, Tailwind CSS).

## 💻 Prasyarat
Sebelum memulai, pastikan sistem Anda memiliki aplikasi berikut:
- **PHP** (minimal versi 8.2)
- **Composer** (untuk dependensi PHP)
- **Node.js & npm** (untuk dependensi frontend Vue/Tailwind)
- **Database Server** (PostgreSQL, pastikan sudah terinstall dan berjalan di sistem Anda)

---

## 🚀 Panduan Setup & Instalasi

Ikuti langkah-langkah berikut untuk menjalankan proyek ini di mesin lokal (development):

### 1. Clone & Install Dependensi Backend
Buka terminal dan jalankan perintah berikut di direktori proyek:
```bash
# Install dependensi PHP (Laravel)
composer install
```

### 2. Install Dependensi Frontend
```bash
# Install package NPM (Vue, Inertia, Tailwind, Vite)
npm install
```

### 3. Setup Environment Variables (.env)
Copy file `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
*(Atau Anda bisa men-copy paste file `.env.example` lalu me-rename hasilnya menjadi `.env` melalui file explorer).*

Setelah file `.env` terbuat, generate Application Key:
```bash
php artisan key:generate
```

### 4. Konfigurasi Database
Buka file `.env` yang baru saja Anda buat, lalu sesuaikan koneksi database untuk menggunakan **PostgreSQL**:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=lentera_db   # Pastikan Anda sudah membuat database kosong bernama lentera_db di pgAdmin/psql
DB_USERNAME=postgres     # Sesuaikan dengan username PostgreSQL Anda
DB_PASSWORD=             # Isi dengan password PostgreSQL Anda
```

### 5. Menjalankan Migrasi & Seeder
Buat struktur tabel dan isi dengan data awal (dummy data/admin) dengan menjalankan:
```bash
php artisan migrate --seed
```
*(Catatan: Perintah di atas akan menjalankan file migrasi dan otomatis memanggil `DatabaseSeeder.php`)*

---

## 🏃‍♂️ Menjalankan Aplikasi (Development)

Karena proyek ini menggunakan Inertia dan Vite, Anda perlu menjalankan **dua terminal** secara bersamaan:

**Terminal 1 (Backend - Laravel):**
```bash
php artisan serve
```
Aplikasi Laravel akan berjalan di `http://localhost:8000` atau `http://127.0.0.1:8000`

**Terminal 2 (Frontend - Vite & Tailwind):**
```bash
npm run dev
```
Perintah ini akan melakukan kompilasi aset Vue & Tailwind secara realtime setiap kali Anda menyimpan perubahan (Hot Module Replacement).

---

## 📂 Struktur Penting
- `resources/js/Pages/` : Berisi komponen/halaman utama Vue.js.
- `resources/js/Components/` : Berisi komponen kecil yang bisa digunakan berulang (tombol, input, dll).
- `routes/web.php` : Tempat mendefinisikan URL/Routing aplikasi.
- `app/Http/Controllers/` : Tempat menyimpan logika backend (Controller).

---

## 🏗️ Arsitektur Aplikasi (Backend)
Proyek ini mengadopsi pola desain **"Fat Service, Skinny Controller"** pada modul Authentication dan modul utama lainnya.

- **Controllers (`app/Http/Controllers`)**: Sangat tipis (*skinny*), hanya menangani *Request*, validasi awal, dan *Response/Redirect*.
- **Form Requests (`app/Http/Requests`)**: Memisahkan logika aturan validasi (seperti `required`, `email`, dll) dari Controller agar rapi.
- **Services (`app/Services`)**: Menampung *business logic* yang tebal (*fat*), operasi database (Insert/Update), dan pengiriman email. 
- **Jobs & Queues (`app/Jobs`)**: Tugas berat (seperti mengirim *Welcome Email* dan pengiriman OTP) dilakukan di belakang layar secara *asynchronous* melalui sistem *Queue* Laravel. Hal ini menjamin pengalaman pengguna (UX) web tetap *real-time* dan memuat seketika tanpa perlu menunggu server SMTP merespons.

*Catatan: Jika fitur pengiriman email (OTP/Welcome) terasa tidak masuk saat didevelopment, pastikan Anda menjalankan Worker dengan perintah:*
```bash
php artisan queue:work
```

Selamat melakukan *coding*! 🚀
