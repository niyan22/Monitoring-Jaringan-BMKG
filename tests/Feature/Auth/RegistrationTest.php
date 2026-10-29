<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Registrasi publik sengaja dinonaktifkan (tidak ada rute /register),
 * jadi akun hanya bisa dibuat oleh admin. Tes ini menjaga agar tetap begitu.
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_is_not_available(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_users_cannot_self_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertGuest();
        $this->assertSame(0, User::count());
        $this->assertContains($response->getStatusCode(), [404, 405]);
    }
}
