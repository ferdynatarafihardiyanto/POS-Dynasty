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

class Tahap11Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'AdminSeeder']);
        $this->artisan('db:seed', ['--class' => 'KategoriSeeder']);
        $this->artisan('db:seed', ['--class' => 'ProdukSeeder']);
        $this->artisan('db:seed', ['--class' => 'CafeTableSeeder']);
    }

    private function getAdminToken()
    {
        $user = User::where('email', 'admin@cafe.test')->first();
        return $user->createToken('test')->plainTextToken;
    }

    public function test_1_qr_meja_01()
    {
        $meja = CafeTable::where('table_number', '01')->first();
        $response = $this->getJson('/api/meja/' . $meja->qr_token);
        $response->assertStatus(200);
    }

    public function test_2_menu_dan_keranjang()
    {
        $meja = CafeTable::where('table_number', '01')->first();
        $response = $this->getJson('/api/menu/meja/' . $meja->qr_token);
        $response->assertStatus(200);
        $response->assertJsonFragment(['nama' => 'Americano']); // assuming Americano seeded
    }

    public function test_3_checkout_berhasil()
    {
        $meja = CafeTable::where('table_number', '01')->first();
        $produk = Produk::where('nama', 'Americano')->first();

        $response = $this->postJson('/api/pesanan', [
            'qr_token' => $meja->qr_token,
            'produk' => [
                [
                    'produk_id' => $produk->id,
                    'jumlah' => 2
                ]
            ]
        ]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('pesanan', ['meja_id' => $meja->id]);
    }

    public function test_4_pos_menerima_pesanan()
    {
        $meja = CafeTable::where('table_number', '01')->first();
        $produk = Produk::where('nama', 'Americano')->first();

        $pesananResponse = $this->postJson('/api/pesanan', [
            'qr_token' => $meja->qr_token,
            'produk' => [['produk_id' => $produk->id, 'jumlah' => 2]]
        ]);
        
        $pesananId = Pesanan::where('nomor_pesanan', $pesananResponse->json('data.nomor_pesanan'))->first()->id;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/pos/pesanan');
        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $pesananId]);
    }

    public function test_5_6_pembayaran_cash_dan_stok_berkurang()
    {
        $meja = CafeTable::where('table_number', '01')->first();
        $produk = Produk::where('nama', 'Americano')->first();
        $stokAwal = $produk->stok;

        $pesananResponse = $this->postJson('/api/pesanan', [
            'qr_token' => $meja->qr_token,
            'produk' => [['produk_id' => $produk->id, 'jumlah' => 2]]
        ]);
        
        $pesanan = Pesanan::where('nomor_pesanan', $pesananResponse->json('data.nomor_pesanan'))->first();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/pos/pembayaran', [
                             'pesanan_id' => $pesanan->id,
                             'metode_pembayaran' => 'cash',
                             'jumlah_bayar' => $pesanan->total_harga + 5000
                         ]);
                         
        $response->assertStatus(200);
        $this->assertEquals($stokAwal - 2, $produk->fresh()->stok);
    }

    public function test_7_8_history_dan_laporan()
    {
        $this->test_5_6_pembayaran_cash_dan_stok_berkurang();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/transaksi');
        $response->assertStatus(200);
        $response->assertJsonFragment(['metode_pembayaran' => 'cash']);

        $responseLaporan = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/laporan');
        $responseLaporan->assertStatus(200);
        
        // Asumsi Americano harga 15000 * 2 = 30000
        $this->assertTrue($responseLaporan->json('data.total_pendapatan') > 0);
    }

    public function test_9_qris_berhasil()
    {
        $meja = CafeTable::where('table_number', '02')->first();
        $produk = Produk::where('nama', 'Latte')->first();

        $pesananResponse = $this->postJson('/api/pesanan', [
            'qr_token' => $meja->qr_token,
            'produk' => [['produk_id' => $produk->id, 'jumlah' => 1]]
        ]);
        
        $pesananResponse->assertStatus(201); // Ensure pesanan created
        $pesanan = Pesanan::where('nomor_pesanan', $pesananResponse->json('data.nomor_pesanan'))->first();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/pos/pembayaran', [
                             'pesanan_id' => $pesanan->id,
                             'metode_pembayaran' => 'qris',
                             'jumlah_bayar' => $pesanan->total_harga
                         ]);
                         
        $response->assertStatus(200);
        $this->assertDatabaseHas('pembayaran', ['pesanan_id' => $pesanan->id, 'metode_pembayaran' => 'qris']);
    }

    public function test_10_stok_tidak_cukup()
    {
        $meja = CafeTable::where('table_number', '01')->first();
        $produk = Produk::where('nama', 'Americano')->first();
        $produk->stok = 1;
        $produk->save();

        $response = $this->postJson('/api/pesanan', [
            'qr_token' => $meja->qr_token,
            'produk' => [['produk_id' => $produk->id, 'jumlah' => 2]]
        ]);
        
        $response->assertStatus(422);
        $this->assertEquals(1, $produk->fresh()->stok);
    }

    public function test_11_produk_nonaktif()
    {
        $meja = CafeTable::where('table_number', '01')->first();
        $produk = Produk::where('nama', 'Americano')->first();
        $produk->aktif = false;
        $produk->save();

        $response = $this->postJson('/api/pesanan', [
            'qr_token' => $meja->qr_token,
            'produk' => [['produk_id' => $produk->id, 'jumlah' => 2]]
        ]);
        
        $response->assertStatus(422);
    }

    public function test_12_meja_nonaktif()
    {
        $meja = CafeTable::where('table_number', '01')->first();
        $meja->status = 'inactive';
        $meja->save();

        $response = $this->getJson('/api/meja/' . $meja->qr_token);
        $response->assertStatus(422);

        $responseOrder = $this->postJson('/api/pesanan', [
            'qr_token' => $meja->qr_token,
            'produk' => [['produk_id' => 1, 'jumlah' => 1]]
        ]);
        $responseOrder->assertStatus(422);
    }

    public function test_13_duplikasi_pembayaran()
    {
        $meja = CafeTable::where('table_number', '01')->first();
        $produk = Produk::where('nama', 'Americano')->first();

        $pesananResponse = $this->postJson('/api/pesanan', [
            'qr_token' => $meja->qr_token,
            'produk' => [['produk_id' => $produk->id, 'jumlah' => 1]]
        ]);
        
        $pesanan = Pesanan::where('nomor_pesanan', $pesananResponse->json('data.nomor_pesanan'))->first();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
             ->postJson('/api/admin/pos/pembayaran', [
                 'pesanan_id' => $pesanan->id,
                 'metode_pembayaran' => 'cash',
                 'jumlah_bayar' => $pesanan->total_harga
             ]);

        $responseKedua = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
             ->postJson('/api/admin/pos/pembayaran', [
                 'pesanan_id' => $pesanan->id,
                 'metode_pembayaran' => 'cash',
                 'jumlah_bayar' => $pesanan->total_harga
             ]);
                         
        $responseKedua->assertStatus(422);
    }

    public function test_14_perubahan_harga_transaksi_lama_aman()
    {
        $meja = CafeTable::where('table_number', '01')->first();
        $produk = Produk::where('nama', 'Americano')->first();
        $hargaLama = $produk->harga;

        $pesananResponse = $this->postJson('/api/pesanan', [
            'qr_token' => $meja->qr_token,
            'produk' => [['produk_id' => $produk->id, 'jumlah' => 1]]
        ]);
        
        $pesanan = Pesanan::where('nomor_pesanan', $pesananResponse->json('data.nomor_pesanan'))->first();

        $pembayaranResponse = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
             ->postJson('/api/admin/pos/pembayaran', [
                 'pesanan_id' => $pesanan->id,
                 'metode_pembayaran' => 'cash',
                 'jumlah_bayar' => $pesanan->total_harga
             ]);

        // Change price
        $produk->harga = $hargaLama + 5000;
        $produk->save();

        $pembayaranId = Pembayaran::where('pesanan_id', $pesanan->id)->first()->id;

        $historyResponse = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                                ->getJson('/api/admin/transaksi/' . $pembayaranId);
        
        $historyResponse->assertJsonFragment(['harga' => $hargaLama]);
    }

    public function test_15_stock_opname()
    {
        $produk = Produk::where('nama', 'Americano')->first();
        $stokSistem = $produk->stok;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/stock-opname', [
                             'catatan' => 'SO Bulanan',
                             'detail' => [
                                 ['produk_id' => $produk->id, 'stok_fisik' => $stokSistem - 2, 'keterangan' => 'Rusak']
                             ]
                         ]);

        $response->assertStatus(201);
        $soId = $response->json('data.id');

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
             ->postJson('/api/admin/stock-opname/' . $soId . '/selesai');

        $this->assertEquals($stokSistem - 2, $produk->fresh()->stok);
    }

    public function test_16_authorization()
    {
        $response = $this->postJson('/api/admin/produk', [
            'nama' => 'Hacker Product'
        ]);
        $response->assertStatus(401);
    }

    public function test_17_manipulasi_harga_ditolak()
    {
        $meja = CafeTable::where('table_number', '01')->first();
        $produk = Produk::where('nama', 'Americano')->first();

        $pesananResponse = $this->postJson('/api/pesanan', [
            'qr_token' => $meja->qr_token,
            'produk' => [['produk_id' => $produk->id, 'jumlah' => 1, 'harga' => 100]] // Manipulated
        ]);
        
        $pesanan = Pesanan::where('nomor_pesanan', $pesananResponse->json('data.nomor_pesanan'))->first();
        $this->assertEquals($produk->harga, $pesanan->total_harga);
    }

    public function test_18_meja_palsu()
    {
        $response = $this->postJson('/api/pesanan', [
            'qr_token' => 'fake-qr',
            'produk' => [['produk_id' => 1, 'jumlah' => 1]]
        ]);
        
        $response->assertStatus(404);
    }
}
