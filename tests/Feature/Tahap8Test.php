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
use Carbon\Carbon;

class Tahap8Test extends TestCase
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

    private function createDummyTransaksi($metode = 'cash', $status = 'berhasil', $tanggal = null)
    {
        $table = CafeTable::where('status', 'active')->first();
        $produk = Produk::first();
        
        $pesanan = Pesanan::create([
            'nomor_pesanan' => 'ORD-TEST-' . rand(1000, 9999),
            'meja_id' => $table->id,
            'status' => 'dibayar',
            'total_harga' => $produk->harga * 2
        ]);
        
        $pesanan->detailPesanan()->create([
            'produk_id' => $produk->id,
            'nama_produk' => $produk->nama,
            'harga' => $produk->harga,
            'jumlah' => 2,
            'subtotal' => $produk->harga * 2
        ]);

        $tanggal = $tanggal ?: now();

        $pembayaran = Pembayaran::create([
            'pesanan_id' => $pesanan->id,
            'nomor_transaksi' => 'TRX-TEST-' . rand(1000, 9999),
            'metode_pembayaran' => $metode,
            'jumlah_bayar' => $pesanan->total_harga + ($metode === 'cash' ? 10000 : 0),
            'kembalian' => $metode === 'cash' ? 10000 : 0,
            'status' => $status,
            'dibayar_pada' => $tanggal
        ]);

        return $pembayaran;
    }

    public function test_1_admin_buka_history()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/transaksi');
        $response->assertStatus(200);
    }

    public function test_2_transaksi_berhasil_muncul()
    {
        $trx = $this->createDummyTransaksi();
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/transaksi');
        $response->assertStatus(200);
        $response->assertJsonFragment(['nomor_transaksi' => $trx->nomor_transaksi]);
    }

    public function test_3_pembayaran_menunggu_tidak_muncul()
    {
        $trx = $this->createDummyTransaksi('cash', 'menunggu');
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/transaksi');
        $response->assertJsonMissing(['nomor_transaksi' => $trx->nomor_transaksi]);
    }

    public function test_4_pembayaran_dibatalkan_tidak_muncul()
    {
        $trx = $this->createDummyTransaksi('cash', 'dibatalkan');
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/transaksi');
        $response->assertJsonMissing(['nomor_transaksi' => $trx->nomor_transaksi]);
    }

    public function test_5_search_nomor_transaksi()
    {
        $trx = $this->createDummyTransaksi();
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/transaksi?cari=' . $trx->nomor_transaksi);
        $response->assertStatus(200);
        $response->assertJsonFragment(['nomor_transaksi' => $trx->nomor_transaksi]);
    }

    public function test_6_search_nomor_pesanan()
    {
        $trx = $this->createDummyTransaksi();
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/transaksi?cari=' . $trx->pesanan->nomor_pesanan);
        $response->assertStatus(200);
        $response->assertJsonFragment(['nomor_pesanan' => $trx->pesanan->nomor_pesanan]);
    }

    public function test_7_filter_cash()
    {
        $this->createDummyTransaksi('cash');
        $this->createDummyTransaksi('qris');
        
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/transaksi?metode_pembayaran=cash');
        
        $response->assertStatus(200);
        foreach ($response->json('data') as $item) {
            $this->assertEquals('cash', $item['metode_pembayaran']);
        }
    }

    public function test_8_filter_qris()
    {
        $this->createDummyTransaksi('cash');
        $this->createDummyTransaksi('qris');
        
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/transaksi?metode_pembayaran=qris');
        
        $response->assertStatus(200);
        foreach ($response->json('data') as $item) {
            $this->assertEquals('qris', $item['metode_pembayaran']);
        }
    }

    public function test_9_filter_tanggal()
    {
        $trx = $this->createDummyTransaksi('cash', 'berhasil', Carbon::now()->subDays(5));
        
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/transaksi?tanggal_mulai=' . Carbon::now()->subDays(6)->toDateString() . '&tanggal_selesai=' . Carbon::now()->subDays(4)->toDateString());
        
        $response->assertStatus(200);
        $response->assertJsonFragment(['nomor_transaksi' => $trx->nomor_transaksi]);
    }

    public function test_10_filter_tanggal_tidak_valid()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/transaksi?tanggal_mulai=' . Carbon::now()->toDateString() . '&tanggal_selesai=' . Carbon::now()->subDays(1)->toDateString());
        
        $response->assertStatus(422); // Validation error
    }

    public function test_11_detail_transaksi()
    {
        $trx = $this->createDummyTransaksi();
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/transaksi/' . $trx->id);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'nomor_transaksi', 'nomor_pesanan', 'meja', 'tanggal',
                'metode_pembayaran', 'daftar_produk' => [['nama_produk', 'harga', 'jumlah', 'subtotal']],
                'total', 'jumlah_bayar', 'kembalian', 'status'
            ]
        ]);
    }

    public function test_12_total_transaksi()
    {
        $this->createDummyTransaksi();
        $this->createDummyTransaksi();
        $this->createDummyTransaksi('cash', 'dibatalkan'); // Should not count

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/laporan');
        $response->assertStatus(200);
        
        // Total valid transactions for today
        $count = Pembayaran::where('status', 'berhasil')->whereDate('dibayar_pada', now())->count();
        $this->assertEquals($count, $response->json('data.total_transaksi'));
    }

    public function test_13_total_pendapatan()
    {
        $this->createDummyTransaksi();
        
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/laporan');
        
        $sum = Pembayaran::with('pesanan')->where('status', 'berhasil')->whereDate('dibayar_pada', now())->get()->sum('pesanan.total_harga');
        $this->assertEquals($sum, $response->json('data.total_pendapatan'));
    }

    public function test_14_cash()
    {
        $this->createDummyTransaksi('cash');
        
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/laporan');
        
        $sumCash = Pembayaran::with('pesanan')->where('status', 'berhasil')->where('metode_pembayaran', 'cash')->whereDate('dibayar_pada', now())->get()->sum('pesanan.total_harga');
        $this->assertEquals($sumCash, $response->json('data.total_cash'));
    }

    public function test_15_qris()
    {
        $this->createDummyTransaksi('qris');
        
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/laporan');
        
        $sumQris = Pembayaran::with('pesanan')->where('status', 'berhasil')->where('metode_pembayaran', 'qris')->whereDate('dibayar_pada', now())->get()->sum('pesanan.total_harga');
        $this->assertEquals($sumQris, $response->json('data.total_qris'));
    }

    public function test_16_produk_terjual()
    {
        $this->createDummyTransaksi();
        
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/laporan');
                         
        $response->assertStatus(200);
        $this->assertGreaterThan(0, $response->json('data.total_produk_terjual'));
    }

    public function test_17_customer_akses_laporan()
    {
        $response = $this->getJson('/api/admin/laporan');
        $response->assertStatus(401);
    }

    public function test_18_customer_akses_history()
    {
        $response = $this->getJson('/api/admin/transaksi');
        $response->assertStatus(401);
    }

    public function test_19_admin_coba_hapus_transaksi()
    {
        $trx = $this->createDummyTransaksi();
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->deleteJson('/api/admin/transaksi/' . $trx->id);
        
        $response->assertStatus(405); // Method not allowed
    }

    public function test_20_admin_coba_ubah_total_transaksi()
    {
        $trx = $this->createDummyTransaksi();
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->putJson('/api/admin/transaksi/' . $trx->id, ['total_harga' => 999999]);
        
        $response->assertStatus(405); // Method not allowed
    }
}
