<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\BahanBaku;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\StockOpname;
use App\Models\RiwayatStok;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStockOpnameWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'AdminSeeder']);
        $this->artisan('db:seed', ['--class' => 'SatuanSeeder']);
    }

    public function test_admin_dapat_melihat_halaman_stock_opname()
    {
        $admin = User::first();
        $response = $this->actingAs($admin)->get(route('admin.stock_opname.index'));
        $response->assertStatus(200);
        $response->assertSee('Cara Melakukan Stock Opname');
    }

    public function test_admin_dapat_melakukan_stock_opname_bahan_baku()
    {
        $admin = User::first();
        $bahan = BahanBaku::create([
            'nama' => 'Biji Kopi Arabica',
            'satuan' => 'Gram',
            'harga_beli' => 200,
            'stok' => 1000,
            'stok_minimum' => 100,
            'aktif' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.stock_opname.store'), [
            'tipe_item' => 'bahan_baku',
            'item_id' => $bahan->id,
            'stok_fisik' => 950,
            'keterangan' => 'Audit fisik mingguan'
        ]);

        $response->assertRedirect(route('admin.stock_opname.index'));
        $this->assertEquals(950, $bahan->fresh()->stok);

        $this->assertDatabaseHas('stock_opname_detail', [
            'bahan_baku_id' => $bahan->id,
            'stok_sistem' => 1000,
            'stok_fisik' => 950,
            'selisih' => -50
        ]);

        $this->assertDatabaseHas('riwayat_stok', [
            'bahan_baku_id' => $bahan->id,
            'jenis' => 'penyesuaian',
            'jumlah' => -50,
            'stok_sebelum' => 1000,
            'stok_sesudah' => 950,
            'user_id' => $admin->id
        ]);

        // Pastikan tabel history opname bisa dirender
        $responseGet = $this->actingAs($admin)->get(route('admin.stock_opname.index'));
        $responseGet->assertStatus(200);
        $responseGet->assertSee('Audit fisik mingguan');
        $responseGet->assertSee('-50');
    }

    public function test_admin_dapat_melakukan_stock_opname_produk()
    {
        $admin = User::first();
        $kategori = Kategori::create(['nama' => 'Kopi', 'aktif' => true]);
        $produk = Produk::create([
            'kategori_id' => $kategori->id,
            'nama' => 'Cold Brew Bottle',
            'hpp' => 10000,
            'harga' => 25000,
            'stok' => 15,
            'aktif' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.stock_opname.store'), [
            'tipe_item' => 'produk',
            'item_id' => $produk->id,
            'stok_fisik' => 20,
            'keterangan' => 'Ketemu botol lebih di chiller'
        ]);

        $response->assertRedirect(route('admin.stock_opname.index'));
        $this->assertEquals(20, $produk->fresh()->stok);

        $this->assertDatabaseHas('stock_opname_detail', [
            'produk_id' => $produk->id,
            'stok_sistem' => 15,
            'stok_fisik' => 20,
            'selisih' => 5
        ]);
    }
}
