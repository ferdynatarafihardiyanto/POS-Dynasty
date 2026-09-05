<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\ModifierGroup;

class AdminProductModifierAssignmentTest extends TestCase
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
    
    protected function getKategori()
    {
        return Kategori::create(['nama' => 'Test Kategori', 'aktif' => 1]);
    }
    
    // A. Create Product tanpa Modifier Group
    public function test_create_product_without_modifier_group()
    {
        $admin = $this->getAdmin();
        $kategori = $this->getKategori();
        
        $response = $this->actingAs($admin)->post('/admin/produk', [
            'kategori_id' => $kategori->id,
            'nama' => 'Produk A',
            'harga' => 10000,
            'hpp' => 0,
            'stok' => 10,
            'aktif' => 1
        ]);
        
        $response->assertRedirect(route('admin.produk.index'));
        $produk = Produk::where('nama', 'Produk A')->first();
        $this->assertEquals(0, $produk->modifierGroups()->count());
    }

    // B. Create Product dengan 1 Modifier Group
    public function test_create_product_with_one_modifier_group()
    {
        $admin = $this->getAdmin();
        $kategori = $this->getKategori();
        $group = ModifierGroup::create(['nama' => 'Topping', 'tipe' => 'multiple', 'aktif' => 1]);
        
        $response = $this->actingAs($admin)->post('/admin/produk', [
            'kategori_id' => $kategori->id,
            'nama' => 'Produk B',
            'harga' => 10000,
            'hpp' => 0,
            'stok' => 10,
            'aktif' => 1,
            'modifier_groups' => [$group->id]
        ]);
        
        $produk = Produk::where('nama', 'Produk B')->first();
        $this->assertEquals(1, $produk->modifierGroups()->count());
        $this->assertEquals($group->id, $produk->modifierGroups->first()->id);
    }
    
    // C. Create Product dengan beberapa Modifier Group
    public function test_create_product_with_multiple_modifier_groups()
    {
        $admin = $this->getAdmin();
        $kategori = $this->getKategori();
        $group1 = ModifierGroup::create(['nama' => 'Topping', 'tipe' => 'multiple', 'aktif' => 1]);
        $group2 = ModifierGroup::create(['nama' => 'Ice Level', 'tipe' => 'single', 'aktif' => 1]);
        
        $response = $this->actingAs($admin)->post('/admin/produk', [
            'kategori_id' => $kategori->id,
            'nama' => 'Produk C',
            'harga' => 10000,
            'hpp' => 0,
            'stok' => 10,
            'aktif' => 1,
            'modifier_groups' => [$group1->id, $group2->id]
        ]);
        
        $produk = Produk::where('nama', 'Produk C')->first();
        $this->assertEquals(2, $produk->modifierGroups()->count());
    }
    
    // D. Update Product menambahkan Modifier Group
    public function test_update_product_adding_modifier_group()
    {
        $admin = $this->getAdmin();
        $kategori = $this->getKategori();
        $produk = Produk::create(['kategori_id' => $kategori->id, 'nama' => 'Produk D', 'harga' => 10, 'hpp' => 0, 'stok' => 1, 'aktif' => 1]);
        $group1 = ModifierGroup::create(['nama' => 'Topping', 'tipe' => 'multiple', 'aktif' => 1]);
        $group2 = ModifierGroup::create(['nama' => 'Ice Level', 'tipe' => 'single', 'aktif' => 1]);
        
        $produk->modifierGroups()->sync([$group1->id]);
        
        $this->actingAs($admin)->put('/admin/produk/' . $produk->id, [
            'kategori_id' => $kategori->id,
            'nama' => 'Produk D',
            'harga' => 10,
            'hpp' => 0,
            'stok' => 1, // field stok not validated in update, but passing is fine
            'aktif' => 1,
            'modifier_groups' => [$group1->id, $group2->id]
        ]);
        
        $this->assertEquals(2, $produk->fresh()->modifierGroups()->count());
    }
    
    // E. Update Product menghapus assignment
    public function test_update_product_removing_assignment()
    {
        $admin = $this->getAdmin();
        $kategori = $this->getKategori();
        $produk = Produk::create(['kategori_id' => $kategori->id, 'nama' => 'Produk E', 'harga' => 10, 'hpp' => 0, 'stok' => 1, 'aktif' => 1]);
        $group1 = ModifierGroup::create(['nama' => 'Topping', 'tipe' => 'multiple', 'aktif' => 1]);
        $group2 = ModifierGroup::create(['nama' => 'Ice Level', 'tipe' => 'single', 'aktif' => 1]);
        
        $produk->modifierGroups()->sync([$group1->id, $group2->id]);
        
        $this->actingAs($admin)->put('/admin/produk/' . $produk->id, [
            'kategori_id' => $kategori->id,
            'nama' => 'Produk E',
            'harga' => 10,
            'hpp' => 0,
            'stok' => 1,
            'aktif' => 1,
            'modifier_groups' => [$group1->id]
        ]);
        
        $this->assertEquals(1, $produk->fresh()->modifierGroups()->count());
        $this->assertEquals($group1->id, $produk->fresh()->modifierGroups->first()->id);
    }
    
    // F. Update Product mengganti assignment
    public function test_update_product_replacing_assignment()
    {
        $admin = $this->getAdmin();
        $kategori = $this->getKategori();
        $produk = Produk::create(['kategori_id' => $kategori->id, 'nama' => 'Produk F', 'harga' => 10, 'hpp' => 0, 'stok' => 1, 'aktif' => 1]);
        $group1 = ModifierGroup::create(['nama' => 'Topping', 'tipe' => 'multiple', 'aktif' => 1]);
        $group2 = ModifierGroup::create(['nama' => 'Ice Level', 'tipe' => 'single', 'aktif' => 1]);
        
        $produk->modifierGroups()->sync([$group1->id]);
        
        $this->actingAs($admin)->put('/admin/produk/' . $produk->id, [
            'kategori_id' => $kategori->id,
            'nama' => 'Produk F',
            'harga' => 10,
            'hpp' => 0,
            'stok' => 1,
            'aktif' => 1,
            'modifier_groups' => [$group2->id]
        ]);
        
        $this->assertEquals(1, $produk->fresh()->modifierGroups()->count());
        $this->assertEquals($group2->id, $produk->fresh()->modifierGroups->first()->id);
    }
    
    // Preserve inactive assignment
    public function test_update_product_preserves_inactive_assignment()
    {
        $admin = $this->getAdmin();
        $kategori = $this->getKategori();
        $produk = Produk::create(['kategori_id' => $kategori->id, 'nama' => 'Produk G', 'harga' => 10, 'hpp' => 0, 'stok' => 1, 'aktif' => 1]);
        $activeGroup = ModifierGroup::create(['nama' => 'Active', 'tipe' => 'multiple', 'aktif' => 1]);
        $inactiveGroup = ModifierGroup::create(['nama' => 'Inactive', 'tipe' => 'multiple', 'aktif' => 0]);
        
        // Product currently has both
        $produk->modifierGroups()->sync([$activeGroup->id, $inactiveGroup->id]);
        
        // Admin updates product, but UI only submits the active group
        $this->actingAs($admin)->put('/admin/produk/' . $produk->id, [
            'kategori_id' => $kategori->id,
            'nama' => 'Produk G',
            'harga' => 10,
            'hpp' => 0,
            'stok' => 1,
            'aktif' => 1,
            'modifier_groups' => [$activeGroup->id]
        ]);
        
        $this->assertEquals(2, $produk->fresh()->modifierGroups()->count()); // Should still have both!
    }
    
    // G. Invalid Modifier Group ID
    public function test_invalid_modifier_group_id_rejected()
    {
        $admin = $this->getAdmin();
        $kategori = $this->getKategori();
        
        $response = $this->actingAs($admin)->post('/admin/produk', [
            'kategori_id' => $kategori->id,
            'nama' => 'Produk Invalid',
            'harga' => 10000,
            'hpp' => 0,
            'stok' => 10,
            'aktif' => 1,
            'modifier_groups' => [999999]
        ]);
        
        $response->assertSessionHasErrors('modifier_groups.0');
    }
    
    // H. Duplicate Modifier Group ID
    public function test_duplicate_modifier_group_id_rejected()
    {
        $admin = $this->getAdmin();
        $kategori = $this->getKategori();
        $group = ModifierGroup::create(['nama' => 'Topping', 'tipe' => 'multiple', 'aktif' => 1]);
        
        $response = $this->actingAs($admin)->post('/admin/produk', [
            'kategori_id' => $kategori->id,
            'nama' => 'Produk Duplicate',
            'harga' => 10000,
            'hpp' => 0,
            'stok' => 10,
            'aktif' => 1,
            'modifier_groups' => [$group->id, $group->id]
        ]);
        
        $response->assertSessionHasErrors('modifier_groups.0');
    }
    
    // I. Invalid Payload
    public function test_invalid_payload_type_rejected()
    {
        $admin = $this->getAdmin();
        $kategori = $this->getKategori();
        
        $response = $this->actingAs($admin)->post('/admin/produk', [
            'kategori_id' => $kategori->id,
            'nama' => 'Produk Invalid Payload',
            'harga' => 10000,
            'hpp' => 0,
            'stok' => 10,
            'aktif' => 1,
            'modifier_groups' => "not_an_array"
        ]);
        
        $response->assertSessionHasErrors('modifier_groups');
    }

    // J. Guest & K. Non-admin
    public function test_unauthorized_access_rejected()
    {
        $kasir = $this->getKasir();
        $kategori = $this->getKategori();
        
        // Guest
        $this->post('/admin/produk', [
            'kategori_id' => $kategori->id,
            'nama' => 'Test',
            'harga' => 10,
            'hpp' => 0,
            'stok' => 10,
        ])->assertRedirect('/admin/login');
        
        // Kasir
        $this->actingAs($kasir)->post('/admin/produk', [
            'kategori_id' => $kategori->id,
            'nama' => 'Test',
            'harga' => 10,
            'hpp' => 0,
            'stok' => 10,
        ])->assertStatus(403);
    }
    
    // K. UI Rendering - Create
    public function test_create_view_shows_active_modifier_groups()
    {
        $admin = $this->getAdmin();
        $activeGroup = ModifierGroup::create(['nama' => 'Varian Kopi', 'tipe' => 'single', 'aktif' => 1]);
        $inactiveGroup = ModifierGroup::create(['nama' => 'Topping Lama', 'tipe' => 'multiple', 'aktif' => 0]);
        
        $response = $this->actingAs($admin)->get('/admin/produk/create');
        
        $response->assertStatus(200);
        $response->assertSee('Varian Kopi'); // Active shows up
        $response->assertDontSee('Topping Lama'); // Inactive should NOT show up at all
    }
    
    // L. UI Rendering - Edit
    public function test_edit_view_shows_assigned_inactive_modifier_groups()
    {
        $admin = $this->getAdmin();
        $kategori = $this->getKategori();
        $produk = Produk::create(['kategori_id' => $kategori->id, 'nama' => 'Produk Z', 'harga' => 10, 'hpp' => 0, 'stok' => 1, 'aktif' => 1]);
        
        $activeGroup = ModifierGroup::create(['nama' => 'Varian Kopi', 'tipe' => 'single', 'aktif' => 1]);
        $inactiveAssignedGroup = ModifierGroup::create(['nama' => 'Topping Lama', 'tipe' => 'multiple', 'aktif' => 0]);
        $inactiveUnassignedGroup = ModifierGroup::create(['nama' => 'Topping Rahasia', 'tipe' => 'multiple', 'aktif' => 0]);
        
        $produk->modifierGroups()->sync([$activeGroup->id, $inactiveAssignedGroup->id]);
        
        $response = $this->actingAs($admin)->get('/admin/produk/' . $produk->id . '/edit');
        
        $response->assertStatus(200);
        $response->assertSee('Varian Kopi');
        
        // The inactive group that IS assigned should be visible (so user knows it's there)
        $response->assertSee('Topping Lama'); 
        $response->assertSee('Inactive'); // The badge/label should be shown
        
        // The inactive group that IS NOT assigned should NOT be visible
        $response->assertDontSee('Topping Rahasia');
    }
}
