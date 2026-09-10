<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\CafeTable;
use App\Models\Pesanan;
use App\Models\DetailPesanan;

class AdminMasterDataTest extends TestCase
{
    use RefreshDatabase;

    protected function getAdmin()
    {
        return User::factory()->create(['role' => 'admin']);
    }

    protected function getKasir()
    {
        return User::factory()->create(['role' => 'kasir']);
    }

    // ==========================================
    // AUTHORIZATION & IDOR TESTS
    // ==========================================
    public function test_guest_cannot_access_master_data()
    {
        $this->get('/admin/kategori')->assertRedirect('/admin/login');
        $this->get('/admin/produk')->assertRedirect('/admin/login');
        $this->get('/admin/meja')->assertRedirect('/admin/login');
    }

    public function test_non_admin_cannot_access_any_crud_endpoints()
    {
        $kasir = $this->getKasir();
        $kategori = Kategori::create(['nama' => 'Test Kategori', 'aktif' => 1]);
        $produk = Produk::create(['kategori_id' => $kategori->id, 'nama' => 'Test Produk', 'harga' => 10000, 'hpp' => 0, 'stok' => 10, 'aktif' => 1]);

        // Pembuktian perlindungan IDOR & Role Authorization (Kasir tidak bisa mengedit/update/delete meskipun tau ID nya)
        $this->actingAs($kasir)->get('/admin/produk/' . $produk->id . '/edit')->assertStatus(403);
        $this->actingAs($kasir)->put('/admin/produk/' . $produk->id, ['nama' => 'Hacked'])->assertStatus(403);
        $this->actingAs($kasir)->delete('/admin/produk/' . $produk->id)->assertStatus(403);
    }

    // ==========================================
    // KATEGORI CRUD & VALIDATION
    // ==========================================
    public function test_admin_can_crud_kategori()
    {
        $admin = $this->getAdmin();
        $this->actingAs($admin);

        // CREATE
        $this->post('/admin/kategori', ['nama' => 'Minuman Dingin', 'deskripsi' => 'Dingin'])->assertRedirect(route('admin.kategori.index'));
        $this->assertDatabaseHas('kategori', ['nama' => 'Minuman Dingin']);
        $kategori = Kategori::first();

        // READ
        $this->get('/admin/kategori')->assertSee('Minuman Dingin');
        $this->get('/admin/kategori/' . $kategori->id . '/edit')->assertSee('Minuman Dingin');

        // UPDATE
        $this->put('/admin/kategori/' . $kategori->id, ['nama' => 'Minuman Panas', 'deskripsi' => 'Panas'])->assertRedirect(route('admin.kategori.index'));
        $this->assertDatabaseHas('kategori', ['nama' => 'Minuman Panas']);

        // DELETE
        $this->delete('/admin/kategori/' . $kategori->id)->assertRedirect(route('admin.kategori.index'));
        $this->assertDatabaseMissing('kategori', ['id' => $kategori->id]);
    }

    public function test_kategori_validation()
    {
        $admin = $this->getAdmin();
        $this->actingAs($admin);

        // Required name
        $this->post('/admin/kategori', [])->assertSessionHasErrors('nama');
        
        // Unique name
        Kategori::create(['nama' => 'Snack', 'aktif' => 1]);
        $this->post('/admin/kategori', ['nama' => 'Snack'])->assertSessionHasErrors('nama');
    }

    // ==========================================
    // PRODUK CRUD & VALIDATION
    // ==========================================
    public function test_admin_can_crud_produk()
    {
        $admin = $this->getAdmin();
        $this->actingAs($admin);
        $kategori = Kategori::create(['nama' => 'Makanan', 'aktif' => 1]);

        // CREATE
        $this->post('/admin/produk', [
            'kategori_id' => $kategori->id,
            'nama' => 'Nasi Goreng',
            'harga' => 20000,
            'hpp' => 0,
            'stok' => 10,
            'aktif' => 1
        ])->assertRedirect(route('admin.produk.index'));
        
        $produk = Produk::first();

        // READ
        $this->get('/admin/produk')->assertSee('Nasi Goreng');

        // UPDATE
        $this->put('/admin/produk/' . $produk->id, [
            'kategori_id' => $kategori->id,
            'nama' => 'Nasi Goreng Spesial',
            'harga' => 25000,
            'hpp' => 0,
            'stok' => 10,
            'aktif' => 1
        ])->assertRedirect(route('admin.produk.index'));
        $this->assertDatabaseHas('produk', ['nama' => 'Nasi Goreng Spesial', 'harga' => 25000]);

        // DELETE
        $this->delete('/admin/produk/' . $produk->id)->assertRedirect(route('admin.produk.index'));
        $this->assertDatabaseMissing('produk', ['id' => $produk->id]);
    }

