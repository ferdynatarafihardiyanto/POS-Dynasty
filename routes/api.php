<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\CafeTableController;

use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\ProdukController;
use App\Http\Controllers\MenuController;

// Admin authentication
Route::post('/admin/login', [AdminAuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'role'])->group(function () {
    Route::post('/admin/logout', [AdminAuthController::class, 'logout']);
    Route::get('/admin/me', [AdminAuthController::class, 'me']);
    Route::get('/admin/tables', [CafeTableController::class, 'index']);

    // Admin Meja
    Route::get('/admin/meja', [\App\Http\Controllers\Admin\MejaController::class, 'daftarMeja']);
    Route::post('/admin/meja', [\App\Http\Controllers\Admin\MejaController::class, 'tambahMeja']);
    Route::get('/admin/meja/{id}', [\App\Http\Controllers\Admin\MejaController::class, 'detailMeja']);
    Route::put('/admin/meja/{id}', [\App\Http\Controllers\Admin\MejaController::class, 'ubahMeja']);
    Route::patch('/admin/meja/{id}/status', [\App\Http\Controllers\Admin\MejaController::class, 'ubahStatusMeja']);
    Route::get('/admin/meja/{id}/qr', [\App\Http\Controllers\Admin\MejaController::class, 'tampilkanQrMeja']);

    // Admin Kategori
    Route::get('/admin/kategori', [KategoriController::class, 'daftarKategori']);
    Route::post('/admin/kategori', [KategoriController::class, 'tambahKategori']);
    Route::get('/admin/kategori/{id}', [KategoriController::class, 'detailKategori']);
    Route::put('/admin/kategori/{id}', [KategoriController::class, 'ubahKategori']);
    Route::patch('/admin/kategori/{id}/status', [KategoriController::class, 'ubahStatusKategori']);

    // Admin Produk
    Route::get('/admin/produk', [ProdukController::class, 'daftarProduk']);
    Route::post('/admin/produk', [ProdukController::class, 'tambahProduk']);
    Route::get('/admin/produk/{id}', [ProdukController::class, 'detailProduk']);
    Route::put('/admin/produk/{id}', [ProdukController::class, 'ubahProduk']);
    Route::patch('/admin/produk/{id}/status', [ProdukController::class, 'ubahStatusProduk']);

    // Admin Pesanan
    Route::get('/admin/pesanan', [\App\Http\Controllers\Admin\PesananController::class, 'daftarPesanan']);
    Route::get('/admin/pesanan/{id}', [\App\Http\Controllers\Admin\PesananController::class, 'detailPesanan']);

    // Admin POS
    Route::get('/admin/pos/pesanan', [\App\Http\Controllers\Admin\POSController::class, 'daftarPesanan']);
    Route::get('/admin/pos/pesanan/aktif', [\App\Http\Controllers\Admin\POSController::class, 'pesananAktif']);
    Route::get('/admin/pos/pesanan/{id}', [\App\Http\Controllers\Admin\POSController::class, 'detailPesanan']);
    Route::patch('/admin/pos/pesanan/{id}/status', [\App\Http\Controllers\Admin\POSController::class, 'ubahStatusPesanan']);
    Route::post('/admin/pos/pembayaran', [\App\Http\Controllers\Admin\POSController::class, 'prosesPembayaran']);

    // Admin Stok
    Route::get('/admin/stok', [\App\Http\Controllers\Admin\StokController::class, 'daftarStok']);
    Route::get('/admin/stok/riwayat', [\App\Http\Controllers\Admin\StokController::class, 'riwayatStok']);
    Route::get('/admin/stok/{produk_id}', [\App\Http\Controllers\Admin\StokController::class, 'detailStok']);
    Route::post('/admin/stok/masuk', [\App\Http\Controllers\Admin\StokController::class, 'stokMasuk']);
    Route::post('/admin/stok/keluar', [\App\Http\Controllers\Admin\StokController::class, 'stokKeluar']);

    // Admin Stock Opname
    Route::get('/admin/stock-opname', [\App\Http\Controllers\Admin\StockOpnameController::class, 'daftarStockOpname']);
    Route::post('/admin/stock-opname', [\App\Http\Controllers\Admin\StockOpnameController::class, 'buatStockOpname']);
    Route::get('/admin/stock-opname/{id}', [\App\Http\Controllers\Admin\StockOpnameController::class, 'detailStockOpname']);
    Route::put('/admin/stock-opname/{id}', [\App\Http\Controllers\Admin\StockOpnameController::class, 'ubahStockOpname']);
    Route::post('/admin/stock-opname/{id}/selesai', [\App\Http\Controllers\Admin\StockOpnameController::class, 'selesaikanStockOpname']);

    // Admin Transaksi
    Route::get('/admin/transaksi', [\App\Http\Controllers\Admin\TransaksiController::class, 'daftarTransaksi']);
    Route::get('/admin/transaksi/{id}', [\App\Http\Controllers\Admin\TransaksiController::class, 'detailTransaksi']);

    // Admin Laporan
    Route::get('/admin/laporan', [\App\Http\Controllers\Admin\LaporanController::class, 'laporanPenjualan']);
    Route::get('/admin/laporan/produk', [\App\Http\Controllers\Admin\LaporanController::class, 'laporanProduk']);
    Route::get('/admin/laporan/pembayaran', [\App\Http\Controllers\Admin\LaporanController::class, 'laporanPembayaran']);
    Route::get('/admin/laporan/harian', [\App\Http\Controllers\Admin\LaporanController::class, 'laporanHarian']);
    Route::get('/admin/laporan/bulanan', [\App\Http\Controllers\Admin\LaporanController::class, 'laporanBulanan']);
    Route::get('/admin/laporan/tren', [\App\Http\Controllers\Admin\LaporanController::class, 'laporanTren']);
});

