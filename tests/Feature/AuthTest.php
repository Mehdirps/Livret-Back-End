<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate');

        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'etablissement_type' => 'school',
        ]);
    }

    public function test_login()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password'
        ]);

        $response->assertStatus(200);
    }

    public function test_register()
    {
        $response = $this->postJson('/api/auth/register', [
            'first_name' => 'New',
            'last_name' => 'User',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'etablissement_type' => 'school'
        ]);

        $response->assertStatus(201);
    }

    public function test_verify_email()
    {
        $response = $this->get('/api/auth/verify_email/test@example.com');

        $response->assertStatus(200);
    }
}
