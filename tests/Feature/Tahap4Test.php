<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\CafeTable;
use App\Models\Produk;
use App\Models\Kategori;

class Tahap4Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'CafeTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'KategoriSeeder']);
        $this->artisan('db:seed', ['--class' => 'ProdukSeeder']);
    }

    public function test_1_qr_valid()
    {
        $table = CafeTable::first();
        $response = $this->getJson('/api/meja/' . $table->qr_token);
        $response->assertStatus(200);
        $response->assertJsonPath('data.nomor_meja', $table->table_number);
    }

    public function test_2_qr_invalid()
    {
        $response = $this->getJson('/api/meja/token-salah');
        $response->assertStatus(404);
    }

    public function test_3_meja_inactive()
    {
        $table = CafeTable::first();
        $table->status = 'inactive';
        $table->save();

        $response = $this->getJson('/api/meja/' . $table->qr_token);
        $response->assertStatus(422);
    }

    public function test_4_public_menu()
    {
        $response = $this->getJson('/api/menu');
        $response->assertStatus(200);
    }

    public function test_5_daftar_kategori()
    {
        $response = $this->getJson('/api/menu/kategori');
        $response->assertStatus(200);
    }

    public function test_6_filter_kategori()
    {
        $kategori = Kategori::first();
        $response = $this->getJson('/api/menu?kategori_id=' . $kategori->id);
        $response->assertStatus(200);
    }

    public function test_7_search()
    {
        $response = $this->getJson('/api/menu?cari=latte');
        $response->assertStatus(200);
    }

    public function test_8_detail_produk()
    {
        $produk = Produk::first();
        $response = $this->getJson('/api/menu/' . $produk->id);
        $response->assertStatus(200);
    }

    public function test_9_produk_inactive()
    {
        $produk = Produk::first();
        $produk->aktif = false;
        $produk->save();

        $response = $this->getJson('/api/menu/' . $produk->id);
        $response->assertStatus(404);
    }

    public function test_10_menu_berdasarkan_qr()
    {
        $table = CafeTable::first();
        $response = $this->getJson('/api/menu/meja/' . $table->qr_token);
        $response->assertStatus(200);
        $response->assertJsonPath('data.meja.nomor', $table->table_number);
    }

    public function test_11_menu_dengan_qr_invalid()
    {
        $response = $this->getJson('/api/menu/meja/token-salah');
        $response->assertStatus(404);
    }

    public function test_12_customer_tanpa_login()
    {
        $table = CafeTable::first();
        $produk = Produk::first();
        $kategori = Kategori::first();

        // Ensure all public endpoints can be accessed without a token
        $this->getJson('/api/meja/' . $table->qr_token)->assertStatus(200);
        $this->getJson('/api/menu')->assertStatus(200);
        $this->getJson('/api/menu/kategori')->assertStatus(200);
        $this->getJson('/api/menu/' . $produk->id)->assertStatus(200);
        $this->getJson('/api/menu/meja/' . $table->qr_token)->assertStatus(200);
    }

    public function test_13_admin_authorization()
    {
        $response = $this->getJson('/api/admin/produk');
        $response->assertStatus(401);
    }
}
