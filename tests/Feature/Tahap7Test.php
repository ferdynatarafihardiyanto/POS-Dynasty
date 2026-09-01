<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\CafeTable;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\User;
use App\Models\Pesanan;
use App\Models\StockOpname;

class Tahap7Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'AdminSeeder']);
        $this->artisan('db:seed', ['--class' => 'CafeTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'KategoriSeeder']);
        $this->artisan('db:seed', ['--class' => 'ProdukSeeder']);
    }

    private function getAdminToken()
    {
        $user = User::where('email', 'admin@cafe.test')->first();
        return $user->createToken('test')->plainTextToken;
    }

    private function createDummyPesanan($produk, $jumlah = 2)
    {
        $table = CafeTable::where('status', 'active')->first();
        $pesanan = Pesanan::create([
            'nomor_pesanan' => 'ORD-TEST-' . rand(1000, 9999),
            'meja_id' => $table->id,
            'status' => 'menunggu_pembayaran',
            'total_harga' => $produk->harga * $jumlah
        ]);
        
        $pesanan->detailPesanan()->create([
            'produk_id' => $produk->id,
            'nama_produk' => $produk->nama,
            'harga' => $produk->harga,
            'jumlah' => $jumlah,
            'subtotal' => $produk->harga * $jumlah
        ]);

        return $pesanan;
    }

    public function test_1_stok_masuk()
    {
        $produk = Produk::first();
        $stokAwal = $produk->stok;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/stok/masuk', [
                             'produk_id' => $produk->id,
                             'jumlah' => 10,
                             'keterangan' => 'tambah'
                         ]);

        $response->assertStatus(200);
        $this->assertEquals($stokAwal + 10, $produk->fresh()->stok);
    }

    public function test_2_stok_keluar()
    {
        $produk = Produk::first();
        $produk->stok = 30;
        $produk->save();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/stok/keluar', [
                             'produk_id' => $produk->id,
                             'jumlah' => 3,
                             'keterangan' => 'rusak'
                         ]);

        $response->assertStatus(200);
        $this->assertEquals(27, $produk->fresh()->stok);
    }

    public function test_3_stok_keluar_lebih_besar()
    {
        $produk = Produk::first();
        $produk->stok = 5;
        $produk->save();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/stok/keluar', [
                             'produk_id' => $produk->id,
                             'jumlah' => 10
                         ]);

        $response->assertStatus(422);
        $this->assertEquals(5, $produk->fresh()->stok);
    }

    public function test_4_customer_order_stok_tetap()
    {
        $produk = Produk::where('aktif', true)->first();
        $produk->stok = 20;
        $produk->save();
        $stokAwal = $produk->stok;

        $pesanan = $this->createDummyPesanan($produk, 2);

        $this->assertEquals($stokAwal, $produk->fresh()->stok);
    }

    public function test_5_pembayaran_berhasil_stok_berkurang()
    {
        $produk = Produk::where('aktif', true)->first();
        $produk->stok = 20;
        $produk->save();

        $pesanan = $this->createDummyPesanan($produk, 2);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/pos/pembayaran', [
                             'pesanan_id' => $pesanan->id,
                             'metode_pembayaran' => 'cash',
                             'jumlah_bayar' => $pesanan->total_harga
                         ]);

        $response->assertStatus(200);
        $this->assertEquals(18, $produk->fresh()->stok);
    }

    public function test_6_stok_tidak_cukup_saat_bayar()
    {
        $produk = Produk::where('aktif', true)->first();
        $produk->stok = 1;
        $produk->save();

        $pesanan = $this->createDummyPesanan($produk, 3);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/pos/pembayaran', [
                             'pesanan_id' => $pesanan->id,
                             'metode_pembayaran' => 'cash',
                             'jumlah_bayar' => $pesanan->total_harga
                         ]);

        $response->assertStatus(422);
        $this->assertEquals(1, $produk->fresh()->stok);
        $this->assertEquals('menunggu_pembayaran', $pesanan->fresh()->status);
    }

    public function test_7_stock_opname_selisih()
    {
        $produk = Produk::first();
        $produk->stok = 20;
        $produk->save();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/stock-opname', [
                             'detail' => [
                                 ['produk_id' => $produk->id, 'stok_fisik' => 18]
                             ]
                         ]);

        $response->assertStatus(201);
        $this->assertEquals(-2, $response->json('data.detail.0.selisih'));
    }

    public function test_8_stock_opname_draft_stok_tetap()
    {
        $produk = Produk::first();
        $produk->stok = 20;
        $produk->save();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
             ->postJson('/api/admin/stock-opname', [
                 'detail' => [
                     ['produk_id' => $produk->id, 'stok_fisik' => 18]
                 ]
             ]);

        $this->assertEquals(20, $produk->fresh()->stok);
    }

    public function test_9_stock_opname_selesai_ubah_stok()
    {
        $produk = Produk::first();
        $produk->stok = 20;
        $produk->save();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/stock-opname', [
                             'detail' => [
                                 ['produk_id' => $produk->id, 'stok_fisik' => 18]
                             ]
                         ]);

        $opnameId = $response->json('data.id');

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
             ->postJson("/api/admin/stock-opname/{$opnameId}/selesai");

        $this->assertEquals(18, $produk->fresh()->stok);
    }

    public function test_10_stock_opname_selesai_dua_kali()
    {
        $produk = Produk::first();
        $produk->stok = 20;
        $produk->save();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/stock-opname', [
                             'detail' => [
                                 ['produk_id' => $produk->id, 'stok_fisik' => 18]
                             ]
                         ]);

        $opnameId = $response->json('data.id');

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
             ->postJson("/api/admin/stock-opname/{$opnameId}/selesai")
             ->assertStatus(200);

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
             ->postJson("/api/admin/stock-opname/{$opnameId}/selesai")
             ->assertStatus(422);
    }

    public function test_11_stock_opname_fisik_negatif()
    {
        $produk = Produk::first();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/stock-opname', [
                             'detail' => [
                                 ['produk_id' => $produk->id, 'stok_fisik' => -5]
                             ]
                         ]);

        $response->assertStatus(422);
    }

    public function test_12_customer_akses_stok()
    {
        $response = $this->getJson('/api/admin/stok');
        $response->assertStatus(401);
    }

    public function test_13_customer_akses_opname()
    {
        $response = $this->getJson('/api/admin/stock-opname');
        $response->assertStatus(401);
    }

    public function test_14_manipulasi_stok_sistem()
    {
        $produk = Produk::first();
        $produk->stok = 20;
        $produk->save();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/stock-opname', [
                             'detail' => [
                                 ['produk_id' => $produk->id, 'stok_fisik' => 18, 'stok_sistem' => 999]
                             ]
                         ]);

        // Should ignore stok_sistem=999 and use 20. Selisih = 18 - 20 = -2
        $response->assertStatus(201);
        $this->assertEquals(-2, $response->json('data.detail.0.selisih'));
        $this->assertEquals(20, $response->json('data.detail.0.stok_sistem'));
    }
}
