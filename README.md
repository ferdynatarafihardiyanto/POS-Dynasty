# ☕ Cafe Management System

Cafe Management System adalah sistem backend komprehensif berbasis **Laravel 11** yang dirancang untuk mendigitalisasi alur pemesanan makanan dan minuman. Sistem ini mendukung dua *role* utama: **Admin** (Kasir/Manajemen) dan **Customer** (Pelanggan), lengkap dengan pemrosesan pesanan menggunakan mekanisme Point of Sales (POS) serta integrasi QR Code Meja.

---

## 🌟 Fitur Utama

### 📱 Mode Customer (Pemesanan Mandiri via QR)
- **Scan QR Meja:** Customer hanya dapat mengakses menu dengan melakukan *scan* QR unik yang terkait langsung dengan meja spesifik. Meja nonaktif tidak dapat digunakan.
- **Menu Digital & Pencarian:** Menampilkan daftar kategori dan produk yang sedang *aktif*. Dilengkapi fitur filter kategori dan pencarian produk secara *real-time*.
- **Keranjang & Checkout:** Memungkinkan customer menambah produk ke keranjang, merubah jumlah, dan melakukan checkout mandiri.
- **Validasi Ketersediaan:** Saat proses pemesanan, sistem secara otomatis menolak (422) apabila stok tidak mencukupi atau meja/produk dalam keadaan nonaktif.

### 💻 Mode Admin (POS & Manajemen)
- **Point of Sales (POS):** Kasir melihat pesanan masuk secara langsung dan dapat menyelesaikannya menggunakan metode **Cash** atau **QRIS**.
- **Manajemen Menu & Stok:** CRUD (Create, Read, Update) untuk Kategori dan Produk. (Penghapusan menggunakan konsep *Soft Disable* / Menonaktifkan status, bukan `DELETE`).
- **Stock Management & Opname:** Mencatat seluruh arus barang (Stok Masuk / Keluar) secara detail dengan riwayat log. Menyediakan fitur **Stock Opname** untuk menyesuaikan jumlah fisik secara otomatis.
- **Manajemen Meja & QR:** Menambah, memodifikasi, dan men-generate ulang Token QR Code untuk setiap meja secara independen.
- **History & Laporan:** Seluruh transaksi yang berhasil diselesaikan dicatat untuk pembukuan (Total transaksi, Pendapatan per metode, Produk Terjual).

---

## 🏗️ Arsitektur Backend

Sistem ini didesain menggunakan pola arsitektur **MVC (Model-View-Controller)** murni dengan proteksi logika bisnis *(Business Logic)* yang ketat:

### 1. Authentication & Authorization
- Menggunakan **Laravel Sanctum** untuk sistem berbasis Token API.
- Customer tidak memiliki/membutuhkan akun login. Otentikasi customer mengandalkan kepemilikan **QR Token** (`qr_token`) yang mewakili meja mereka.
- Seluruh rute administratif (`/api/admin/*`) dilindungi oleh *Middleware* eksklusif. Customer akan menerima *401/403 Unauthorized* jika mencoba mengaksesnya.

### 2. Konsep *Single Source of Truth* & Snapshot Harga
- Ketika pesanan dibuat, harga tidak mengandalkan *input* dari *request payload* (anti manipulasi harga / *Price Manipulation*). Backend secara otomatis mengambil harga dari basis data saat itu.
- Saat melakukan pembayaran (POS), harga total direkam dalam bentuk **Snapshot** pada tabel `pembayaran`. Ini menjamin bahwa riwayat transaksi dan laporan keuangan masa lalu **TIDAK AKAN** berubah meskipun Admin mengubah harga referensi Master Produk pada keesokan harinya.

### 3. ACID Compliance & Database Transactions
- Untuk mencegah data setengah jadi, alur Checkout dan Pembayaran dibungkus rapat dengan blok **`DB::beginTransaction()`** dan **`DB::rollBack()`**.
- Apabila terjadi *error* secara sistem, jaringan, maupun kondisi logis yang gagal (misal: *Race Condition* di mana dua customer di meja yang sama memperebutkan stok terakhir), stok tidak akan bocor atau menjadi negatif.

### 4. *Double Payment Prevention*
- Setiap penyelesaian transaksi di level Kasir dikunci dengan pengecekan ganda (*Double Checking*). Jika status pesanan sudah lunas (Paid), sistem secara mutlak akan menolak pembayaran ulang dan tidak akan pernah mengurangi stok dua kali.

### 5. *Soft Disable* (Data Integrity)
- Sistem melarang keras aksi `DELETE` fisik (HTTP 405) pada Entitas Kategori dan Produk. Hal ini diimplementasikan untuk menjaga integritas dan relasi *Foreign Key* pada `detail_pesanan` dan `riwayat_stok` yang sudah terjadi sebelumnya. Sebagai gantinya, status dibuat menjadi `aktif = false`.

---

## 🗄️ Relasi Database

Sistem dirancang dengan skema relasional yang dinamis:
- **`cafe_tables`** ➔ Memiliki relasi (1:N) dengan `pesanan`
- **`kategori`** ➔ Memiliki relasi (1:N) dengan `produk`
- **`produk`** ➔ Memiliki relasi (1:N) dengan `detail_pesanan`, `riwayat_stok`, `detail_stock_opname`
- **`pesanan`** ➔ Entitas utama yang menghubungkan Meja dengan (1:N) `detail_pesanan` dan memicu (1:1) `pembayaran`.

---

## 🚀 Panduan Instalasi (Development)

Pastikan Anda memiliki **PHP 8.2+**, **Composer**, dan **MySQL** (Misal: Laragon, XAMPP, dsb).

1. **Clone & Install Dependency**
   ```bash
   git clone <repo_url>
   cd Dynasty
   composer install
   ```

2. **Setup Konfigurasi Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Atur koneksi `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` sesuai MySQL Anda di dalam file `.env`.*

3. **Migrate & Seed (Dummy Data)**
   ```bash
   php artisan migrate:fresh --seed
   ```
   *Ini akan menciptakan akun admin default (`admin@cafe.test` / `password123`) serta dummy meja, kategori, dan produk.*

4. **Jalankan Development Server**
   ```bash
   php artisan serve
   ```
   *API siap digunakan di URL: `http://127.0.0.1:8000`*

---

## 🧪 Testing

Sistem dilengkapi dengan sekumpulan skenario End-To-End (E2E) dan Feature Testing terintegrasi (>140 Assertions) guna memastikan keamanan dan konsistensi dari tahap Registrasi QR Meja hingga Pemotongan Stok.

Jalankan seluruh tes dengan perintah:
```bash
php artisan test
```

Jika Anda ingin melakukan pengujian API secara manual, gunakan **Postman** dengan meng-import file `Cafe_Management_Postman_Collection.json` yang telah disediakan di root direktori project ini.