    public function test_admin_can_upload_and_update_produk_image()
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $admin = $this->getAdmin();
        $this->actingAs($admin);
        $kategori = Kategori::create(['nama' => 'Minuman', 'aktif' => 1]);

        $file = \Illuminate\Http\UploadedFile::fake()->image('kopi.jpg');

        // CREATE with image
        $response = $this->post('/admin/produk', [
            'kategori_id' => $kategori->id,
            'nama' => 'Kopi Latte',
            'harga' => 25000,
            'hpp' => 10000,
            'stok' => 50,
            'aktif' => 1,
            'gambar' => $file
        ]);
        $response->assertRedirect(route('admin.produk.index'));

        $produk = Produk::where('nama', 'Kopi Latte')->first();
        $this->assertNotNull($produk);
        $this->assertNotNull($produk->gambar);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($produk->gambar);

        // UPDATE with new image
        $newFile = \Illuminate\Http\UploadedFile::fake()->image('kopi_baru.png');
        $oldImagePath = $produk->gambar;

        $response = $this->put('/admin/produk/' . $produk->id, [
            'kategori_id' => $kategori->id,
            'nama' => 'Kopi Latte Creamy',
            'harga' => 28000,
            'hpp' => 12000,
            'stok' => 45,
            'aktif' => 1,
            'gambar' => $newFile
        ]);
        $response->assertRedirect(route('admin.produk.index'));

        $produk->refresh();
        \Illuminate\Support\Facades\Storage::disk('public')->assertMissing($oldImagePath);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($produk->gambar);

        // DELETE cleans up image
        $imageToDelete = $produk->gambar;
        $this->delete('/admin/produk/' . $produk->id);
        \Illuminate\Support\Facades\Storage::disk('public')->assertMissing($imageToDelete);
    }

    public function test_produk_validation_and_deletion_restriction()
    {
        $admin = $this->getAdmin();
        $this->actingAs($admin);
        
        // Invalid Category ID
        $this->post('/admin/produk', [
            'kategori_id' => 999, // Does not exist
            'nama' => 'Nasi',
            'harga' => 10000,
            'hpp' => 0,
            'stok' => 0
        ])->assertSessionHasErrors('kategori_id');

        // Deletion restriction test (Cannot delete product with detail_pesanan)
        $kategori = Kategori::create(['nama' => 'Test', 'aktif' => 1]);
        $produk = Produk::create(['kategori_id' => $kategori->id, 'nama' => 'Item', 'harga' => 10, 'hpp' => 0, 'stok' => 10, 'aktif' => 1]);
        $meja = CafeTable::create(['table_number' => '1', 'qr_token' => 't1', 'status' => 'active']);
        $pesanan = Pesanan::create([
            'nomor_pesanan' => 'ORD-123',
            'meja_id' => $meja->id,
            'total_harga' => 10,
            'status' => 'pending'
        ]);
        DetailPesanan::create([
            'pesanan_id' => $pesanan->id, 
            'produk_id' => $produk->id, 
            'nama_produk' => $produk->nama,
            'jumlah' => 1, 
            'harga' => 10, 
            'subtotal' => 10
        ]);

        $response = $this->delete('/admin/produk/' . $produk->id);
        $response->assertSessionHas('error'); // Blocked by controller logic
        $this->assertDatabaseHas('produk', ['id' => $produk->id]); // Still exists
    }

    // ==========================================
    // MEJA CRUD & VALIDATION
    // ==========================================
    public function test_admin_can_crud_meja()
    {
        $admin = $this->getAdmin();
        $this->actingAs($admin);

        // CREATE
        $this->post('/admin/meja', [
            'table_number' => 'A1',
            'name' => 'Meja VIP',
            'qr_token' => 'token_123',
            'status' => 'active'
        ])->assertRedirect(route('admin.meja.index'));
        
        $meja = CafeTable::first();

        // READ
        $this->get('/admin/meja')->assertSee('A1');

        // UPDATE
        $this->put('/admin/meja/' . $meja->id, [
            'table_number' => 'A2',
            'name' => 'Meja Reguler',
            'qr_token' => 'token_123', // IDOR / Unique test, should pass since it's the same ID
            'status' => 'inactive'
        ])->assertRedirect(route('admin.meja.index'));
        $this->assertDatabaseHas('cafe_tables', ['table_number' => 'A2', 'status' => 'inactive']);

        // DELETE
        $this->delete('/admin/meja/' . $meja->id)->assertRedirect(route('admin.meja.index'));
        $this->assertDatabaseMissing('cafe_tables', ['id' => $meja->id]);
    }
}
