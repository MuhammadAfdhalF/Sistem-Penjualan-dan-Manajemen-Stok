<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_form_can_be_displayed()
    {
        $response = $this->get(route('login')); 
        $response->assertStatus(200);
        $response->assertSee('Login');
    }

    public function test_user_can_login_with_correct_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'), // password terenkripsi
        ]);

        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password123', // plaintext dikirim form
        ]);

        $response->assertRedirect('/produk');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->from(route('login'))->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'salahpassword',
        ]);

        $response->assertRedirect(route('login')); // balik ke login
        $response->assertSessionHasErrors('email'); // Laravel default login akan beri error pada `email`
        $this->assertGuest();
    }
}
