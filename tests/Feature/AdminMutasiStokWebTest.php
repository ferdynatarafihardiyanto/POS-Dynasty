<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\BahanBaku;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\RiwayatStok;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMutasiStokWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'AdminSeeder']);
        $this->artisan('db:seed', ['--class' => 'SatuanSeeder']);
    }

    public function test_admin_dapat_melihat_halaman_mutasi_stok()
    {
        $admin = User::first();
        $response = $this->actingAs($admin)->get(route('admin.stok.index'));
        $response->assertStatus(200);
        $response->assertSee('Mutasi Stok');
        $response->assertSee('Catat Mutasi Stok');
    }

    public function test_admin_dapat_catat_mutasi_masuk_bahan_baku()
    {
        $admin = User::first();
        $bahan = BahanBaku::create([
            'nama' => 'Biji Kopi Robusta',
            'satuan' => 'Gram',
            'harga_beli' => 150,
            'stok' => 500,
            'stok_minimum' => 50,
            'aktif' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.stok.store'), [
            'tipe_item' => 'bahan_baku',
            'item_id' => $bahan->id,
            'tipe' => 'masuk',
            'qty' => 200,
            'keterangan' => 'Beli dari Supplier'
        ]);

        $response->assertRedirect(route('admin.stok.index'));
        $this->assertEquals(700, $bahan->fresh()->stok);

        $this->assertDatabaseHas('riwayat_stok', [
            'bahan_baku_id' => $bahan->id,
            'jenis' => 'masuk',
            'jumlah' => 200,
            'stok_sebelum' => 500,
            'stok_sesudah' => 700,
            'user_id' => $admin->id
        ]);
    }

    public function test_admin_dapat_catat_mutasi_keluar_bahan_baku()
    {
        $admin = User::first();
        $bahan = BahanBaku::create([
            'nama' => 'Susu Segar',
            'satuan' => 'ml',
            'harga_beli' => 30,
            'stok' => 1000,
            'stok_minimum' => 100,
            'aktif' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.stok.store'), [
            'tipe_item' => 'bahan_baku',
            'item_id' => $bahan->id,
            'tipe' => 'keluar',
            'qty' => 250,
            'keterangan' => 'Susu tumpah / rusak'
        ]);

        $response->assertRedirect(route('admin.stok.index'));
        $this->assertEquals(750, $bahan->fresh()->stok);

        $this->assertDatabaseHas('riwayat_stok', [
            'bahan_baku_id' => $bahan->id,
            'jenis' => 'keluar',
            'jumlah' => 250,
            'stok_sebelum' => 1000,
            'stok_sesudah' => 750,
            'user_id' => $admin->id
        ]);
    }

    public function test_mutasi_keluar_gagal_jika_stok_tidak_cukup()
    {
        $admin = User::first();
        $bahan = BahanBaku::create([
            'nama' => 'Syrup Vanilla',
            'satuan' => 'ml',
            'harga_beli' => 50,
            'stok' => 100,
            'stok_minimum' => 20,
            'aktif' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.stok.store'), [
            'tipe_item' => 'bahan_baku',
            'item_id' => $bahan->id,
            'tipe' => 'keluar',
            'qty' => 500, // melebihi 100
            'keterangan' => 'Keluar berlebih'
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(100, $bahan->fresh()->stok);
    }
}
