<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Satuan;
use App\Models\BahanBaku;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SatuanUnitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'AdminSeeder']);
        $this->artisan('db:seed', ['--class' => 'SatuanSeeder']);
    }
    public function test_admin_dapat_mengakses_halaman_satuan()
    {
        $admin = User::first() ?? User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.satuan.index'));
        $response->assertStatus(200);
        $response->assertSee('Master Satuan Unit');
    }

    public function test_admin_dapat_menambah_satuan()
    {
        $admin = User::first() ?? User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.satuan.store'), [
            'nama' => 'Galon',
            'keterangan' => 'Satuan air galon',
            'aktif' => 1,
        ]);

        $response->assertRedirect(route('admin.satuan.index'));
        $this->assertDatabaseHas('satuans', [
            'nama' => 'Galon',
        ]);
    }

    public function test_admin_dapat_mengubah_satuan()
    {
        $admin = User::first() ?? User::factory()->create(['role' => 'admin']);
        $satuan = Satuan::firstOrCreate(['nama' => 'TestEdit'], ['keterangan' => 'Ket awal', 'aktif' => true]);

        $response = $this->actingAs($admin)->put(route('admin.satuan.update', $satuan->id), [
            'nama' => 'TestEditUpdated',
            'keterangan' => 'Ket diperbarui',
            'aktif' => 0,
        ]);

        $response->assertRedirect(route('admin.satuan.index'));
        $this->assertDatabaseHas('satuans', [
            'id' => $satuan->id,
            'nama' => 'TestEditUpdated',
            'aktif' => 0,
        ]);
    }

    public function test_satuan_muncul_di_halaman_bahan_baku()
    {
        $admin = User::first() ?? User::factory()->create(['role' => 'admin']);
        Satuan::firstOrCreate(['nama' => 'KilogramTest'], ['aktif' => true]);

        $response = $this->actingAs($admin)->get(route('admin.bahan-baku.index'));
        $response->assertStatus(200);
        $response->assertSee('KilogramTest');
    }
}
