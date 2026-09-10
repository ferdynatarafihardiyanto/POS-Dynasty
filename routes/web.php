<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\Admin\DashboardController;
use App\Http\Controllers\Web\Admin\POSController;

use App\Models\CafeTable;
use App\Models\Kategori;
use App\Models\Produk;

$getCustomerViewData = function ($qrToken = null) {
    try {
        $table = null;
        if ($qrToken) {
            $table = CafeTable::where('qr_token', $qrToken)->first();
        }
        
        if (!$table) {
            $table = CafeTable::where('status', 'active')->first();
        }

        $categories = Kategori::where('aktif', true)->get(['id', 'nama', 'deskripsi']);
        
        $products = Produk::with('kategori')
            ->where('aktif', true)
            ->whereHas('kategori', function ($q) {
                $q->where('aktif', true);
            })
            ->get();

        $availableTables = CafeTable::where('status', 'active')->get(['id', 'table_number', 'name', 'qr_token']);

        return [
            'tableInfo' => $table ? [
                'id' => $table->id,
                'number' => (string) $table->table_number,
                'name' => $table->name ?? ('Meja ' . $table->table_number),
                'token' => $table->qr_token,
                'status' => $table->status,
            ] : null,
            'categories' => $categories,
            'products' => $products,
            'availableTables' => $availableTables->map(function ($t) {
                return [
                    'id' => $t->id,
                    'number' => (string) $t->table_number,
                    'name' => $t->name ?? ('Meja ' . $t->table_number),
                    'token' => $t->qr_token,
                    'capacity' => isset($t->kapasitas) && $t->kapasitas ? "{$t->kapasitas} Orang" : null,
                ];
            }),
            'qrToken' => $qrToken,
        ];
    } catch (\Throwable $e) {
        return [
            'tableInfo' => null,
            'categories' => collect(),
            'products' => collect(),
            'availableTables' => collect(),
            'qrToken' => $qrToken,
        ];
    }
};

Route::get('/', function (\Illuminate\Http\Request $request) use ($getCustomerViewData) {
    $qrToken = $request->query('qr_token') ?: $request->query('token');
    return view('app', $getCustomerViewData($qrToken));
});
Route::get('/menu', function () {
    return view('app');
});
Route::get('/meja/{number?}', function () {
    return view('app');
});

Route::get('/menu/meja/{qr_token}', function ($qr_token) use ($getCustomerViewData) {
    return view('app', $getCustomerViewData($qr_token));
});


Route::prefix('admin')->name('admin.')->group(function () {
    
    // GUEST ROUTES
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'processLogin']);
    });

    // AUTH ROUTES
    Route::middleware(['auth', 'role'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Master Data CRUD
        Route::resource('kategori', \App\Http\Controllers\Web\Admin\KategoriController::class)->except(['show']);
        Route::get('/produk/export/pdf', [\App\Http\Controllers\Web\Admin\ProdukController::class, 'exportPdf'])->name('produk.export.pdf');
        Route::resource('produk', \App\Http\Controllers\Web\Admin\ProdukController::class)->except(['show']);

        Route::get('/meja/{meja}/print', [\App\Http\Controllers\Web\Admin\CafeTableController::class, 'print'])->name('meja.print');
        Route::resource('meja', \App\Http\Controllers\Web\Admin\CafeTableController::class)->except(['show'])->parameters([
            'meja' => 'meja'
        ]);
        Route::resource('satuan', \App\Http\Controllers\Web\Admin\SatuanController::class)->except(['show']);
        
        Route::get('/profil', function() { return view('admin.profil.index'); })->name('profil.index');
        Route::get('/karyawan', function() { return view('admin.karyawan.index'); })->name('karyawan.index');
        
        Route::resource('bahan-baku', \App\Http\Controllers\Web\Admin\BahanBakuController::class)->parameters(['bahan-baku' => 'bahanBaku']);
        Route::get('/resep', [\App\Http\Controllers\Web\Admin\ResepController::class, 'index'])->name('resep.index');
        Route::post('/resep/detail', [\App\Http\Controllers\Web\Admin\ResepController::class, 'storeDetail'])->name('resep.detail.store');
        Route::delete('/resep/detail/{id}', [\App\Http\Controllers\Web\Admin\ResepController::class, 'destroyDetail'])->name('resep.detail.destroy');
        Route::resource('modifier-groups', \App\Http\Controllers\Web\Admin\ModifierGroupController::class)->except(['show'])->parameters([
            'modifier-groups' => 'modifier_group'
        ]);
        Route::resource('modifier-options', \App\Http\Controllers\Web\Admin\ModifierOptionController::class)->only(['store', 'update', 'destroy'])->parameters([
            'modifier-options' => 'modifier_option'
        ]);
        
        Route::get('/stok', [\App\Http\Controllers\Web\Admin\StokController::class, 'index'])->name('stok.index');
        Route::post('/stok', [\App\Http\Controllers\Web\Admin\StokController::class, 'store'])->name('stok.store');
        Route::get('/stok/export/excel', [\App\Http\Controllers\Web\Admin\StokController::class, 'exportExcel'])->name('stok.export.excel');
        Route::get('/stok/export/pdf', [\App\Http\Controllers\Web\Admin\StokController::class, 'exportPdf'])->name('stok.export.pdf');
        Route::get('/stock-opname', [\App\Http\Controllers\Web\Admin\StockOpnameController::class, 'index'])->name('stock_opname.index');
        Route::post('/stock-opname', [\App\Http\Controllers\Web\Admin\StockOpnameController::class, 'store'])->name('stock_opname.store');
        
        Route::get('/transaksi', [\App\Http\Controllers\Web\Admin\TransaksiController::class, 'index'])->name('transaksi.index');
        Route::get('/transaksi/{id}/print', [\App\Http\Controllers\Web\Admin\TransaksiController::class, 'print'])->name('transaksi.print');
        Route::get('/pengeluaran', [\App\Http\Controllers\Web\Admin\PengeluaranController::class, 'index'])->name('pengeluaran.index');
        Route::post('/pengeluaran', [\App\Http\Controllers\Web\Admin\PengeluaranController::class, 'store'])->name('pengeluaran.store');
        Route::get('/laporan', [\App\Http\Controllers\Web\Admin\LaporanController::class, 'index'])->name('laporan.index');
    });

    // POS ROUTES (Accessible by Admin and Kasir)
    Route::middleware(['auth', 'role:admin,kasir'])->group(function () {
        Route::get('/pos', [POSController::class, 'index'])->name('pos.index');
        Route::post('/pos/checkout', [POSController::class, 'checkout'])->name('pos.checkout');
        Route::get('/pos/pesanan-aktif', [POSController::class, 'pesananAktif'])->name('pos.pesanan_aktif');
        Route::patch('/pos/pesanan/{id}/status', [POSController::class, 'ubahStatusPesanan'])->name('pos.ubah_status');
        Route::post('/pos/pesanan/{id}/bayar', [POSController::class, 'bayarPesananMeja'])->name('pos.bayar_pesanan');
    });
});