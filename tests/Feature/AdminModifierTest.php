<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\ModifierGroup;
use App\Models\ModifierOption;
use App\Models\Kategori;
use App\Models\Produk;

class AdminModifierTest extends TestCase
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
    public function test_guest_cannot_access_modifier_management()
    {
        $this->get('/admin/modifier-groups')->assertRedirect('/admin/login');
    }

    public function test_non_admin_cannot_access_modifier_management_and_idor_protection()
    {
        $kasir = $this->getKasir();
        $group = ModifierGroup::create(['nama' => 'Test Group', 'tipe' => 'single']);
        $option = ModifierOption::create(['modifier_group_id' => $group->id, 'nama' => 'Test Option']);

        $this->actingAs($kasir)->get('/admin/modifier-groups')->assertStatus(403);
        $this->actingAs($kasir)->get('/admin/modifier-groups/' . $group->id . '/edit')->assertStatus(403);
        $this->actingAs($kasir)->put('/admin/modifier-groups/' . $group->id, ['nama' => 'Hacked'])->assertStatus(403);
        $this->actingAs($kasir)->delete('/admin/modifier-groups/' . $group->id)->assertStatus(403);
        
        $this->actingAs($kasir)->put('/admin/modifier-options/' . $option->id, ['nama' => 'Hacked Option'])->assertStatus(403);
    }

    // ==========================================
    // MODIFIER GROUP CRUD
    // ==========================================
    public function test_admin_can_crud_modifier_group()
    {
        $admin = $this->getAdmin();
        $this->actingAs($admin);

        // CREATE
        $this->post('/admin/modifier-groups', [
            'nama' => 'Sugar Level',
            'tipe' => 'single',
            'wajib_diisi' => 1,
            'min_pilihan' => 1,
            'max_pilihan' => 1,
            'aktif' => 1
        ])->assertRedirect(route('admin.modifier-groups.index'));
        
        $group = ModifierGroup::first();
        $this->assertEquals('Sugar Level', $group->nama);

        // READ
        $this->get('/admin/modifier-groups')->assertSee('Sugar Level');
        $this->get('/admin/modifier-groups/' . $group->id . '/edit')->assertSee('Sugar Level');

        // UPDATE
        $this->put('/admin/modifier-groups/' . $group->id, [
            'nama' => 'Sweetness',
            'tipe' => 'multiple', // changed
            'wajib_diisi' => 0,
            'min_pilihan' => 0,
            'max_pilihan' => 2,
            'aktif' => 1
        ])->assertRedirect(route('admin.modifier-groups.index'));
        
        $this->assertDatabaseHas('modifier_groups', ['nama' => 'Sweetness', 'tipe' => 'multiple']);

        // DELETE
        $this->delete('/admin/modifier-groups/' . $group->id)->assertRedirect(route('admin.modifier-groups.index'));
        $this->assertDatabaseMissing('modifier_groups', ['id' => $group->id]);
    }

    public function test_modifier_group_validation_and_integrity()
    {
        $admin = $this->getAdmin();
        $this->actingAs($admin);

        // Validation required fields
        $this->post('/admin/modifier-groups', [])->assertSessionHasErrors(['nama', 'tipe']);

        // Duplicate name validation
        ModifierGroup::create(['nama' => 'Sugar', 'tipe' => 'single']);
        $this->post('/admin/modifier-groups', ['nama' => 'Sugar', 'tipe' => 'multiple'])->assertSessionHasErrors('nama');

        // Integrity test: cannot delete if attached to product
        $group = ModifierGroup::create(['nama' => 'Topping', 'tipe' => 'multiple']);
        $kategori = Kategori::create(['nama' => 'Food', 'aktif' => 1]);
        $produk = Produk::create(['kategori_id' => $kategori->id, 'nama' => 'Boba', 'harga' => 10000, 'stok' => 10, 'aktif' => 1]);
        
        $group->produks()->attach($produk->id);
        
        $this->delete('/admin/modifier-groups/' . $group->id)->assertSessionHas('error');
        $this->assertDatabaseHas('modifier_groups', ['id' => $group->id]);
    }

    // ==========================================
    // MODIFIER OPTION CRUD
    // ==========================================
    public function test_admin_can_crud_modifier_option()
    {
        $admin = $this->getAdmin();
        $this->actingAs($admin);
        $group = ModifierGroup::create(['nama' => 'Sugar Level', 'tipe' => 'single']);

        // CREATE
        $this->post('/admin/modifier-options', [
            'modifier_group_id' => $group->id,
            'nama' => 'Normal Sugar',
            'harga_tambahan' => 0,
            'aktif' => 1
        ])->assertRedirect(route('admin.modifier-groups.edit', $group->id));
        
        $option = ModifierOption::first();
        $this->assertEquals('Normal Sugar', $option->nama);
        $this->assertEquals(0, $option->harga_tambahan);

        // UPDATE
        $this->put('/admin/modifier-options/' . $option->id, [
            'nama' => 'Less Sugar',
            'harga_tambahan' => 1000,
            'aktif' => 1
        ])->assertRedirect(route('admin.modifier-groups.edit', $group->id));
        
        $this->assertDatabaseHas('modifier_options', ['nama' => 'Less Sugar', 'harga_tambahan' => 1000]);

        // DELETE
        $this->delete('/admin/modifier-options/' . $option->id)->assertRedirect(route('admin.modifier-groups.edit', $group->id));
        $this->assertDatabaseMissing('modifier_options', ['id' => $option->id]);
    }

    public function test_modifier_option_validation_and_negative_price()
    {
        $admin = $this->getAdmin();
        $this->actingAs($admin);
        $group = ModifierGroup::create(['nama' => 'Sugar Level', 'tipe' => 'single']);

        // Negative price rejected
        $this->post('/admin/modifier-options', [
            'modifier_group_id' => $group->id,
            'nama' => 'Normal Sugar',
            'harga_tambahan' => -5000,
            'aktif' => 1
        ])->assertSessionHasErrors('harga_tambahan');
        
        // Invalid group rejected
        $this->post('/admin/modifier-options', [
            'modifier_group_id' => 999,
            'nama' => 'Normal Sugar',
            'harga_tambahan' => 0,
            'aktif' => 1
        ])->assertSessionHasErrors('modifier_group_id');
    }
}
