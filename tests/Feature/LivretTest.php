<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
use App\Models\User;
use App\Models\Livret;

class LivretTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate');

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'etablissement_type' => 'school',
        ]);

        Livret::create([
            'livret_name' => 'New Livret',
            'slug' => \Str::slug('New Livret'),
            'establishment_type' => 'school',
            'establishment_name' => 'New Establishment',
            'establishment_address' => '123 New Street',
            'establishment_phone' => '123-456-7890',
            'establishment_email' => 'mail@mail.com',
            'establishment_website' => 'http://www.example.com',
            'user_id' => $user->id
        ]);
    }

    public function test_show_livret()
    {
        $response = $this->get('/api/livret/new-livret/1');

        $response->assertStatus(200);
    }

    public function test_store_livret()
    {
        $user = User::first();
        $token = auth()->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/dashboard/first_login', [
            'livret_name' => 'New Livret 2',
            'slug' => \Str::slug('New Livret 2'),
            'establishment_type' => 'school',
            'establishment_name' => 'New Establishment',
            'establishment_address' => '123 New Street',
            'establishment_phone' => '0609606060',
            'establishment_email' => 'mail@mail.com',
            'establishment_website' => 'http://www.example.com',
            'user_id' => 1
        ]);

        $response->assertStatus(201)
                 ->assertJson(['success' => true, 'message' => 'Livret créé avec succès.']);
    }

    public function test_update_livret()
    {
        $user = User::first();
        $token = auth()->login($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/dashboard/profile/update_livret', [
            'livret_name' => 'Updated Livret',
            'description' => 'Updated Description',
            'slug' => \Str::slug('Updated Livret'),
            'establishment_type' => 'school',
            'establishment_name' => 'Updated Establishment',
            'establishment_address' => '123 Updated Street',
            'establishment_phone' => '0606060606',
            'establishment_email' => 'mail@mail.com',
            'establishment_website' => 'http://www.example.com',
            'facebook' => 'http://www.facebook.com',
            'twitter' => 'http://www.twitter.com',
            'instagram' => 'http://www.instagram.com',
            'linkedin' => 'http://www.linkedin.com',
            'tripadvisor' => 'http://www.tripadvisor.com',
        ]);

        $response->assertStatus(200)
             ->assertJson([
             'message' => 'Votre livret a été mis à jour avec succès',
             'livret' => [
                'livret_name' => 'Updated Livret',
                'slug' => \Str::slug('Updated Livret'),
                'establishment_type' => 'school',
                'establishment_name' => 'Updated Establishment',
                'establishment_address' => '123 Updated Street',
                'establishment_phone' => '0606060606',
                'establishment_email' => 'mail@mail.com',
                'establishment_website' => 'http://www.example.com',
                'facebook' => 'http://www.facebook.com',
                'twitter' => 'http://www.twitter.com',
                'instagram' => 'http://www.instagram.com',
                'linkedin' => 'http://www.linkedin.com',
                'tripadvisor' => 'http://www.tripadvisor.com',
                ]]);
        
    }
}
