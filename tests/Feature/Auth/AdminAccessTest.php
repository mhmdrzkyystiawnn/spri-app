<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/admin/verifications');

        $response->assertRedirect('/login');
    }

    public function test_non_admin_user_is_forbidden(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $response = $this->actingAs($user)->get('/admin/verifications');

        $response->assertForbidden();
    }

    public function test_admin_user_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/verifications');

        $response->assertOk();
        $response->assertSee('Daftar Berkas Permohonan SPRI');
    }

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@imigrasi.go.id',
            'password' => bcrypt('SecretAdmin123!'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@imigrasi.go.id',
            'password' => 'SecretAdmin123!',
        ]);

        $response->assertRedirect('/admin/verifications');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->admin()->create([
            'email' => 'admin@imigrasi.go.id',
            'password' => bcrypt('SecretAdmin123!'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'admin@imigrasi.go.id',
            'password' => 'WrongPassword',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_can_logout(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
