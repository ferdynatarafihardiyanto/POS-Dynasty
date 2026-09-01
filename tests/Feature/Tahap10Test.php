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

class Tahap10Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'AdminSeeder']);
        $this->artisan('db:seed', ['--class' => 'KategoriSeeder']);
        $this->artisan('db:seed', ['--class' => 'ProdukSeeder']);
    }

    private function getAdminToken()
    {
        $user = User::where('email', 'admin@cafe.test')->first();
        return $user->createToken('test')->plainTextToken;
    }

    public function test_1_tambah_meja()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/meja', [
                             'nomor_meja' => '01',
                             'nama_meja' => 'Meja 01'
                         ]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('cafe_tables', ['table_number' => '01', 'name' => 'Meja 01', 'qr_token' => 'meja-01', 'status' => 'active']);
    }

    public function test_2_tambah_meja_duplikat()
    {
        CafeTable::create(['table_number' => '01', 'name' => 'Meja 01', 'qr_token' => 'meja-01', 'status' => 'active']);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/meja', [
                             'nomor_meja' => '01',
                             'nama_meja' => 'Meja 01'
                         ]);
        $response->assertStatus(422);
    }

    public function test_3_edit_nama_meja()
    {
        $meja = CafeTable::create(['table_number' => '01', 'name' => 'Meja 01', 'qr_token' => 'meja-01', 'status' => 'active']);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->putJson('/api/admin/meja/' . $meja->id, [
                             'nomor_meja' => '01',
                             'nama_meja' => 'Meja Indoor 01'
                         ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('cafe_tables', ['name' => 'Meja Indoor 01']);
    }

    public function test_4_nonaktifkan_meja()
    {
        $meja = CafeTable::create(['table_number' => '01', 'name' => 'Meja 01', 'qr_token' => 'meja-01', 'status' => 'active']);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->patchJson('/api/admin/meja/' . $meja->id . '/status', [
                             'aktif' => false
                         ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('cafe_tables', ['status' => 'inactive']);
    }

    public function test_5_customer_scan_qr_meja_aktif()
    {
        $meja = CafeTable::create(['table_number' => '01', 'name' => 'Meja 01', 'qr_token' => 'meja-01', 'status' => 'active']);
        
        $response = $this->getJson('/api/meja/' . $meja->qr_token);
        $response->assertStatus(200);
        $response->assertJsonFragment(['nama_meja' => 'Meja 01']);

        $responseMenu = $this->getJson('/api/menu/meja/' . $meja->qr_token);
        $responseMenu->assertStatus(200);
    }

    public function test_6_customer_scan_qr_meja_nonaktif()
    {
        $meja = CafeTable::create(['table_number' => '02', 'name' => 'Meja 02', 'qr_token' => 'meja-02', 'status' => 'inactive']);
        
        $response = $this->getJson('/api/meja/' . $meja->qr_token);
        $response->assertStatus(422);

        $responseMenu = $this->getJson('/api/menu/meja/' . $meja->qr_token);
        $responseMenu->assertStatus(422);
    }

    public function test_7_qr_meja_01()
    {
        $meja = CafeTable::create(['table_number' => '01', 'name' => 'Meja 01', 'qr_token' => 'meja-01', 'status' => 'active']);
        
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/meja/' . $meja->id . '/qr');
        $response->assertStatus(200);
        $response->assertJsonFragment(['url' => url('/menu/meja/meja-01')]);
    }

    public function test_8_qr_meja_02()
    {
        $meja = CafeTable::create(['table_number' => '02', 'name' => 'Meja 02', 'qr_token' => 'meja-02', 'status' => 'active']);
        
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/meja/' . $meja->id . '/qr');
        $response->assertStatus(200);
        $response->assertJsonFragment(['url' => url('/menu/meja/meja-02')]);
    }

    public function test_9_customer_buat_pesanan_dari_meja_03()
    {
        $meja = CafeTable::create(['table_number' => '03', 'name' => 'Meja 03', 'qr_token' => 'meja-03', 'status' => 'active']);
        $produk = Produk::first();

        $response = $this->postJson('/api/pesanan', [
            'qr_token' => $meja->qr_token,
            'items' => [
                [
                    'produk_id' => $produk->id,
                    'jumlah' => 2
                ]
            ],
            // Oh wait, PesananController.php uses 'produk' not 'items'
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

    public function test_10_admin_melihat_pos()
    {
        $meja = CafeTable::create(['table_number' => '03', 'name' => 'Meja 03', 'qr_token' => 'meja-03', 'status' => 'active']);
        $pesanan = Pesanan::create([
            'nomor_pesanan' => 'ORD-POS-001',
            'meja_id' => $meja->id,
            'status' => 'menunggu_pembayaran',
            'total_harga' => 50000
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/pos/pesanan');
        
        $response->assertStatus(200);
        $response->assertJsonFragment(['meja' => '03']); 
    }

    public function test_11_customer_coba_post_admin()
    {
        $response = $this->postJson('/api/admin/meja', [
            'nomor_meja' => '09',
            'nama_meja' => 'Test'
        ]);
        $response->assertStatus(401);
    }

    public function test_12_edit_meja_kode_berubah()
    {
        $meja = CafeTable::create(['table_number' => '01', 'name' => 'Meja 01', 'qr_token' => 'meja-01', 'status' => 'active']);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->putJson('/api/admin/meja/' . $meja->id, [
                             'nomor_meja' => '10',
                             'nama_meja' => 'Meja 10'
                         ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('cafe_tables', ['qr_token' => 'meja-10']);
    }

    public function test_13_history_transaksi_tetap_menampilkan_meja()
    {
        $meja = CafeTable::create(['table_number' => '02', 'name' => 'Meja 02', 'qr_token' => 'meja-02', 'status' => 'active']);
        $pesanan = Pesanan::create([
            'nomor_pesanan' => 'ORD-TRX-001',
            'meja_id' => $meja->id,
            'status' => 'dibayar',
            'total_harga' => 50000
        ]);

        $pembayaran = Pembayaran::create([
            'pesanan_id' => $pesanan->id,
            'nomor_transaksi' => 'TRX-TEST-001',
            'metode_pembayaran' => 'cash',
            'jumlah_bayar' => 50000,
            'kembalian' => 0,
            'status' => 'berhasil',
            'dibayar_pada' => now()
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/transaksi');
        
        $response->assertStatus(200);
        $response->assertJsonFragment(['nomor_transaksi' => 'TRX-TEST-001']);
        
        $responseDetail = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/transaksi/' . $pembayaran->id);
        
        $responseDetail->assertStatus(200);
        $responseDetail->assertJsonFragment(['meja' => '02']);
    }
}
