<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\CafeTable;
use Illuminate\Support\Facades\Hash;

class Tahap2Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'AdminSeeder']);
        $this->artisan('db:seed', ['--class' => 'CafeTableSeeder']);
    }

    public function test_admin_login_success()
    {
        $response = $this->postJson('/api/admin/login', [
            'email' => 'admin@cafe.test',
            'password' => 'password123'
        ]);
        $response->assertStatus(200)->assertJsonStructure(['data' => ['token']]);
    }

    public function test_admin_login_invalid()
    {
        $response = $this->postJson('/api/admin/login', [
            'email' => 'admin@cafe.test',
            'password' => 'wrongpassword'
        ]);
        $response->assertStatus(401);
    }

    public function test_admin_me_with_token()
    {
        $user = User::where('email', 'admin@cafe.test')->first();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                         ->getJson('/api/admin/me');
        $response->assertStatus(200);
    }

    public function test_admin_unauthorized_without_token()
    {
        $response = $this->getJson('/api/admin/me');
        $response->assertStatus(401);
    }

    public function test_admin_logout()
    {
        $user = User::where('email', 'admin@cafe.test')->first();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                         ->postJson('/api/admin/logout');
        $response->assertStatus(200);
    }

    public function test_token_invalid_after_logout()
    {
        $user = User::where('email', 'admin@cafe.test')->first();
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeaders(['Authorization' => "Bearer $token"])->postJson('/api/admin/logout');

        \Illuminate\Support\Facades\Auth::forgetGuards();

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                         ->getJson('/api/admin/me');
        $response->assertStatus(401);
    }

    public function test_valid_qr_token()
    {
        $table = CafeTable::first();
        $response = $this->getJson('/api/meja/' . $table->qr_token);
        $response->assertStatus(200)
                 ->assertJsonPath('data.nomor_meja', $table->table_number);
    }

    public function test_invalid_qr_token()
    {
        $response = $this->getJson('/api/meja/invalid123');
        $response->assertStatus(404);
    }

    public function test_admin_table_list()
    {
        $user = User::where('email', 'admin@cafe.test')->first();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
                         ->getJson('/api/admin/tables');
        $response->assertStatus(200);
    }
}
