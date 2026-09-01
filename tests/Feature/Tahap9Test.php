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

class Tahap9Test extends TestCase
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

    public function test_1_tambah_kategori()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/kategori', [
                             'nama' => 'Coffee Baru',
                             'deskripsi' => 'Deskripsi'
                         ]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('kategori', ['nama' => 'Coffee Baru', 'aktif' => true]);
    }

    public function test_2_tambah_kategori_duplikat()
    {
        $kategori = Kategori::first();
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/kategori', [
                             'nama' => $kategori->nama,
                             'deskripsi' => 'Deskripsi'
                         ]);
        $response->assertStatus(422);
    }

    public function test_3_ubah_kategori()
    {
        $kategori = Kategori::first();
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->putJson('/api/admin/kategori/' . $kategori->id, [
                             'nama' => 'Kopi Update',
                             'deskripsi' => 'Update deskripsi'
                         ]);
        $response->assertStatus(200);
        $this->assertDatabaseHas('kategori', ['nama' => 'Kopi Update']);
    }

    public function test_4_nonaktifkan_kategori()
    {
        $kategori = Kategori::first();
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
             ->patchJson('/api/admin/kategori/' . $kategori->id . '/status', [
                 'aktif' => false
             ]);

        $response = $this->getJson('/api/menu/kategori');
        $response->assertJsonMissing(['nama' => $kategori->nama]);
    }

    public function test_5_customer_akses_admin()
    {
        $response = $this->postJson('/api/admin/kategori', ['nama' => 'Test']);
        $response->assertStatus(401);
    }

    public function test_6_tambah_produk()
    {
        $kategori = Kategori::first();
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/produk', [
                             'kategori_id' => $kategori->id,
                             'nama' => 'Test Espresso',
                             'deskripsi' => 'Test',
                             'harga' => 15000,
                             'stok' => 20
                         ]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('produk', ['nama' => 'Test Espresso', 'stok' => 20]);
        $this->assertDatabaseHas('riwayat_stok', ['jenis' => 'masuk', 'jumlah' => 20, 'keterangan' => 'Stok awal produk']);
    }

    public function test_7_produk_muncul_menu_customer()
    {
        $kategori = Kategori::first();
        $produk = Produk::create(['kategori_id' => $kategori->id, 'nama' => 'Test Menu', 'harga' => 10000, 'stok' => 10, 'aktif' => true]);

        $response = $this->getJson('/api/menu');
        $response->assertJsonFragment(['nama' => 'Test Menu']);
    }

    public function test_8_nonaktifkan_produk()
    {
        $produk = Produk::first();
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
             ->patchJson('/api/admin/produk/' . $produk->id . '/status', [
                 'aktif' => false
             ]);

        $response = $this->getJson('/api/menu');
        $response->assertJsonMissing(['nama' => $produk->nama]);
    }

    public function test_9_aktifkan_kembali_produk()
    {
        $produk = Produk::first();
        $produk->aktif = false;
        $produk->save();

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
             ->patchJson('/api/admin/produk/' . $produk->id . '/status', [
                 'aktif' => true
             ]);

        $response = $this->getJson('/api/menu');
        $response->assertJsonFragment(['nama' => $produk->nama]);
    }

    public function test_10_edit_harga_produk()
    {
        $produk = Produk::first();
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
             ->putJson('/api/admin/produk/' . $produk->id, [
                 'kategori_id' => $produk->kategori_id,
                 'nama' => $produk->nama,
                 'harga' => 30000,
                 'stok' => $produk->stok
             ]);

        $response = $this->getJson('/api/menu');
        $response->assertJsonFragment(['harga' => 30000]);
    }

    public function test_11_edit_produk_stok_tetap()
    {
        $produk = Produk::first();
        $stokAwal = $produk->stok;

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
             ->putJson('/api/admin/produk/' . $produk->id, [
                 'kategori_id' => $produk->kategori_id,
                 'nama' => $produk->nama,
                 'harga' => $produk->harga,
                 'stok' => 999
             ]);

        $this->assertEquals($stokAwal, $produk->fresh()->stok);
    }

    public function test_12_gunakan_endpoint_stok_masuk()
    {
        $produk = Produk::first();
        $stokAwal = $produk->stok;

        $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
             ->postJson('/api/admin/stok/masuk', [
                 'produk_id' => $produk->id,
                 'jumlah' => 10,
                 'keterangan' => 'tambah'
             ]);

        $this->assertEquals($stokAwal + 10, $produk->fresh()->stok);
    }

    public function test_13_transaksi_lama_harga_tetap()
    {
        $table = CafeTable::where('status', 'active')->first();
        $produk = Produk::first();
        $hargaLama = $produk->harga;
        
        $pesanan = Pesanan::create([
            'nomor_pesanan' => 'ORD-TEST-' . rand(1000, 9999),
            'meja_id' => $table->id,
            'status' => 'dibayar',
            'total_harga' => $hargaLama * 2
        ]);
        
        $pesanan->detailPesanan()->create([
            'produk_id' => $produk->id,
            'nama_produk' => $produk->nama,
            'harga' => $hargaLama,
            'jumlah' => 2,
            'subtotal' => $hargaLama * 2
        ]);

        $pembayaran = Pembayaran::create([
            'pesanan_id' => $pesanan->id,
            'nomor_transaksi' => 'TRX-TEST-' . rand(1000, 9999),
            'metode_pembayaran' => 'cash',
            'jumlah_bayar' => $hargaLama * 2,
            'kembalian' => 0,
            'status' => 'berhasil',
            'dibayar_pada' => now()
        ]);

        // Edit harga produk
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
             ->putJson('/api/admin/produk/' . $produk->id, [
                 'kategori_id' => $produk->kategori_id,
                 'nama' => $produk->nama,
                 'harga' => $hargaLama + 5000,
                 'stok' => $produk->stok
             ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/transaksi/' . $pembayaran->id);
        
        $response->assertJsonFragment(['harga' => $hargaLama]); // Should retain old price
    }

    public function test_14_kategori_nonaktif_tambah_produk()
    {
        $kategori = Kategori::first();
        $kategori->aktif = false;
        $kategori->save();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/produk', [
                             'kategori_id' => $kategori->id,
                             'nama' => 'Test Product',
                             'harga' => 10000,
                             'stok' => 10
                         ]);
        $response->assertStatus(422);
    }

    public function test_15_produk_nonaktif_admin_tetap_lihat()
    {
        $produk = Produk::first();
        $produk->aktif = false;
        $produk->save();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/produk');
        
        $response->assertStatus(200);
        $response->assertJsonFragment(['nama' => $produk->nama]);
    }
}
