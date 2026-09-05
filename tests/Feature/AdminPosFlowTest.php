<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\CafeTable;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\Pesanan;

class AdminPosFlowTest extends TestCase
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
    
    // 1. POS Page Access
    public function test_kasir_can_access_pos_page()
    {
        $kasir = $this->getKasir();
        
        $response = $this->actingAs($kasir)->get(route('admin.pos.index'));
        
        $response->assertStatus(200);
        $response->assertSee('Kasir');
        $response->assertSee('posData');
    }
    
    // 2. Guest Blocked
    public function test_guest_blocked_from_pos()
    {
        $response = $this->get(route('admin.pos.index'));
        $response->assertRedirect('/admin/login');
    }
    
    // 3. Checkout Valid Order
    public function test_checkout_valid_order_creates_pesanan()
    {
        $kasir = $this->getKasir();
        $meja = CafeTable::create(['table_number' => '1', 'name' => 'Table 1', 'status' => 'active', 'qr_token' => 'token1']);
        $kategori = Kategori::create(['nama' => 'Minuman', 'aktif' => 1]);
        $produk = Produk::create(['kategori_id' => $kategori->id, 'nama' => 'Kopi', 'harga' => 15000, 'stok' => 10, 'aktif' => 1]);
        
        $payload = [
            'meja_id' => $meja->id,
            'items' => [
                [
                    'produk_id' => $produk->id,
                    'jumlah' => 2,
                    'modifiers' => []
                ]
            ],
            'catatan' => 'Test notes',
            'payment_method' => 'cash',
            'cash_received' => 30000
        ];
        
        $response = $this->actingAs($kasir)->postJson(route('admin.pos.checkout'), $payload);
        
        $response->assertStatus(200)
                 ->assertJson(['success' => true]);
                 
        $pesanan = Pesanan::where('meja_id', $meja->id)->first();
        $this->assertNotNull($pesanan);
        $this->assertEquals(30000, $pesanan->total_harga);
        $this->assertEquals('dibayar', $pesanan->status);
    }
    
    // 4. Checkout Invalid Payload
    public function test_checkout_invalid_payload_rejected()
    {
        $kasir = $this->getKasir();
        
        // No items
        $payload = [
            'meja_id' => 999, // Invalid table
            'items' => []
        ];
        
        $response = $this->actingAs($kasir)->postJson(route('admin.pos.checkout'), $payload);
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['meja_id', 'items']);
    }
    
    // 5. Checkout with Modifiers
    public function test_checkout_with_modifiers()
    {
        $kasir = $this->getKasir();
        $meja = CafeTable::create(['table_number' => '2', 'name' => 'Table 2', 'status' => 'active', 'qr_token' => 'token2']);
        $kategori = Kategori::create(['nama' => 'Minuman', 'aktif' => 1]);
        $produk = Produk::create(['kategori_id' => $kategori->id, 'nama' => 'Es Kopi', 'harga' => 15000, 'stok' => 10, 'aktif' => 1]);
        
        $group = ModifierGroup::create([
            'nama' => 'Ukuran', 
            'tipe' => 'single', 
            'wajib_diisi' => 1,
            'min_pilihan' => 1,
            'max_pilihan' => 1,
            'aktif' => 1
        ]);
        $produk->modifierGroups()->sync([$group->id]);
        
        $option = ModifierOption::create([
            'modifier_group_id' => $group->id,
            'nama' => 'Large',
            'harga_tambahan' => 5000,
            'aktif' => 1
        ]);
        
        $payload = [
            'meja_id' => $meja->id,
            'items' => [
                [
                    'produk_id' => $produk->id,
                    'jumlah' => 1,
                    'modifiers' => [$option->id]
                ]
            ],
            'payment_method' => 'cash',
            'cash_received' => 20000
        ];
        
        $response = $this->actingAs($kasir)->postJson(route('admin.pos.checkout'), $payload);
        
        $response->assertStatus(200);
        $pesanan = Pesanan::latest()->first();
        $this->assertEquals(20000, $pesanan->total_harga); // 15k + 5k
    }
}
