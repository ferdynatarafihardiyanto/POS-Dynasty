<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\Kategori;
use App\Models\Produk;

class Tahap3Test extends TestCase
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

    public function test_1_list_kategori()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/kategori');
        $response->assertStatus(200);
    }

    public function test_2_tambah_kategori()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/kategori', [
                             'nama' => 'Minuman Baru',
                             'deskripsi' => 'Minuman segar',
                             'aktif' => true
                         ]);
        $response->assertStatus(201);
    }

    public function test_3_duplikat_kategori()
    {
        $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
             ->postJson('/api/admin/kategori', [
                 'nama' => 'Coffee',
                 'aktif' => true
             ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/kategori', [
                             'nama' => 'Coffee',
                             'aktif' => true
                         ]);
        $response->assertStatus(422);
    }

    public function test_4_ubah_kategori()
    {
        $kategori = Kategori::first();
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->putJson('/api/admin/kategori/' . $kategori->id, [
                             'nama' => $kategori->nama, // Same name, should not trigger unique validation error for itself
                             'deskripsi' => 'Deskripsi Baru',
                             'aktif' => true
                         ]);
        $response->assertStatus(200);
    }

    public function test_5_hapus_kategori_yang_digunakan()
    {
        $kategori = Kategori::has('produk')->first();
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->deleteJson('/api/admin/kategori/' . $kategori->id);
        // Sejak Tahap 9, fitur hapus diganti menjadi soft disable, sehingga DELETE route dihapus.
        $response->assertStatus(405);
    }

    public function test_6_list_produk()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->getJson('/api/admin/produk');
        $response->assertStatus(200);
    }

    public function test_7_tambah_produk()
    {
        $kategori = Kategori::first();
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/produk', [
                             'kategori_id' => $kategori->id,
                             'nama' => 'Produk Baru',
                             'harga' => 15000,
                             'stok' => 10,
                             'aktif' => true
                         ]);
        $response->assertStatus(201);
    }

    public function test_8_kategori_tidak_valid()
    {
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/produk', [
                             'kategori_id' => 99999,
                             'nama' => 'Produk Gagal',
                             'harga' => 15000,
                             'stok' => 10,
                             'aktif' => true
                         ]);
        $response->assertStatus(422);
    }

    public function test_9_harga_negatif()
    {
        $kategori = Kategori::first();
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/produk', [
                             'kategori_id' => $kategori->id,
                             'nama' => 'Produk Minus',
                             'harga' => -1000,
                             'stok' => 10,
                             'aktif' => true
                         ]);
        $response->assertStatus(422);
    }

    public function test_10_stok_negatif()
    {
        $kategori = Kategori::first();
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->getAdminToken()])
                         ->postJson('/api/admin/produk', [
                             'kategori_id' => $kategori->id,
                             'nama' => 'Produk Stok Minus',
                             'harga' => 10000,
                             'stok' => -1,
                             'aktif' => true
                         ]);
        $response->assertStatus(422);
    }

    public function test_11_public_menu()
    {
        $response = $this->getJson('/api/menu');
        $response->assertStatus(200);
    }

    public function test_12_produk_inactive()
    {
        $produk = Produk::first();
        $produk->aktif = false;
        $produk->save();

        $response = $this->getJson('/api/menu');
        $response->assertJsonMissing(['nama' => $produk->nama]);
    }

    public function test_13_kategori_inactive()
    {
        $kategori = Kategori::first();
        $kategori->aktif = false;
        $kategori->save();

        $produkNames = $kategori->produk->pluck('nama')->toArray();

        $response = $this->getJson('/api/menu');
        
        foreach ($produkNames as $name) {
            $response->assertJsonMissing(['nama' => $name]);
        }
    }

    public function test_14_filter_kategori()
    {
        $kategori = Kategori::first();
        $response = $this->getJson('/api/menu?kategori_id=' . $kategori->id);
        
        // Assert all returned items belong to this category
        $data = $response->json('data');
        foreach ($data as $item) {
            $this->assertEquals($kategori->id, $item['kategori_id']);
        }
    }

    public function test_15_search()
    {
        $response = $this->getJson('/api/menu?cari=latte');
        $data = $response->json('data');
        
        // At least one result must contain 'latte' (case insensitive)
        $this->assertNotEmpty($data);
        foreach ($data as $item) {
            $this->assertStringContainsStringIgnoringCase('latte', $item['nama']);
        }
    }

    public function test_16_customer_tanpa_token()
    {
        $response = $this->getJson('/api/menu');
        $response->assertStatus(200);
    }

    public function test_17_customer_mengakses_admin()
    {
        $response = $this->getJson('/api/admin/produk');
        $response->assertStatus(401);
    }
}
