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
use App\Models\Pembayaran;

class Tahap6Test extends TestCase
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

    private function createDummyPesanan($status = 'menunggu_pembayaran')
    {
        $table = CafeTable::where('status', 'active')->first();
        $pesanan = Pesanan::create([
            'nomor_pesanan' => 'ORD-TEST-' . rand(1000, 9999),
            'meja_id' => $table->id,
            'status' => $status,
            'total_harga' => 58000
        ]);
        return $pesanan;
    }

    public function test_1_admin_buka_pos()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/pos/pesanan');
        $response->assertStatus(200);
    }

    public function test_2_pesanan_menunggu_muncul()
    {
        $pesanan = $this->createDummyPesanan('menunggu_pembayaran');
        
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/pos/pesanan');
        
        $response->assertStatus(200);
        $response->assertJsonFragment(['nomor_pesanan' => $pesanan->nomor_pesanan]);
    }

    public function test_3_pesanan_dibayar_tidak_muncul()
    {
        $pesanan = $this->createDummyPesanan('dibayar');
        
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/pos/pesanan');
        
        $response->assertStatus(200);
        $response->assertJsonMissing(['nomor_pesanan' => $pesanan->nomor_pesanan]);
    }

    public function test_4_cash_berhasil()
    {
        $pesanan = $this->createDummyPesanan();
        
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/pos/pembayaran', [
                             'pesanan_id' => $pesanan->id,
                             'metode_pembayaran' => 'cash',
                             'jumlah_bayar' => 100000
                         ]);
        
        $response->assertStatus(200);
        $response->assertJsonPath('data.kembalian', 100000 - 58000);
        $this->assertEquals('dibayar', $pesanan->fresh()->status);
    }

    public function test_5_cash_kurang()
    {
        $pesanan = $this->createDummyPesanan();
        
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/pos/pembayaran', [
                             'pesanan_id' => $pesanan->id,
                             'metode_pembayaran' => 'cash',
                             'jumlah_bayar' => 50000
                         ]);
        
        $response->assertStatus(422);
    }

    public function test_6_qris_berhasil()
    {
        $pesanan = $this->createDummyPesanan();
        
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/pos/pembayaran', [
                             'pesanan_id' => $pesanan->id,
                             'metode_pembayaran' => 'qris',
                             'jumlah_bayar' => 58000
                         ]);
        
        $response->assertStatus(200);
        $response->assertJsonPath('data.kembalian', 0);
    }

    public function test_7_qris_kurang()
    {
        $pesanan = $this->createDummyPesanan();
        
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/pos/pembayaran', [
                             'pesanan_id' => $pesanan->id,
                             'metode_pembayaran' => 'qris',
                             'jumlah_bayar' => 50000
                         ]);
        
        $response->assertStatus(422);
    }

    public function test_8_qris_lebih()
    {
        $pesanan = $this->createDummyPesanan();
        
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/pos/pembayaran', [
                             'pesanan_id' => $pesanan->id,
                             'metode_pembayaran' => 'qris',
                             'jumlah_bayar' => 60000
                         ]);
        
        $response->assertStatus(422);
    }

    public function test_9_pesanan_sudah_dibayar()
    {
        $pesanan = $this->createDummyPesanan('dibayar');
        
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/pos/pembayaran', [
                             'pesanan_id' => $pesanan->id,
                             'metode_pembayaran' => 'cash',
                             'jumlah_bayar' => 100000
                         ]);
        
        $response->assertStatus(422);
    }

    public function test_10_pesanan_tidak_ada()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/pos/pembayaran', [
                             'pesanan_id' => 99999,
                             'metode_pembayaran' => 'cash',
                             'jumlah_bayar' => 100000
                         ]);
        
        $response->assertStatus(404);
    }

    public function test_11_customer_akses_pos()
    {
        $response = $this->getJson('/api/admin/pos/pesanan');
        $response->assertStatus(401);
    }

    public function test_12_harga_dimanipulasi()
    {
        $pesanan = $this->createDummyPesanan();
        
        // Frontend sends incorrect total_harga
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/pos/pembayaran', [
                             'pesanan_id' => $pesanan->id,
                             'metode_pembayaran' => 'qris',
                             'jumlah_bayar' => 20000,
                             'total_harga' => 20000
                         ]);
        
        // Should be rejected because backend uses total_harga = 58000, so jumlah_bayar 20000 is not enough for QRIS
        $response->assertStatus(422);
    }

    public function test_13_kembalian_dimanipulasi()
    {
        $pesanan = $this->createDummyPesanan();
        
        // Frontend sends wrong kembalian
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/pos/pembayaran', [
                             'pesanan_id' => $pesanan->id,
                             'metode_pembayaran' => 'cash',
                             'jumlah_bayar' => 100000,
                             'kembalian' => 900000
                         ]);
        
        $response->assertStatus(200);
        // Backend overrides kembalian
        $response->assertJsonPath('data.kembalian', 100000 - 58000);
    }

    public function test_14_double_payment()
    {
        $pesanan = $this->createDummyPesanan();
        
        // Pay once
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
             ->postJson('/api/admin/pos/pembayaran', [
                 'pesanan_id' => $pesanan->id,
                 'metode_pembayaran' => 'cash',
                 'jumlah_bayar' => 100000
             ])->assertStatus(200);

        // Pay again simultaneously
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/pos/pembayaran', [
                             'pesanan_id' => $pesanan->id,
                             'metode_pembayaran' => 'cash',
                             'jumlah_bayar' => 100000
                         ]);

        // Should be rejected
        $response->assertStatus(422);
    }
}
