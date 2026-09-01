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

class Tahap5Test extends TestCase
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

    public function test_1_buat_pesanan()
    {
        $table = CafeTable::where('status', 'active')->first();
        $produk = Produk::where('aktif', true)->first();

        $response = $this->postJson('/api/pesanan', [
            'qr_token' => $table->qr_token,
            'produk' => [
                ['produk_id' => $produk->id, 'jumlah' => 2]
            ]
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.status', 'menunggu_pembayaran');
    }

    public function test_2_qr_invalid()
    {
        $produk = Produk::where('aktif', true)->first();

        $response = $this->postJson('/api/pesanan', [
            'qr_token' => 'invalid-token',
            'produk' => [
                ['produk_id' => $produk->id, 'jumlah' => 2]
            ]
        ]);

        $response->assertStatus(404);
    }

    public function test_3_meja_inactive()
    {
        $table = CafeTable::first();
        $table->status = 'inactive';
        $table->save();
        $produk = Produk::where('aktif', true)->first();

        $response = $this->postJson('/api/pesanan', [
            'qr_token' => $table->qr_token,
            'produk' => [
                ['produk_id' => $produk->id, 'jumlah' => 2]
            ]
        ]);

        $response->assertStatus(422);
    }

    public function test_4_produk_valid()
    {
        $table = CafeTable::where('status', 'active')->first();
        $produk = Produk::where('aktif', true)->first();

        $response = $this->postJson('/api/pesanan', [
            'qr_token' => $table->qr_token,
            'produk' => [
                ['produk_id' => $produk->id, 'jumlah' => 1]
            ]
        ]);

        $response->assertStatus(201);
    }

    public function test_5_produk_inactive()
    {
        $table = CafeTable::where('status', 'active')->first();
        $produk = Produk::first();
        $produk->aktif = false;
        $produk->save();

        $response = $this->postJson('/api/pesanan', [
            'qr_token' => $table->qr_token,
            'produk' => [
                ['produk_id' => $produk->id, 'jumlah' => 1]
            ]
        ]);

        $response->assertStatus(422);
    }

    public function test_6_produk_tidak_ada()
    {
        $table = CafeTable::where('status', 'active')->first();

        $response = $this->postJson('/api/pesanan', [
            'qr_token' => $table->qr_token,
            'produk' => [
                ['produk_id' => 99999, 'jumlah' => 1]
            ]
        ]);

        $response->assertStatus(422);
    }

    public function test_7_jumlah_nol()
    {
        $table = CafeTable::where('status', 'active')->first();
        $produk = Produk::where('aktif', true)->first();

        $response = $this->postJson('/api/pesanan', [
            'qr_token' => $table->qr_token,
            'produk' => [
                ['produk_id' => $produk->id, 'jumlah' => 0]
            ]
        ]);

        $response->assertStatus(422);
    }

    public function test_8_jumlah_negatif()
    {
        $table = CafeTable::where('status', 'active')->first();
        $produk = Produk::where('aktif', true)->first();

        $response = $this->postJson('/api/pesanan', [
            'qr_token' => $table->qr_token,
            'produk' => [
                ['produk_id' => $produk->id, 'jumlah' => -2]
            ]
        ]);

        $response->assertStatus(422);
    }

    public function test_9_order_kosong()
    {
        $table = CafeTable::where('status', 'active')->first();

        $response = $this->postJson('/api/pesanan', [
            'qr_token' => $table->qr_token,
            'produk' => []
        ]);

        $response->assertStatus(422);
    }

    public function test_10_perhitungan_total()
    {
        $table = CafeTable::where('status', 'active')->first();
        $produk1 = Produk::where('aktif', true)->first();
        $produk2 = Produk::where('aktif', true)->skip(1)->first();

        $response = $this->postJson('/api/pesanan', [
            'qr_token' => $table->qr_token,
            'produk' => [
                ['produk_id' => $produk1->id, 'jumlah' => 2],
                ['produk_id' => $produk2->id, 'jumlah' => 1]
            ]
        ]);

        $response->assertStatus(201);
        $expectedTotal = ($produk1->harga * 2) + ($produk2->harga * 1);
        $response->assertJsonPath('data.total_harga', $expectedTotal);
    }

    public function test_11_harga_dimanipulasi()
    {
        $table = CafeTable::where('status', 'active')->first();
        $produk = Produk::where('aktif', true)->first();

        $response = $this->postJson('/api/pesanan', [
            'qr_token' => $table->qr_token,
            'produk' => [
                ['produk_id' => $produk->id, 'jumlah' => 2, 'harga' => 1]
            ]
        ]);

        $response->assertStatus(201);
        $expectedTotal = $produk->harga * 2;
        $response->assertJsonPath('data.total_harga', $expectedTotal);
    }

    public function test_12_detail_pesanan()
    {
        $table = CafeTable::where('status', 'active')->first();
        $produk = Produk::where('aktif', true)->first();

        $postResponse = $this->postJson('/api/pesanan', [
            'qr_token' => $table->qr_token,
            'produk' => [
                ['produk_id' => $produk->id, 'jumlah' => 2]
            ]
        ]);
        
        $nomorPesanan = $postResponse->json('data.nomor_pesanan');

        $response = $this->getJson("/api/pesanan/{$nomorPesanan}?qr_token={$table->qr_token}");
        $response->assertStatus(200);
        $response->assertJsonPath('data.nomor_pesanan', $nomorPesanan);
    }

    public function test_13_pesanan_meja_lain()
    {
        $table1 = CafeTable::where('status', 'active')->first();
        $table2 = CafeTable::where('status', 'active')->skip(1)->first();
        $produk = Produk::where('aktif', true)->first();

        $postResponse = $this->postJson('/api/pesanan', [
            'qr_token' => $table1->qr_token,
            'produk' => [
                ['produk_id' => $produk->id, 'jumlah' => 2]
            ]
        ]);
        
        $nomorPesanan = $postResponse->json('data.nomor_pesanan');

        // Access with wrong token
        $response = $this->getJson("/api/pesanan/{$nomorPesanan}?qr_token={$table2->qr_token}");
        $response->assertStatus(403);
    }

    public function test_14_admin_list_pesanan()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/pesanan');
        $response->assertStatus(200);
    }

    public function test_15_customer_mengakses_admin()
    {
        $response = $this->getJson('/api/admin/pesanan');
        $response->assertStatus(401);
    }

    public function test_16_stok()
    {
        $table = CafeTable::where('status', 'active')->first();
        $produk = Produk::where('aktif', true)->first();
        $initialStok = $produk->stok;

        $response = $this->postJson('/api/pesanan', [
            'qr_token' => $table->qr_token,
            'produk' => [
                ['produk_id' => $produk->id, 'jumlah' => 3]
            ]
        ]);

        $response->assertStatus(201);
        
        $produkAfter = Produk::find($produk->id);
        $this->assertEquals($initialStok, $produkAfter->stok);
    }
}
