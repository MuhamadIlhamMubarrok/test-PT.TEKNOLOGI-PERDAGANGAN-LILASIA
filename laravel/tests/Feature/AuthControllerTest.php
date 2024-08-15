<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_register_a_user()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/registerUser', $data);

        $response->assertStatus(200)->assertJson([
            'status' => true,
            'message' => 'Successfully create User',
            'data' => [
                'name' => 'John Doe',
                'email' => 'johndoe@example.com',
            ],
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'johndoe@example.com',
        ]);
    }

    /** @test */
    public function it_can_login_a_user()
    {
        // Siapkan data pengguna
        $user = User::factory()->create([
            'password' => Hash::make('password123'), // Password yang di-hash
        ]);

        // Siapkan data login
        $data = [
            'email' => $user->email,
            'password' => 'password123',
        ];

        // Kirim permintaan login
        $response = $this->postJson('/api/loginUser', $data);

        // Asseri bahwa status respons adalah 200 (OK) dan token ada
        $response
            ->assertStatus(200)
            ->assertJson([
                'status' => true,
                'message' => 'Successfully login User',
            ])
            ->assertJsonStructure(['status', 'message', 'token']);
    }

    /** @test */
    public function it_cannot_login_with_invalid_data()
    {
        // Data login yang tidak valid
        $data = [
            'email' => '',
            'password' => '',
        ];

        // Kirim permintaan login
        $response = $this->postJson('/api/loginUser', $data);

        // Asseri bahwa status respons adalah 401 (Unauthorized) dengan error validasi
        $response
            ->assertStatus(401)
            ->assertJson([
                'status' => false,
                'message' => 'Fail Process Login',
            ])
            ->assertJsonStructure(['status', 'message', 'data']);
    }

    /** @test */
    public function it_cannot_login_with_wrong_credentials()
    {
        // Siapkan data pengguna
        $user = User::factory()->create([
            'password' => Hash::make('password123'), // Password yang di-hash
        ]);

        // Data login yang salah
        $data = [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ];

        // Kirim permintaan login
        $response = $this->postJson('/api/loginUser', $data);

        // Asseri bahwa status respons adalah 401 (Unauthorized) dengan pesan kredensial salah
        $response->assertStatus(401)->assertJson([
            'status' => false,
            'message' => 'email and password not match',
        ]);
    }

    /** @test */
    public function it_can_logout_a_user()
    {
        // Siapkan pengguna dan token
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Kirim permintaan logout
        $response = $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer ' . $user->currentAccessToken()->plainTextToken,
        ]);

        // Asseri bahwa status respons adalah 200 (OK)
        $response->assertStatus(200)
                 ->assertJson([
                     'status' => true,
                     'message' => 'Successfully logged out',
                 ]);

        // Asseri bahwa token telah dihapus
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => 'App\Models\User',
        ]);
    }

    
}