// Public Menu
Route::get('/menu', [MenuController::class, 'index']);
Route::get('/menu/kategori', [MenuController::class, 'daftarKategori']);
Route::get('/menu/meja/{qr_token}', [MenuController::class, 'menuBerdasarkanMeja']);
Route::get('/menu/{id}', [MenuController::class, 'show']);

// Public Cafe Table
Route::get('/meja', [CafeTableController::class, 'index']);
Route::get('/meja/{qr_token}', [CafeTableController::class, 'show']);

// Public Pesanan (Customer)
Route::post('/pesanan', [\App\Http\Controllers\Customer\PesananController::class, 'buatPesanan']);
Route::get('/pesanan', [\App\Http\Controllers\Customer\PesananController::class, 'daftarPesanan']);
Route::get('/pesanan/riwayat', [\App\Http\Controllers\Customer\PesananController::class, 'riwayatPesanan']);
Route::get('/pesanan/{nomor_pesanan}', [\App\Http\Controllers\Customer\PesananController::class, 'detailPesanan']);
Route::post('/pesanan/{nomor_pesanan}/bayar', [\App\Http\Controllers\Customer\PesananController::class, 'bayarOnline']);

// Public Store Profile (Untuk Sinkronisasi Struk & Identitas Kafe)
Route::get('/profil-toko', [\App\Http\Controllers\Customer\PesananController::class, 'getProfilToko']);
Route::post('/profil-toko', [\App\Http\Controllers\Customer\PesananController::class, 'saveProfilToko']);

// Midtrans Payment Gateway (Sandbox/Production)
Route::post('/pesanan/{nomor_pesanan}/snap-token', [\App\Http\Controllers\Api\MidtransController::class, 'createSnapToken']);
Route::post('/pesanan/{nomor_pesanan}/midtrans-confirm', [\App\Http\Controllers\Api\MidtransController::class, 'confirmSuccess']);
Route::match(['get', 'post'], '/midtrans/notification', [\App\Http\Controllers\Api\MidtransController::class, 'handleNotification']);
